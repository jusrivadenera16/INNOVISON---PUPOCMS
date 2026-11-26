const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const test = require('node:test');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const lineAt = (source, index) => source.slice(0, index).split('\n').length;
const blank = value => value.replace(/[^\r\n]/g, ' ');
const placeholder = value => '0' + blank(value).slice(1);

function files(directory, extension) {
    return fs.readdirSync(path.join(root, directory), { withFileTypes: true })
        .flatMap(entry => entry.isDirectory()
            ? files(directory + '/' + entry.name, extension)
            : entry.name.endsWith(extension) ? [directory + '/' + entry.name] : [])
        .sort();
}

function maskBlade(source) {
    return source
        .replace(/\{\{--[\s\S]*?--\}\}|<!--[\s\S]*?-->/g, blank)
        .replace(/@php\b(?!\s*\()[\s\S]*?@endphp\b|<\?php\b[\s\S]*?\?>/g, blank)
        .replace(/\{!![\s\S]*?!!\}|\{\{[\s\S]*?\}\}/g, placeholder);
}

// Consume complete quoted values so data-onclick and text inside title are not attributes.
function tags(source) {
    const masked = maskBlade(source).replace(/\\(["'])/g, '$1');
    const lineStarts = [0];
    for (const match of masked.matchAll(/\n/g)) lineStarts.push(match.index + 1);
    const lineNumber = index => {
        let low = 0;
        let high = lineStarts.length;
        while (low + 1 < high) {
            const middle = (low + high) >>> 1;
            if (lineStarts[middle] <= index) low = middle;
            else high = middle;
        }
        return low + 1;
    };
    return [...masked.matchAll(/<([a-z][\w:-]*)\b((?:[^"'<>]|"[^"]*"|'[^']*')*)>/gi)]
        .map(match => {
            const attributes = new Map();
            const pattern = /(?:^|[\t\n\f\r /])([^\s"'<>/=]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g;
            for (const attribute of match[2].matchAll(pattern)) {
                attributes.set(attribute[1].toLowerCase(), attribute[2] ?? attribute[3] ?? attribute[4] ?? '');
            }
            return { name: match[1].toLowerCase(), attributes, line: lineNumber(match.index) };
        });
}

function markupFindings(source) {
    const findings = [];
    for (const tag of tags(source)) {
        for (const [name, value] of tag.attributes) {
            if (name === 'style' || /^on[a-z]+$/i.test(name)) findings.push(`${tag.line}: inline ${name} attribute`);
            if (['href', 'src', 'action', 'formaction', 'xlink:href'].includes(name)
                && /^\s*(?:javascript|vbscript)\s*:/i.test(value)) findings.push(`${tag.line}: executable ${name} URL`);
        }
        const type = (tag.attributes.get('type') || '').trim().toLowerCase();
        const executableScript = tag.name === 'script' && !tag.attributes.has('src')
            && (!type || /^(?:module|(?:text|application)\/(?:java|ecma)script)$/.test(type));
        if ((tag.name === 'style' || executableScript) && !tag.attributes.get('nonce')?.trim()) {
            findings.push(`${tag.line}: ${tag.name} block missing nonce`);
        }
    }
    return findings;
}

function sinkFindings(source) {
    const findings = [];
    const rules = [
        ['string code compilation', /\b(?:eval|Function)\s*\(/g],
        ['string timer callback', /\b(?:setTimeout|setInterval)\s*\(\s*["'`]/g],
        ['style/handler attribute sink', /\bsetAttribute\s*\(\s*(["'])(?:style|on[a-z]+)\1/gi],
        ['namespaced style/handler sink', /\bsetAttributeNS\s*\([^,]*,\s*(["'])(?:style|on[a-z]+)\1/gi],
        ['CSS text sink (review CSP behavior)', /\.\s*(?:cssText|style)\s*=(?!=)/g],
    ];
    for (const [label, pattern] of rules) {
        for (const match of source.matchAll(pattern)) findings.push(`${lineAt(source, match.index)}: ${label}`);
    }
    // This is deliberately a local source heuristic, not nonce data-flow verification.
    for (const match of source.matchAll(/document\.createElement\s*\(\s*(["'])(style|script)\1\s*\)/g)) {
        const prefix = source.slice(Math.max(0, match.index - 100), match.index);
        const binding = prefix.match(/\b(?:const|let|var)\s+(\w+)\s*=\s*[^;{}]*$/)?.[1];
        const after = source.slice(match.index + match[0].length, match.index + match[0].length + 800);
        const escaped = binding?.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const nonce = binding && new RegExp(`\\b${escaped}\\.(?:nonce\\s*=|setAttribute\\(\\s*["']nonce["']\\s*,)`).exec(after);
        const insertion = binding && new RegExp(`\\b(?:appendChild|append|prepend)\\(\\s*${escaped}\\b`).exec(after);
        if (!nonce || (insertion && insertion.index < nonce.index)) {
            findings.push(`${lineAt(source, match.index)}: dynamic ${match[2]} needs nonce-before-insertion review`);
        }
    }
    return findings;
}

function balancedEnd(source, start) {
    let depth = 0;
    let quote = '';
    for (let index = start; index < source.length; index++) {
        const char = source[index];
        if (quote) {
            if (char === '\\') index++;
            else if (char === quote) quote = '';
        } else if (char === '"' || char === "'") quote = char;
        else if (char === '(') depth++;
        else if (char === ')' && --depth === 0) return index + 1;
    }
    throw new Error('Unbalanced Blade directive at line ' + lineAt(source, start));
}

function sanitizeScript(source) {
    source = maskBlade(source);
    const directive = /@(json|js|elseif|if|unless|isset|empty|foreach|for|while)\s*\(/g;
    let output = '';
    let cursor = 0;
    let match;
    while ((match = directive.exec(source))) {
        const end = balancedEnd(source, directive.lastIndex - 1);
        const replacement = ['json', 'js'].includes(match[1]) ? 'null'
            : match[1] === 'elseif' ? '} else if (true) {'
                : ['foreach', 'for', 'while'].includes(match[1]) ? 'for (const __cspItem of []) {'
                    : 'if (true) {';
        output += source.slice(cursor, match.index) + replacement + blank(source.slice(match.index, end));
        cursor = end;
        directive.lastIndex = end;
    }
    output += source.slice(cursor);
    return output.replace(/@else\b/g, '} else {')
        .replace(/@end(?:if|unless|isset|empty|foreach|for|while)\b/g, '}');
}

function scriptBlocks(source) {
    const masked = maskBlade(source);
    const blocks = [];
    const opening = /<script\b((?:[^"'<>]|"[^"]*"|'[^']*')*)>/gi;
    let match;
    while ((match = opening.exec(masked))) {
        const endTag = /<\/script\s*>/gi;
        endTag.lastIndex = opening.lastIndex;
        const end = endTag.exec(masked);
        assert.ok(end, 'Unclosed script tag at line ' + lineAt(source, match.index));
        const tag = tags(match[0])[0];
        const type = (tag.attributes.get('type') || '').trim().toLowerCase();
        if (!tag.attributes.has('src') && (!type || type === 'module' || /^(?:text|application)\/(?:java|ecma)script$/.test(type))) {
            blocks.push({ source: source.slice(opening.lastIndex, end.index), line: lineAt(source, opening.lastIndex), module: type === 'module' });
        }
        opening.lastIndex = endTag.lastIndex;
    }
    return blocks;
}

function compileScript(source, filename, module = false) {
    if (!module) return new vm.Script(source, { filename });
    const result = spawnSync(process.execPath, ['--check', '--input-type=module'], { input: source, encoding: 'utf8', timeout: 10000 });
    if (result.error) throw result.error;
    assert.equal(result.status, 0, `${filename}: ${result.stderr}`);
}

function policies(source) {
    const result = new Map();
    for (const match of source.matchAll(/headers->set\('Content-Security-Policy(-Report-Only)?',\s*implode\('; ',\s*\[([\s\S]*?)\]\)\)/g)) {
        const directives = new Map();
        for (const line of match[2].matchAll(/^\s*"([^"\r\n]+)"/gm)) {
            const [name, ...values] = line[1].split(/\s+/);
            directives.set(name, values);
        }
        result.set(match[1] ? 'report-only' : 'enforced', directives);
    }
    return result;
}

function allows(policy, directive, value) {
    const sources = policy.get(directive) || policy.get(directive.replace(/-(?:elem|attr)$/, '')) || policy.get('default-src') || [];
    const target = new URL(value, 'https://clinic.invalid');
    return sources.some(source => {
        if (source === "'self'") return target.origin === 'https://clinic.invalid';
        if (source === '*') return ['http:', 'https:', 'ws:', 'wss:'].includes(target.protocol);
        if (/^[a-z]+:$/.test(source)) return target.protocol === source;
        if (!/^https?:\/\/|^wss?:\/\//.test(source)) return false;
        const allowed = new URL(source);
        const host = allowed.hostname.startsWith('*.')
            ? target.hostname.endsWith(allowed.hostname.slice(1)) && target.hostname !== allowed.hostname.slice(2)
            : target.hostname === allowed.hostname;
        return target.protocol === allowed.protocol && host && target.port === allowed.port
            && (allowed.pathname.endsWith('/') ? target.pathname.startsWith(allowed.pathname) : target.pathname === allowed.pathname);
    });
}

function remoteResources(source) {
    const result = [];
    for (const tag of tags(source)) {
        const directive = { script: 'script-src', img: 'img-src', iframe: 'frame-src', audio: 'media-src', video: 'media-src', source: 'media-src' }[tag.name]
            || (tag.name === 'link' && tag.attributes.get('rel') === 'stylesheet' ? 'style-src' : '');
        const url = tag.attributes.get(tag.name === 'link' ? 'href' : 'src');
        if (directive && /^https?:\/\//.test(url || '')) result.push({ directive, url, line: tag.line });
    }
    for (const match of source.matchAll(/\b(?:fetch|WebSocket|EventSource)\s*\(\s*(["'`])((?:https?|wss?):\/\/[^"'`\s]+)\1/g)) {
        result.push({ directive: 'connect-src', url: match[2], line: lineAt(source, match.index) });
    }
    for (const match of source.matchAll(/\burl\(\s*["']?(https?:\/\/[^"')\s]+)/g)) {
        result.push({ directive: /\.(?:woff2?|ttf|otf|eot)(?:[?#]|$)/i.test(match[1]) ? 'font-src' : 'img-src', url: match[1], line: lineAt(source, match.index) });
    }
    return result;
}

const adminFiles = files('resources/views/admin', '.blade.php');
const studentFiles = files('resources/views/student', '.blade.php');
const viewFiles = files('resources/views', '.blade.php').filter(file => !file.startsWith('resources/views/emails/'));
const views = viewFiles.map(file => ({ file, source: read(file) }));
const publicScripts = files('public/js', '.js').map(file => ({ file, source: read(file) }));
const assetNames = new Set(views.flatMap(({ source }) => [...source.matchAll(/asset\(["'](js\/[^"']+\.js)["']\)/g)].map(match => 'public/' + match[1])));
const adminScripts = publicScripts.filter(({ file }) => assetNames.has(file));
const outsideScripts = publicScripts.filter(({ file }) => !assetNames.has(file));
const policyInventory = policies(read('app/Http/Middleware/SecurityHeaders.php'));
const format = (file, findings) => findings.map(finding => `${file}:${finding}`);

test('attribute scanner handles boundaries, multiline/escaped templates, Blade arrows, and inert JSON', () => {
    const source = `<button data-onclick="safe" data-style="safe" title="onclick='text' style='text'" nonce="{{ request()->attributes->get('csp_nonce') }}">x</button>
        <div\n onClick = "open()" style='display:none'></div>
        <a href=javascript:alert(1)>x</a><svg onload=x()></svg>
        <style nonce="{{ request()->attributes->get('csp_nonce') }}">x{color:red}</style>
        <script type="application/json">{}</script><script src="/js/x.js"></script>
        <script>run()</script><style>x{}</style>`;
    const findings = markupFindings(source);
    assert.equal(findings.length, 6, findings.join('\n'));
    assert.equal(markupFindings('<div data-style="x" data-onclick="x"></div>').length, 0);
    assert.equal(markupFindings(String.raw`const html = '<div style=\"color:red\" onerror=\"fail()\"></div>';`).length, 2);
    assert.equal(markupFindings('<button\tonfocus=x()>').length, 1);
    assert.equal(markupFindings('<img/onerror=fail()>').length, 1);
    assert.equal(sinkFindings('node.style.display = "flex"; node.style.setProperty("--x", "1"); node.onclick = function () {};').length, 0);
    assert.equal(sinkFindings('const style = document.createElement("style"); style.nonce = nonce; document.head.appendChild(style);').length, 0);
    assert.equal(sinkFindings('const style = document.createElement("style"); document.head.appendChild(style); style.nonce = nonce;').length, 1);
    assert.equal(sinkFindings('let t = document.getElementById(id) || document.createElement("style"); t.nonce = nonce; document.head.appendChild(t);').length, 0);
    assert.equal(sinkFindings('node.setAttribute("style", css); node.style.cssText = css; Function("run()"); setTimeout("run()", 1);').length, 4);
});

test('Blade sanitizer balances nested PHP expressions and preserves both JavaScript branches', () => {
    const source = `const value = @json(route('x', ['key' => fn($x) => nested($x, ')')]));
        const other = @js(old('x') ? ['x' => foo()] : []);
        @if(foo(')')) if (value) { run({{ $id }}); } @elseif(bar()) run(); @else stop(); @endif
        @foreach($items as $item) record({!! json_encode($item) !!}); @endforeach`;
    assert.doesNotThrow(() => new vm.Script(sanitizeScript(source)));
    assert.throws(() => new vm.Script(sanitizeScript('@if($x) const broken = ; @endif')), SyntaxError);
    assert.throws(() => sanitizeScript('@json(foo('), /Unbalanced/);
    assert.equal(scriptBlocks('<script nonce="{{ request()->attributes->get(\'csp_nonce\') }}">let a = 1;</script>').length, 1);
    assert.equal(scriptBlocks('@php($x = 1) <script nonce="x">let a = 1;</script> @php $y = 2; @endphp').length, 1);
    assert.doesNotThrow(() => compileScript('export default 1;', 'module fixture', true));
});

test('inventory includes every browser-rendered view and referenced public script', t => {
    assert.ok(adminFiles.length >= 109, 'Admin inventory unexpectedly shrank.');
    assert.ok(studentFiles.length >= 23, 'Student inventory unexpectedly shrank.');
    assert.ok(publicScripts.length > 0);
    for (const file of assetNames) assert.ok(fs.existsSync(path.join(root, file)), 'Missing referenced asset: ' + file);
    t.diagnostic(`${adminFiles.length} admin views; ${studentFiles.length} student views; ${viewFiles.length} browser views; ${publicScripts.length} public scripts; ${adminScripts.length} directly referenced scripts.`);
});

test('all browser markup and referenced JS templates contain no inline attributes or nonce-less blocks', () => {
    const findings = [...views, ...adminScripts].flatMap(({ file, source }) => format(file, markupFindings(source)));
    assert.equal(findings.length, 0, '\n' + findings.join('\n'));
});

test('unreferenced public scripts are inventoried separately from browser assets', t => {
    for (const { file, source } of outsideScripts) {
        t.diagnostic(`Unreferenced asset: ${file}; ${markupFindings(source).length} markup findings; ${sinkFindings(source).length} sink findings (not rendered by browser views).`);
    }
});

test('all browser scripts have no unsafe execution/style sinks or unreviewed dynamic nodes', () => {
    const findings = [
        ...views.flatMap(({ file, source }) => scriptBlocks(source).flatMap(block => format(file, sinkFindings(block.source).map(finding => finding.replace(/^\d+/, number => String(Number(number) + block.line - 1)))))),
        ...adminScripts.flatMap(({ file, source }) => format(file, sinkFindings(source))),
    ];
    assert.equal(findings.length, 0, '\n' + findings.join('\n'));
});

test('all executable browser inline scripts and referenced scripts parse without execution', t => {
    const findings = [];
    let count = 0;
    for (const { file, source } of views) {
        for (const block of scriptBlocks(source)) {
            count++;
            try {
                const sanitized = sanitizeScript(block.source);
                assert.ok(!/@(?:json|js|if|else|endif|foreach|endforeach)\b/.test(sanitized), 'Unprocessed Blade directive');
                compileScript(sanitized, `${file}:${block.line}`, block.module);
            } catch (error) { findings.push(`${file}:${block.line}: ${error.message}`); }
        }
    }
    for (const { file, source } of adminScripts) {
        try { compileScript(source, file, /^\s*(?:import\s+(?!\()|export\b)/m.test(source)); }
        catch (error) { findings.push(`${file}: ${error.message}`); }
    }
    t.diagnostic(`Parsed ${count} inline scripts plus ${adminScripts.length} referenced scripts. Blade values/conditions are stubs, not rendered runtime validation.`);
    assert.equal(findings.length, 0, '\n' + findings.join('\n'));
});

test('module scripts do not redeclare global bindings owned by the admin layout', () => {
    const layout = scriptBlocks(read('resources/views/layouts/admin.blade.php'))
        .filter(block => !block.module).map(block => sanitizeScript(block.source)).join('\n;\n');
    for (const { file, source } of views.filter(view => view.file.startsWith('resources/views/admin/'))) {
        for (const block of scriptBlocks(source).filter(block => !block.module)) {
            assert.doesNotThrow(() => new vm.Script(layout + '\n;\n' + sanitizeScript(block.source)), file);
        }
    }
});

test('student pages do not redeclare bindings owned by their layout or another script block', () => {
    const findings = [];
    for (const { file, source } of views.filter(view => view.file.startsWith('resources/views/student/'))) {
        const layoutName = source.match(/@extends\(['"]layouts\.(student|evaluation)['"]\)/)?.[1];
        const layout = layoutName ? scriptBlocks(read(`resources/views/layouts/${layoutName}.blade.php`)) : [];
        const scripts = [...layout, ...scriptBlocks(source)].filter(block => !block.module).map(block => sanitizeScript(block.source));
        try { new vm.Script(scripts.join('\n;\n'), { filename: file }); }
        catch (error) { findings.push(`${file}: ${error.message}`); }
    }
    assert.deepEqual(findings, []);
});

test('remote-resource matcher handles wildcard boundaries, schemes, and directive fallback', () => {
    const policy = new Map([['default-src', ["'self'"]], ['connect-src', ['https://*.botpress.cloud', 'wss://*.botpress.cloud']], ['img-src', ['data:', 'blob:']]]);
    assert.equal(allows(policy, 'connect-src', 'https://api.botpress.cloud/v1'), true);
    assert.equal(allows(policy, 'connect-src', 'https://evilbotpress.cloud/v1'), false);
    assert.equal(allows(policy, 'connect-src', 'https://botpress.cloud/v1'), false);
    assert.equal(allows(policy, 'connect-src', 'wss://socket.botpress.cloud/v1'), true);
    assert.equal(allows(policy, 'script-src', 'https://clinic.invalid/js/app.js'), true);
    assert.equal(allows(policy, 'img-src', 'data:image/png;base64,AA'), true);
    assert.equal(remoteResources("fetch('https://example.org/api'); <a href='https://docs.example.org'>Docs</a>").length, 1);
});

for (const name of ['enforced', 'report-only']) {
    test(`browser direct remote resources are permitted by the ${name} CSP`, t => {
        const policy = policyInventory.get(name);
        if (!policy && name === 'report-only') return t.skip('No Report-Only policy configured.');
        assert.ok(policy, 'Unable to extract current CSP from SecurityHeaders.');
        const findings = [...views, ...adminScripts].flatMap(({ file, source }) => remoteResources(source)
            .filter(resource => !allows(policy, resource.directive, resource.url))
            .map(resource => `${file}:${resource.line}: ${resource.directive} excludes ${new URL(resource.url).origin}`));
        assert.equal(findings.length, 0, '\n' + findings.join('\n'));
    });
}

test('the single enforced global policy uses nonce scripts/styles and disables inline attributes', () => {
    const policy = policyInventory.get('enforced');
    assert.ok(policy, 'Unable to extract enforced CSP.');
    assert.equal(policyInventory.size, 1, 'Keep a single global policy.');
    assert.match(read('app/Http/Kernel.php'), /protected\s+\$middleware\s*=\s*\[[\s\S]*?SecurityHeaders::class/);
    for (const name of ['script-src', 'style-src']) {
        assert.ok(policy.get(name)?.includes("'nonce-{$nonce}'"), `${name} does not use the request nonce.`);
        assert.ok(!policy.get(name).includes("'unsafe-inline'"), `${name} still permits unsafe-inline.`);
    }
    assert.deepEqual(policy.get('script-src-attr'), ["'none'"]);
    assert.deepEqual(policy.get('style-src-attr'), ["'none'"]);
});

test('Apache preserves application CSP and applies its restrictive fallback only when absent', () => {
    const configuration = read('public/.htaccess');
    const headers = configuration.split(/\r?\n/).filter(line => /^\s*Header\b.*Content-Security-Policy\b/i.test(line));
    assert.equal(headers.length, 1, 'Keep the nonce-based application policy out of static Apache configuration.');
    assert.match(headers[0], /Header always set Content-Security-Policy/);
    assert.match(headers[0], /"expr=-z resp\('Content-Security-Policy'\)"\s*$/);
    assert.match(headers[0], /default-src 'none'/);
    assert.doesNotMatch(configuration, /Header\s+(?:always\s+|onsuccess\s+)?unset\s+Content-Security-Policy\b/i);
});

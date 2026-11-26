#!/usr/bin/env node
'use strict';

// Source fixtures, not authenticated workflow tests. No application JavaScript runs.
const fs = require('node:fs');
const path = require('node:path');
const { createHash } = require('node:crypto');
const { execFileSync } = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const options = {
    viewports: [[1440, 900], [1024, 768], [768, 1024], [390, 844]],
    themes: ['light', 'dark'], branches: ['true', 'false'], states: [], limit: 12,
    chrome: process.env.CSP_BROWSER_EXECUTABLE || '',
    playwright: process.env.CSP_PLAYWRIGHT_MODULE || 'playwright',
    layout: 'resources/views/layouts/admin.blade.php', json: false, selfTest: false,
};
const args = [];
for (const arg of process.argv.slice(2)) {
    if (arg === '--help') {
        console.log(`Usage: node tests/browser/admin-style-comparison.cjs [options] file-or-glob ...
Examples:
  node tests/browser/admin-style-comparison.cjs activity_logs api-testing 'settings*' student-assistants 'user_management*'
  node tests/browser/admin-style-comparison.cjs --viewports=1440x900,390x844 --json walkin
Options:
  --viewports=WxH,...   Actual browser viewport/media-query sizes
  --themes=light,dark   Theme attributes on html and body
  --branches=true,false Literal Blade ternary arms; loops/if blocks are flattened
  --state=selector::classes  Extra class-state scenario (repeatable)
  --limit=N            Maximum printed differences per file (default 12)
  --json               Full machine-readable report on stdout
  --self-test          Verify ID/media specificity and hidden/reset detection
  --chrome=PATH        Chrome executable
  --playwright=PATH    Playwright module directory
  --layout=PATH        Shared current layout CSS for BOTH sides; 'none' disables it

HEAD versus a cached worktree snapshot; literal includes are expanded recursively.
Blade/PHP is stripped, not executed. HTML JS string/template literals are mounted
without executing application JS; unresolved template parents are reported.
Initial styles, explicit reveals, and source-detected empty-display resets are
compared. Auto margins use typed CSS values plus geometry (avoids Blink's stale
resolved auto-margin values). Exit 1 means differences or structural gaps.`);
        process.exit(0);
    } else if (arg === '--json') options.json = true;
    else if (arg === '--self-test') options.selfTest = true;
    else if (arg.startsWith('--')) {
        const equal = arg.indexOf('=');
        if (equal < 0) throw new Error('Expected --option=value: ' + arg);
        const name = arg.slice(2, equal), value = arg.slice(equal + 1);
        if (name === 'viewports') options.viewports = value.split(',').map(size => {
            const pair = size.split('x').map(Number);
            if (pair.length !== 2 || pair.some(n => !Number.isInteger(n) || n < 100)) throw new Error('Invalid viewport: ' + size);
            return pair;
        });
        else if (name === 'themes' || name === 'branches') options[name] = value.split(',');
        else if (name === 'state') {
            const split = value.indexOf('::');
            if (split < 1) throw new Error('Expected --state=selector::classes');
            options.states.push({ selector: value.slice(0, split), classes: value.slice(split + 2).split(/\s+/).filter(Boolean) });
        } else if (name === 'limit') options.limit = Number(value);
        else if (['chrome', 'playwright', 'layout'].includes(name)) options[name] = value;
        else throw new Error('Unknown option: ' + name);
    } else args.push(arg);
}
if (!args.length && !options.selfTest) args.push('activity_logs', 'api-testing', 'settings*', 'student-assistants', 'user_management*');
const files = [...new Set(args.flatMap(arg => {
    let pattern = arg.replaceAll('\\', '/');
    if (!pattern.includes('/')) pattern = 'resources/views/admin/' + pattern;
    if (!pattern.endsWith('.php')) pattern += '.blade.php';
    return fs.globSync(pattern, { cwd: root }).filter(file => fs.statSync(path.join(root, file)).isFile()).map(file => file.replaceAll('\\', '/'));
}))].sort();
if (!files.length && !options.selfTest) throw new Error('No Blade files matched.');

const sourceCache = new Map();
function read(file, revision) {
    const key = revision + ':' + file;
    if (!sourceCache.has(key)) sourceCache.set(key, revision === 'HEAD'
        ? execFileSync('git', ['show', 'HEAD:' + file], { cwd: root, encoding: 'utf8', maxBuffer: 16 * 1024 * 1024, stdio: ['ignore', 'pipe', 'pipe'] })
        : fs.readFileSync(path.join(root, file), 'utf8'));
    return sourceCache.get(key);
}
const tagPattern = /<([a-z][\w:-]*)\b(?:\{\{[\s\S]*?\}\}|"[^"]*"|'[^']*'|[^'">])*>/gi;
const scriptPattern = /<script\b(?:"[^"]*"|'[^']*'|[^'">])*>([\s\S]*?)<\/script\s*>/gi;
const stylePattern = /<style\b(?:"[^"]*"|'[^']*'|[^'">])*>([\s\S]*?)<\/style\s*>/gi;
const escapeRegex = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

function quotedEnd(text, start) {
    const quote = text[start];
    let output = '', index = start + 1;
    for (; index < text.length; index++) {
        const char = text[index];
        if (char === '\\') {
            const next = text[++index];
            output += ({ n: '\n', r: '\r', t: '\t' })[next] ?? next ?? '';
        } else if (quote === '`' && char === '$' && text[index + 1] === '{') {
            index = balancedEnd(text, index + 1, '{', '}');
            output += 'Fixture';
        } else if (char === quote) return { end: index, value: output };
        else output += char;
    }
    return { end: index, value: output };
}
function balancedEnd(text, start, open = '(', close = ')') {
    let depth = 0;
    for (let index = start; index < text.length; index++) {
        const char = text[index];
        if ('\'"`'.includes(char)) index = quotedEnd(text, index).end;
        else if (char === open) depth++;
        else if (char === close && --depth === 0) return index;
    }
    throw new Error('Unbalanced fixture expression: ' + text.slice(start, start + 100));
}
function literalArm(expression, truthy) {
    const ternary = expression.match(/\?\s*(['"])(.*?)\1\s*:\s*(['"])(.*?)\3\s*$/s);
    if (ternary) return truthy ? ternary[2] : ternary[4];
    const literal = expression.trim().match(/^(['"])([\s\S]*)\1$/);
    if (literal) return literal[2];
    return expression.includes('csp_nonce') ? 'csp-fixture-nonce' : 'Fixture';
}
function stripEchoes(text, truthy) {
    return text.replace(/\{\{--[\s\S]*?--\}\}/g, '').replace(/\{\{([\s\S]*?)\}\}|\{!!([\s\S]*?)!!\}/g,
        (_, escaped, raw) => literalArm(escaped ?? raw, truthy));
}
function stripDirectives(text, include) {
    text = text.replace(/@php\b[\s\S]*?@endphp/g, '');
    let output = '', cursor = 0;
    const directive = /@(include(?:If|When|Unless)?|extends|section|push|prepend|stack|yield|if|elseif|unless|isset|empty|foreach|forelse|for|while|switch|case|auth|guest|can|cannot|json|class|selected|checked|disabled|readonly|method|php)\b/g;
    for (let match; (match = directive.exec(text));) {
        let start = directive.lastIndex;
        while (/\s/.test(text[start] || '') && start < text.length) start++;
        if (text[start] !== '(') continue;
        const end = balancedEnd(text, start), expression = text.slice(start + 1, end);
        output += text.slice(cursor, match.index);
        if (match[1].startsWith('include')) {
            const view = expression.match(/(?:^|,)\s*['"]([\w.-]+)['"]/);
            if (view) output += include('resources/views/' + view[1].replaceAll('.', '/') + '.blade.php');
        } else if (match[1] === 'json') output += 'null';
        cursor = end + 1;
        directive.lastIndex = cursor;
    }
    return (output + text.slice(cursor)).replace(/@(end\w+|else|default|break|csrf|auth|guest)\b/g, '');
}
function htmlLiterals(script) {
    const results = [];
    for (let index = 0; index < script.length; index++) {
        if (script.startsWith('//', index)) { index = script.indexOf('\n', index); if (index < 0) break; }
        else if (script.startsWith('/*', index)) { const end = script.indexOf('*/', index + 2); index = end < 0 ? script.length : end + 1; }
        else if ('\'"`'.includes(script[index])) {
            const literal = quotedEnd(script, index);
            if (/<[a-z][\s\S]*>/i.test(literal.value)) results.push({ html: literal.value, index, end: literal.end });
            index = literal.end;
        }
    }
    return results;
}
function templateContext(script, literal) {
    const prefix = script.slice(0, literal.index), suffix = script.slice(literal.end + 1);
    const jquery = prefix.match(/\$\(['"]#([\w-]+)['"]\)\.(?:html|append)\s*\(\s*$/);
    if (jquery) return { parent: jquery[1] };
    let owner = prefix.match(/(\w+)\.(?:innerHTML|insertAdjacentHTML)\s*(?:=|\([^,]*,)\s*$/)?.[1];
    if (!owner) {
        const value = prefix.match(/\b(\w+)\s*\+?=\s*$/)?.[1];
        if (value) owner = suffix.match(new RegExp('\\b(\\w+)\\.innerHTML\\s*=\\s*' + escapeRegex(value) + '\\b'))?.[1];
    }
    if (!owner) return { parent: null };
    const idFor = variable => [...prefix.matchAll(new RegExp('\\b' + escapeRegex(variable) + '\\s*=\\s*document\\.getElementById\\([\x22\x27]([^\x22\x27]+)[\x22\x27]\\)', 'g'))].at(-1)?.[1] || null;
    const parent = idFor(owner);
    if (parent) return { parent };
    const escaped = escapeRegex(owner);
    const created = [...prefix.matchAll(new RegExp('\\b' + escaped + '\\s*=\\s*document\\.createElement\\([\x22\x27](\\w+)[\x22\x27]\\)', 'g'))].at(-1);
    if (!created) return { parent: null };
    const append = suffix.match(new RegExp('\\b(\\w+)\\.(?:appendChild|append)\\(\\s*' + escaped + '\\s*\\)'));
    const classes = [...prefix.slice(created.index).matchAll(new RegExp('\\b' + escaped + '\\.className\\s*=\\s*([\x22\x27])([^\x22\x27]*)\\1', 'g'))].at(-1)?.[2] || '';
    return { parent: append ? idFor(append[1]) : null, wrapper: { tag: created[1], classes } };
}
function clearDisplayIds(scripts) {
    const ids = new Set();
    for (const script of scripts) {
        for (const binding of script.matchAll(/\b(\w+)\s*=\s*document\.getElementById\(['"]([^'"]+)['"]\)/g)) {
            const name = escapeRegex(binding[1]);
            if (new RegExp('\\b' + name + '\\??\\.style\\.display\\s*=\\s*[^;\\n]*([\x22\x27])\\1|\\b' + name + '\\??\\.style\\.removeProperty\\(\\s*[\x22\x27]display').test(script)) ids.add(binding[2]);
        }
        for (const direct of script.matchAll(/document\.getElementById\(['"]([^'"]+)['"]\)\??\.style\.(?:display\s*=\s*[^;\n]*(['"])\2|removeProperty\(['"]display['"]\))/g)) ids.add(direct[1]);
    }
    return [...ids];
}
function build(file, revision, truthy) {
    const templates = [], scripts = [], warnings = [];
    function stylesheetInclude(view, ancestors = []) {
        if (ancestors.includes(view)) throw new Error('Recursive stylesheet include: ' + view);
        const source = stripEchoes(read(view, revision), truthy);
        return stripDirectives(source, included => stylesheetInclude(included, [...ancestors, view]));
    }
    function annotate(html, namespace) {
        let index = 0;
        return html.replace(tagPattern, (tag, name) => ['style', 'link', 'meta'].includes(name.toLowerCase()) ? tag
            : tag.replace(/^(<[\w:-]+)/, '$1 data-csp-key="' + namespace + ':' + index++ + '"'));
    }
    function expand(view, ancestors = []) {
        if (ancestors.includes(view)) { warnings.push('Recursive include skipped: ' + view); return ''; }
        let source;
        try { source = stripEchoes(read(view, revision), truthy); }
        catch { warnings.push('Missing include: ' + view); return ''; }
        source = source.replace(scriptPattern, (_, script) => {
            scripts.push(script);
            for (const [index, literal] of htmlLiterals(script).entries()) {
                const html = literal.html.replace(scriptPattern, '').replace(stylePattern, '');
                templates.push({ key: view + ':template:' + (scripts.length - 1) + ':' + index, html, ...templateContext(script, literal), source: view });
            }
            return '';
        });
        const blocks = [];
        source = source.replace(stylePattern, style => {
            if (revision !== 'HEAD' && !/\bnonce=/.test(style)) warnings.push('Style block without nonce: ' + view);
            style = stripDirectives(style, included => stylesheetInclude(included, [view]));
            blocks.push(style.replace(/^<style\b[^>]*>/i, '<style nonce="csp-fixture-nonce">'));
            return '@@CSP_STYLE_' + (blocks.length - 1) + '@@';
        }).replace(/<link\b[^>]*>/gi, '');
        // Annotate each source independently so includes or extra style blocks do not shift keys.
        source = annotate(source, view + ':markup');
        source = stripDirectives(source, included => expand(included, [...ancestors, view]));
        return source.replace(/@@CSP_STYLE_(\d+)@@/g, (_, index) => blocks[index]);
    }
    const html = expand(file);
    for (const template of templates) template.html = annotate(stripDirectives(template.html, () => ''), template.key);
    return { html, templates, clearIds: clearDisplayIds(scripts), warnings };
}

async function install(page, fixture, layoutCss, theme, strict, state) {
    const policy = "default-src 'none'; style-src 'nonce-csp-fixture-nonce'" + (strict ? '' : " 'unsafe-inline'") + "; style-src-attr " + (strict ? "'none'" : "'unsafe-inline'") + "; script-src 'none'; img-src data:;";
    await page.setContent('<!doctype html><html data-theme="' + theme + '"><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="' + policy + '">' + layoutCss + '<style nonce="csp-fixture-nonce">*,*::before,*::after{animation:none!important;transition:none!important}</style></head><body data-theme="' + theme + '"><main class="main">' + fixture.html + '</main></body></html>', { waitUntil: 'domcontentloaded' });
    return page.evaluate(({ templates, state }) => {
        const unresolved = [];
        for (const template of templates) {
            const target = template.parent && document.getElementById(template.parent);
            if (!target) unresolved.push({ key: template.key, parent: template.parent });
            let holder = target;
            if (template.wrapper) {
                holder = document.createElement(template.wrapper.tag);
                holder.className = template.wrapper.classes;
                (target || document.querySelector('main')).append(holder);
            } else if (!holder) {
                holder = document.createElement(/^\s*<(?:tr|tbody|thead)\b/i.test(template.html) ? 'table' : 'div');
                document.querySelector('main').append(holder);
            }
            const range = document.createRange(); range.selectNodeContents(holder);
            holder.append(range.createContextualFragment(template.html));
        }
        if (state) for (const node of document.querySelectorAll(state.selector)) node.classList.add(...state.classes);
        document.body.getBoundingClientRect();
        return unresolved;
    }, { templates: fixture.templates, state });
}
async function baselineElements(page) {
    return page.evaluate(() => [...document.querySelectorAll('[style][data-csp-key]')].map(node => ({
        key: node.dataset.cspKey, id: node.id, tag: node.localName,
        classes: typeof node.className === 'string' ? node.className : node.getAttribute('class'),
        style: node.getAttribute('style'), properties: [...node.style], hidden: node.style.display === 'none',
    })));
}
async function measure(page, elements, clearIds) {
    return page.evaluate(({ elements, clearIds }) => {
        const nodes = new Map([...document.querySelectorAll('[data-csp-key]')].map(node => [node.dataset.cspKey, node]));
        const results = [];
        for (const item of elements) {
            const node = nodes.get(item.key);
            if (!node) { results.push({ key: item.key, phase: 'structure', property: 'element', value: 'missing' }); continue; }
            const computed = getComputedStyle(node);
            for (const property of item.properties) {
                const rect = node.getBoundingClientRect();
                const typed = /^margin-(left|right)$/.test(property) && node.computedStyleMap().get(property)?.toString();
                const value = typed === 'auto' ? 'auto:' + [rect.x, rect.y, rect.width, rect.height].join(',') : computed.getPropertyValue(property);
                results.push({ key: item.key, phase: 'initial', property, value });
            }
            if (!item.hidden) continue;
            const original = node.style.display;
            for (const display of ['block', 'flex', 'grid', 'inline-flex', 'table-row']) {
                node.style.display = display;
                results.push({ key: item.key, phase: 'reveal:' + display, property: 'display', value: getComputedStyle(node).display });
            }
            if (clearIds.includes(item.id)) {
                node.style.removeProperty('display');
                results.push({ key: item.key, phase: 'source-reset:display-empty', property: 'display', value: getComputedStyle(node).display });
            }
            node.style.display = original;
        }
        return results;
    }, { elements, clearIds });
}

(async () => {
    const { chromium } = require(options.playwright === 'playwright' ? 'playwright' : path.resolve(root, options.playwright));
    const browser = await chromium.launch({ ...(options.chrome ? { executablePath: options.chrome } : {}), headless: true });
    const report = { baseline: 'HEAD', fixturesOnly: true, viewports: options.viewports, files: [] };
    let context;
    try {
        context = await browser.newContext({ offline: true });
        await context.route('**/*', route => route.abort());
        const beforePage = await context.newPage(), afterPage = await context.newPage();
        if (options.selfTest) {
            const assert = require('node:assert/strict');
            const css = '<style nonce="csp-fixture-nonce">.migrated.migrated.migrated{font-size:16px}.hidden.hidden.hidden{display:none}@media(max-width:600px){#probe{font-size:13px}}</style>';
            const oldFixture = { html: css + '<p id="probe" data-csp-key="font" style="font-size:16px">Fixture</p><div id="reset" data-csp-key="reset" style="display:none">Fixture</div>', templates: [] };
            const newFixture = { html: css + '<p id="probe" data-csp-key="font" class="migrated">Fixture</p><div id="reset" data-csp-key="reset" class="hidden">Fixture</div>', templates: [] };
            assert.deepEqual(clearDisplayIds(["const reset = document.getElementById('reset'); reset.style.display = allowed ? '' : 'none';"]), ['reset']);
            const templateScript = "const grid=document.getElementById('first');const card=document.createElement('div');card.className='card';card.innerHTML='<small>Fixture</small>';grid.appendChild(card);function later(){const grid=document.getElementById('second');}";
            assert.deepEqual(templateContext(templateScript, htmlLiterals(templateScript)[0]), { parent: 'first', wrapper: { tag: 'div', classes: 'card' } });
            for (const width of [1440, 390]) {
                await beforePage.setViewportSize({ width, height: 900 }); await afterPage.setViewportSize({ width, height: 900 });
                await install(beforePage, oldFixture, '', 'light', false, null); await install(afterPage, newFixture, '', 'light', true, null);
                const elements = await baselineElements(beforePage);
                const oldValues = await measure(beforePage, elements, ['reset']), newValues = await measure(afterPage, elements, ['reset']);
                const find = (values, key, phase) => values.find(value => value.key === key && value.phase === phase).value;
                assert.equal(find(oldValues, 'font', 'initial'), '16px');
                assert.equal(find(newValues, 'font', 'initial'), width < 600 ? '13px' : '16px');
                assert.notEqual(find(oldValues, 'reset', 'source-reset:display-empty'), find(newValues, 'reset', 'source-reset:display-empty'));
                for (const value of oldValues.filter(value => value.phase.startsWith('reveal:'))) assert.equal(value.value, find(newValues, value.key, value.phase));
            }
            console.log('PASS self-test: media-query ID specificity, direct .style reveals, empty-display reset detection, lexical template-parent inference.');
            return;
        }
        const layoutSource = options.layout === 'none' ? '' : stripEchoes(read(options.layout, 'worktree'), true);
        const layoutCss = [...layoutSource.matchAll(stylePattern)].map(match => '<style nonce="csp-fixture-nonce">' + match[1] + '</style>').join('\n');
        for (const file of files) {
            const result = { file, worktreeSha256: createHash('sha256').update(read(file, 'worktree')).digest('hex'), comparisons: 0, reveals: 0, scenarios: 0, styledElements: 0, warnings: [], differences: [] };
            const warningSet = new Set(), differenceKeys = new Set();
            for (const branch of options.branches) {
                const before = build(file, 'HEAD', branch === 'true'), after = build(file, 'worktree', branch === 'true');
                [...before.warnings, ...after.warnings].forEach(warning => warningSet.add(warning));
                if (before.templates.length !== after.templates.length) warningSet.add('HTML literal count differs: HEAD=' + before.templates.length + ', worktree=' + after.templates.length);
                for (const [width, height] of options.viewports) {
                    await beforePage.setViewportSize({ width, height }); await afterPage.setViewportSize({ width, height });
                    for (const theme of options.themes) for (const state of [null, ...options.states]) {
                        const scenario = { viewport: width + 'x' + height, theme, branch, state: state ? state.selector + '::' + state.classes.join(' ') : 'initial' };
                        await install(beforePage, before, layoutCss, theme, false, state);
                        const unresolved = await install(afterPage, after, layoutCss, theme, true, state);
                        unresolved.forEach(item => warningSet.add('Template uses fallback parent: ' + item.key + (item.parent ? ' (missing #' + item.parent + ')' : '')));
                        const elements = await baselineElements(beforePage);
                        result.styledElements = Math.max(result.styledElements, elements.length);
                        const oldValues = await measure(beforePage, elements, after.clearIds), newValues = await measure(afterPage, elements, after.clearIds);
                        const values = new Map(newValues.map(value => [value.key + '|' + value.phase + '|' + value.property, value.value]));
                        const metadata = new Map(elements.map(item => [item.key, item]));
                        for (const entry of oldValues) {
                            result.comparisons++;
                            if (entry.phase.startsWith('reveal:') || entry.phase.startsWith('source-reset:')) result.reveals++;
                            const current = values.get(entry.key + '|' + entry.phase + '|' + entry.property) ?? 'missing';
                            if (entry.value === current) continue;
                            const key = JSON.stringify([scenario, entry.key, entry.phase, entry.property, entry.value, current]);
                            if (differenceKeys.has(key)) continue;
                            differenceKeys.add(key);
                            const item = metadata.get(entry.key);
                            result.differences.push({ ...scenario, source: entry.key, element: item.id ? '#' + item.id : item.tag + (item.classes ? '.' + item.classes.split(/\s+/).join('.') : ''), phase: entry.phase, property: entry.property, before: entry.value, after: current });
                        }
                        result.scenarios++;
                    }
                }
            }
            result.warnings = [...warningSet]; report.files.push(result);
            if (!options.json) {
                console.log(file + ': ' + result.differences.length + ' differences; ' + result.comparisons + ' comparisons, ' + result.reveals + ' reveals/resets, ' + result.scenarios + ' scenarios, ' + result.styledElements + ' styled elements');
                for (const difference of result.differences.slice(0, options.limit)) console.log('  ' + JSON.stringify(difference));
                for (const warning of result.warnings.slice(0, options.limit)) console.log('  WARNING ' + warning);
            }
        }
        report.changedDuringRun = [...sourceCache].filter(([key, value]) => key.startsWith('worktree:') && fs.readFileSync(path.join(root, key.slice(9)), 'utf8') !== value).map(([key]) => key.slice(9));
        report.totals = report.files.reduce((total, file) => ({ comparisons: total.comparisons + file.comparisons, reveals: total.reveals + file.reveals, differences: total.differences + file.differences.length }), { comparisons: 0, reveals: 0, differences: 0 });
        if (options.json) console.log(JSON.stringify(report, null, 2));
        else console.log('TOTAL ' + JSON.stringify(report.totals));
        if (!options.json && report.changedDuringRun.length) console.log('WARNING files changed during fixture run: ' + report.changedDuringRun.join(', '));
        process.exitCode = report.totals.differences || report.changedDuringRun.length || report.files.some(file => file.warnings.some(warning => /Missing include|count differs/.test(warning))) ? 1 : 0;
    } finally {
        if (context) await context.close();
        await browser.close();
    }
})().catch(error => { console.error(error.stack || error); process.exitCode = 1; });

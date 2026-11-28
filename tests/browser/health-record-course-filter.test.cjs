const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

test('audience changes update the existing custom course dropdown without stale office choices under CSP', {
    skip: !process.env.CSP_PLAYWRIGHT_MODULE, timeout: 30000,
}, async () => {
    const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE);
    const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/admin/health_records.blade.php'), 'utf8');
    const dropdownStart = source.indexOf("document.querySelectorAll('.health-filter-custom-source').forEach");
    const dropdownEnd = source.indexOf('function syncHealthStudentTypeFilterVisibility()', dropdownStart);
    const syncStart = source.indexOf('function syncHealthCourseFilter()');
    const syncEnd = source.indexOf("document.addEventListener('click', function (event)", syncStart);
    const data = {
        '': { label: 'Course / Department / Office', placeholder: 'All Courses / Departments / Offices', options: [
            { value: 'Marketing', label: 'BSBA-MM - Marketing' }, { value: 'Registrar Office', label: 'Registrar Office' },
        ] },
        student: { label: 'Course', placeholder: 'All Courses', options: [{ value: 'Marketing', label: 'BSBA-MM - Marketing' }] },
        applicant: { label: 'Course', placeholder: 'All Courses', options: [{ value: 'Marketing', label: 'BSBA-MM - Marketing' }] },
        admin: { label: 'Department / Office', placeholder: 'All Departments / Offices', options: [{ value: 'Registrar Office', label: 'Registrar Office' }] },
        faculty: { label: 'Department / Office', placeholder: 'All Departments / Offices', options: [] },
    };
    const browser = await chromium.launch({ headless: true, executablePath: process.env.CSP_BROWSER_EXECUTABLE });
    try {
        const page = await browser.newPage();
        const errors = []; page.on('pageerror', error => errors.push(error.message));
        const origin = 'http://127.0.0.1/health-record-course-filter';
        await page.route(origin, route => route.fulfill({ contentType: 'text/html', body: `<html><head>
            <meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'nonce-test'; style-src 'nonce-test'; script-src-attr 'none'; style-src-attr 'none'">
            <style nonce="test">select,.health-filter-custom-menu{display:none}.is-open .health-filter-custom-menu{display:block}</style>
            </head><body>
            <div class="health-filter-select-wrap" id="audience"><select class="health-filter-custom-source" id="userTypeFilter">
            <option value="">All</option><option value="student">Students</option><option value="applicant">Applicants</option><option value="admin">Admin</option><option value="faculty">Faculty</option></select></div>
            <label for="courseFilter"></label><div class="health-filter-select-wrap" id="course"><select id="courseFilter" class="health-filter-custom-source"></select></div>
            <script nonce="test">window.violations=[];addEventListener('securitypolicyviolation', e=>violations.push(e.effectiveDirective));
            document.getElementById('courseFilter').dataset.courseChoices=JSON.stringify(${JSON.stringify(data)});
            const healthUserTypeFilter=document.getElementById('userTypeFilter');
            ${source.slice(dropdownStart, dropdownEnd)}${source.slice(syncStart, syncEnd)}</script></body></html>` }));
        await page.goto(origin);
        async function choose(group, value) {
            await page.locator(`#${group} .health-filter-custom-trigger`).click();
            await page.locator(`#${group} .health-filter-custom-option[data-value="${value}"]`).click();
        }
        await choose('course', 'Registrar Office');
        await choose('audience', 'student');
        assert.equal(await page.locator('label').textContent(), 'Course');
        assert.equal(await page.locator('#courseFilter').inputValue(), '', 'An incompatible office selection is cleared.');
        assert.deepEqual(await page.locator('#course .health-filter-custom-option').allTextContents(), ['All Courses', 'BSBA-MM - Marketing']);
        await choose('course', 'Marketing');
        await choose('audience', 'applicant');
        assert.equal(await page.locator('#courseFilter').inputValue(), 'Marketing', 'A still-valid course is retained.');
        await choose('audience', 'admin');
        assert.equal(await page.locator('label').textContent(), 'Department / Office');
        assert.deepEqual(await page.locator('#course .health-filter-custom-option').allTextContents(), ['All Departments / Offices', 'Registrar Office']);
        await choose('course', 'Registrar Office');
        assert.equal(await page.locator('#courseFilter').inputValue(), 'Registrar Office');
        await choose('audience', 'faculty');
        assert.equal(await page.locator('#courseFilter').inputValue(), '');
        assert.deepEqual(await page.locator('#course .health-filter-custom-option').allTextContents(), ['All Departments / Offices']);
        await choose('audience', '');
        assert.equal(await page.locator('label').textContent(), 'Course / Department / Office');
        assert.equal(await page.locator('#course .health-filter-custom-option').count(), 3);
        assert.deepEqual(await page.evaluate(() => violations), []);
        assert.deepEqual(errors, []);
    } finally { await browser.close(); }
});

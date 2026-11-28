(function () {
    'use strict';
    const root = document.getElementById('healthPdfProgress');
    if (!root) return;
    const phase = document.getElementById('healthPdfPhase');
    const message = document.getElementById('healthPdfMessage');
    const meter = document.getElementById('healthPdfMeter');
    const retry = document.getElementById('healthPdfRetry');
    const download = document.getElementById('healthPdfDownload');
    let id = root.dataset.exportId;
    let timer;
    let stopped = false;
    let busy = false;

    async function request(url, options) {
        const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options,
            headers: { Accept: 'application/json', ...options?.headers } });
        if (response.redirected || response.status === 401 || response.status === 419) throw new Error('Your session has expired. Please sign in again, then reopen this export page.');
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'The export could not be completed. Please try again.');
        return body;
    }

    function fail(error) {
        phase.textContent = 'Export needs attention';
        message.textContent = error.message;
        retry.hidden = false;
        meter.value = 0;
    }

    async function poll() {
        if (stopped || busy) return;
        busy = true;
        try {
            const data = await request(root.dataset.startUrl + '/' + encodeURIComponent(id));
            const total = Number(data.total || 0);
            if (data.status === 'ready') {
                phase.textContent = 'Your PDF is ready';
                message.textContent = total.toLocaleString() + ' matching records in one PDF (' + data.pages + ' pages).';
                meter.value = 100;
                download.href = root.dataset.startUrl + '/' + encodeURIComponent(id) + '/download';
                download.hidden = false;
                return;
            }
            if (data.status === 'failed') throw new Error('The server could not build this PDF. You can try again; if it fails again, ask the administrator to check the PDF worker.');
            if (data.status === 'queued') {
                phase.textContent = 'Waiting for the PDF worker';
                message.textContent = 'The export is queued. If this does not change, the hosting administrator should check that the scheduler or PDF worker is running.';
                meter.value = 0;
            } else if (data.status === 'preparing') {
                phase.textContent = 'Checking and sorting records';
                message.textContent = Number(data.scanned).toLocaleString() + ' of ' + Number(data.source_total).toLocaleString() + ' source records checked; ' + total.toLocaleString() + ' matched.';
                meter.value = 20 * Math.min(1, data.scanned / Math.max(1, data.source_total));
            } else if (data.status === 'rendering') {
                phase.textContent = 'Generating PDF batches';
                message.textContent = Number(data.rendered).toLocaleString() + ' of ' + total.toLocaleString() + ' matching records rendered.';
                meter.value = 20 + 70 * Math.min(1, data.rendered / Math.max(1, total));
            } else if (data.status === 'merging') {
                phase.textContent = 'Combining your PDF';
                message.textContent = 'All records are rendered. Combining the batches into one downloadable file.';
                meter.value = 95;
            }
            if (!stopped) timer = setTimeout(poll, 3000);
        } catch (error) { fail(error); }
        finally { busy = false; }
    }

    async function start() {
        if (busy || stopped) return;
        retry.hidden = true;
        download.hidden = true;
        if (!id) {
            busy = true;
            try {
                const data = await request(root.dataset.startUrl, { method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify(JSON.parse(root.dataset.filters)) });
                id = data.id;
                const current = new URL(window.location.href);
                current.searchParams.set('pdf_export', id);
                window.history.replaceState(null, '', current);
            } catch (error) { fail(error); return; }
            finally { busy = false; }
        }
        await poll();
    }

    retry.addEventListener('click', function () {
        clearTimeout(timer);
        id = '';
        start();
    });
    window.addEventListener('pagehide', function () { stopped = true; clearTimeout(timer); });
    window.addEventListener('pageshow', function (event) { if (event.persisted) { stopped = false; start(); } });
    start();
}());

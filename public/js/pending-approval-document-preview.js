(() => {
    'use strict';
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[char]));
    window.PendingApprovalDocumentPreview = {
        render(doc, enabled = false) {
            // Called only when opening a pending record's View Info modal.
            // Other record views retain the existing logo-only thumbnail.
            if (!enabled || !doc?.url || !['image', 'pdf'].includes(doc.preview_type)) return '';
            let url;
            try {
                url = new URL(doc.url, window.location.href);
                if (!['http:', 'https:'].includes(url.protocol) || url.origin !== window.location.origin) return '';
            } catch { return ''; }
            const title = escape(doc.title || 'Document');
            if (doc.preview_type === 'image') {
                return `<span class="verification-doc-preview pending-document-preview"><img src="${escape(url.href)}" alt="${title} preview" loading="lazy" decoding="async"></span>`;
            }
            url.hash = 'toolbar=0&navpanes=0&view=FitH';
            return `<span class="verification-doc-preview pending-document-preview"><iframe src="${escape(url.href)}" title="${title} preview" loading="lazy" referrerpolicy="same-origin"></iframe></span>`;
        },
    };
})();

<dialog class="requirement-sample-dialog" id="requirementSampleDialog" aria-labelledby="requirementSampleTitle" aria-describedby="requirementSampleDescription">
    <div class="requirement-sample-card">
        <header class="requirement-sample-header">
            <div class="requirement-sample-heading">
                <span class="requirement-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h1Z"></path><path d="M14 3v5h5M8 13h8M8 17h5"></path></svg>
                </span>
                <span>
                    <h3 id="requirementSampleTitle" data-guide-title>Sample Guide</h3>
                    <p id="requirementSampleDescription" data-guide-description></p>
                </span>
            </div>
            <button type="button" class="requirement-sample-close" data-guide-close aria-label="Close sample guide">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </header>
        <div class="requirement-sample-body">
            <div class="requirement-sample-examples">
                <section class="requirement-sample-example">
                    <h4><span aria-hidden="true">&#10003;</span> Correct Example</h4>
                    <img data-guide-correct-image alt="">
                    <p data-guide-correct-caption></p>
                </section>
                <section class="requirement-sample-example is-wrong">
                    <h4><span aria-hidden="true">&times;</span> Do Not Upload</h4>
                    <img data-guide-wrong-image alt="">
                    <p data-guide-wrong-caption></p>
                </section>
            </div>
            <aside class="requirement-sample-reminders">
                <span aria-hidden="true">i</span>
                <div>
                    <strong>Additional Reminders</strong>
                    <p data-guide-reminder></p>
                    <p data-guide-file-info></p>
                </div>
            </aside>
        </div>
    </div>
</dialog>
<script>
    (function () {
        const sampleGuideDialog = document.getElementById('requirementSampleDialog');
        let activeSampleGuideButton = null;

        const setGuideText = (selector, value) => {
            const target = sampleGuideDialog?.querySelector(selector);
            if (target) target.textContent = value || '';
        };

        const setGuideImage = (selector, source, alt) => {
            const image = sampleGuideDialog?.querySelector(selector);
            if (!image) return;
            if (source) image.src = source;
            else image.removeAttribute('src');
            image.alt = alt || '';
        };

        document.querySelectorAll('[data-file-picker]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.filePicker);
                if (input && !input.disabled) input.click();
            });
        });

        document.querySelectorAll('[data-upload-dropzone]').forEach((dropzone) => {
            const input = document.getElementById(dropzone.dataset.inputId);
            if (!input) return;

            dropzone.addEventListener('dragover', (event) => {
                if (input.disabled) return;
                event.preventDefault();
                dropzone.classList.add('is-dragging');
                if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
            });
            dropzone.addEventListener('dragleave', (event) => {
                if (!dropzone.contains(event.relatedTarget)) dropzone.classList.remove('is-dragging');
            });
            dropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragging');
                if (input.disabled || !event.dataTransfer?.files?.length) return;

                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(event.dataTransfer.files[0]);
                    input.files = transfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (error) {
                    const errorMessage = dropzone.querySelector('[data-upload-error]');
                    if (errorMessage) errorMessage.textContent = 'Could not attach the dropped file. Choose it instead.';
                }
            });
        });

        document.querySelectorAll('[data-sample-guide]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!sampleGuideDialog?.showModal) return;
                activeSampleGuideButton = button;
                setGuideText('[data-guide-title]', button.dataset.guideTitle);
                setGuideText('[data-guide-description]', button.dataset.guideDescription);
                setGuideText('[data-guide-correct-caption]', button.dataset.guideCorrectCaption);
                setGuideText('[data-guide-wrong-caption]', button.dataset.guideWrongCaption);
                setGuideText('[data-guide-reminder]', button.dataset.guideReminder);
                setGuideText('[data-guide-file-info]', button.dataset.guideFileInfo);
                setGuideImage('[data-guide-correct-image]', button.dataset.guideCorrectImage, button.dataset.guideCorrectAlt);
                setGuideImage('[data-guide-wrong-image]', button.dataset.guideWrongImage, button.dataset.guideWrongAlt);
                sampleGuideDialog.querySelector('.requirement-sample-examples').hidden = !button.dataset.guideCorrectImage || !button.dataset.guideWrongImage;
                sampleGuideDialog.showModal();
            });
        });

        sampleGuideDialog?.querySelector('[data-guide-close]')?.addEventListener('click', () => sampleGuideDialog.close());
        sampleGuideDialog?.addEventListener('click', (event) => {
            if (event.target === sampleGuideDialog) sampleGuideDialog.close();
        });
        sampleGuideDialog?.addEventListener('close', () => activeSampleGuideButton?.focus());

        document.addEventListener('invalid', (event) => {
            if (!event.target.matches?.('.requirement-file-input')) return;
            const card = event.target.closest('.requirement-card');
            card?.classList.add('has-invalid-file');
            const picker = card?.querySelector('[data-file-picker]');
            window.setTimeout(() => picker?.focus(), 0);
        }, true);

        document.querySelectorAll('.requirement-file-input').forEach((input) => {
            input.addEventListener('change', () => input.closest('.requirement-card')?.classList.remove('has-invalid-file'));
        });
    })();
</script>

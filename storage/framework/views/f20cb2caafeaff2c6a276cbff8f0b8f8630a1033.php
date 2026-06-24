<style>
    .voice-field-wrap {
        position: relative;
        display: block;
        width: 100%;
    }

    .voice-field-wrap input[type="text"],
    .voice-field-wrap input[type="email"],
    .voice-field-wrap input[type="tel"],
    .voice-field-wrap input[type="number"],
    .voice-field-wrap input[type="search"],
    .voice-field-wrap input:not([type]),
    .voice-field-wrap textarea {
        padding-right: 44px;
    }

    .voice-field-inline-mic {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        width: 26px;
        height: 26px;
        border: none;
        border-radius: 999px;
        background: rgba(139, 0, 0, 0.5);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, opacity 0.2s ease, transform 0.2s ease;
        padding: 0;
        opacity: 0.5;
        pointer-events: auto;
    }

    .voice-field-inline-mic svg {
        width: 13px;
        height: 13px;
        fill: currentColor;
    }

    .voice-field-inline-mic:hover,
    .voice-field-inline-mic:focus-visible {
        background: rgba(139, 0, 0, 0.75);
        opacity: 1;
        outline: none;
    }

    .voice-field-inline-mic.listening {
        background: rgba(21, 128, 61, 0.85);
        opacity: 1;
    }

    .voice-field-inline-mic:disabled {
        cursor: not-allowed;
        opacity: 0.25;
    }

    .voice-field-wrap textarea + .voice-field-inline-mic {
        top: 10px;
        transform: none;
    }
</style>

<script>
    (function () {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            return;
        }

        const supportedSelector = [
            'textarea',
            'input[type="text"]',
            'input[type="email"]',
            'input[type="tel"]',
            'input[type="number"]',
            'input[type="search"]',
            'input:not([type])'
        ].join(',');

        const recognition = new SpeechRecognition();
        recognition.lang = navigator.language || document.documentElement.lang || 'en-US';
        recognition.interimResults = true;
        recognition.continuous = false;
        recognition.maxAlternatives = 1;

        let activeField = null;
        let activeButton = null;
        let isListening = false;
        let lastTranscript = '';

        function setActiveWrapper(field) {
            document.querySelectorAll('.voice-field-wrap.active').forEach(function (wrapper) {
                if (!field || !wrapper.contains(field)) {
                    wrapper.classList.remove('active');
                }
            });

            if (field) {
                const wrapper = field.closest('.voice-field-wrap');
                if (wrapper) {
                    wrapper.classList.add('active');
                }
            }
        }

        function isEligibleField(field) {
            return !!field && field.matches(supportedSelector) && !field.readOnly && !field.disabled;
        }

        function micIconSvg() {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15a3 3 0 0 0 3-3V7a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3zm5-3a1 1 0 1 1 2 0 7 7 0 0 1-6 6.92V21h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3v-2.08A7 7 0 0 1 5 12a1 1 0 1 1 2 0 5 5 0 1 0 10 0z"/></svg>';
        }

        function updateButtonState(button, listening) {
            if (!button) {
                return;
            }

            button.classList.toggle('listening', listening);
            button.setAttribute('aria-label', listening ? 'Stop voice input' : 'Use voice input');
        }

        function convertSpokenDigits(text) {
            const digitMap = {
                'zero': '0',
                'oh': '0',
                'one': '1',
                'two': '2',
                'three': '3',
                'four': '4',
                'five': '5',
                'six': '6',
                'seven': '7',
                'eight': '8',
                'nine': '9'
            };

            return text
                .toLowerCase()
                .replace(/[-,]/g, ' ')
                .split(/\s+/)
                .map(function (token) {
                    return digitMap[token] ?? token;
                })
                .join('');
        }

        function normalizeTranscript(text) {
            return String(text || '').trim();
        }

        function normalizeTranscriptForField(field, transcript) {
            const base = normalizeTranscript(transcript);
            if (!base) {
                return '';
            }

            const fieldName = (field.name || '').toLowerCase();
            const fieldType = (field.type || '').toLowerCase();
            const looksNumeric = fieldType === 'number' || fieldType === 'tel' || fieldName.includes('contact') || fieldName.includes('phone');

            if (!looksNumeric) {
                return base;
            }

            const numericText = convertSpokenDigits(base).replace(/[^\d]/g, '');
            return numericText || base;
        }

        function appendTranscript(field, transcript) {
            const normalizedTranscript = normalizeTranscriptForField(field, transcript);
            if (!normalizedTranscript) {
                return;
            }

            const start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
            const end = typeof field.selectionEnd === 'number' ? field.selectionEnd : field.value.length;
            const currentValue = field.value || '';
            const spacer = normalizedTranscript && currentValue && start > 0 && !/\s$/.test(currentValue.slice(0, start)) ? ' ' : '';

            field.value = currentValue.slice(0, start) + spacer + normalizedTranscript + currentValue.slice(end);
            const caretPosition = (currentValue.slice(0, start) + spacer + normalizedTranscript).length;

            if (typeof field.setSelectionRange === 'function') {
                field.setSelectionRange(caretPosition, caretPosition);
            }

            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function ensureMicForField(field) {
            if (!isEligibleField(field) || field.dataset.voiceMicReady === 'true') {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'voice-field-wrap';
            field.parentNode.insertBefore(wrapper, field);
            wrapper.appendChild(field);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'voice-field-inline-mic';
            button.innerHTML = micIconSvg();
            button.setAttribute('aria-label', 'Use voice input');
            button.title = 'Use voice input';

            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            button.addEventListener('click', function (event) {
                event.preventDefault();

                if (isListening && activeButton === button) {
                    recognition.stop();
                    return;
                }

                activeField = field;
                activeButton = button;
                setActiveWrapper(field);
                field.focus({ preventScroll: true });

                try {
                    recognition.start();
                } catch (error) {
                    console.warn('Voice recognition could not start.', error);
                }
            });

            wrapper.appendChild(button);
            field.dataset.voiceMicReady = 'true';
        }

        function initializeVoiceFields(root) {
            const scope = root && typeof root.querySelectorAll === 'function' ? root : document;
            scope.querySelectorAll(supportedSelector).forEach(ensureMicForField);
        }

        function handleFieldActivation(field) {
            if (!field || !field.matches(supportedSelector)) {
                return;
            }

            if (!field.dataset.voiceMicReady && isEligibleField(field)) {
                ensureMicForField(field);
            }

            if (!isEligibleField(field)) {
                setActiveWrapper(null);
                return;
            }

            activeField = field;
            activeButton = activeField.closest('.voice-field-wrap')?.querySelector('.voice-field-inline-mic') ?? null;
            setActiveWrapper(activeField);
        }

        recognition.addEventListener('start', function () {
            isListening = true;
            lastTranscript = '';
            updateButtonState(activeButton, true);
        });

        recognition.addEventListener('end', function () {
            isListening = false;
            updateButtonState(activeButton, false);
        });

        recognition.addEventListener('result', function (event) {
            const result = event.results?.[event.resultIndex];
            const transcript = normalizeTranscript(result?.[0]?.transcript);
            if (transcript) {
                lastTranscript = transcript;
            }

            if (activeField && result?.isFinal && lastTranscript) {
                appendTranscript(activeField, lastTranscript);
                lastTranscript = '';
            }
        });

        recognition.addEventListener('error', function (event) {
            isListening = false;
            updateButtonState(activeButton, false);

            const messageMap = {
                'not-allowed': 'Microphone permission was denied. Please allow microphone access in your browser and try again.',
                'service-not-allowed': 'Voice input is blocked by the browser. Please allow microphone access and try again.',
                'no-speech': 'No speech was detected. Please try again and speak clearly.',
                'audio-capture': 'No microphone was found. Please connect a microphone and try again.'
            };

            window.alert(messageMap[event.error] || 'Voice input could not start. Please try again.');
        });

        document.addEventListener('focusin', function (event) {
            handleFieldActivation(event.target);
        });

        document.addEventListener('pointerdown', function (event) {
            const field = event.target.closest(supportedSelector);
            handleFieldActivation(field);
        });

        document.addEventListener('focusout', function (event) {
            const field = event.target;
            if (!field || !field.closest('.voice-field-wrap')) {
                return;
            }

            window.setTimeout(function () {
                const activeElement = document.activeElement;
                if (isEligibleField(activeElement)) {
                    activeField = activeElement;
                    activeButton = activeField.closest('.voice-field-wrap')?.querySelector('.voice-field-inline-mic') ?? null;
                    setActiveWrapper(activeField);
                    return;
                }

                if (!isListening) {
                    activeField = null;
                    activeButton = null;
                    setActiveWrapper(null);
                }
            }, 0);
        });

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function (node) {
                        if (!(node instanceof Element)) {
                            return;
                        }

                        if (node.matches?.(supportedSelector)) {
                            ensureMicForField(node);
                        }

                        initializeVoiceFields(node);
                    });
                }

                if (mutation.type === 'attributes' && mutation.target instanceof Element && mutation.target.matches(supportedSelector)) {
                    if (isEligibleField(mutation.target) && !mutation.target.dataset.voiceMicReady) {
                        ensureMicForField(mutation.target);
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['disabled', 'readonly']
        });

        document.addEventListener('DOMContentLoaded', function () {
            initializeVoiceFields();
        });
        initializeVoiceFields();
    })();
</script>
<?php /**PATH C:\xampp\htdocs\INNOVISON---PUPOCMS\resources\views/partials/student_voice_input_support.blade.php ENDPATH**/ ?>
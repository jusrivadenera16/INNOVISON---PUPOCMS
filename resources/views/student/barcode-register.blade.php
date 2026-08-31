@extends('layouts.student')

@section('title', 'Scan / Bio')

@push('styles')
<style>
    /* --- MAIN CARD CONTAINER --- */
    .barcode-card {
        max-width: 860px;
        margin: 50px auto;
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .page-intro {
        margin-bottom: 20px;
    }

    .page-intro h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #600000;
    }

    .page-intro p {
        margin: 6px 0 0;
        color: #6b7b7d;
        font-size: 14px;
        line-height: 1.5;
    }

    .scan-bio-grid {
        display: grid;
        grid-template-columns: 1.25fr 0.95fr;
        gap: 22px;
        align-items: start;
    }

    .scan-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .scan-section h3 {
        margin: 0 0 8px;
        font-size: 20px;
        font-weight: 800;
        color: #8B0000;
    }

    .scan-section-note {
        color: #64748b;
        font-size: 13px;
        margin: 0 0 18px;
        line-height: 1.5;
    }

    /* --- TYPOGRAPHY & HEADINGS --- */
    .barcode-card h2 {
        margin-bottom: 20px;
        font-weight: 800;
    }
    .barcode-card h2.not-registered { color: #8B0000; }
    .barcode-card h2.registered { color: #15803d; }
    
    .student-info-box {
        background: #f8fafc;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: left;
        border: 1px solid #e2e8f0;
    }
    .student-info-box p {
        margin: 5px 0;
        font-size: 14px;
        color: #334155;
    }

    /* --- INPUTS --- */
    .barcode-input {
        width: 100%;
        padding: 12px 15px;
        font-size: 18px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        margin-bottom: 15px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .barcode-input:focus {
        border-color: #8B0000;
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
    }
    .input-success {
        background: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
        color: #15803d !important;
    }

    /* --- BUTTONS --- */
    .btn-scan, .btn-submit, .btn-reset {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-scan { background: #f59e0b; color: #fff; }
    .btn-scan:hover { background: #d97706; }

    .btn-upload { background: #0f766e; color: #fff; }
    .btn-upload:hover { background: #0d5f58; }

    .btn-submit { background: #a31b1b; color: #fff; }
    .btn-submit:hover { background: #821515; }

    .btn-reset {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
        margin-top: 15px;
    }
    .btn-reset:hover { background: #ffe4e6; }

    /* --- SCANNER & MESSAGES --- */
    #reader {
        width: 100%;
        margin: 20px auto;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        display: none;
    }

    .submitted-message {
        background: #dcfce7;
        color: #15803d;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        margin-bottom: 15px;
        border: 1px solid #bbf7d0;
    }

    .field-error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #fecaca;
        font-size: 13px;
    }

    .scan-helper {
        color: #64748b;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .scan-status {
        display: none;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .scan-status.info {
        display: block;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .scan-status.success {
        display: block;
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .scan-status.error {
        display: block;
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .upload-input {
        display: none;
    }

    .biosync-card {
        text-align: left;
    }

    .biosync-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #fffbeb;
        color: #92400e;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .biosync-list {
        margin: 0;
        padding-left: 18px;
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
    }

    .biosync-list li + li {
        margin-top: 8px;
    }

    .biosync-panel {
        margin-top: 18px;
        padding: 16px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
    }

    .biosync-panel strong {
        display: block;
        margin-bottom: 8px;
        color: #1e293b;
    }

    @media (max-width: 860px) {
        .scan-bio-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="barcode-card">
   

    {{-- HEADER LOGIC --}}
    @if($user->barcode)
        <h2 class="registered">✅ Scan / Bio Linked</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Your clinic scan or bio identity is already linked to your account.</p>
    @else
        <h2 class="not-registered">Set Up Scan / Bio</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Scan your physical ID or use your bio-linked flow to connect it with your account.</p>
    @endif

    <div class="student-info-box">
        <p><strong>Student Name:</strong> {{ $user->name }}</p>
        <p><strong>Student Number:</strong> {{ $studentContext['student_number'] ?? $user->student_number ?? 'N/A' }}</p>
    </div>

    <div class="scan-bio-grid">
        <section class="scan-section" style="opacity: 0.8;">
    <h3>Barcode (Deprecated)</h3>
    <p class="scan-section-note" style="font-size: 0.9em; color: #666;">
        <em>Note: Barcode scanning is being phased out. We will be transitioning to the new OCR system for ID validation.</em>
    </p>

    <form method="POST" action="{{ route('barcode.store') }}">
        @csrf
        {{-- The input is now disabled so users cannot edit it --}}
        <input type="text" name="barcode" id="barcode" 
               class="barcode-input {{ $user->barcode ? 'input-success' : '' }}"
               readonly disabled 
               placeholder="No barcode found."
               value="{{ $user->barcode ?? '' }}">

        {{-- Interactive buttons are hidden because we only want a Read-Only view --}}
        @if(!$user->barcode)
            <p style="color: #999;">Barcode entry is currently disabled.</p>
        @endif
    </form>

    @if($user->barcode)
        {{-- Keep the Unlink option if you still want them to be able to clear their old data --}}
        <form method="POST" action="{{ route('barcode.reset') }}" onsubmit="return confirm('Are you sure you want to unlink this old barcode?')">
            @csrf
            <button type="submit" class="btn-reset">
                Unlink Barcode
            </button>
        </form>
    @endif
</section>

        <section class="scan-section biosync-card">
            <h3>BioSync</h3>
            <p class="scan-section-note">This section is reserved for biometric.</p>

            <div class="biosync-status">Pending Setup</div>

            

            <div class="biosync-panel">
                <strong>Current status</strong>
                BioSync is not yet connected in this portal. Barcode registration remains the active identity method for now.
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const startButton = document.getElementById('start-scan');
    const barcodeInput = document.getElementById('barcode');
    const readerDiv = document.getElementById('reader');
    const uploadInput = document.getElementById('barcode-image-input');
    const statusBox = document.getElementById('scan-status');
    const validateUrl = "{{ route('barcode.validate') }}";
    const csrfToken = "{{ csrf_token() }}";

    let scanner = null;
    let isScannerRunning = false;
    let isProcessing = false;

    const supportedFormats = window.Html5QrcodeSupportedFormats ? [
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.CODE_93,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.ITF,
        Html5QrcodeSupportedFormats.CODABAR,
        Html5QrcodeSupportedFormats.QR_CODE
    ] : [];

    const scannerConfig = {
        fps: 12,
        qrbox: { width: 400, height: 150 },
        aspectRatio: 1.777778
    };

    if (supportedFormats.length) {
        scannerConfig.formatsToSupport = supportedFormats;
    }

    function createScannerInstance() {
        if (supportedFormats.length) {
            return new Html5Qrcode('reader', { formatsToSupport: supportedFormats });
        }

        return new Html5Qrcode('reader');
    }

    function setStatus(message, type = 'info') {
        if (!statusBox) {
            return;
        }

        if (!message) {
            statusBox.textContent = '';
            statusBox.className = 'scan-status';
            return;
        }

        statusBox.textContent = message;
        statusBox.className = `scan-status ${type}`;
    }

    async function stopScanner() {
        if (!scanner || !isScannerRunning) {
            readerDiv.style.display = 'none';
            return;
        }

        try {
            await scanner.stop();
        } catch (error) {
            console.warn(error);
        } finally {
            isScannerRunning = false;
            readerDiv.style.display = 'none';
        }
    }

    async function validateScannedBarcode(decodedValue) {
        const barcode = (decodedValue || '').trim();
        if (!barcode) {
            barcodeInput.value = '';
            barcodeInput.classList.remove('input-success');
            setStatus('No readable barcode value was detected.', 'error');
            return;
        }

        setStatus('Validating barcode...', 'info');

        try {
            const response = await fetch(validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ barcode })
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload.valid) {
                throw new Error(payload.message || 'Barcode validation failed.');
            }

            const finalBarcode = (payload.barcode || barcode).trim();
            barcodeInput.value = finalBarcode;
            barcodeInput.classList.add('input-success');
            setStatus(payload.message || 'Barcode validated successfully.', 'success');
        } catch (error) {
            barcodeInput.value = '';
            barcodeInput.classList.remove('input-success');
            setStatus(error.message || 'Unable to validate barcode right now.', 'error');
        }
    }

    async function scanUploadedImage(file) {
        if (!file) {
            return;
        }

        await stopScanner();

        if (!scanner) {
            scanner = createScannerInstance();
        }

        readerDiv.style.display = 'block';
        setStatus('Reading uploaded image...', 'info');

        try {
            const decodedText = await scanner.scanFile(file, true);
            await validateScannedBarcode(decodedText);
        } catch (error) {
            barcodeInput.value = '';
            barcodeInput.classList.remove('input-success');
            setStatus('No readable 1D barcode found in the uploaded image. Try a clearer photo.', 'error');
        } finally {
            readerDiv.style.display = 'none';
            if (typeof scanner.clear === 'function') {
                try {
                    await scanner.clear();
                } catch (error) {
                    console.warn(error);
                }
            }
        }
    }

    if (startButton) {
        startButton.addEventListener('click', async function () {
            if (isScannerRunning || isProcessing) {
                return;
            }

            if (!scanner) {
                scanner = createScannerInstance();
            }

            try {
                readerDiv.style.display = 'block';
                setStatus('Opening camera scanner...', 'info');

                const cameras = await Html5Qrcode.getCameras();
                if (!cameras || !cameras.length) {
                    setStatus('No camera was found on this device.', 'error');
                    readerDiv.style.display = 'none';
                    return;
                }

                const preferredCamera = cameras.find((camera) => {
                    const label = (camera.label || '').toLowerCase();
                    return label.includes('back') || label.includes('rear') || label.includes('environment');
                }) || cameras[0];

                await scanner.start(
                    preferredCamera.id,
                    scannerConfig,
                    async (decodedText) => {
                        if (isProcessing) {
                            return;
                        }

                        isProcessing = true;
                        await stopScanner();
                        await validateScannedBarcode(decodedText);
                        isProcessing = false;
                    },
                    () => {}
                );

                isScannerRunning = true;
                setStatus('Scanner ready. Align the 1D barcode inside the frame.', 'info');
            } catch (error) {
                isScannerRunning = false;
                readerDiv.style.display = 'none';
                setStatus('Unable to start camera scanner. Check camera permission and try again.', 'error');
                console.error(error);
            }
        });
    }

    if (uploadInput) {
        uploadInput.addEventListener('change', async function (event) {
            const file = event.target.files && event.target.files[0];
            await scanUploadedImage(file);
            uploadInput.value = '';
        });
    }
</script>
@endpush

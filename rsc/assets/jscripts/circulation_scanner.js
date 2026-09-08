/**
 * Circulation Barcode Scanner Controller
 * Provides real-time camera scanning & photo capture fallback for 1D/2D barcodes.
 */

class CirculationScanner {
    constructor(options = {}) {
        this.pageType = options.pageType || 'charge'; // 'charge' or 'discharge'
        this.circulationMode = options.circulationMode || 'accessnum'; // 'accessnum' or 'isbnissn'
        this.currentTarget = options.defaultTarget || 'accessnum'; // 'patron_id' or 'accessnum'
        this.html5QrCode = null;
        this.isScanning = false;
        this.availableCameras = [];
        this.selectedCameraId = null;
        this.torchEnabled = false;
        this.autoSubmit = options.autoSubmit || false;
        this.audioCtx = null;

        this.initDOMElements();
        this.bindEvents();
    }

    initDOMElements() {
        this.modal = document.getElementById('circulationScanModal');
        this.readerContainer = document.getElementById('circulationScanReader');
        this.statusText = document.getElementById('circulationScanStatus');
        this.targetSelector = document.getElementById('circulationScanTarget');
        this.cameraSelect = document.getElementById('circulationCameraSelect');
        this.torchBtn = document.getElementById('circulationTorchBtn');
        this.closeBtn = document.getElementById('circulationScanClose');
        this.autoSubmitCheck = document.getElementById('circulationAutoSubmit');
        this.fileInput = document.getElementById('circulationFileInput');
        this.fileBtn = document.getElementById('circulationFileBtn');
        this.badgeFeedback = document.getElementById('circulationScanFeedback');
    }

    bindEvents() {
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeScanner());
        }

        if (this.targetSelector) {
            this.targetSelector.addEventListener('change', (e) => {
                this.currentTarget = e.target.value;
                this.updateTargetUI();
            });
        }

        if (this.cameraSelect) {
            this.cameraSelect.addEventListener('change', async (e) => {
                this.selectedCameraId = e.target.value;
                if (this.isScanning) {
                    await this.stopCamera();
                    await this.startCamera();
                }
            });
        }

        if (this.torchBtn) {
            this.torchBtn.addEventListener('click', () => this.toggleTorch());
        }

        if (this.autoSubmitCheck) {
            this.autoSubmitCheck.addEventListener('change', (e) => {
                this.autoSubmit = e.target.checked;
                try {
                    localStorage.setItem('circ_scanner_autosubmit_' + this.pageType, this.autoSubmit ? '1' : '0');
                } catch(err) {}
            });

            try {
                const savedPref = localStorage.getItem('circ_scanner_autosubmit_' + this.pageType);
                if (savedPref !== null) {
                    this.autoSubmit = savedPref === '1';
                    this.autoSubmitCheck.checked = this.autoSubmit;
                }
            } catch(err) {}
        }

        if (this.fileBtn && this.fileInput) {
            this.fileBtn.addEventListener('click', () => this.fileInput.click());
            this.fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isScanning) {
                this.closeScanner();
            }
        });
    }

    playBeep() {
        try {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, this.audioCtx.currentTime); // A5 note
            gain.gain.setValueAtTime(0.2, this.audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.12);
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.start();
            osc.stop(this.audioCtx.currentTime + 0.12);
        } catch (e) {
            // Audio context not allowed or supported
        }

        // Trigger vibration
        if (navigator.vibrate) {
            navigator.vibrate([70]);
        }
    }

    updateTargetUI() {
        if (!this.statusText) return;
        let targetLabel = 'Barcode / QR';
        if (this.currentTarget === 'patron_id' || this.currentTarget === 'pid') {
            targetLabel = 'Patron ID / IC';
        } else if (this.currentTarget === 'accessnum') {
            targetLabel = 'Item Barcode / Accession';
        }
        this.statusText.textContent = `Point camera at: ${targetLabel}`;
        if (this.targetSelector) {
            this.targetSelector.value = this.currentTarget;
        }
    }

    async openScanner(target = 'accessnum') {
        this.currentTarget = target;
        this.updateTargetUI();
        if (this.badgeFeedback) {
            this.badgeFeedback.style.display = 'none';
        }

        if (this.modal) {
            this.modal.classList.add('show');
            this.modal.style.display = 'flex';
        }

        await this.startCamera();
    }

    async closeScanner() {
        await this.stopCamera();
        if (this.modal) {
            this.modal.classList.remove('show');
            this.modal.style.display = 'none';
        }
    }

    async startCamera() {
        if (typeof Html5Qrcode === 'undefined') {
            this.showError('Scanner library not loaded. Please refresh the page.');
            return;
        }

        if (!this.html5QrCode) {
            this.html5QrCode = new Html5Qrcode('circulationScanReader');
        }

        try {
            if (this.availableCameras.length === 0) {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    this.availableCameras = devices;
                    if (this.cameraSelect) {
                        this.cameraSelect.innerHTML = '';
                        devices.forEach((device, index) => {
                            const opt = document.createElement('option');
                            opt.value = device.id;
                            opt.text = device.label || `Camera ${index + 1}`;
                            this.cameraSelect.appendChild(opt);
                        });
                        this.cameraSelect.style.display = devices.length > 1 ? 'inline-block' : 'none';
                    }
                    if (!this.selectedCameraId) {
                        // Prefer back / environment camera
                        const backCamera = devices.find(d => /back|rear|environment/i.test(d.label));
                        this.selectedCameraId = backCamera ? backCamera.id : devices[0].id;
                        if (this.cameraSelect) this.cameraSelect.value = this.selectedCameraId;
                    }
                }
            }

            const cameraConfig = this.selectedCameraId 
                ? { deviceId: { exact: this.selectedCameraId } }
                : { facingMode: 'environment' };

            const config = {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    // Wide scanning box suitable for 1D barcodes and QR codes
                    return {
                        width: Math.floor(viewfinderWidth * 0.85),
                        height: Math.floor(Math.min(minEdge * 0.7, 240))
                    };
                },
                aspectRatio: 1.333333,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.CODABAR,
                    Html5QrcodeSupportedFormats.ITF,
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.DATA_MATRIX
                ],
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            };

            await this.html5QrCode.start(
                cameraConfig,
                config,
                (decodedText, decodedResult) => this.onScanSuccess(decodedText, decodedResult),
                (errorMessage) => {
                    // Constant scan frame cycle - silent ignore
                }
            );

            this.isScanning = true;
            this.updateTorchSupport();

        } catch (err) {
            console.warn('Live camera stream error:', err);
            this.handleCameraError(err);
        }
    }

    async updateTorchSupport() {
        if (!this.torchBtn) return;
        try {
            const capabilities = this.html5QrCode.getRunningTrackCapabilities();
            if (capabilities && capabilities.torch) {
                this.torchBtn.style.display = 'inline-flex';
            } else {
                this.torchBtn.style.display = 'none';
            }
        } catch(e) {
            this.torchBtn.style.display = 'none';
        }
    }

    async toggleTorch() {
        if (!this.isScanning || !this.html5QrCode) return;
        try {
            this.torchEnabled = !this.torchEnabled;
            await this.html5QrCode.applyVideoConstraints({
                advanced: [{ torch: this.torchEnabled }]
            });
            this.torchBtn.classList.toggle('active', this.torchEnabled);
            this.torchBtn.innerHTML = this.torchEnabled 
                ? '<i class="fa-solid fa-lightbulb"></i> Torch ON' 
                : '<i class="fa-regular fa-lightbulb"></i> Torch OFF';
        } catch (err) {
            console.error('Torch toggle failed', err);
        }
    }

    async stopCamera() {
        if (this.html5QrCode && this.isScanning) {
            try {
                await this.html5QrCode.stop();
            } catch (err) {
                console.warn('Error stopping camera:', err);
            }
            this.isScanning = false;
            this.torchEnabled = false;
        }
    }

    handleCameraError(err) {
        let msg = 'Could not access camera.';
        const errStr = String(err).toLowerCase();
        if (errStr.includes('notallowederror') || errStr.includes('permission')) {
            msg = 'Camera permission was denied. Please allow camera permissions in your browser settings.';
        } else if (errStr.includes('notfounderror') || errStr.includes('devicesnotfound')) {
            msg = 'No camera found on this device.';
        } else if (errStr.includes('insecure') || window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            msg = 'Direct video streaming requires HTTPS on mobile browsers. Use the "Take Photo / File" button below to capture barcodes.';
        }
        this.showError(msg);
    }

    showError(msg) {
        if (this.statusText) {
            this.statusText.innerHTML = `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${msg}</span>`;
        }
    }

    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!this.html5QrCode) {
            this.html5QrCode = new Html5Qrcode('circulationScanReader');
        }

        try {
            if (this.isScanning) {
                await this.stopCamera();
            }
            if (this.statusText) {
                this.statusText.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i> Analyzing photo...`;
            }
            const decodedText = await this.html5QrCode.scanFile(file, true);
            this.onScanSuccess(decodedText);
        } catch (err) {
            this.showError('Could not detect barcode in uploaded image. Please try again.');
        } finally {
            event.target.value = '';
        }
    }

    onScanSuccess(decodedText, decodedResult) {
        let cleanText = String(decodedText || '').trim();
        if (!cleanText) return;

        this.playBeep();

        // If scanning accession number in standard mode and input is numeric, pad to 10 digits
        if (this.currentTarget === 'accessnum' && this.circulationMode === 'accessnum') {
            if (/^\d+$/.test(cleanText)) {
                cleanText = cleanText.padStart(10, '0');
            }
        }

        // Show feedback badge
        if (this.badgeFeedback) {
            this.badgeFeedback.textContent = `Scanned: ${cleanText}`;
            this.badgeFeedback.style.display = 'block';
        }

        // Target field population
        const targetInput = document.getElementById(this.currentTarget);
        if (targetInput) {
            targetInput.value = cleanText;
            // Dispatch input event for any listeners
            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            targetInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (this.pageType === 'charge') {
            this.handleChargeWorkflow(cleanText);
        } else if (this.pageType === 'discharge') {
            this.handleDischargeWorkflow(cleanText);
        } else if (this.pageType === 'paysearch') {
            this.handlePaysearchWorkflow(cleanText);
        }
    }

    handleChargeWorkflow(scannedValue) {
        const patronInput = document.getElementById('patron_id');
        const accessInput = document.getElementById('accessnum');
        const form = patronInput ? patronInput.closest('form') : null;

        if (this.currentTarget === 'patron_id') {
            // Patron card was scanned -> Switch target to item barcode
            setTimeout(() => {
                this.currentTarget = 'accessnum';
                this.updateTargetUI();
                if (accessInput) accessInput.focus();
            }, 600);
        } else {
            // Accession/ISBN was scanned
            setTimeout(() => {
                this.closeScanner();
                if (accessInput) accessInput.focus();

                // If patron ID is already filled and auto-submit is enabled, submit!
                if (this.autoSubmit && patronInput && patronInput.value.trim() !== '' && accessInput && accessInput.value.trim() !== '') {
                    if (form) {
                        form.submit();
                    }
                }
            }, 500);
        }
    }

    handleDischargeWorkflow(scannedValue) {
        const accessInput = document.getElementById('accessnum');
        const form = accessInput ? accessInput.closest('form') : null;

        setTimeout(() => {
            if (this.autoSubmit) {
                if (form && accessInput && accessInput.value.trim() !== '') {
                    form.submit();
                }
            } else {
                this.closeScanner();
                if (accessInput) accessInput.focus();
            }
        }, 500);
    }

    handlePaysearchWorkflow(scannedValue) {
        const pidInput = document.getElementById(this.currentTarget) || document.getElementById('pid');
        const form = pidInput ? pidInput.closest('form') : null;

        setTimeout(() => {
            if (this.autoSubmit && form && pidInput && pidInput.value.trim() !== '') {
                form.submit();
            } else {
                this.closeScanner();
                if (pidInput) pidInput.focus();
            }
        }, 500);
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Scanner - Alchy Enterprises</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        #reader {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.3);
        }
        #reader__dashboard_section_swaplink {
            display: none !important;
        }
        #html5-qrcode-button-camera-stop,
        #html5-qrcode-button-camera-start {
            background: #3b82f6 !important;
            color: white !important;
            border: none !important;
            padding: 12px 24px !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s !important;
        }
        #html5-qrcode-button-camera-stop:hover,
        #html5-qrcode-button-camera-start:hover {
            background: #2563eb !important;
            transform: scale(1.05);
        }
        .scanner-frame {
            position: relative;
            background: #101828;
            border-radius: 1.5rem;
            padding: 1.5rem;
            border: 2px solid #1B2537;
        }
        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen p-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">📱 Receipt Scanner</h1>
                <p class="text-gray-400">Scan QR codes from printed receipts to verify authenticity</p>
            </div>

            <!-- Scanner Container -->
            <div class="scanner-frame mb-8">
                <!-- Status Display -->
                <div id="status-display" class="mb-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 hidden">
                    <div class="flex items-center gap-3">
                        <div class="pulse-ring">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <p id="status-text" class="text-blue-300 font-medium"></p>
                    </div>
                </div>

                <!-- Scanner Area -->
                <div id="reader" class="mb-6 bg-black"></div>

                <!-- Instructions -->
                <div class="bg-[#0d1829] rounded-xl p-6 border border-[#1B2537]">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        How to Use
                    </h3>
                    <ol class="space-y-3 text-gray-300">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                            <span>Click "Start Scanning" or allow camera access when prompted</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                            <span>Position the QR code from your printed receipt within the camera frame</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                            <span>Hold steady until the QR code is detected and scanned</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                            <span>You'll be automatically redirected to the verification result</span>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Manual Entry Option -->
            <div class="bg-[#101828] rounded-2xl p-6 border border-[#1B2537] mb-8">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Manual Verification
                </h3>
                <p class="text-gray-400 mb-4 text-sm">If your camera isn't working or you prefer manual entry, paste the verification URL or hash below:</p>
                
                <form id="manual-form" class="space-y-4">
                    <div>
                        <input 
                            type="text" 
                            id="manual-input" 
                            placeholder="Paste verification URL or hash here..." 
                            class="w-full px-4 py-3 bg-[#0d1829] border border-[#1B2537] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all"
                        >
                    </div>
                    <button 
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-all hover:scale-105"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verify Receipt
                    </button>
                </form>
            </div>

            <!-- Security Notice -->
            <div class="bg-gradient-to-r from-emerald-500/10 to-green-500/10 rounded-2xl p-6 border border-emerald-500/30">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-emerald-400 mb-2">🔒 Privacy & Security</h3>
                        <p class="text-gray-300 text-sm">Your camera stream is processed locally in your browser. No video or images are uploaded to our servers. We only verify the QR code content against our secure database.</p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center mt-8">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                @else
                    <a href="/" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Home
                    </a>
                @endauth
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p>© {{ date('Y') }} Alchy Enterprises Inc. • Powered by HTML5 QR Code Scanner</p>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode;
        const statusDisplay = document.getElementById('status-display');
        const statusText = document.getElementById('status-text');

        function showStatus(message, type = 'info') {
            statusDisplay.classList.remove('hidden', 'bg-blue-500/10', 'border-blue-500/30', 'bg-red-500/10', 'border-red-500/30', 'bg-emerald-500/10', 'border-emerald-500/30');
            
            if (type === 'error') {
                statusDisplay.classList.add('bg-red-500/10', 'border-red-500/30');
            } else if (type === 'success') {
                statusDisplay.classList.add('bg-emerald-500/10', 'border-emerald-500/30');
            } else {
                statusDisplay.classList.add('bg-blue-500/10', 'border-blue-500/30');
            }
            
            statusText.textContent = message;
            statusDisplay.classList.remove('hidden');
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`QR Code detected: ${decodedText}`);
            showStatus('✓ QR Code detected! Verifying receipt...', 'success');
            
            // Stop scanning
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    // Redirect to verification page
                    window.location.href = decodedText;
                }).catch(err => {
                    console.error('Error stopping scanner:', err);
                    window.location.href = decodedText;
                });
            } else {
                window.location.href = decodedText;
            }
        }

        function onScanError(errorMessage) {
            // Ignore routine scanning errors (occurs when no QR code is in view)
            // Only log severe errors
            if (!errorMessage.includes('No MultiFormat Readers')) {
                console.debug('Scan error:', errorMessage);
            }
        }

        // Initialize scanner
        try {
            html5QrCode = new Html5Qrcode("reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 300, height: 300 },
                aspectRatio: 1.0,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            // Start scanning with back camera
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    showStatus('📷 Camera ready! Point at QR code to scan...', 'info');
                    
                    // Prefer back camera on mobile
                    const cameraId = devices.length > 1 ? devices[1].id : devices[0].id;
                    
                    html5QrCode.start(
                        { facingMode: "environment" }, // Use back camera
                        config,
                        onScanSuccess,
                        onScanError
                    ).catch(err => {
                        showStatus('⚠️ Camera access denied. Please allow camera permission or use manual entry below.', 'error');
                        console.error('Camera start error:', err);
                    });
                } else {
                    showStatus('❌ No camera found. Please use manual entry below.', 'error');
                }
            }).catch(err => {
                showStatus('⚠️ Unable to access camera. Please use manual entry below.', 'error');
                console.error('Camera detection error:', err);
            });
        } catch (error) {
            showStatus('⚠️ Scanner initialization failed. Please use manual entry below.', 'error');
            console.error('Scanner init error:', error);
        }

        // Manual form submission
        document.getElementById('manual-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('manual-input').value.trim();
            
            if (!input) {
                showStatus('⚠️ Please enter a verification URL or hash', 'error');
                return;
            }

            // Check if it's a URL or just a hash
            if (input.startsWith('http')) {
                window.location.href = input;
            } else {
                // Assume it's just the hash
                window.location.href = `/verify-receipt/${input}`;
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrCode) {
                html5QrCode.stop().catch(err => console.error('Cleanup error:', err));
            }
        });
    </script>
</body>
</html>

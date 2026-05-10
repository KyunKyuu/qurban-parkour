<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Vouchers - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QR Code Library - Client Side -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0; /* Minimize margins for full page use */
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                margin: 0;
                padding: 10mm;
            }
            
            .no-print {
                display: none !important;
            }
            
            .voucher {
                page-break-inside: avoid;
            }
            
            /* Force page break after every 2 vouchers (A4 fits ~2 of 134mm height) */
            .voucher:nth-child(2n) {
                page-break-after: always;
            }
        }
        
        .voucher {
            width: 190mm;
            height: 134mm; /* Adjusted based on 1280x905px image ratio */
            position: relative;
            margin-bottom: 5mm;
            background-image: url("{{ asset('/images/voucher/utama.jpeg') }}?v={{ time() }}");
            background-size: cover;
            background-repeat: no-repeat;
            /* Border optional if image defines edges, keeping usually helps cut guides */
            border: 1px dashed #ccc; 
            overflow: hidden;
        }
        
        .qr-section {
            position: absolute;
            bottom: 4mm;
            right: 4mm;
            width: 32mm; /* Increased width slightly */
            height: auto;
            min-height: 28mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 2mm;
            border-radius: 4px;
        }
        
        .qr-code {
            width: 100%;
            height: 100%;
        }

        .qr-code img, .qr-code canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <!-- Print Button (Hidden when printing) -->
    <div class="no-print max-w-4xl mx-auto mb-4 bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Print Preview - {{ $vouchers->count() }} Voucher</h1>
            <p class="text-sm text-gray-600">Pastikan background graphics diaktifkan saat print.</p>
        </div>
        <div class="space-x-2">
            <button onclick="window.print()" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 font-medium">
                🖨️ Print
            </button>
            <a href="{{ route('admin.vouchers.print') }}" class="inline-block bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 font-medium">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Vouchers Container -->
    <div class="max-w-[210mm] mx-auto space-y-4">
        @foreach($vouchers as $index => $voucher)
            <div class="voucher">
                <!-- QR Code Section Only -->
                <div class="qr-section">
                    <div class="qr-code" id="qr-{{ $index }}" data-url="{{ rtrim(config('app.url'), '/') }}/claim/{{ $voucher->code }}"></div>
                    <div class="text-[8px] text-center font-bold leading-tight mt-1">
                        {{ parse_url(config('app.url'), PHP_URL_HOST) }}<br>/claim/{{ $voucher->code }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Generate QR codes using JavaScript (client-side)
        document.addEventListener('DOMContentLoaded', function() {
            const qrElements = document.querySelectorAll('[id^="qr-"]');
            
            if (qrElements.length === 0) return;
            
            qrElements.forEach(function(element) {
                const url = element.getAttribute('data-url');
                try {
                    new QRCode(element, {
                        text: url,
                        width: 128,
                        height: 128,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } catch (error) {
                    console.error('Error generating QR:', error);
                }
            });
        });
    </script>
</body>
</html>

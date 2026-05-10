<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Voucher {{ $voucher->code }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
        
        .voucher-container {
            position: relative;
            width: 297mm;
            height: 210mm;
            background-image: url("{{ public_path('images/voucher/utama.jpeg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .qr-section {
            position: absolute;
            bottom: 8mm; /* Stick to bottom right, scaled relative to A4 */
            right: 8mm;
            width: 45mm; /* Scaled up from the A5 version */
            height: auto;
            min-height: 40mm;
            background: rgba(255, 255, 255, 0.95);
            padding: 3mm;
            border-radius: 6px;
            text-align: center;
        }

        .qr-code {
            width: 100%;
            margin-bottom: 2mm;
        }


        .qr-text {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="voucher-container">
        <!-- QR Section -->
        <div class="qr-section">
            <img src="{{ $voucher->qr_png }}" class="qr-code" alt="QR Code">
            <div class="qr-text">
                {{ parse_url(config('app.url'), PHP_URL_HOST) }}<br>
                /claim/{{ $voucher->code }}
            </div>
        </div>
    </div>
</body>
</html>

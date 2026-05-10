<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            color: #1f2937;
        }

        .sheet {
            width: 100%;
            height: 100%;
            padding: 42px 54px;
            box-sizing: border-box;
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, 0.18), transparent 32%),
                radial-gradient(circle at bottom left, rgba(22, 163, 74, 0.18), transparent 26%),
                linear-gradient(145deg, #fffdf7, #f5efe1);
            border: 12px solid #155e3b;
            position: relative;
        }

        .frame {
            border: 2px solid #c89f2d;
            height: 100%;
            padding: 34px;
            box-sizing: border-box;
        }

        .arabesque {
            position: absolute;
            width: 110px;
            height: 110px;
            border: 2px solid rgba(200, 159, 45, 0.35);
            border-radius: 32px;
            transform: rotate(45deg);
        }

        .arabesque.top {
            top: 18px;
            right: 18px;
        }

        .arabesque.bottom {
            bottom: 18px;
            left: 18px;
        }

        .kicker {
            text-align: center;
            letter-spacing: 4px;
            font-size: 11px;
            color: #92400e;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .title {
            text-align: center;
            font-size: 34px;
            font-weight: 700;
            color: #14532d;
            margin: 0;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #4b5563;
            margin: 14px auto 26px;
            max-width: 620px;
            line-height: 1.6;
        }

        .recipient {
            text-align: center;
            margin: 26px 0 10px;
            font-size: 14px;
            color: #6b7280;
        }

        .name {
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
        }

        .statement {
            text-align: center;
            font-size: 14px;
            line-height: 1.8;
            color: #374151;
            margin: 0 auto 28px;
            max-width: 650px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .summary td {
            border: 1px solid rgba(148, 163, 184, 0.45);
            padding: 12px 14px;
            font-size: 13px;
        }

        .summary .label {
            width: 28%;
            background: rgba(21, 94, 59, 0.08);
            color: #14532d;
            font-weight: 700;
        }

        .footer {
            display: table;
            width: 100%;
            margin-top: 28px;
        }

        .footer-column {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .signature {
            text-align: right;
        }

        .signature-line {
            width: 210px;
            border-top: 1px solid #111827;
            margin: 44px 0 8px auto;
        }

        .small {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
        }

        .campaign {
            font-size: 16px;
            font-weight: 700;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="arabesque top"></div>
        <div class="arabesque bottom"></div>

        <div class="frame">
            <div class="kicker">{{ $campaignName }}</div>
            <h1 class="title">{{ $certificateTitle }}</h1>
            <p class="subtitle">{{ $certificateSubtitle }}</p>

            <p class="recipient">Diberikan kepada</p>
            <div class="name">{{ $claim->name }}</div>

            <p class="statement">
                Sebagai bentuk penghargaan atas partisipasi dan kepercayaan Anda dalam program
                <strong>{{ $campaignName }}</strong> melalui kategori
                <strong>{{ $claim->display_category_label }}</strong>.
                Semoga ikhtiar ini menjadi amal yang diterima, membawa keberkahan bagi penerima manfaat,
                serta menguatkan gerakan kebaikan di tengah umat dan komunitas.
            </p>

            <table class="summary">
                <tr>
                    <td class="label">Program</td>
                    <td>{{ $campaignName }}</td>
                </tr>
                <tr>
                    <td class="label">Partisipasi</td>
                    <td>{{ $claim->display_category_label }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Penerbitan</td>
                    <td>{{ optional($claim->certificate_generated_at ?? now())->format('d M Y H:i') }}</td>
                </tr>
            </table>

            <div class="footer">
                <div class="footer-column">
                    <div class="campaign">{{ $campaignName }}</div>
                    <div class="small">
                        Sertifikat penghargaan ini diterbitkan secara digital
                        sebagai bentuk apresiasi atas partisipasi Anda.
                    </div>
                </div>
                <div class="footer-column signature">
                    <div class="small">Koordinator Program</div>
                    <div class="signature-line"></div>
                    <div><strong>{{ config('qurban.default_pic_label') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PI Invoice Ready</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f5f7; color: #0f172a; margin: 0; padding: 0; }
        .container { max-width: 680px; margin: 0 auto; background: #ffffff; padding: 28px; }
        .brand { font-size: 16px; font-weight: bold; letter-spacing: 0.04em; color: #0f172a; }
        .title { font-size: 22px; font-weight: 700; margin: 16px 0 8px; color: #0f172a; }
        .text { font-size: 14px; line-height: 1.6; color: #334155; }
        .cta { display: inline-block; padding: 12px 18px; background: #0f172a; color: #ffffff; text-decoration: none; font-weight: 700; border-radius: 4px; font-size: 13px; }
        .muted { color: #64748b; font-size: 12px; }
        .divider { border-top: 1px solid #e2e8f0; margin: 20px 0; }
        .link { color: #0f172a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">{{ $siteName }}</div>
        <div class="title">Your PI Invoice Is Ready</div>
        <p class="text">Hello {{ $recipientName }},</p>
        <p class="text">
            Your Proforma Invoice (PI) for {{ $contextLabel }} #{{ $referenceNo }} has been prepared and is now available.
        </p>
        <p class="text">
            <a href="{{ $viewUrl }}" class="cta">View PI Invoice</a>
        </p>
        <p class="text">
            You can also download the PDF here:
            <a href="{{ $downloadUrl }}" class="link">{{ $downloadUrl }}</a>
        </p>
        @if($attachPdf)
            <p class="text">For your convenience, the PDF is also attached to this email.</p>
        @endif
        <div class="divider"></div>
        <p class="muted">
            If you have any questions, reply to this email or contact us at {{ $supportEmail }}.
        </p>
    </div>
</body>
</html>

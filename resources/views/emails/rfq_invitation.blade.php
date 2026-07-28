<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request for Quotation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px;">
    <h2>Request for Quotation ({{ $rfq->rfq_no }})</h2>

    <p>Dear <strong>{{ $vendor->shop_name }}</strong>,</p>

    <p>We kindly request your quotation for the items detailed in the attached RFQ document.</p>

    <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
        <p style="margin: 0;"><strong>RFQ Number:</strong> {{ $rfq->rfq_no }}</p>
        <p style="margin: 0;"><strong>Due Date:</strong> {{ $rfq->due_date ? $rfq->due_date->format('d M, Y') : 'As soon as possible' }}</p>
    </div>

    <p>Please reply to this email with your itemized prices, lead times, and terms.</p>

    <p>Best regards,<br>
    <strong>Procurement Team</strong><br>
    {{ config('app.name') }}</p>
</body>
</html>

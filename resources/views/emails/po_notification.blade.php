<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background: #6777ef; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Purchase Order Notice</h2>
        </div>
        <div class="content">
            <p>Dear {{ $vendor->shop_name }},</p>
            <p>We are pleased to issue Purchase Order <strong>{{ $po->po_no ?? ('PO-' . $po->id) }}</strong> to your company.</p>
            <p><strong>Total Value:</strong> {{ $po->currency ? $po->currency->symbol : 'kr.' }} {{ number_format($po->total_amount, 2) }}</p>
            <p>Please find the official Purchase Order document attached as a PDF to this email. Kindly acknowledge receipt and issue the Proforma Invoice (PI) accordingly.</p>
            <br>
            <p>Best regards,<br><strong>Procurement Team</strong><br>Copenhagen Tourist Point</p>
        </div>
        <div class="footer">
            <p>This is an automated procurement email notification.</p>
        </div>
    </div>
</body>
</html>

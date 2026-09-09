<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Payment Request</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 32px 28px; text-align: center; color: #ffffff; }
        .brand { font-size: 20px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; color: #38bdf8; }
        .invoice-badge { display: inline-block; background: rgba(255,255,255,0.15); padding: 4px 14px; border-radius: 20px; font-size: 13px; margin-top: 10px; font-weight: 600; color: #f1f5f9; }
        .body-content { padding: 32px 28px; }
        .greeting { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center; margin: 24px 0; }
        .amount-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #166534; letter-spacing: 0.05em; margin-bottom: 4px; }
        .amount-val { font-size: 30px; font-weight: 800; color: #15803d; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px; }
        .details-table td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .details-table td.label { color: #64748b; font-weight: 500; }
        .details-table td.val { text-align: right; font-weight: 700; color: #0f172a; }
        .cta-container { text-align: center; margin: 30px 0; }
        .btn-pay { display: inline-block; background: #0070ba; color: #ffffff !important; padding: 14px 36px; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,112,186,0.3); }
        .direct-link { font-size: 12px; color: #64748b; word-break: break-all; margin-top: 12px; }
        .footer { padding: 20px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">{{ $settings->site_name ?? 'B2B Viking ERP' }}</div>
            <div class="invoice-badge">Invoice #{{ $invoice->invoice_no }}</div>
        </div>

        <div class="body-content">
            <div class="greeting">Dear {{ $invoice->order?->user?->name ?? 'Valued Customer' }},</div>
            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 16px 0;">
                Please find your invoice details below. You can view the full itemized breakdown and securely pay online via PayPal Express or view bank transfer instructions by clicking the button below.
            </p>

            <div class="amount-box">
                <div class="amount-label">Outstanding Balance Due</div>
                <div class="amount-val">kr. {{ number_format($invoice->due_amount, 2) }}</div>
            </div>

            <table class="details-table">
                <tr>
                    <td class="label">Invoice Number</td>
                    <td class="val">{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <td class="label">Invoice Date</td>
                    <td class="val">{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</td>
                </tr>
                @if($invoice->due_date)
                <tr>
                    <td class="label">Payment Due Date</td>
                    <td class="val">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Total Invoice Amount</td>
                    <td class="val">kr. {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Amount Paid</td>
                    <td class="val" style="color: #16a34a;">kr. {{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
            </table>

            <div class="cta-container">
                <a href="{{ $paymentUrl }}" class="btn-pay" target="_blank">
                    💳 Pay Online with PayPal Express
                </a>
                <div class="direct-link">
                    Or copy this link to your browser:<br>
                    <a href="{{ $paymentUrl }}" style="color: #0284c7;">{{ $paymentUrl }}</a>
                </div>
            </div>
        </div>

        <div class="footer">
            If you have already settled this invoice, please disregard this notice.<br>
            {{ $settings->site_name ?? 'B2B Viking' }} &bull; {{ $settings->contact_email ?? '' }}
        </div>
    </div>
</body>
</html>

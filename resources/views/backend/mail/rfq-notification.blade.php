<!DOCTYPE html>
<html>
<head>
    <title>Request for Quotation</title>
</head>
<body>
    <h2>Request for Quotation: {{ $rfq->rfq_no }}</h2>
    
    <p>Dear Vendor,</p>
    
    <p>You have been invited to submit a quotation for the following items. Please provide your best prices by the due date.</p>
    
    <p><strong>Due Date:</strong> {{ $rfq->due_date ? $rfq->due_date->format('d M, Y') : 'N/A' }}</p>
    
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity Requested</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rfq->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p>Please reply to this email with your quotation attached, or contact our procurement team directly.</p>
    
    <p>Thank you,<br>
    B2BViking ERP Procurement Team</p>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Not Found — B2B Viking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 30px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card-box {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body>
    <div class="card-box">
        <div class="mb-3 text-warning">
            <i class="fas fa-barcode fa-3x"></i>
        </div>
        <h4 class="font-weight-bold text-dark mb-2">Barcode Not Recognized</h4>
        <p class="text-muted small mb-3">
            The scanned barcode code <strong class="font-monospace text-dark">[{{ $scannedCode }}]</strong> was not found in our catalog or logistics manifest.
        </p>
        <a href="{{ url('/') }}" class="btn btn-primary btn-sm px-4">
            <i class="fas fa-home mr-1"></i> Return to Store
        </a>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Print Bin Barcode</title>
    <style>
        body { font-family: monospace; text-align: center; margin: 0; padding: 20px; }
        .barcode-container { border: 1px solid #000; padding: 20px; display: inline-block; }
        .bin-name { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .barcode-code { font-size: 18px; margin-top: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="barcode-container">
        <div class="bin-name">{{ $warehouseBin->name }}</div>
        <div>
            <!-- We would typically use a library like DNS1D to render the actual barcode image here -->
            <!-- Example placeholder: -->
            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $warehouseBin->barcode }}&code=Code128&translate-esc=true" alt="Barcode" />
        </div>
        <div class="barcode-code">{{ $warehouseBin->barcode }}</div>
    </div>
</body>
</html>

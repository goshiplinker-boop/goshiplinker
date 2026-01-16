<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifest</title>
    <style>
        body {
            font-size: 10px;
            font-family: "source_sans_proregular", Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
        }

        .manifest-page {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-table img {
            display: block;
        }

        .header-table h5 {
            font-size: 14px;
            color: #333;
        }

        table {
            width: 100%;
        }

        th,td {
            text-align: left;
            font-size: 10px;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
            font-size: 12px;
            padding: 5px;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            padding: 5px;
        }

        .text-muted {
            font-size: 10px;
            color: #777;
        }

        .text-center{
            text-align: center;
        }

        .text-end{
            text-align: right;
        }

        .font-size-16{
            font-size:16px;
        }

        .powered-by{
            position:relative; 
            top:22px;
        }

        .barcode-align{
            padding:5px;
        }
    </style>
</head>
<body>
    @foreach ($allManifestData as $manifestData)
    <div class="manifest-page">
        <table class="header-table">
            <tr>
                <td width="50%">
                    <p><strong>Manifest ID:</strong> {{ $manifestData['id'] }}</p>
                    <p><strong>Courier Name:</strong> {{ $manifestData['courier_name'] }}</p>
                    <p><strong>Seller Name:</strong> {{ $manifestData['seller_name'] }}</p>
                    <p><strong>Pickup Location Name:</strong> {{ $manifestData['pickup_location_name'] }}</p>
                    <p><strong>Payment Mode:</strong> {{ $manifestData['payment_mode'] }}</p>
                </td>
                <td width="50%" style="text-align: right;">
                    <img src="{{ public_path('assets/images/companies/logo/'.$manifestData['company_logo']) }}" alt="Company Logo" style="max-height:70px;">
                    <div class="font-size-16">{{ $manifestData['seller_name'] }}</div>
                    <p>Manifest Date: {{ \Carbon\Carbon::now()->format('d-m-y H:i:s') }}</p>
                </td>
            </tr>
        </table>
        <!-- Orders Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Order Number</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Qty</th>
                    <th>Tracking Number</th>
                    <th>Product Name and SKU</th>
                    <th class="text-center">Tracking Barcode</th>
                </tr>
            </thead>
            <tbody>
                @php $orderCounter = 1; @endphp
                @foreach ($manifestData['orders'] as $order)
                <tr>
                    <td class="text-center">{{ $orderCounter++ }}</td>
                    <td>{{ $order['order_number'] }}</td>
                    <td class="text-center">{{ $order['currency_symbol'] }}{{ $order['amount'] }}</td>
                    <td class="text-center">{{ $order['qty'] }}</td>
                    <td>{{ $order['tracking_number'] }}</td>
                    <td>
                        @foreach ($order['products'] as $product)
                        {{ $product['name'] }} <br>
                        <span class="text-muted">SKU: {{ $product['sku'] }}</span><br>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <div class="barcode-align">
                            <img src="{{ $order['tracking_id_barcode'] }}" alt="barcode">
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-center"><b>Following details filled by field executive of {{ $manifestData['courier_name'] }}</b></p>
        <!-- Footer Section -->
        <table class="footer-table">
            <tr>
                <td width="50%">
                    <p><strong>FE Name:</strong> ______________________</p>
                    <br>
                    <p><strong>FE Phone:</strong> ______________________</p>
                    <br>
                    <p><strong>FE Signature:</strong> ______________________</p>
                </td>
                <td width="50%">
                    <p><strong>Pickup Time:</strong> ______________________</p>
                    <br>
                    <p><strong>Total Picked Items:</strong> ______________________</p>
                    <br>
                    <p><strong>Seller Signature:</strong> ______________________</p>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="text-muted">This is a system-generated document.</td> 
                <td class="text-end">
                    <p class="text-muted powered-by">Powered by</p>
                    <img src="{{ public_path('assets/images/logo/PM_Logo.png')}}" alt="Parcel Mind" style="max-height:40px;">
                </td>    
            </tr>    
        </table>    
    </div>
    @endforeach
</body>
</html>
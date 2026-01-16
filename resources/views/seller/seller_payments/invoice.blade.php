<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #000; border: 1px solid #f3f3f3ff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        th { background-color: #f5f5f5; text-align: left; }
        .no-border td, .no-border th { border: none !important; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .small { font-size: 12px; }
        .invoice-title { font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
@php
    $CompanyName = $CompanyName ?? 'UserFirst Labs';
    $CompanyBrand = $CompanyBrand ?? 'Parcelmind';
    $CompanyAddress = $CompanyAddress ?? "Parcelmind, DLF Cyber City, Gurugram, Haryana 122002, India";
    $CompanyEmail = $CompanyEmail ?? 'hello@parcelmind.com';
    $CompanyPhone = $CompanyPhone ?? '+91 9717585218';
    $CompanyGstin = $CompanyGstin ?? 'GSTIN-XXXXXXXXXX';
    $CompanyPan = $CompanyPan ?? 'PAN-XXXXX';

    // Seller (from subscription)
    $Seller = $subscription->company ?? null;

    // dates & amounts
    $invoiceDate = \Carbon\Carbon::parse($subscription->created_at)->format('d M Y');
    $expiryDate = isset($subscription->expiry_date) ? \Carbon\Carbon::parse($subscription->expiry_date)->format('d M Y') : '-';
    $paidAmount = floatval($subscription->paid_amount ?? 0);
    $taxPercent = floatval($subscription->tax_percent ?? 0); // set if applicable
    $isPdf = $isPdf ?? false; // set true when rendering PDF to use file:// paths
@endphp
<table class="invoice-box">
    <!-- Header with Logo and Invoice Info -->
    <tr class="no-border">
        <td style="width:60%; border:none;">
            <img src="{{ public_path('assets/images/logo/PM_Logo.png') }}"  alt=" " style="max-height:60px;">
        </td>
        <td style="width:40%; border:none;">
            <table style="width:100%; border:none;">
                <tr>
                    <td colspan="2" class="text-right invoice-title" style="border:none;">INVOICE</td>
                </tr>
                <tr>
                    <td style="border:none;"><strong>Invoice No:</strong></td>
                    <td class="text-right" style="border:none;">{{ $invoice_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="border:none;"><strong>Date:</strong></td>
                    <td class="text-right" style="border:none;">{{ $invoiceDate }}</td>
                </tr>
                <tr>
                    <td style="border:none;"><strong>Payment ID:</strong></td>
                    <td class="text-right" style="border:none;">{{ $subscription->payment_order_id ?? '-' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- From / To Section -->
<table style="margin-top:15px;">
    <tr>
        <th style="width:50%;">Invoice From</th>
        <th style="width:50%;">Billed To</th>
    </tr>
    <tr>
        <td>
            <strong>{{ $CompanyName }}</strong><br>
            <span class="small" style="white-space:pre-line;">{{ $CompanyAddress }}</span><br>
            <span class="small"><strong>Email:</strong> {{ $CompanyEmail }}</span><br>
            <span class="small"><strong>Phone:</strong> {{ $CompanyPhone }}</span><br>
            <span class="small"><strong>MSME:</strong> UDYAM-HR-05-0155423</span><br>
            <span class="small"><strong>GSTIN:</strong></span>
        </td>
        <td>
            @if($Seller)
                <strong>{{ $Seller->legal_registered_name ?? $Seller->brand_name ?? 'N/A' }}</strong><br>
                <span class="small" style="white-space:pre-line;">
                    {{ $Seller->registered_address ?? ($Seller->address_line_1 ?? 'N/A') }}
                    @if(isset($Seller->city) || isset($Seller->state) || isset($Seller->zip))
                        , {{ $Seller->city ?? '' }} {{ $Seller->state ?? '' }} {{ $Seller->zip ?? '' }}
                    @endif
                </span><br>
                <span class="small"><strong>Email:</strong> {{ $Seller->company_email_id ?? 'N/A' }}</span><br>
                <span class="small"><strong>Phone:</strong> {{ $Seller->company_phone ?? 'N/A' }}</span><br>
                <span class="small"><strong>GSTIN:</strong> {{ $Seller->gstin ?? 'N/A' }}</span>
            @else
                N/A
            @endif
        </td>
    </tr>
</table>

<!-- Subscription Details -->
<h3 style="margin-top:18px;">Subscription Details</h3>
<table>
    <thead>
        <tr>
            <th>Plan Name</th>
            <th>Monthly Price (₹)</th>
            <th>Credits</th>
            <th>Expiry Date</th>
            <th class="text-right">Total (₹)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $planName = $subscription->plan->name ?? 'N/A';
            $monthly = floatval($subscription->plan->price_per_month ?? 0);
            $setup = floatval($subscription->plan->setup_fee ?? 0);
            $credits = $subscription->total_credits ?? '-';
            $lineTotal = $monthly + $setup;
        @endphp
        <tr>
            <td>{{ $planName }}</td>
            <td>₹{{ number_format($monthly, 2) }}</td>
            <td>{{ $credits }}</td>
            <td>{{ $expiryDate }}</td>
            <td class="text-right">₹{{ number_format($lineTotal, 2) }}</td>
        </tr>
    </tbody>
</table>

<!-- Payment Summary -->
@php
    if($taxPercent > 0 && $paidAmount > 0) {
        $baseAmount = $paidAmount / (1 + ($taxPercent/100));
        $taxAmount = $paidAmount - $baseAmount;
    } else {
        $baseAmount = $lineTotal;
        $taxAmount = 0;
    }
@endphp

<table style="margin-top:20px; width:50%; float:right;">
    <tr>
        <th class="text-right" style="width:50%;">Subtotal:</th>
        <td class="text-right">₹{{ number_format($baseAmount, 2) }}</td>
    </tr>
    <tr>
        <th class="text-right">GST ({{ number_format($taxPercent, 2) }}%):</th>
        <td class="text-right">₹{{ number_format($taxAmount, 2) }}</td>
    </tr>
    <tr>
        <th class="text-right">Total Paid:</th>
        <td class="text-right"><strong>₹{{ number_format($paidAmount, 2) }}</strong></td>
    </tr>
</table>

<!-- Footer -->
<table style="" class="no-border">
    <tr>
        <td class="small" style="border:none;">
            System generated invoice; signature not required.<br>
            Thank you for your payment.
        </td>
    </tr>
</table>
</body>
</html>

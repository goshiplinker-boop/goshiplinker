<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Invoice Label</title>
      <style>
         body {
            font-size: 10px;
            font-family: "source_sans_proregular", Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            margin: 0;
            padding: 0;
         }
         table, .footer-notes{
            border-collapse: collapse;
            margin-left: -37px;
         }
         table, th, td {
            border: 1px solid #000;
         }
         th, td {
            padding: 2px;
            text-align: left;
         }
         small{
            font-size: 7px;
         }
         .text-left{
            text-align:left!important;
         }
         .text-right{
            text-align:right!important;
         }   
         .pm-store-logo-height {
            max-height: 50px;
         }
         .tax-inv-font {
            font-size: 15px;
         }
         .header-table {
            margin-top: -37px;
            border-bottom:hidden;
         }
         .header-table td, .details-table td{
         padding-left:5px;
         }   
         .details-table {
            border-bottom:hidden;
         }
         .details-table td {
            vertical-align: top;
         }
         .product-table th, .product-table td {
            text-align: center;
            font-size: 9px;
         }
      </style>         
   </head>
   <body>
      <!-- Header Section -->
      <table class="header-table" width="275">
         <tr>
            <td width="50%">
               <img src="{{ public_path('assets/images/channels/logos/' . $logo) }}" alt="Store Logo" class="pm-store-logo-height">
            </td>
            <td width="50%">
               <b class="tax-inv-font">TAX INVOICE</b><br>
               <span><b>Invoice No:</b> {{$orders->invoice_number}}</span><br>
               <span><b>Invoice Date:</b> {{$invoice_date}}</span><br>
               <span><b>Order No:</b> {{$orders->vendor_order_number}}</span><br>
               <span><b>Payment Mode:</b> {{$orders->payment_mode}}</span><br>
               <span><b>Place Of Supply:</b> {{$orders->company->state_code??""}}</span><br>
            </td>
         </tr>
      </table>

      <!-- Store and Billing Details -->
      <table class="details-table" width="275">
         <tr>
            <td width="50%">
               <b>STORE ADDRESS</b><br>
               @php
                  $locationParts = array_filter([
                     $orders->company->state_code,
                     $orders->company->country_code,
                     $orders->company->pincode
                  ]);
               @endphp
               {{$orders->company->brand_name}}<br>
               @if($orders->company->address)
                  {{ $orders->company->address }}
                   @if(count($locationParts)) 
                      ,
                  @endif
               @endif
                  @if(count($locationParts))
                  {{ implode(', ', $locationParts) }}
               @endif<br>
               {{$orders->company->company_email_id}}<br>
               GSTIN: {{$orders->company->company_gstin}}
            </td>
            <td width="50%">
               <b>BILLING ADDRESS</b><br>
               <b>{{ $orders->b_fullname }}</b><br>
               @php
                   $addressPart1 = array_filter([
                       $orders->b_city,
                       $orders->b_complete_address,
                       $orders->b_landmark,
                   ]);
                
                   $addressPart2 = array_filter([
                       $orders->b_state_code,
                       $orders->b_country_code,
                       $orders->b_zipcode,
                   ]);
               @endphp
               {{ implode(', ', $addressPart1) }}<br>
               {{ implode(', ', $addressPart2) }}
            </td>
         </tr>
      </table>

      <!-- Product Table -->
      <table class="product-table" width="275">
         <thead>
            <tr>
               <th>#</th>
               <th class="text-left">Product name</br><small>HSN</small></th>
               <th>Unit</br>Price</th>
               <th>Qty</th>
               <th>Discount</th>
               <th>Taxable</br>Amount</th>
               <th>Tax </br><small>(Value | %)</small></th>
               <th  class="text-right">Amount</th>   
            </tr>
         </thead>
         <tbody>
            @foreach ($orders->orderProducts as $k=>$product)
            <tr>
               <td>{{ $k+1 }}</td>
               <td class="text-left">{{ $product->product_name }}<br><small><b>HSN:</b>{{ $product->hsn}}</small></td>
               <td>{{ getCurrencySymbol($orders->currency_code) }}{{ $product->unit_price }}</td>
               <td>{{ $product->quantity }}</td>
               <td>{{ getCurrencySymbol($orders->currency_code) }}{{ $product->discount }}</td>
               <td style="text-align:right">{{ getCurrencySymbol($orders->currency_code) }}{{ $product->total_price }}</td> <!-- Calculate taxable value server-side if not present -->
               <td>{{ $product->tax_rate*100 }}%</td> <!-- IGST is missing; ensure it’s fetched correctly -->
               <td  class="text-right">{{ $product->total_price }}</td>
            </tr>
            @endforeach
            @foreach($orders->orderTotals as $total)
            <tr style="border:1px solid black" >
               <td colspan="7" class="text-right">
                  {{$total->title}}    
               </td>
               <td class="text-right">
                  {{ getCurrencySymbol($orders->currency_code) }}{{$total->value}}
               </td>
            </tr>
            @endforeach
            <tr>
               <td colspan="3"></td>
               <td colspan="5" class="text-center">@if (isset(auth()->guard('web')->user()->name)) {{auth()->guard('web')->user()->name }} @endif<br>@if($signature)<img src="{{ public_path('assets/images/companies/logo/' . $signature) }}" alt="Store Logo" class="pm-store-logo-height">@endif <br>Authorized Signature</td>
            </tr>  
         </tbody>
      </table>
      <!-- <div class="footer-notes">
         <small>Powered by <i>Parcelmind</i></small>  
      </div> -->
   </body>
</html>

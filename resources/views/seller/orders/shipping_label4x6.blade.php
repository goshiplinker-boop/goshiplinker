<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Shipping Label</title>
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
         .display-18{
         font-size: 18px; 
         }
         small{
         font-size: 7px;
         }
         .text-left{
         text-align:left!important;
         }
         .text-center{
         text-align:center!important;
         }      
         .text-right{
         text-align:right!important;
         }  
         .pm-barcode-height {
         max-height: 50px;
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
         .product-table .sc{
         text-align: right;
         }
         .d-none{display:none!important}
      </style>
   </head>
   <body>
      @if(!empty($settings['enable_logo']))
      <table class="header-table" width="275">
         <tr>
            <td class="text-center">
               <img src="{{ public_path('assets/images/channels/logos/' . $logo) }}" alt="Store Logo" class="pm-store-logo-height">
            </td>
         </tr>
      </table>
      @endif
      <table width="275" class="{{ empty($settings['enable_logo']) ? 'header-table' : '' }}">
         <tr>
            <td width="50%">
               <b>SHIP TO:</b><br>
               <b>{{$orders->s_fullname}}, {{ !empty($settings['hide_buyer_mobile']) ? 'xxxxxxx' : $orders->s_phone }}</b><br>
               {{$orders->s_complete_address}}, {{$orders->s_landmark}}<br>  
               {{$orders->s_city}}, {{$orders->s_state_code}}, {{$orders->s_zipcode}}
               @if($courier_location_code)
                  <br>
                  <b style="font-size:13px;">Route Code: {{$courier_location_code}}</b>
               @endif
            </td>
            <td width="50%" class="text-center">
               <b class="display-18">{{strtoupper($orders->payment_mode)}}</b><br>
              
               <b class="display-14">
                  @if (strtolower($orders->payment_mode) != 'prepaid')                    
                     @if (!empty($settings['hide_order_amount']))
                        xxxxxxx
                     @else
                        {{ getCurrencySymbol($orders->currency_code) . ($orders->order_total ?? '') }}
                     @endif
                  @endif

            </td>
         </tr>
      </table>
      <table class="details-table" width="275">
         <tr>
            <td width="50%">
               <b>ORDER DETAILS:</b><br>
               Order No: {{$orders->vendor_order_number}}<br>
               Order Date: {{$orders->channel_order_date}}<br>
               Weight(Kg): {{$orders->package_dead_weight}}<br>
               Dimension(cm): {{$orders->package_length}}x{{$orders->package_breadth}}x{{$orders->package_height}}
            </td>
            <td width="50%">
               @if(empty($settings['hide_return_address']))                  
                  <b>Shipper Details:</b><br>
                  {{ $orders->shipmentInfo->return_location_address }}
               @endif
            </td>

         </tr>
      </table>
      <table class="details-table" width="275">
         <tr>
            <td class="text-center">
               {{ $courierTitle }}
               <div>{{$orders->shipmentInfo->tracking_id  ?? 'N/A'}}</div>
               <img src="{{$tracking_id_barcode}}" alt="barcode" class="pm-barcode-height">
            </td>
         </tr>
         @if($order_id_barcode)
         <tr>
            <td class="text-center">
               <div>{{$orders->vendor_order_number  ?? 'N/A'}}</div>
               <img src="{{$order_id_barcode}}" alt="barcode" class="pm-barcode-height">
            </td>
         </tr>
         @endif
      </table>
      <table class="product-table" width="275">
         <thead>
            <tr>
               <th>#</th>
               <th class="text-left">Product Name<br>SKU</td>
               <th>Sale<br>Price</td>
               <th>Qty</td>
               <th class="text-right">Amount</td>
            </tr>
         </thead>
         <tbody>
            @foreach($orders->orderProducts as $k=> $product)
            <tr >
               <td>{{ $k+1}}</td>
               <td class="text-left">{{ !empty($settings['hide_product_name']) ? 'xxxxx' : $product->product_name }}
               <br>{{ !empty($settings['hide_product_sku']) ? 'xxxxx' : $product->sku }}</td>
               <td>{{ !empty($settings['hide_order_amount']) ? 'xxxxxxx' : getCurrencySymbol($orders->currency_code) . number_format($product->unit_price, 2) }}</td>
               <td>{{ !empty($settings['hide_order_amount']) ? 'xxxxxxx' : $product->quantity }} </td>
               <td class="text-right">{{ !empty($settings['hide_order_amount']) ? 'xxxxxxx' : getCurrencySymbol($orders->currency_code) .  number_format($product->total_price, 2) }}</td>
            </tr>
            @endforeach
            @foreach($orders->orderTotals as $total)
            <tr style="border:1px solid black" >
               <td colspan="4" style="text-align:right">
                  {{$total->title}}    
               </td>
               <td style="text-align:right">
               {{ !empty($settings['hide_order_amount']) ? 'xxxxxxx' : getCurrencySymbol($orders->currency_code) .  $total->value }}

               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
      @if(isset($settings['order_notes']) && !empty($settings['order_notes']) && !empty($notes))
         <div class="footer-notes">
            <br> <b>Order Notes:</b>
            {!! $notes !!}
         </div>
      @endif

      @if(!empty($settings['notes']))
      <div class="footer-notes">
         <br>
         {!! $settings['notes'] !!}
      </div>
      @endif
   </body>
</html>
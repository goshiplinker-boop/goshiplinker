<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Track Shipment</title>
        <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
        <link rel="preload" href="{{ asset(env('PUBLIC_ASSETS') . '/css/theme.min.css') }}" data-hs-appearance="default" as="style">
        <script>
            window.hs_config = {
                "themeAppearance": {
                    "layoutSkin": "default"
                }
            };
        </script>
        <style>
            /* Custom Styling */
            {{$manageTrackingPage->custom_style_script }}
        </style>
    </head>
   <body id="{{$manageTrackingPage->company_id }}">
      <main id="content" role="main" class="main">
         @if(isset($jsonData['announcement']))
         <a href="{{$jsonData['announcement_url']??''}}">
            <div class="alert alert-primary text-center" style="background-color:{{ $jsonData['theme_color']??'' }};" role="alert">{{$jsonData['announcement']}}</div>
         </a>
         @endif
         <div class="container pt-3">
            <div class="d-flex justify-content-center">
                <a href="{{ $jsonData['website_url'] }}">
                    <img src="{{!empty($jsonData['website_logo'])? asset(env('PUBLIC_ASSETS').'/images/tracking/logos/' . basename($jsonData['website_logo'])) : asset(env('PUBLIC_ASSETS') . '/svg/logos/logo.svg') }}"  alt="Image Description" style="width: 8rem;">
                </a>
            </div>
            <hr>
            <div class="content container-fluid p-0 mb-7">
               <div class="row justify-content-lg-center">
                  <div class="col-lg-10">
                     <div class="row">
                        <div class="col-lg-4">
                           <div class="card card-body mb-3 mb-lg-5">
                              <span>Current Status</span>
                              <h1 class="text-primary">{{$order->shipment_status??$order->status_name}}</h1>
                           </div>
                           <div class="card mb-3 mb-lg-5">
                              <div class="card-header card-header-content-between">
                                 <h4 class="card-header-title">Order Details</h4>
                              </div>
                              <div class="card-body">
                                 <table class="table">
                                    <thead class="thead-light">
                                       <tr>
                                          <th scope="col">Order Number</th>
                                          <th scope="col">{{$order->vendor_order_number}}</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <tr>
                                          <th scope="row">Order Date</th>
                                          <td>{{$order->channel_order_date}}</td>
                                       </tr>
                                       <tr>
                                          <th scope="row">Order Amount</th>
                                          <td>{{ getCurrencySymbol($order->currency_code) }}{{$order->order_total}}</td>
                                       </tr>
                                       <tr>
                                          <th scope="row">Payment Mode</th>
                                          <td>{{$order->payment_method??$order->payment_mode}}</td>
                                       </tr>
                                       @if($order->courier_title)
                                       <tr>
                                          <th scope="row">Courier Name</th>
                                          <td>{{$order->courier_title}}</td>
                                       </tr>
                                       @endif
                                       @if($order->tracking_id)
                                       <tr>
                                          <th scope="row">Tracking ID</th>
                                          <td>{{$order->tracking_id}}</td>
                                       </tr>
                                       @endif
                                       @if($order->package_count)
                                       <tr>
                                          <th scope="row">Package Count</th>
                                          <td>{{$order->package_count}}</td>
                                       </tr>
                                       @endif
                                       @if($order->edd && \Carbon\Carbon::parse($order->edd)->gte(\Carbon\Carbon::today()))
                                       <tr>
                                          <th scope="row">Expected Delivery Date</th>
                                          <td><span class="badge bg-primary rounded-pill">{{$order->edd}}</span></td>
                                       </tr>
                                       @endif
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-8">
                           <div class="card mb-3 mb-lg-5">
                              <div class="card-header card-header-content-between">
                                 <h4 class="card-header-title">Shipping activity stream</h4>
                              </div>
                              <div class="card-body card-body-height" style="height: 24rem;">
                                 <ul class="step step-icon-xs">
                                    @foreach($scans as $status_date => $scandetails)
                                       <li class="step-item">
                                          <div class="step-content-wrapper">
                                             <span class="step-divider">{{$status_date}}</span>
                                          </div>
                                       </li>
                                       @foreach($scandetails as $scan)
                                          <li class="step-item">
                                             <div class="step-content-wrapper">
                                                <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                                <div class="step-content">
                                                   <h5 class="mb-1">
                                                      <a class="text-dark" href="javascript:;">{{$scan['current_status']}}</a>
                                                   </h5>
                                                   <p class="fs-6 mb-0">{{$scan['current_time']}}, {{$scan['current_location']}}</p>
                                                </div>
                                             </div>
                                          </li>
                                       @endforeach
                                    @endforeach                                   
                                 </ul>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  @if(isset($jsonData['promotional_banner']))
                  <div class="col-lg-10 mb-5">
                     <a href="{{$jsonData['promotional_url']??''}}"><img class="img-fluid"  src="{{ asset(env('PUBLIC_ASSETS').'/images/tracking/banner/' . $jsonData['promotional_banner']) }}" ></a>
                  </div>
                  @endif
                  @if(isset($jsonData['youtube_video']))
                  <div class="col-lg-10">
                     <iframe style="width: 100%; height: 350px" src="{{$jsonData['youtube_video']}}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                  </div>
                  @endif
               </div>
            </div>
            <div class="row footer border-top border-bottom w-100 p-2">               
               <div class="col-md-10 mt-2">            
                     @if(isset($jsonData['support_email_address']) || (isset($jsonData['support_contact_number'])))
                     <b>@if(isset($jsonData['support_email_address'])) Support Email: {{ $jsonData['support_email_address'] }} @endif @if(isset($jsonData['support_contact_number'])) | Support Phone: {{$jsonData['support_contact_number']}} @endif</b>
                     @endif
               </div>
                <div class="col-md-2">
                    Powered By
                    <a href="https://parcelmind.com/"><img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}" alt="Logo" data-hs-theme-appearance="default"></a>
                </div>
            </div>
         </div>
      </main>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/theme.min.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/hs.theme-appearance.js') }}"></script>
   </body>
</html>
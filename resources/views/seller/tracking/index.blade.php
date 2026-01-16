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
<body id="{{ $manageTrackingPage->company_id }}">
    <main id="content" role="main" class="main">
        <div class="container pt-3">
            <div class="d-flex justify-content-center">
                <a href="{{ $jsonData['website_url'] }}">
                    <img src="{{!empty($jsonData['website_logo'])? asset(env('PUBLIC_ASSETS').'/images/tracking/logos/' . basename($jsonData['website_logo'])) : asset(env('PUBLIC_ASSETS') . '/svg/logos/logo.svg') }}"  alt="Image Description" style="width: 8rem;">
                </a>
            </div>
            <hr>
            <div class="mx-auto mb-5" style="max-width: 30rem;">
                <div class="card card-lg mb-2">
                    <div class="card-body">
                        <form class="js-validate needs-validation" novalidate action="{{route('shipment_history',$manageTrackingPage->website_domain)}}" method="POST">
                            @csrf
                            <div class="text-center">
                                <div class="mb-4">
                                    <h1 class="display-5">{{ $jsonData['heading_title'] ?? 'Track Your Order' }}</h1>
                                    <p>{{ $jsonData['heading_sub_title'] ?? 'Enter your Order ID or Tracking Number' }}</p>
                                </div>
                            </div>
                            <div class="mb-4">
                                @if(in_array('order_number',$jsonData['tracking_type']))
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="formInlineRadio1" class="form-check-input" name="tracking_type" value="order_number" checked>
                                        <label class="form-check-label" for="formInlineRadio1">Order Number</label>
                                    </div>
                                @endif
                                @if(in_array('tracking_number',$jsonData['tracking_type']))
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="formInlineRadio2" class="form-check-input" name="tracking_type" value="tracking_number" @if(!in_array('order_number',$jsonData['tracking_type'])) checked @endif>
                                        <label class="form-check-label" for="formInlineRadio2">Tracking Number</label>
                                    </div>
                                @endif
                            </div>
                            <div class="mb-4">
                                <input type="hidden" name="cid" value="{{ $manageTrackingPage->company_id }}">
                                <label class="form-label" for="trackingInput">Enter Number</label>
                                <input type="text" class="form-control form-control-lg" name="tracking_value" id="trackingInput" placeholder="Enter Number" value="{{ old('tracking_value','') }}" required>
                                <span class="invalid-feedback">Please enter a valid value.</span>
                                @if(session('error'))
                                <div class="text-danger">{{session('error')}}</div>
                                 @endif                            
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" style="background-color:{{ $jsonData['theme_color']??'' }};">
                                    Track Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row footer border-top border-bottom w-100 p-2">
                <div class="col-md-10 mt-2">            
                        @if(isset($jsonData['support_email_address']) || (isset($jsonData['support_contact_number'])))
                        <b>@if(isset($jsonData['support_email_address'])) Support Email: {{ $jsonData['support_email_address'] }} @endif @if(isset($jsonData['support_contact_number'])) |  Support Phone: {{$jsonData['support_contact_number']}} @endif</b>                                      
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
    <script>
        (function() {
            window.onload = function() {
                // INITIALIZATION OF BOOTSTRAP VALIDATION
                HSBsValidation.init('.js-validate', {
                    onSubmit: data => {
                        data.event.preventDefault();
                        const form = document.querySelector('.js-validate');
                        form.submit();
                    }
                });

                // INITIALIZATION OF TOGGLE PASSWORD
                new HSTogglePassword('.js-toggle-password');
            };
        })();
    </script>
</body>
</html>

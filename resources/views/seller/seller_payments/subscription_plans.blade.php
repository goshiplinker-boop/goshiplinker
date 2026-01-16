<x-layout>
   <x-slot name="title">Payment Plans</x-slot>
   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error'))
         <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif
      <meta name="csrf-token" content="{{ csrf_token() }}">
      @if(empty(session('subscription_plan')))
      <p class="text-center">
            <a href="{{route('trial_subscription')}}" class="btn btn-warning">Activate Your Trial Now – It’s Free!</a>                        
         @elseif(session('subscription_plan')=='trial' && session('subscription_status')=='1')
            <p class="lead">You have 7 days of free trial left, Checkout our premium plan <a href="{{ route('subscription_plans') }}">here</a></p>
      </p>
      @endif 
      <h2 class="card-header text-center p-1">Get more for less with our affordable plans</h2>
      <h6 class="card-header text-center">Discover our super-affordable plans and pricing options designed to help you save more while spending less.</h6>
      <div class="text-center">
         <ul class="nav nav-segment mb-2" role="tablist">
            @if(isset($plans[1]))
            <li class="nav-item">
               <a class="nav-link active" id="nav-1-eg1-tab" href="#nav-1-eg1" data-bs-toggle="pill" data-bs-target="#nav-1-eg1" role="tab" aria-controls="nav-1-eg1" aria-selected="true">Monthly</a>
            </li>
            @endif
            @if(isset($plans[3]))
            <li class="nav-item">
               <a class="nav-link" id="nav-3-eg1-tab" href="#nav-3-eg1" data-bs-toggle="pill" data-bs-target="#nav-3-eg1" role="tab" aria-controls="nav-3-eg1" aria-selected="true">3 Months</a>
            </li>
            @endif
            @if(isset($plans[6]))
            <li class="nav-item">
               <a class="nav-link" id="nav-6-eg1-tab" href="#nav-6-eg1" data-bs-toggle="pill" data-bs-target="#nav-6-eg1" role="tab" aria-controls="nav-6-eg1" aria-selected="false">6 Months</a>
            </li>
            @endif
            @if(isset($plans[12]))
            <li class="nav-item">
               <a class="nav-link" id="nav-12-eg1-tab" href="#nav-12-eg1" data-bs-toggle="pill" data-bs-target="#nav-12-eg1" role="tab" aria-controls="nav-12-eg1" aria-selected="false">Yearly</a>
            </li>
            @endif
         </ul>
      </div>
      @foreach($plans as $duration=>$plan)
      <div class="tab-content">
         <div class="tab-pane fade m-auto w-50 @if($duration==1) show active @endif" id="nav-{{$duration}}-eg1" role="tabpanel" aria-labelledby="nav-{{$duration}}-eg1-tab">
            <div class="row mb-3">
               @foreach($plan as $plan_duration)
                  <div class="col-md mb-3">
                     <div class="card card-lg form-check form-check-select-stretched h-100 zi-1">
                        <div class="card-header text-center p-2">
                           <span class="card-subtitle">{{$plan_duration['name']}}</span>
                           <h2 class="card-title display-3 text-dark">
                              @if($plan_duration['id']==1 || $plan_duration['id']==2)
                                 {{$plan_duration['name']}}
                              @else
                                 ₹<span id="pricingCount1" data-hs-toggle-switch-item-options='{
                                       "min": 22,
                                       "max": 32
                                       }'>@if($plan_duration['durations']['duration_months']==12) {{intval($plan_duration['durations']['total_amount']/12)}} @else {{intval($plan_duration['price_per_month'])}} @endif</span>
                                 <span class="fs-6 text-muted">/ mon</span> @if($plan_duration['durations']['duration_months']==1)<p class="fs-6" style="color:green;"> or @if($plan_duration['id']==3) ₹4692/year and save 8% @elseif($plan_duration['id']==4) ₹26622/year and save 10% @elseif($plan_duration['id']==5) ₹22800/year and save 5% @endif </p> @endif
                              @endif
                           </h2>
                           <p class="card-text">@if($plan_duration['id']==1 || $plan_duration['id']==2)Forever free @else ₹{{intval($plan_duration['durations']['total_amount'])}} for {{$duration}} months @endif</p>
                        </div>
                        <div class="card-body d-flex justify-content-center p-3">
                           <ul class="list-checked list-checked-primary mb-0">
                              <li class="list-checked-item">@if($plan_duration['sales_channels']>5) Unlimited @else {{$plan_duration['sales_channels']}} @endif Sales Channel</li>
                              <li class="list-checked-item">@if($plan_duration['couriers']>5) Unlimited @else {{$plan_duration['couriers']}}@endif  Courier</li>
                              <li class="list-checked-item">@if($plan_duration['pickup_locations']>5) Unlimited @else {{$plan_duration['pickup_locations']}} @endif  Pickup Location</li>
                              @if($plan_duration['support_type'])
                                 <li class="list-checked-item">{{$plan_duration['support_type']}}</li>
                              @endif
                              <li class="list-checked-item">{{$plan_duration['durations']['shipment_credits']}} Shipments Credits @if($plan_duration['id']==2) Monthly @endif</li>
                              @if($plan_duration['setup_fee'] > 0)
                              <li class="list-checked-item">₹{{intval($plan_duration['setup_fee'])}} Initial Setup Fees <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="One time fees for initial setup"></i></li>
                              @endif
                           </ul>
                        </div>
                        <div class="card-footer border-0 text-center p-2">
                           @if($plan_duration['id'] !=1 && $plan_duration['id'] !=2)
                              <div class="alert alert-light" role="alert">
                                 Total amount ₹{{intval($plan_duration['durations']['total_amount']+$plan_duration['setup_fee'])}} <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Total amount exclusive of all taxes(₹{{intval($plan_duration['durations']['total_amount'])}}+₹{{intval($plan_duration['setup_fee'])}}=₹{{intval($plan_duration['durations']['total_amount']+$plan_duration['setup_fee'])}})"></i>
                              </div>
                           @endif
                           <div class="d-grid mb-2">
                              @if($plan_duration['id']==1 || $plan_duration['id']==2)    
                                 @if ($plan_duration['id']==2 && !empty(session('subscription_plan')) && session('subscription_plan') =='Free')
                                    <button type="button" class="form-check-select-stretched-btn btn btn-success">@if($plan_duration['id']==2) Activated @endif</button>
                                     @else                        
                                    <button type="button" class="form-check-select-stretched-btn btn btn-primary"><a href="@if($plan_duration['id']==1) {{route('trial_subscription')}} @else {{route('free_subscription')}} @endif" class="text-white">Activate Now</a></button>
                                 @endif  
                              @else
                                 @if (!empty(session('subscription_status')) && session('subscription_plan') == $plan_duration['name'])
                                    <button type="button" class="form-check-select-stretched-btn btn btn-success">Activated</button>
                                 @else
                                    <button type="button" class="form-check-select-stretched-btn btn btn-primary {{ !$is_shopify_user ? 'razorpay' : '' }}"  plan_id="{{$plan_duration['id']}}" duration_months="{{$plan_duration['durations']['duration_months']}}" plan_name="{{$plan_duration['name']}}" plan_amount="{{intval($plan_duration['durations']['total_amount']+$plan_duration['setup_fee'])}}" @if($is_shopify_user) data-bs-toggle="modal" data-bs-target="#bd-example-modal-sm" @endif>Pay Now</button>
                                 @endif
                              
                              @endif
                           </div>
                           <p class="card-text small"><i class="bi bi-info-circle me-1"></i> Terms &amp; conditions apply</p>
                        </div>
                     </div>
                     <!-- End Card -->
                  </div>                 
               @endforeach                                                         
            </div>           
         </div>
      </div>
      @endforeach    
      <!-- Modal -->
      <div id="bd-example-modal-sm" class="modal fade bd-example-modal-sm" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
               <div class="modal-header">
               <h5 class="modal-title h4" id="mySmallModalLabel">Payment Option</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                  <div class="row">
                     <div class="col-sm-12">                     
                        <p id="modal-plan" modal_plan_id=""></p>
                        <p>Amount Rs  <span id="modal-amount"></span></p>
                        <p>Duration: <span id="modal-duration-months"></span> Months</p>
                        <!-- optgroup  select -->
                        <div class="tom-select-custom">
                        <select class="js-select form-select" autocomplete="off"
                                 data-hs-tom-select-options='{
                                    "placeholder": "Select a payment option..."
                                 }' id="channel_id">
                           <option value="">Select payment method...</option>
                              @if($shopify_channels->isNotEmpty())
                                 <optgroup label="Pay with Shopify Payments">
                                    @foreach($shopify_channels as $channel)
                                       <option value="{{$channel->channel_id}}">{{ $channel->channel_title }}</option>
                                    @endforeach                             
                                 </optgroup>                              
                              @endif
                              @if($is_other_user)
                                 <optgroup label="Pay Directly with Card/Net Banking">
                                    <option value="razorpay">Credit/Debit Card or Net Banking</option>
                                 </optgroup>
                              @endif
                        </select>
                        </div> 
                        <p id="payerror" style="color:red;"></p>     
                        <!-- End outgroup select -->
                     </div>   
                  </div>   
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-primary pay_now" >Go for payment</button>
               </div>
            </div>
         </div>
      </div>
      <!-- End Modal -->
   </x-slot>
</x-layout>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById('bd-example-modal-sm'); // Get modal element

    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var planId = button.getAttribute('plan_id'); 
        var durationMonths = button.getAttribute('duration_months'); 
        var plan = button.getAttribute('plan_name'); 
        var amount = button.getAttribute('plan_amount'); 
        // Set values inside modal (assuming you have elements with these IDs)
        document.getElementById('modal-amount').textContent = amount;
        document.getElementById('modal-plan').textContent = plan+' Plan';
        document.getElementById('modal-plan').setAttribute('modal_plan_id', planId);
        document.getElementById('modal-duration-months').textContent = durationMonths;
        document.getElementById('modal-duration-months').setAttribute('modal_duration_month', durationMonths);
    });
});
   $(document).ready(function () {
      $('#payerror').text('');
      $(".pay_now").on('click', function() {
         $('#payerror').text('');
         var channel_id = $("#channel_id").val(); 
         if (!channel_id || channel_id == "0") {
            $('#payerror').text('Please select payment Method');
            return false;
         }
         var plan_id = document.getElementById('modal-plan').getAttribute('modal_plan_id');
         var duration_months = document.getElementById('modal-duration-months').getAttribute('modal_duration_month');
         if (channel_id =='razorpay') {
            window.location.href = "{{ route('payment.index') }}?plan_id=" + plan_id + "&duration_months=" + duration_months;            
         } else {           
            window.location.href = "{{ route('shopify.payment.create') }}?plan_id=" + plan_id + "&duration_months=" + duration_months + "&channel_id=" + channel_id;
         }
      });
   });
   $(document).on("click", ".razorpay", function() {
      var plan_id = $(this).attr("plan_id");
      var duration_months = $(this).attr("duration_months");

      window.location.href = "{{ route('payment.index') }}?plan_id=" + plan_id + "&duration_months=" + duration_months;
   });
</script>
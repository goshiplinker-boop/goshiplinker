<x-layout>
    <x-slot name="title">{{__('message.order_edit.order_tittle')}}</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('order_view', $order) }}</x-slot>
    <x-slot name="page_header_title">
        <div class="d-sm-flex align-items-sm-center">
            <h1 class="page-header-title me-3">{{ $order->vendor_order_number }}</h1>
            @if(strtolower($order->payment_mode)=='cod')
                <span class="badge bg-primary rounded-pill me-3"> COD</span>
            @else 
                <span class="badge bg-success rounded-pill me-3">Prepaid</span> 
            @endif
            <span class="badge bg-soft-info text-info me-3">
                <span class="legend-indicator bg-info"></span>{{$order->status->status_name??'N/A'}}
            </span>
            <span><i class="bi-calendar-week"></i> {{ $order->created_at->format('M d, Y, h:i A') }}</span>
        </div>
		<div class="d-flex gap-2 mt-2">
			@if($shipmentInfo)
			<a class="text-body me-3" href="{{route('lableDownload',['order_ids'=>$order->id]) }}" target="_blank" ><i class="bi-printer me-1"></i>{{__('message.order_edit.print_shipping')}}</a>
			@endif
			<a class="text-body me-3" href="{{route('invoiceDownload',['order_ids'=>$order->id]) }}" target="_blank"><i class="bi-printer me-1"></i>{{__('message.order_edit.print_invoice')}}</a>
			<!-- Dropdown -->
			<div class="dropdown">
				<a class="text-body" href="javascript:;" id="moreOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">{{__('message.order_edit.more_options')}} <i class="bi-chevron-down"></i></a>
				<div class="dropdown-menu mt-1" aria-labelledby="moreOptionsDropdown">		
                    <a class="dropdown-item" id="cloneOrders" order_id="{{$order->id}}"><i class="bi-clipboard dropdown-item-icon"></i>Clone</a>			
					@if($order->status_code=='N')                    
                        <a class="dropdown-item" id="cancelOrders" order_id="{{$order->id}}"><i class="bi-clipboard dropdown-item-icon"></i> Cancel Order</a>
                        <a class="dropdown-item" id="archiveOrders" order_id="{{$order->id}}"><i class="bi-clipboard dropdown-item-icon"></i> Archive Order</a>
                        <a class="dropdown-item" id="onholdOrders" order_id="{{$order->id}}"><i class="bi-clipboard dropdown-item-icon"></i> Hold Order</a>
                        @if(strtolower($order->payment_mode)=='cod')
                            <a class="dropdown-item" id="paidOrders" order_id="{{$order->id}}" channel_code="{{$order->channelSetting->channel_code}}"><i class="bi-clipboard dropdown-item-icon"></i> Mark As Paid</a>
                        @endif    
					@endif
				</div>
			</div>
			<!-- End Dropdown -->
		</div>
    </x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
                @if($order->status_code=='N')    
                    <a href="{{ route('order_edit', $order->id) }}" class="btn btn-primary">{{ __('message.edit') }}</a>
                @endif
                <a href="javascript:history.back()" class="btn btn-light btn-sm"> <i class="bi bi-chevron-left"></i>{{__('message.back')}}</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="main">
        @if (session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            <strong>Success!</strong> {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            <strong>Error!</strong>  {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">
                            {{ __('message.order_edit.order_details') }}
                            <span class="badge bg-soft-dark text-dark rounded-circle ms-1">{{ $orderProducts->count() }}</span>
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="w-50">{{ __('message.order_edit.product_name') }}</th>
                                        <th style="width:8%;">{{ __('message.order_edit.HSN') }}</th>
                                        <th style="width:6%;">{{ __('message.order_edit.Qty') }}</th>
                                        <th style="width:12%;">{{ __('message.order_edit.price') }}</th>
                                        <th style="width:8%;">{{ __('message.order_edit.tax') }}(%)</th>
                                        <th class="text-end" style="width:12%;">{{ __('message.order_edit.total') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($orderProducts as $orderProduct)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-start">
                                                    <span id="product_{{ $orderProduct->id }}" class="fw-medium">{{ $orderProduct->product_name }}</span>

                                                    @if($order->status_code == 'N')
                                                        <!-- <button type="button"
                                                                class="btn btn-sm btn-link text-decoration-none ms-2 p-0"
                                                                data-bs-toggle="modal"
                                                                data-bs-target=".bd-example-modal-xl"
                                                                id="edit_{{ $orderProduct->id }}"
                                                                aria-label="Edit product {{ $orderProduct->id }}">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button> -->
                                                    @endif
                                                </div>

                                                <div class="fs-6 text-body mt-1">
                                                    <small id="sku_{{ $orderProduct->id }}">SKU: {{ $orderProduct->sku }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span>{{ $orderProduct->hsn }}</span>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span id="qty_{{ $orderProduct->id }}">{{ $orderProduct->quantity }}</span>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span>{{ getCurrencySymbol($order->currency_code) }}{{ $orderProduct->unit_price }}</span>
                                        </td>

                                        <td class="text-center align-middle">
                                            <span>{{ $orderProduct->tax_rate }}</span>
                                        </td>

                                        <td class="text-end align-middle">
                                            <span class="fw-semibold">{{ getCurrencySymbol($order->currency_code) }}{{ $orderProduct->total_price }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals (right aligned) -->
                        <div class="row justify-content-md-end mt-4">
                            <div class="col-md-8 col-lg-7">
                                <dl class="row text-sm-end mb-0">
                                    @foreach ($order->orderTotals as $orderTotal)
                                        <dt class="col-sm-6">{{ $orderTotal->title }}:</dt>
                                        <dd class="col-sm-6">{{ getCurrencySymbol($order->currency_code) }}{{ $orderTotal->value }}</dd>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">
                            {{__('message.order_edit.shipping_activity')}}
                            <span class="badge bg-soft-dark text-dark ms-1">
                                <span class="legend-indicator bg-dark"></span> {{ $history ? $history->current_shipment_status : 'N/A'  }} 
                            </span>
                        </h4>
                    </div>
					<div class="card-body">
						@forelse ($groupedTrackingHistory as $date => $histories)
						<ul class="step step-icon-xs">
							<li class="step-item">
								<div class="step-content-wrapper">
									<span class="step-divider">{{ \Carbon\Carbon::parse($date)->format('l, d F') }}</span>
								</div>
							</li>
							@foreach ($histories as $history)
							<li class="step-item">
								<div class="step-content-wrapper">
									<span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
									<div class="step-content">
										<p class="fs-6 mb-0">
											{{ $history->current_shipment_status }}
										</p>
										<p class="fs-6 mb-0">
											{{ \Carbon\Carbon::parse($history->current_shipment_status_date)->format('h:i A') }} ,  {{ $history->current_shipment_location }} 
										</p>
									</div>
								</div>
							</li>
							@endforeach
						</ul>
						@empty
						<p class="fs-6 mb-0">No tracking history available.</p>
						@endforelse
						<span class="small">{{ __('message.order_edit.times_zone') }}</span>
					</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-header-title">{{__('message.order_edit.customer')}}</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush list-group-no-gutters">
                            <li class="list-group-item">
                                <a class="d-flex align-items-center" href="javascript:;">
                                    <div class="icon icon-soft-info icon-circle"><i class="bi bi-person"></i></div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="text-body text-inherit"> {{ $order->fullname }}</span>
                                    </div>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a class="d-flex align-items-center" href="javascript:;">
                                    <div class="icon icon-soft-info icon-circle"><i class="bi-basket"></i></div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="text-body text-inherit">{{ $ordersCount }}  {{__('message.order_edit.orders')}}</span>
                                    </div>
                                    <!-- <div class="flex-grow-1 text-end"><i class="bi-chevron-right text-body"></i></div> -->
                                </a>
                            </li>
                            <!-- chennal info -->
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5> Channel info</h5>

                                </div>
                                <ul class="list-unstyled list-py-2 text-body">
                                    <li>
                                        @if($order->channelSetting->brand_logo)  
                                            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/logos/' . $order->channelSetting->brand_logo) }}" style="width:50px;">
                                        @else
                                            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/' . $order->channelSetting->channel_code . '.png') }}" style="width:50px;">
                                        @endif
                                    </li>
                                </ul>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5> {{__('message.order_edit.contact_info')}}</h5>
                                    <!-- <a class="link" href="{{ route('order_edit', $order->id) }}">{{__('message.edit')}}</a> -->
                                </div>
                                <ul class="list-unstyled list-py-2 text-body">
                                    <li><i class="bi-at me-2"></i> {{ $order->email ?? 'No Email' }} </li>
                                    <li><i class="bi-phone me-2"></i> {{ $order->phone_number ?? 'No Phone Number' }}</li>
                                </ul>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>Shipping address</h5>
                                    <!-- <a class="link" href="{{ route('order_edit', $order->id) }}">{{__('message.edit')}}</a> -->
                                </div>
                                <span class="d-block text-body">
                                    {{ $order->s_complete_address }}<br>
                                    {{ $order->s_phone }}<br>
                                    {{ $order->s_landmark }}<br>
                                    {{ $order->s_city }}<br>
                                    {{ $shippingState}}<br>
                                    {{ $order->s_zipcode }}<br>
                                    {{ $shippingCountry}}
                                </span>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>Billing address</h5>
                                    <!-- <a class="link" href="{{ route('order_edit', $order->id) }}">{{__('message.edit')}}</a> -->
                                </div>
                                <span class="d-block text-body">
                                    {{ $order->b_complete_address }}<br>
                                    {{ $order->b_phone }}<br>
                                    {{ $order->b_landmark }}<br>
                                    {{ $order->b_city }}<br>
                                    {{ $billingState}}<br>
                                    {{ $order->b_zipcode }}<br>
                                    {{ $billingCountry}}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{__('message.order_edit.other_information')}}</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush list-group-no-gutters">
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{__('message.order_edit.shipment_info')}}</h5>
                                </div>
                                <ul class="list-unstyled list-py-2 text-body">                                    
                                    <li>
                                        <i class="bi bi-info-circle me-2"></i>
                                    @if($shipmentInfo)
                                        <a href="javascript:" target="_blank">{{ $shipmentInfo->tracking_id }}</a>
                                        @else
                                        No shipment information available.
                                        @endif
                                    </li>
                                    <li>
                                        <i class="bi bi-building me-2"></i> Courier Name  {{ $shipmentInfo && $courier && $shipmentInfo->courier_id === $courier->id ? $courier->name : 'N/A' }}
                                    </li>
                                    <li>
                                        <i class="bi bi-geo-alt me-2"></i> Pickup Address: {{ $shipmentInfo ? $shipmentInfo->pickedup_location_address : 'N/A' }}
                                    </li>
                                </ul>
                            </li>                          
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{__('message.order_edit.package_info')}}</h5>
                                </div>
                                <ul class="list-unstyled list-py-2 text-body">
                                    <li><i class="bi-box-seam me-2"></i>{{__('message.order_edit.dead_weight')}}: {{$order->package_dead_weight}} Kg</li>
                                    <li><i class="bi bi-aspect-ratio me-2"></i>{{__('message.order_edit.box_length')}} {{$order->package_length}} {{__('message.order_edit.cms')}}</li>
                                    <li><i class="bi bi-aspect-ratio me-2"></i>{{__('message.order_edit.box_breadth')}} {{$order->package_breadth}} {{__('message.order_edit.cms')}}</li>
                                    <li><i class="bi bi-aspect-ratio me-2"></i>{{__('message.order_edit.box_height')}}{{$order->package_height}} {{__('message.order_edit.cms')}}</li>
                                    <li><i class="bi-box-seam me-2"></i>{{__('message.order_edit.volumetric_weight')}} {{ round(($order->package_length * $order->package_breadth * $order->package_height) / 5000,2) }} Kg
                                    </li>
                                </ul>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{__('message.order_edit.order_tag')}}</h5>
                                </div>
                                @if($tags)
                                    @foreach($tags as $tag)
                                        <span class="badge bg-secondary rounded-pill">{{$tag}}</span>
                                    @endforeach
                                @endif                              
                            </li>
                            <li class="list-group-item">
                                @if($notes) {!! $notes !!} @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- product edit modal-->
        <!-- Modal -->
        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="order_product_edit" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title h4" id="order_product_edit">Edit Order Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="order_product_form" class="needs-validation" action="{{ route('order_product_update') }}" method="POST" novalidate>
                        @csrf
                         <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="modal-body">
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" width="50%">Product Name</th>
                                        <th scope="col">SKU</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($orderProducts as $orderProduct)     
                                    <tr id="order-product-row-{{$orderProduct->id}}">
                                        <td scope="row">
                                            <input type="text" class="form-control" name="order_products[{{$orderProduct->id}}][product_name]" id="product_name" placeholder="Product name" aria-label="product name" value="{{ $orderProduct->product_name }}" required>
                                            <span class="invalid-feedback">Please Enter your product name.</span>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="order_products[{{$orderProduct->id}}][product_sku]" id="product_sku" placeholder="Your product sku" aria-label="Your product sku" value="{{ $orderProduct->sku }}" required>
                                            <span class="invalid-feedback">Please Enter your Product sku.</span>                                        
                                        </td>
                                        <td style="width:7%;">
                                            <input type="number" class="form-control" name="order_products[{{$orderProduct->id}}][qty]" id="qty" placeholder="qty" aria-label="Your product qty"   min="1" value="{{$orderProduct->quantity}}" required>
                                            <span class="invalid-feedback">Please enter your product quantity (must be at least 1).</span>                                        
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-soft-danger order_product_delete" order_product_id="{{$orderProduct->id}}" data-order-id="{{$order->id}}"><i class="bi bi-trash"></i></button>                        
                                        </td>
                                    </tr>
                                @endforeach      
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Modal -->
    </x-slot>
</x-layout>
<script>    
    $(document).on('click', '.order_product_delete', function (e) {
        e.preventDefault();
        const orderProductId = $(this).attr('order_product_id');

        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }

        $.ajax({
            url: "{{ route('order_products_delete') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                order_product_id: orderProductId
            },
            success: function (response) {
                 location.reload(); 
            },
            error: function (xhr) {
                alert('An error occurred. Please try again.');
                console.log(xhr.responseText);
            }
        });
    });
   $('#cloneOrders').on('click', function (e) {
        e.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const order_id = $(this).attr('order_id');

        $.ajax({
            url: '{{ route('order_clone') }}',
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_id: order_id,
                _token: csrfToken
            }),
            success: function(response) {
                location.reload();                
            },
            error: function(xhr) {
                alert('Failed to clone order.');
                console.error(xhr);
            }
        });

    });
    $('#cancelOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const order_id = $('#cancelOrders').attr('order_id');
        //alert(order_id);   
        $.ajax({
            url: '{{ route('cancelOrders') }}',
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: order_id,
                status_code: 'C',
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });
    $('#archiveOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const order_id = $('#archiveOrders').attr('order_id');
        $.ajax({
            url: '{{ route('archiveOrders') }}',
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: order_id,
                status_code: 'A',
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });
    $('#onholdOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const order_id = $('#onholdOrders').attr('order_id');
        $.ajax({
            url: '{{ route('onholdOrders') }}',
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: order_id,
                status_code: 'H',
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });
    $('#paidOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const order_id = $('#paidOrders').attr('order_id');
        const channel_code = $('#paidOrders').attr('channel_code');
        //alert(order_id);   
        $.ajax({
            url: '{{ route('mark_paid_Order') }}',
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_id: order_id,
                channel_code: channel_code,
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });
</script>
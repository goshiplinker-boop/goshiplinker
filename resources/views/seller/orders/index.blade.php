<x-layout>
    <x-slot name="title">{{__('message.orders.page_title')}}</x-slot>
    <x-slot name="page_header_title">
        <h1 class="page-header-title">{{__('message.orders.heading_title')}}<span class="badge bg-soft-dark text-dark ms-2">{{ $counts['all'] ?? '0' }}</span></h1>
        <div class="d-flex mt-2">
            <div class="dropdowm">
                <a class="text-body" href="javascript:;" id="moreOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">More options <i class="bi-chevron-down"></i></a>
                <div class="dropdown-menu mt-1" aria-labelledby="moreOptionsDropdown" style="">
                    <a class="dropdown-item" href="{{ route('unfulfilled_orders') }}"><i class="bi-folder-plus dropdown-item-icon"></i> {{__('message.sidebar.unfullfill')}}</a>
                </div>
            </div>
        </div>
    </x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-plus"></i> {{__('message.orders.add_orders')}}</button>
            <ul class="dropdown-menu">
                @if($exist_custom_channel==1)
                <li><a class="dropdown-item" href="{{ route('order_create') }}"><i class="bi bi-plus"></i> {{ __('message.orders.add') }}</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ route('add_orders') }}"><i class="bi bi-plus"></i> {{ __('message.orders.bulk') }}</a></li>
                @if($exist_custom_channel==1)
                <li><a class="dropdown-item" id="createOrderBtn"><i class="bi bi-plus"></i> {{__('message.orders.test_orders')}}</a></li>
                @endif
            </ul>
            <a class="btn btn-primary btn-sm" id="syncOrders" href="{{ route('syncOrders', ['companyId' => $company_id, 'syncType' => 'manual']) }}"><i class="bi bi-arrow-repeat"></i> {{__('message.orders.sync')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($payment_mappings->isNotEmpty())
            @foreach($payment_mappings as $payment_mapping)
                <div class="alert alert-soft-danger alert-dismissible" role="alert">
                    Please map payment gateway to sync orders of <a target="_blank" class="alert-link"
                        href="{{ route($payment_mapping->channel_code.'.edit', $payment_mapping->channel_id) }}">{{$payment_mapping->channel_title}}</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
        @if($courier_mappings->isNotEmpty())
            @foreach($courier_mappings as $courier_mapping)
                <div class="alert alert-soft-danger alert-dismissible" role="alert">
                    Please map couriers to sync orders of <a target="_blank" class="alert-link"
                        href="{{ route($courier_mapping->channel_code.'.edit', $courier_mapping->channel_id) }}">{{$courier_mapping->channel_title}}</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
        <ul class="nav nav-segment">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'new' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'new','limit'=>$page_size]) }}">
                    New <span class="badge bg-secondary rounded-pill">{{ $counts['new'] ?? '0' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'readytoship' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'readytoship','limit'=>$page_size]) }}">
                    Ready to Ship <span class="badge bg-secondary rounded-pill">{{ $counts['readytoship'] ?? '0' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'manifested' ? 'active' : '' }}"
                    id="manifests-tab"
                    role="button"
                    href="{{ route('order_list', ['tab' => 'manifested','limit'=>$page_size]) }}">
                    Manifest <span class="badge bg-secondary rounded-pill">{{ $counts['manifested'] ?? '0' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'shipped' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'shipped','limit'=>$page_size]) }}">
                    Shipped <span class="badge bg-secondary rounded-pill">{{ $counts['shipped'] ?? '0' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'int' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'int','limit'=>$page_size]) }}">
                    In-Transit <span class="badge bg-secondary rounded-pill">{{ $counts['int'] ?? '0' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'del' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'del','limit'=>$page_size]) }}">
                    Delivered <span class="badge bg-secondary rounded-pill">{{ $counts['del'] ?? '0' }}</span>
                </a>
            </li>           
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}"
                    href="{{ route('order_list', ['tab' => 'all','limit'=>$page_size]) }}">
                    ALL <span class="badge bg-secondary rounded-pill">{{ $counts['all'] ?? '0' }}</span>
                </a>
            </li>
        </ul>
        <meta name="csrf-token" content="{{ csrf_token() }}">
       <div class="pm-pagination fw-semibold">{{ $orders->links('pagination::bootstrap-5') }}</div>
        <div class="card">
            <div class="/*table-responsive*/ datatable-custom position-relative">
                    <!-- Header -->
                    <div class="card-header card-header-content-md-between">
                        <div>
                            <!-- Button to open the date range picker-->
                            <button type="button" id="js-daterangepicker-predefined" class="btn btn-white btn-sm">
                                <i class="bi-calendar-week me-1"></i>
                                <span class="js-daterangepicker-predefined-preview"></span>
                            </button>
                            <!-- Offcanvas -->
                            <button class="btn btn-white btn-sm position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                                <i class="bi bi-funnel me-1"></i>{{__('message.orders.filters')}}
                                @if(!empty($order_filters))
                                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary">
                                        Filters Applied
                                    </span>
                                @endif
                            </button>
                            @if(!empty($order_filters))
                                <a href="{{ route('order_list', ['tab'=>$tab,'clear_filters' => 1]) }}" class="btn btn-danger btn-sm ms-2">Clear Filters</a>
                            @endif
                          
                            <!-- Product Filter Modal -->
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                                <div class="offcanvas-header">
                                    <h5 id="offcanvasRightLabel">{{__('message.orders.fliter')}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <form class="needs-validation" action="{{route('order_list')}}" id="fiter-form" method="POST" enctype="multipart/form-data" novalidate>
                                        @csrf
                                        <div class="row">
                                            <input type="hidden" name="limit" value="{{$page_size}}" >
                                            <input type="hidden" name="tab" value="{{$tab}}" >
                                            <div class="col-sm-12 mb-3">
                                                <label for="phone_number" class="form-label">Customer Phone Number</label>
                                                <input type="numeric" id="phone_number" name="phone_number" class="form-control" value="@if(isset($filters['phone_number'])) {{$filters['phone_number']}} @endif" placeholder="Customer phone number" minlength="10" maxlength="10">
                                            </div>
                                            @if($tab && ($tab =='all' || $tab =='readytoship'))
                                                <div class="col-sm-12 mb-3">
                                                    <label for="order_status_codes" class="form-label">Order Status</label>
                                                    <div class="tom-select-custom tom-select-custom-with-tags">
                                                        <select class="js-select form-select" name="order_status_codes[]" autocomplete="off"
                                                                multiple data-hs-tom-select-options='{
                                                                "placeholder": "Select Order Status",
                                                                "hideSearch": true
                                                                }'>
                                                                <option value=""></option>
                                                                @if($statuses->isNotEmpty())
                                                                    @foreach($statuses as $status)
                                                                        @if($tab == 'readytoship' && in_array($status->status_code, ['P', 'M']))
                                                                            <option value="{{ $status->status_code }}"
                                                                                @if(isset($filters['order_status_codes']) && in_array($status->status_code, $filters['order_status_codes'])) selected @endif>
                                                                                {{ $status->status_name }}
                                                                            </option>
                                                                        @elseif($tab == 'all')
                                                                            <option value="{{ $status->status_code }}"
                                                                                @if(isset($filters['order_status_codes']) && in_array($status->status_code, $filters['order_status_codes'])) selected @endif>
                                                                                {{ $status->status_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($tab && $tab !='new')
                                                <div class="col-sm-12 mb-3">
                                                    <label for="channel_title" class="form-label">Pick up Location</label>
                                                    <div class="tom-select-custom tom-select-custom-with-tags">
                                                        <select class="js-select form-select" name="pickup_location_id" autocomplete="off"
                                                                data-hs-tom-select-options='{
                                                                "placeholder": "Select Pickup Location",
                                                                "hideSearch": true
                                                                }'>
                                                            <option value=""></option>
                                                            @if($all_locations->isNotEmpty())
                                                                @foreach($all_locations as $location)
                                                                <option value="{{$location->id}}" @if(isset($filters['pickup_location_id']) && $filters['pickup_location_id'] == $location->id) selected @endif>{{$location->location_title}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 mb-3">
                                                    <label for="courier_ids" class="form-label">Courier</label>
                                                    <div class="tom-select-custom tom-select-custom-with-tags">
                                                        <select id="courier_ids" class="js-select form-select" name="courier_ids" autocomplete="off"
                                                                data-hs-tom-select-options='{
                                                                "placeholder": "Select Courier",
                                                                "hideSearch": false
                                                                }'>
                                                                <option value=""></option>
                                                            @if($all_couriers->isNotEmpty())
                                                                @foreach($all_couriers as $courier)
                                                                <option value="{{$courier->courier_id}}"  data-sub-main-courier-code="{{ $courier->courier_code }}"  @if(isset($filters['courier_ids']) && $filters['courier_ids'] == $courier->courier_id) selected @endif >{{$courier->courier_title}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- ✅ Sub Courier Filters --}}
                                                @foreach($agg_couriers as $courier_code => $subCouriers)
                                                    <div class="col-sm-12 mb-3 sub-courier-block" data-parent="{{ $courier_code }}" style="display: none;">
                                                        <label class="form-label">Sub Courier</label>
                                                        <div class="tom-select-custom tom-select-custom-with-tags">
                                                            <select class="js-select form-select agg-select"
                                                                    name="agg_courier_name[{{ $courier_code }}]"                                                                    
                                                                    data-hs-tom-select-options='{"placeholder":"Select Sub Courier","hideSearch":false}'>
                                                                <option value=""></option>
                                                                @foreach($subCouriers as $agg)
                                                                    <option value="{{ $agg->courier_name }}"
                                                                        @if(!empty($filters['agg_courier_name'][$courier_code]) &&
                                                                            in_array($agg->courier_name, (array)$filters['agg_courier_name'][$courier_code]))
                                                                            selected
                                                                        @endif>
                                                                        {{ $agg->courier_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <div class="col-sm-12 mb-3">
                                                    <label for="tracking_numbers" class="form-label">Tracking Number</label>
                                                    <input type="text" id="tracking_numbers" name="tracking_numbers" class="form-control" value="@if(isset($filters['tracking_numbers'])) {{$filters['tracking_numbers']}} @endif" placeholder="Tracking with comma seprated">
                                                </div>
                                                <div class="col-sm-12 mb-3">
                                                    <label for="payment_mode" class="form-label">Label Generated</label>
                                                    <div class="tom-select-custom tom-select-custom-with-tags">
                                                        <select class="js-select form-select" name="label_generated" autocomplete="off"
                                                                data-hs-tom-select-options='{
                                                                "placeholder": "Select Label Generated",
                                                                "hideSearch": true
                                                                }'>
                                                            <option value=""></option>
                                                            <option value="0" @if(isset($filters['label_generated']) && $filters['label_generated']=='0') selected @endif>No</option>
                                                            <option value="1" @if(isset($filters['label_generated']) && $filters['label_generated']=='1') selected @endif>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 mb-3">
                                                    <label for="shipment_status_code" class="form-label">Shipment Status</label>
                                                    <div class="tom-select-custom tom-select-custom-with-tags">
                                                        <select class="js-select form-select" name="shipment_status_code" autocomplete="off"
                                                                 data-hs-tom-select-options='{
                                                                "placeholder": "Select Order Status",
                                                                "hideSearch": true
                                                                }'>
                                                                <option value=""></option>
                                                                @if($parent_shipment_statuses->isNotEmpty())
                                                                    @foreach($parent_shipment_statuses as $parent_shipment_code=>$parent_shipment_name)
                                                                    <option value="{{$parent_shipment_code}}" @if(isset($filters['shipment_status_code']) && $filters['shipment_status_code'] == $parent_shipment_code) selected @endif >{{$parent_shipment_name}}</option>
                                                                    @endforeach
                                                                @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 mb-3">
                                                <label for="channel_ids" class="form-label">Channel</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" name="channel_id" autocomplete="off"
                                                             data-hs-tom-select-options='{
                                                            "placeholder": "Select Channel",
                                                            "hideSearch": true
                                                            }'>
                                                            <option value=""></option>
                                                        @if($all_channels->isNotEmpty())
                                                            @foreach($all_channels as $channel)
                                                            <option value="{{$channel->channel_id}}"   @if(isset($filters['channel_id']) && $filters['channel_id']==$channel->channel_id) selected @endif >{{$channel->channel_title}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-12 mb-3">
                                                <label for="payment_mode" class="form-label">Payment Mode</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" name="payment_mode" autocomplete="off"
                                                             data-hs-tom-select-options='{
                                                            "placeholder": "Select Payment Mode",
                                                            "hideSearch": true
                                                            }'>
                                                        <option value=""></option>
                                                        <option value="cod" @if(isset($filters['payment_mode']) && $filters['payment_mode']=='cod') selected @endif>Cash on Delivery(COD)</option>
                                                        <option value="prepaid" @if(isset($filters['payment_mode']) && $filters['payment_mode']=='prepaid') selected @endif>Prepaid</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 mb-3">
                                                <label for="vendor_order_numbers" class="form-label">Order Number</label>
                                                <input type="text" id="vendor_order_numbers"  name="vendor_order_numbers" class="form-control"  value="@if(isset($filters['vendor_order_numbers'])) {{$filters['vendor_order_numbers']}} @endif" placeholder="Order numbers with comma seprated">
                                            </div>
                                            @if($tab =='new' || $tab =='readytoship' || $tab =='all')
                                            <div class="col-sm-12 mb-3">
                                                <label for="product_sku" class="form-label">Product SKU</label>
                                                <input type="text" id="product_sku" name="sku" class="form-control" value="@if(isset($filters['sku'])) {{$filters['sku']}} @endif" placeholder="Product SKUs with comma seprated">
                                            </div>
                                            @endif
                                            @if($tab =='new'  || $tab =='all')
                                            <div class="col-sm-12 mb-3">
                                                <label for="order_tags" class="form-label">Order Tags</label>
                                                <input type="text" id="order_tags"  name="order_tags" class="form-control" value="@if(isset($filters['order_tags'])) {{$filters['order_tags']}} @endif" placeholder="Order tags with comma seprated">
                                            </div>                                          

                                            <div class="col-sm-12 mb-3">
                                                <label for="order_history" class="form-label">Buyer Previous Order History</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" name="order_history" autocomplete="off"
                                                            data-hs-tom-select-options='{
                                                            "placeholder": "Select Buyer Previous Order History",
                                                            "hideSearch": true
                                                            }'>
                                                            <option value=""></option>
                                                            @foreach($customer_order_histories as $nooforders=>$novalue)
                                                            <option value="{{$nooforders}}" @if(isset($filters['order_history']) && $filters['order_history'] == $nooforders) selected @endif >{{$novalue}}</option>
                                                            @endforeach
                                                    </select>
                                                </div>
                                            </div>                                            

                                            <div class="col-sm-12 mb-3">
                                                <label for="order_weight" class="form-label">Order Weight</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" name="order_weight" autocomplete="off"
                                                            data-hs-tom-select-options='{
                                                            "placeholder": "Select Order Weight",
                                                            "hideSearch": true
                                                            }'>
                                                        <option value=""></option>
                                                        @foreach($orderweights as $weight=>$weightname)
                                                            <option value="{{$weight}}" @if(isset($filters['order_weight']) && $filters['order_weight'] == $weight) selected @endif >{{$weightname}}</option>
                                                        @endforeach                                                        
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        </div>  
                                        <input type="hidden" name="select_all" value="false" id="selectAllInput">                     
                                    </form>
                                    </div>
                                    <div class="offcanvas-footer">
                                            <div class="col">
                                                <div class="d-grid">
                                                    <button type="submit" id="filter_buttonxx" class="btn btn-primary">Apply Filter</button>
                                                </div>
                                            </div>
                                        </div> 
                           
                               
                            </div>
                            <!-- End Product Filter Modal -->                                                       
                            <!-- End Offcanvas -->
                        </div>
                        <div class="d-grid d-sm-flex gap-2">
                            <!-- Datatable Info -->
                            <div id="datatableWithCheckboxSelectCounterInfo" class="align-content-center me-2" style="display: none;">
                                <span id="datatableWithCheckboxSelectCounter">0</span> Selected
                            </div>
                            <div id="datatableWithCheckboxSelectAllCounterInfo" class="align-content-center me-2" data-total-orders="{{ $counts[$tab] ?? '0' }}" style="display: none;">
                                Click here to select all <span id="datatableWithCheckboxSelectAllCounter">0</span> 
                            </div>                           
                            @if( ($tab && $tab !='manifested'))
                            <button type="button" id="downloadCSVButton" class="btn btn-white btn-sm btn-icon" onclick="downloadCSV()">
                                <i class="bi bi-download"></i>
                            </button>
                            @endif
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            @if($tab !='int' && $tab !='shipped')
                            <div class="dropdown">
                                <button type="button" class="btn btn-white btn-sm dropdown-toggle w-100"
                                    id="usersExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-border-all me-2"></i> Bulk Actions
                                </button>
                                <div class="dropdown-menu dropdown-menu-sm-end" aria-labelledby="usersExportDropdown">
                                    <span class="dropdown-header">Options</span>
                                    @if($tab=='new')
                                        <a class="dropdown-item" href="javascript:;" data-bs-toggle="offcanvas" data-bs-target="#BulkShipOrders" aria-controls="BulkShipOrders"><i class="bi bi-truck"></i> Ship Orders</a>
                                    @endif
                                    @if($tab && $tab=='readytoship')
                                        <a class="dropdown-item" id="bulk_manifest_create" href="javascript:;"><i class="bi bi-truck"></i> Create Manifest</a>
                                        <a class="dropdown-item" id="bulk_unassign_orders" href="javascript:;"><i class="bi bi-truck"></i> Unassign Order</a>
                                    @endif
                                    @if($tab=='new')
                                        <a class="dropdown-item" id="archiveOrders" href="javascript:;"><i class="bi bi-archive"></i> Archive Orders</a>
                                        <a class="dropdown-item" id="cancelOrders" href="javascript:;"><i class="bi bi-x-circle"></i> Cancel Orders</a>
                                        <a class="dropdown-item" id="onholdOrders" href="javascript:;"><i class="bi bi-x-circle"></i> Hold Orders</a>
                                    @endif
                                    @if($tab && $tab=='manifested')
                                        <a class="dropdown-item" id="shippedOrders" href="javascript:;"><i class="bi bi-cart"></i> Mark Status as Shipped</a>   
                                    @endif
                                    @if($tab == 'del')
                                        <a class="dropdown-item" id="completedOrders" href="javascript:;">
                                        <i class="bi bi-check-circle"></i> Mark as Completed</a>
                                    @endif
                                    @if($tab != 'del')
                                        <div class="dropdown-divider"></div>
                                        <span class="dropdown-header">Download options</span>
                                    @endif
                                    @if($tab && $tab=='manifested')
                                        <a class="dropdown-item" id="viewManifest" href="javascript:;"><img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">Downlaod Manifest</a>
                                    @else
                                    @endif
                                    @if($tab != 'del' && $tab !='all')
                                    <a class="dropdown-item" id="downloadInvoice" href="javascript:;"><img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">Download Invoice</a>
                                    @endif
                                    @if($tab && ($tab=='readytoship' ||  $tab=='all'))
                                        <a class="dropdown-item" id="downloadShippingLabel" href="javascript:;"><img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">Download Shipping Label</a>
                                    @endif
                                    @if($tab && $tab=='readytoship')
                                        <a class="dropdown-item" id="downloadCombinedPdf" href="javascript:;"><img class="avatar avatar-xss avatar-4x3 me-2"src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">Both Invoice & Shipping Label</a>
                                    @endif
                                </div>
                            </div>
                            @endif
                            <!-- End Dropdown -->
                        </div>
                    </div>
                    <!-- End Header -->
                    <!-- Offcanvas -->
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="BulkShipOrders"
                        aria-labelledby="BulkShipOrdersLabel">
                        <div class="offcanvas-header">
                            <h5 id="BulkShipOrdersLabel">Ship Order Now</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <div class="row">
                                <div class="col-sm-12 mb-3">
                                    <label for="inputGroupLightGenderSelect" class="form-label">{{__('message.orders.select_partner')}}</label>
                                    <!-- Tom Select -->
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off"
                                            data-hs-tom-select-options='{
                                                "searchInDropdown": false,
                                                "hidePlaceholderOnSearch": false,
                                                "placeholder": "Select Any Courier"
                                             }' name="courier_id" id="courier_id">
                                            <option value=""></option>
                                            @if($all_couriers->isNotEmpty())
                                                @foreach($all_couriers as $courier)
                                                    <option value="{{$courier->courier_id}}" courier_code="{{$courier->courier_code}}">{{$courier->courier_title}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <!-- End Tom Select -->
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="inputGroupLightGenderSelect" class="form-label">{{__('message.orders.change_pickup')}}</label>
                                    <!-- Tom Select -->
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off"
                                            data-hs-tom-select-options='{
                                                "searchInDropdown": false,
                                                "hidePlaceholderOnSearch": false
                                             }' name="pickup_location_id" id="pickup_location_id">
                                            @if($all_locations->isNotEmpty())
                                                @foreach($all_locations as $location)
                                                <option value="{{$location->id}}" @if($location->default) selected @endif>{{$location->location_title}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <!-- End Tom Select -->
                                </div>
                                <div class="col-sm-12 mb-3">
                                                                  
                                    <label for="inputGroupLightGenderSelect" class="form-label return_pickup">{{__('message.orders.change_RTO_location')}}</label>
                                    <!-- Tom Select -->
                                    <div class="tom-select-custom return_pickup">
                                        <select class="js-select form-select" autocomplete="off"
                                            data-hs-tom-select-options='{
                                                "searchInDropdown": false,
                                                "hidePlaceholderOnSearch": false
                                             }' name="return_pickup_location_id" id="return_pickup_location_id">
                                            @if($all_locations->isNotEmpty())
                                                @foreach($all_locations as $location)
                                                    <option value="{{$location->id}}" @if($location->default) selected @endif>{{$location->location_title}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <!-- End Tom Select -->
                                </div>
                                @php
                                    $allowedIps = explode(',', env('REQUEST_RESPONSE_ALLOWED_IPS'));
                                    $ip = request('ip') ?? '';
                                @endphp
                                <div class="col-sm-12 text-end">
                                    <button type="button" id="assign_tracking_number" class="btn btn-primary btn-sm w-100"><i class="bi bi-truck"></i> Ship Now</button>
                                    @if (in_array(request()->ip(), $allowedIps) || $ip == 1)
                                        <button type="button" id="print_response" class="btn btn-primary btn-sm w-100 mt-4" response="print_response"><i class="bi bi-truck"></i> Print Request Response</button>
                                    @endif
                                    
                                </div>
                                <div class="col-sm-12 text-end mt-4 d-none" id="shipping_calculate_div">
                                    <button type="button" id="calculate_shipping" class="btn btn-primary w-100"><i class="bi bi-truck"></i> Calculate Shipping</button>                                   
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Offcanvas -->
                    @if($orders->isEmpty())
                    <div class="d-flex justify-content-center align-items-center">
                        <p class="text-center my-5">No data Found</p>
                    </div>
                    @else
                    <!-- Table -->
                    <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                @if($tab && $tab=='manifested')
                                <th class="table-column-pe-0">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="datatableWithCheckboxSelectAll">
                                        <label class="form-check-label" for="datatableWithCheckboxSelectAll"></label>
                                    </div>
                                </th>
                                <th class="table-column-ps-0">{{__('message.orders.mainefest_id')}}</th>
                                <th>{{__('message.orders.courier_name')}}</th>
                                <th>{{__('message.orders.packup_address')}}</th>
                                <th>{{__('message.orders.total_orders')}}</th>
                                <th>Payment</th>   
                                <th>{{__('message.orders.order_status')}}</th>                            
                                <th>{{__('message.orders.pickup')}}</th>
                                @else
                                <th class="table-column-pe-0">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="datatableWithCheckboxSelectAll">
                                        <label class="form-check-label" for="datatableWithCheckboxSelectAll"></label>
                                    </div>
                                </th>
                                <th class="table-column-ps-0">{{__('message.orders.order_details')}}</th>
                                @if(!request()->has('tab') || $tab != 'manifested')
                                    <th>{{ __('message.orders.customer_details') }}</th>
                                @endif
                                <th>{{__('message.orders.product_details')}}</th>
                                <th>{{__('message.orders.package_details')}}</th>
                                <th>{{__('message.orders.payment_details')}}</th>
                                @if($tab && $tab !='new' && $tab !='manifested')
                                    <th>Tracking</th>
                                @endif
                                <th>{{__('message.orders.order_status')}}</th>
                                @endif
                                <th>{{__('message.action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr class="order-row">
                                @if($tab && $tab=='manifested')
                                    <td class="table-column-pe-0">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input rowCheckbox" id="rowCheckbox_{{ $order->manifest_id }}" value="{{ $order->manifest_id }}">
                                            <label class="form-check-label" for="rowCheckbox_{{ $order->manifest_id }}"></label>
                                        </div>
                                    </td>
                                    <td class="table-column-ps-0">                                   
                                        <a href="javascript:void(0)" 
                                            class="view_manifest_orders"
                                            data-manifest-id="{{ $order->manifest_id }}"
                                            data-manifest-count="{{ $order->manifest_ordercount }}"
                                            data-manifest-orders="0"
                                            data-pickup-created="{{ $order->pickup_created }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#ordersModal">
                                            {{$order->manifest_id}}
                                        </a><br>
                                        <small>{{$order->manifest_created_at}}</small>
                                    </td>
                                    <td>{{$order->courier_title ?? 'N/A'}}</td>
                                    <td>{{$order->pickedup_location_address ?? 'N/A'}}</td>
                                    <td>{{$order->manifest_ordercount}}</td> 
                                    <td>
                                        @if(strtolower($order->payment_mode)=='cod')
                                            <span class="badge bg-primary rounded-pill">COD</span>
                                        @else 
                                            <span class="badge bg-success rounded-pill">Prepaid</span> 
                                        @endif
                                    </td>                            
                                    <td>
                                        <span class="badge btn-soft-dark text-body">
                                            <span class="legend-indicator bg-dark"></span>{{ $order->status_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(in_array($order->courier_code, config('app.manual_pickup_generate_couriers', [])))
                                            @if($order->pickedup_date)
                                                Pickup generated
                                            @else
                                                <a class="pickup_create btn btn-outline-secondary" manifest_id="{{ $order->manifest_id }}" pickup_location_id="{{ $order->pickedup_location_id }}" courier_id="{{ $order->courier_id }}"> Schedule Pickup</a>
                                            @endif
                                        @else
                                        N/A   
                                        @endif
                                    </td>
                                @else
                                    <td class="table-column-pe-0">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input rowCheckbox" id="rowCheckbox_{{ $order->id }}" value="{{ $order->id }}">
                                            <label class="form-check-label" for="rowCheckbox_{{ $order->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="table-column-ps-0">
                                        <a href="{{ route('order_view',$order->id)}}">{{$order->vendor_order_number}}</a><br>
                                        <small>{{ \Carbon\Carbon::parse($order->channel_order_date)->format('d M Y H:i:s') }}</small>
                                        </br>
                                        @if($order->brand_logo)                                           
                                        <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/logos/' . $order->brand_logo) }}" style="width:50px;">
                                        @else
                                        <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/'. $order->channel_code.'.png') }}" style="width:50px;">
                                        @endif
                                    </td>
                                    @if(!request()->has('tab') || $tab != 'manifested')
                                    <td>
                                        {{$order->fullname}}<br>
                                        {{$order->s_phone}}<br>
                                        {{$order->s_city}}, {{$order->s_zipcode}}
                                    </td>
                                    @endif
                                    <td>
                                    
                                    {{ $order->product_name }}<br>
                                    SKU: {{ $order->sku }}<br>
                                    Qty: {{ $order->quantity }}<br>
                                
                                    </td>
                                    <td>
                                        Weight: {{$order->package_dead_weight}} Kg<br>
                                        {{$order->package_length}} x {{$order->package_breadth}} x
                                        {{$order->package_height}} (cm)<br>
                                        Vol wt: {{ round(($order->package_length * $order->package_breadth * $order->package_height) / 5000,2) }} Kg
                                        @if ($tab && $tab=='new')
                                           <a href="javascript:void(0);" 
                                                class="update-packages-btn" 
                                                data-order-id="{{ $order->id }}" 
                                                data-packages-url="{{ route('orders.packages.json', $order->id) }}" 
                                                data-packages-save-url="{{ route('orders.packages.store', $order->id) }}">
                                                <i class="text-primary bi bi-pencil-square me-1"></i>
                                            </a>

                                        @endif
                                    </td>   
                                    <td>
                                        {{ getCurrencySymbol($order->currency_code) }}{{$order->order_total}}<br>
                                        @if(strtolower($order->payment_mode)=='cod')
                                            <span class="badge bg-primary rounded-pill">COD</span>
                                        @else 
                                            <span class="badge bg-success rounded-pill">Prepaid</span> 
                                        @endif
                                        @if(strtolower($order->financial_status)=='partially_paid')
                                            <span class="badge bg-danger rounded-pill">Partialy Paid</span> 
                                        @endif
                                    </td>
                                    
                                    @if($tab && $tab !='new' && $tab !='manifested')
                                        <td>
                                            @if(isset($order->courier_id))
                                               {{ ( $order->agrigation_coutier_name !=null && $order->courier_title != $order->agrigation_coutier_name) ? $order->courier_title . ' - ' . $order->agrigation_coutier_name : $order->courier_title }}</br>
                                                @if(isset($manageTracking->website_domain))
                                                    <a href="{{route('track_details',[$manageTracking->website_domain,$order->tracking_id])}}" target="_blank">{{$order->tracking_id??'N/A'}} </a>
                                                    @if($order->label_generated)
                                                        </br>
                                                        <span class="badge bg-success rounded-pill">Label Generated</span> 
                                                    @endif
                                                @else
                                                    {{$order->tracking_id??'N/A'}}
                                                @endif
                                                </br>
                                                @if(isset($shipment_statuses[$order->current_status]))
                                                    <span class="badge {{$shipment_statuses[$order->current_status]['status_colour']}} rounded-pill">{{$shipment_statuses[$order->current_status]['name']}}</span>
                                                @endif
                                                @if (in_array(request()->ip(), $allowedIps) || $ip == 1)
                                                 </br>
                                                    <span><i class="bi bi-arrow-repeat refreshStutusBtn" courier_id="{{$order->courier_id}}" tracking_id="{{$order->tracking_id}}"></i></span>
                                                @endif
                                            @else 
                                                N/A 
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @if($order->status_code === 'C')
                                        <span class="badge bg-soft-danger text-danger">
                                            <span class="legend-indicator bg-danger"></span> {{ $order->status_name }}
                                        </span>
                                        @elseif($order->status_code === 'N')
                                        <span class="badge bg-soft-primary text-primary">
                                            <span class="legend-indicator bg-primary"></span> {{ $order->status_name }}
                                        </span>
                                        @elseif($order->status_code === 'A')
                                        <span class="badge bg-soft-warning text-warning">
                                            <span class="legend-indicator bg-warning"></span> {{ $order->status_name }}
                                        </span>
                                        @elseif($order->status_code === 'M')
                                        <span class="badge btn-soft-dark text-body">
                                            <span class="legend-indicator bg-dark"></span> {{ $order->status_name }}
                                        </span>
                                        @elseif($order->status_code === 'P')
                                        <span class="badge bg-soft-warning text-secondary">
                                            <span class="legend-indicator bg-secondary"></span> {{ $order->status_name }}
                                        </span>
                                        @else
                                        <span class="badge bg-soft-info text-info">
                                            <span class="legend-indicator bg-info"></span> {{ $order->status_name }}
                                        </span>
                                        @endif
                                    </td>
                                @endif    
                                @if($tab && $tab == 'manifested')
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-white btn-sm dropdown-toggle" id="ordersExportDropdown1" data-bs-toggle="dropdown" aria-expanded="false">View</button>
                                            <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="ordersExportDropdown1">
                                                <span class="dropdown-header">Options</span>
                                                @if($order->status_code == 'M')
                                                    <a class="dropdown-item shippedOrders" data-manifest-id="{{ $order->manifest_id }}"><i class="bi bi-truck"></i> Mark as Shipped</a>
                                                @endif
                                                @if($order->pickup_created == 0) 
                                                <button id="deleteBtn" class="dropdown-item"  data-manifest-id="{{ $order->manifest_id }}"><i class="bi bi-trash"></i> Delete Manifast</button>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <span class="dropdown-header">Download options</span>
                                                <a class="dropdown-item" href="{{ route('view_manifest',['manifest_ids'=> $order->manifest_id]) }}">
                                                    <img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">
                                                    Download Manifest
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="{{ route('order_view', $order->id) }}"><i class="bi-eye"></i></a>
                                            <!-- Button Group -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty @if($order->status_code == 'N') cal_shiping @endif" id="ordersExportDropdown1" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="ordersExportDropdown1">
                                                    <span class="dropdown-header">Options</span>
                                                    @if($order->status_code == 'N')
                                                        <a  class="dropdown-item btn-ship-order" data-id="{{ $order->id }}" data-company-id="{{ $order->company_id }}"  data-package_dead_weight="{{ $order->package_dead_weight }}" data-package_length="{{ $order->package_length }}"  data-package_breadth="{{ $order->package_breadth }}" data-package_height="{{ $order->package_height }}" data-shipping_pin="{{ $order->s_zipcode }}" data-pickup_pincode="{{ $defaultPickup->zipcode??'' }}" data-pickup_id="{{ $defaultPickup->id??'' }}" data-payment_mode="{{ $order->payment_mode }}" data-amount="{{ $order->order_total }}" data-bs-toggle="modal" data-bs-target="#shipOrderModal"><i class="bi bi-truck"></i> Ship Order</a>

                                                        <!-- <a class="dropdown-item" href="javascript:;" data-bs-toggle="offcanvas" data-bs-target="#BulkShipOrders" aria-controls="BulkShipOrders"><i class="bi bi-truck"></i> Ship Order</a> -->
                                                        <a class="dropdown-item" href="{{ route('order_view', $order->id) }}"><i class="bi bi-pencil-square"></i> Edit Order</a>                                                        
                                                        <a class="dropdown-item cancelOrders" order_id="{{ $order->id }}"><i class="bi bi-x-circle"></i> Cancel Order</a>
                                                        <a class="dropdown-item archiveOrders" order_id="{{ $order->id }}"><i class="bi bi-trash3"></i> Archive Order</a>
                                                        <a class="dropdown-item onholdOrders" order_id="{{ $order->id }}"><i class="bi bi-trash3"></i> Hold Order</a>
                                                    @endif
                                                    @if($tab && $tab == 'readytoship' && $order->status_code == 'P')
                                                            <a class="dropdown-item manifest_create" order_id="{{ $order->id }}"><i class="bi bi-truck"></i> Create Manifest</a>
                                                            <a class="dropdown-item unassign_orders" order_id="{{ $order->id }}"><i class="bi bi-truck"></i> Unassign Order</a>
                                                    @endif
                                                    <div class="dropdown-divider"></div>
                                                    <span class="dropdown-header">Download options</span>                                                       
                                                        <a class="dropdown-item" href="{{ route('invoiceDownload',['order_ids'=>$order->id]) }}">
                                                        <img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">
                                                        Download Invoice Label
                                                    </a>
                                                    @if($order->tracking_id)
                                                    <a class="dropdown-item" href="{{route('lableDownload',['order_ids'=>$order->id]) }}">
                                                        <img class="avatar avatar-xss avatar-4x3 me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/pdf-icon.svg') }}" alt="Image Description">
                                                        Download Shipping Label
                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                    <!-- Modal Manifest -->
                    <div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="ordersModalLabel">Manifest #</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Order Number</th>
                                                <th>Tracking Number</th>
                                                <th>Payment Details</th>
                                                <th>Shipping Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ordersModalBody">
                                        <!-- Dynamically filled content based on clicked link -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Footer -->
                   
                    <!-- End Footer -->
            </div>
            <!-- End Card -->          
            <div class="card-footer">
                <div class="pm-pagination fw-semibold">{{ $orders->links('pagination::bootstrap-5') }}</div>
            </div>

<!-- End Button trigger modal -->
{{-- Modal (single modal for all orders) --}}
<!-- Modal: updatePackageModal -->
<div id="updatePackageModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePackageModalTitle">Update Packages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="orderId" value="">

                <div class="table-responsive">
                    <table class="table table-bordered" id="packageTable">
                        <thead>
                            <tr>
                                <th>Package Count</th>
                                <th>Length (cm)</th>
                                <th>Breadth (cm)</th>
                                <th>Height (cm)</th>
                                <th>Weight (kg)</th>
                                <!-- Action column: Add button placed in header -->
                                <th style="width:120px; text-align:center;">
                                    Action
                                    <div class="mt-1">
                                        <button type="button" id="addPackageBtn" class="btn btn-sm btn-outline-primary" title="Add package">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- rows injected dynamically -->
                        </tbody>
                    </table>
                </div>

                <div id="packageErrors" class="text-danger" style="display:none;"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePackagesBtn">Save Packages</button>
            </div>
        </div>
    </div>
</div>


    <!-- End Modal -->
         <!-- Request response Modal -->
         <div class="modal fade" id="jsonModal" tabindex="-1" aria-labelledby="jsonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="jsonModalLabel">Request Response</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>                  
                    </div>
                    <div class="modal-body">
                        <pre id="jsonContent" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
                    </body>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
      </div>

   <x-order-modal id="shipOrderModal" title="Ship Order" />
    </x-slot>
</x-layout>
<script>
//start code for shipping rate in popup
const compareUrl = "{{ route(panelPrefix().'.shipping.rate_comparison') }}";

let selectedCourierId = null;
let currentOrderId = null;

/**
 * Load courier rates inside modal
 */

function loadCourierRatesFromModal(payload,order_id,pickup_id) {

    $('#shipOrderModalBody').html(`
        <div id="rateLoader" class="text-center my-3">
            <div class="spinner-border text-primary"></div>
            <div>Calculating shipping rates…</div>
        </div>
    `);

    $.ajax({
        url: compareUrl,
        method: 'POST',
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
        },
        contentType: 'application/json',
        data: JSON.stringify(payload),

        success: function (rates) {
            $('#rateLoader').remove();

            if (!Array.isArray(rates) || rates.length === 0) {
                $('#shipOrderModalBody').html(
                    '<div class="alert alert-soft-danger">No courier rates found</div>'
                );
                return;
            }

            let html = `
                <h6 class="mb-3">Available Couriers</h6>
                <div id="courierRateList">
            `;

            rates.forEach((rate,index) => {
                html += `
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${rate.courier_name}</strong>
                                ${index==0 ? '<span class="badge bg-success ms-2">Best Price</span>' : ''}
                                <br>
                                <small class="text-muted">
                                    Chargeable Weight: ${rate.chargeable_weight} Kg
                                </small>
                            </div>

                            <div class="text-end">
                                <div class="h5 text-primary mb-2">₹${rate.cost}</div>
                                <button class="btn btn-sm btn-primary ship-order-btn"
                                        data-courier-id="${rate.courier_id}"
                                        data-pickup-id='${pickup_id}'
                                        data-order-id='${order_id}'>
                                    Ship Now
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;

            $('#shipOrderModalBody').html(html);
        },

        error: function () {
            $('#shipOrderModalBody').html(
                '<div class="alert alert-soft-danger">Failed to load courier rates</div>'
            );
        }
    });
}
$(document).on('click', '.ship-order-btn', function () {

    const courier_id = $(this).data('courier-id');
    const pickup_location_id = $(this).data('pickup-id');
    const order_id = $(this).data('order-id');
    const return_pickup_location_id = pickup_location_id;
    // Disable all buttons to prevent double click
    $('.ship-order-btn').prop('disabled', true);
    $(this).text('Shipping...');

    shipOrder({
        courier_id: courier_id,
        pickup_location_id: pickup_location_id,
        order_ids:[order_id],
        return_pickup_location_id:return_pickup_location_id
    });
});


$(document).on('click', '.btn-ship-order', function () {

    const orderId = $(this).data('id');
   
    const payload = {
        destination_pincode: String($(this).data('shipping_pin')),
        origin_pincode: String($(this).data('pickup_pincode')),
        length: Number($(this).data('package_length')),
        breadth:Number($(this).data('package_breadth')),
        height: Number($(this).data('package_height')),
        weight: Number($(this).data('package_dead_weight')),
        amount: Number($(this).data('amount')),
        is_cod: $(this).data('payment_mode') !== 'prepaid' ? 1 : 0,
        courier_id:"",
        seller_company_id:$(this).data('company-id')
       
    };
    
    pickup_id=Number($(this).data('pickup_id'));
    loadCourierRatesFromModal(payload,orderId,pickup_id);

    new bootstrap.Modal('#orderShipModal').show();
});

function shipOrder(payload) {
    $.ajax({
        url: routes.shiporders, // your ship order API
        method: 'POST',
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
        },
        contentType: 'application/json',
        data: JSON.stringify(payload),

        success: function (res) {
            $('#shipOrderModal').modal('hide');
             location.reload();
        },

        error: function () {
            $('.ship-order-btn').prop('disabled', false).text('Ship now');
        }
    });
}
//code end for showing courier rate in popup
</script>

 <script>
    const routes = {
        cancelOrders: "{{route('cancelOrders')}}",
        archiveOrders: "{{route('archiveOrders')}}",
        shippedOrders: "{{route('shippedOrders')}}",
        onholdOrders: "{{route('onholdOrders')}}",
        shiporders: "{{route('shiporders')}}",
        completedOrders: "{{route('completedOrders')}}",
        create_manifest: "{{route('create_manifest')}}",
        manifest_delete:"{{route('manifest.delete')}}",
        delete_manifest_order:"{{ route('delete_manifest_order') }}",
        manifest_orders:"{{ route('manifest_orders') }}",
        export_orders:"{{ route('export.orders') }}",  
        pickup_create: "{{ route('pickup_create') }}", 
        create_order: "{{ route('createOrder') }}", 
        unassign_order: "{{ route('unassign_order') }}",
        update_package_details: "{{ route('update_package')}}" ,
        calculate_shipping: "{{ route('calculate_shipping')}}" 
    };
    const total_orders = {{ $counts[$tab] ?? 0 }};
    const orderFilters = @json($filters);
    const { _token, ...orderFiltersWithoutToken } = orderFilters;
   // const csvParams = orderFiltersWithoutToken;
  //  const csvParams = new URLSearchParams(orderFiltersWithoutToken).toString();
    document.getElementById('filter_buttonxx').addEventListener('click', function(event) {
    document.getElementById('fiter-form').submit(); 
     });   
    
    //calculate shipping amount
    $('#courier_id').on('change', function () {
        const shipping_calculate_couriers = @json($shipping_calculate_couriers);
        var courier_code = $(this).find('option:selected').attr('courier_code');

        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        var nooforders = selectedOrders.length;

        if (nooforders <= 1 && shipping_calculate_couriers.includes(courier_code)) {
            $('#shipping_calculate_div').removeClass("d-none");
        } else {
            $('#shipping_calculate_div').addClass("d-none");
        }
    });    
    $(document).on('click', '.cal_shiping', function (e) {       
        if ($(e.target).is('input[type="checkbox"], select')) return;
         e.preventDefault();
        // Uncheck all checkboxes first
        $('.rowCheckbox').prop('checked', false).trigger('change');
        // Then check only the clicked row's checkbox
        const checkbox = $(this).closest('.order-row').find('.rowCheckbox');
        checkbox.prop('checked', true).trigger('change');
    }); 
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainSelect = document.querySelector('select[name="courier_ids"]');
    if (!mainSelect) return;

    // Detect Tom Select instance (if initialized)
    const tomSelectInstance = mainSelect.tomselect || null;

    function getSelectedCourierCodes() {
        if (tomSelectInstance) {
            // Get actual selected option elements from Tom Select
            return tomSelectInstance.items.map(value => {
                const option = mainSelect.querySelector(`option[value="${value}"]`);
                return option ? option.dataset.subMainCourierCode : null;
            }).filter(Boolean);
        } else {
            // Fallback for plain <select>
            return Array.from(mainSelect.selectedOptions)
                .map(opt => opt.dataset.subMainCourierCode)
                .filter(v => v && v.trim() !== '');
        }
    }

    function toggleSubCouriers() {
        const selectedCodes = getSelectedCourierCodes();
        console.log("✅ Selected courier codes:", selectedCodes);

        document.querySelectorAll('.sub-courier-block').forEach(block => {
            const parent = block.dataset.parent;
            block.style.display = selectedCodes.includes(parent) ? 'block' : 'none';
        });
    }

    // Event binding
    if (tomSelectInstance) {
        tomSelectInstance.on('change', toggleSubCouriers);
    } else {
        mainSelect.addEventListener('change', toggleSubCouriers);
    }

    // Initial run
    toggleSubCouriers();
});
</script>

<script>
    $('.refreshStutusBtn').on('click', function () {

        var tracking_number = $(this).attr("tracking_id");
        var courier_id = $(this).attr("courier_id");

        // Build URL dynamically
        var url = "/shipping/admin/orders/track/" + tracking_number + "/" + courier_id;

        $.get(url, function (response) {
            //console.log("GET request done");
            location.reload(); // refresh page
        });
    });
</script>

<script src="{{ asset(env('PUBLIC_ASSETS') . '/js/pm-order-custom.js') }}"></script>


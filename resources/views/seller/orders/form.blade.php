<x-layout>
    <x-slot name="title">{{ isset($is_edit) && $is_edit ? __('Edit Order') : __('Create Order') }}</x-slot>
    <x-slot name="breadcrumbs">{{ isset($is_edit) && $is_edit ? 'order edit' : 'order create' }}</x-slot>

    <x-slot name="page_header_title">
        <div class="d-sm-flex align-items-sm-center justify-content-between">
            <h1 class="page-header-title me-3">{{ isset($is_edit) && $is_edit ? __('Edit Order') : __('New Order') }}</h1>
        </div>
    </x-slot>

    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
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
        @if ($errors->any())
            <div class="alert alert-soft-danger">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif(session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Prepare products array for the form: prefer old('products'), then $order->order_products / relation, else empty --}}
        @php
            $oldProducts = old('products');
            if (!$oldProducts) {
                if (isset($order)) {
                    if (isset($order->order_products) && is_array($order->order_products)) {
                        $oldProducts = $order->order_products;
                    } elseif (isset($order->orderProducts) && is_iterable($order->orderProducts)) {
                        // convert collection to array of arrays
                        $oldProducts = collect($order->orderProducts)->map(function($p){
                            // handle both object and array shapes
                            if (is_array($p)) return $p;
                            if (is_object($p)) return [
                                'order_product_id' => $p->id ?? ($p['id'] ?? 0),
                                'product_name' => $p->product_name ?? ($p['product_name'] ?? ''),
                                'sku' => $p->sku ?? ($p['sku'] ?? ''),
                                'quantity' => $p->quantity ?? ($p['quantity'] ?? 1),
                                'unit_price' => $p->unit_price ?? ($p['unit_price'] ?? 0),
                                'tax_rate' => $p->tax_rate ?? ($p['tax_rate'] ?? 0),
                                'tax_amount' => $p->tax_amount ?? ($p['tax_amount'] ?? 0),
                                'total_price' => $p->total_price ?? ($p['total_price'] ?? 0),
                            ];
                            return (array) $p;
                        })->toArray();
                    } else {
                        $oldProducts = [];
                    }
                } else {
                    $oldProducts = [];
                }
            }
            // fallback: ensure at least one empty product row when both oldProducts and order-products empty
            if (count($oldProducts) === 0) {
                $oldProducts = [
                    ['order_product_id'=>0,'product_name'=>'','sku'=>'','quantity'=>1,'unit_price'=>0,'tax_rate'=>0,'tax_amount'=>0,'total_price'=>0]
                ];
            }
        @endphp

        <form id="createOrderForm" action="{{ isset($is_edit) && $is_edit ? route('orders.update', $order->id) : route('order_add') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @if(isset($is_edit) && $is_edit)
                @method('PUT')
            @endif

            <div class="row">
                <!-- Left column -->
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <!-- Order Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ __('message.order_create.order_details') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('message.order_create.order_number') }}</label>
                                    <input type="text" class="form-control" {{ isset($is_edit) && $is_edit ? "readonly":"" }} name="vendor_order_number" placeholder="Enter order number" value="{{ old('vendor_order_number', $order->vendor_order_number ?? '') }}" required>
                                    <span class="invalid-feedback">Order number is required.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('message.order_create.payment_mode') }}</label>
                                    @if(isset($is_edit) && $is_edit)
                                        <input type="text"  class="form-control" name="payment_mode" value="{{$order->payment_mode}}" readonly>                                    
                                    @else
                                        <select class="form-select" name="payment_mode" required>
                                            <option value="">Select Payment Mode</option>
                                            <option value="prepaid" @if(old('payment_mode', $order->payment_mode ?? '') == 'prepaid') selected @endif>Prepaid</option>
                                            <option value="cod" @if(old('payment_mode', $order->payment_mode ?? '') == 'cod') selected @endif>COD</option>
                                        </select>
                                        <span class="invalid-feedback">Please select a payment mode.</span>
                                    @endif
                                </div>
                                <input type="hidden" value="{{ old('channel_id', $order->channel_id ?? $channel_id) }}" name="channel_id">
                            </div>
                        </div>
                    </div>

                    <!-- Product Section -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-header-title">{{ __('message.order_create.products') }}</h4>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addProductRow">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm align-middle" id="productsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30%">Product Name</th>
                                        <th width="15%">SKU</th>
                                        <th width="5%">Quantity</th>
                                        <th width="10%">Unit Price</th>
                                        <th width="10%">Tax (%)</th>
                                        @if(!isset($is_edit) || !$is_edit)
                                             <th width="10%">Tax Amount</th>
                                        @endif
                                        <th width="15%">Total</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($oldProducts as $i => $p)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="products[{{ $i }}][order_product_id]" class="form-control" value="{{ old("products.$i.order_product_id", $p['order_product_id'] ?? 0) }}">
                                                <input type="text" name="products[{{ $i }}][product_name]" class="form-control" required value="{{ old("products.$i.product_name", $p['product_name'] ?? '') }}">
                                                <span class="invalid-feedback">This field is required</span>
                                            </td>
                                            <td>
                                                <input type="text" name="products[{{ $i }}][sku]" class="form-control"  value="{{ old("products.$i.sku", $p['sku'] ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $i }}][quantity]" class="form-control" min="1" value="{{ old("products.$i.quantity", $p['quantity'] ?? 1) }}" required>
                                                <span class="invalid-feedback">required</span>
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $i }}][unit_price]" class="form-control" min="0" step="0.01" value="{{ old("products.$i.unit_price", $p['unit_price'] ?? 0) }}" required>
                                                <span class="invalid-feedback">required</span>
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $i }}][tax_rate]" class="form-control" min="0" step="0.01" value="{{ old("products.$i.tax_rate", $p['tax_rate'] ?? 0) }}"  {{ isset($is_edit) && $is_edit ? "disabled":"" }}>
                                            </td>
                                            @if(!isset($is_edit) || !$is_edit)
                                             <td>
                                                <input type="number" name="products[{{ $i }}][tax_amount]" class="form-control" min="0" step="0.01" value="{{ old("products.$i.tax_amount", $p['tax_amount'] ?? 0) }}" readonly>
                                            </td>
                                            @endif
                                            <td>
                                                <input type="text" name="products[{{ $i }}][total_price]" class="form-control" readonly value="{{ old("products.$i.total_price", $p['total_price'] ?? '0.00') }}" />
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger removeProduct"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Package Info Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ __('message.order_create.package_info') ?? 'Package Info' }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-3">
                                    <label class="form-label">Length (cm)</label>
                                    <input type="number" step="0.01" min="0" name="package_length" id="package_length" class="form-control" placeholder="L" value="{{ old('package_length', $order->package_length ?? '') }}">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Breadth (cm)</label>
                                    <input type="number" step="0.01" min="0" name="package_breadth" id="package_breadth" class="form-control" placeholder="B" value="{{ old('package_breadth', $order->package_breadth ?? '') }}">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Height (cm)</label>
                                    <input type="number" step="0.01" min="0" name="package_height" id="package_height" class="form-control" placeholder="H" value="{{ old('package_height', $order->package_height ?? '') }}">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Package Weight (Kg)</label>
                                    <input type="number" step="0.01" min="0" name="package_dead_weight" id="package_dead_weight" class="form-control" placeholder="e.g. 1.25" value="{{ old('package_dead_weight', $order->package_dead_weight ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Charges Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ __('message.order_create.charges') ?? 'Charges & Discount' }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <!-- Visible inputs: NO name attributes (so they are not submitted directly) -->
                                <div class="col-3">
                                    <label class="form-label">Shipping Charges</label>
                                    <input type="number" step="0.01" min="0" id="shipping_charges_input" class="form-control" value="{{ old('shipping_charges', $order_totals['shipping'] ?? 0) }}">
                                </div>
                                <div class="col-2">
                                    <label class="form-label">COD Charges</label>
                                    <input type="number" step="0.01" min="0" id="cod_charges_input" class="form-control" value="{{ old('cod_charges', $order_totals['cod_charges'] ?? 0) }}">
                                </div>
                                <div class="col-2">
                                    <label class="form-label">Gift Wrap</label>
                                    <input type="number" step="0.01" min="0" id="giftwrap_input" class="form-control" value="{{ old('giftwrap', $order_totals['giftwrap'] ?? 0) }}">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Order Discount (Fixed Amount)</label>
                                    <!-- visible percent input: no name -->
                                    <input type="number" step="0.01" min="0" id="order_discount_fix" class="form-control" value="{{ old('order_discount_percent', $order_totals['discount'] ?? 0) }}" >
                                </div>
                                <div class="col-2">
                                    <label class="form-label">{{ __('message.order_create.currency') }}</label>
                                    <input type="text" name="currency_code" class="form-control" placeholder="e.g. INR" value="{{ old('currency_code', $order->currency_code ?? 'INR') }}" required>
                                </div>
                            </div>

                            <hr>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <small>Subtotal</small>
                                    <span id="summary_subtotal_text">{{ number_format((float) old('sub_total', $order->sub_total ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>Tax</small>
                                    <span id="summary_tax_text">{{ number_format((float) old('order_tax_total', $order_totals['tax'] ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>Shipping</small>
                                    <span id="summary_shipping_text">{{ number_format((float) old('shipping_charges', $order_totals['shipping'] ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>COD Charges</small>
                                    <span id="summary_cod_text">{{ number_format((float) old('cod_charges', $order_totals['cod_charges'] ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>Gift Wrap Charges</small>
                                    <span id="summary_giftwrap_text">{{ number_format((float) old('giftwrap', $order_totals['giftwrap'] ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>Discount</small>
                                    <span id="summary_discount_text">{{ number_format((float) old('order_discount', $order_totals['discount'] ?? 0), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Grand Total</strong>
                                    <strong id="summary_grand_text">{{ number_format((float) old('order_total', $order->order_total ?? 0), 2) }}</strong>
                                </div>
                            </div>

                            <!-- Hidden fields (these have the names the server expects) -->
                            <input type="hidden" name="sub_total" id="sub_total_hidden" value="{{ old('sub_total', $order->sub_total ?? 0) }}">
                            <input type="hidden" name="order_tax_total" id="order_tax_total_hidden" value="{{ old('order_tax_total', $order_totals['tax'] ?? 0) }}">
                            <input type="hidden" name="shipping_charges" id="shipping_charges_hidden" value="{{ old('shipping_charges', $order_totals['shipping'] ?? 0) }}">
                            <input type="hidden" name="cod_charges" id="cod_charges_hidden" value="{{ old('cod_charges', $order_totals['cod_charges'] ?? 0) }}">
                            <input type="hidden" name="giftwrap" id="giftwrap_hidden" value="{{ old('giftwrap', $order_totals['giftwrap'] ?? 0) }}">
                            <!-- this hidden contains the computed discount amount (not percentage) -->
                            <input type="hidden" name="order_discount" id="order_discount_hidden" value="{{ old('order_discount', $order_totals['discount'] ?? 0) }}">
                            <input type="hidden" name="order_total" id="order_total_hidden" value="{{ old('order_total', $order->order_total ?? 0) }}">
                        </div>
                    </div>

                </div>

                <!-- Right column -->
                <div class="col-lg-4">
                    <!-- Customer Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ __('message.order_create.customer_info') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" value="{{ old('fullname', $order->fullname ?? '') }}" required>
                                 <span class="invalid-feedback">required</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $order->email ?? '') }}">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone_number" class="form-control" maxlength="10" value="{{ old('phone_number', $order->phone_number ?? '') }}" required>
                                <span class="invalid-feedback">required</span>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping & Billing -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ __('message.order_create.s_address') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="s_fullname" id="s_fullname" class="form-control" value="{{ old('s_fullname', $order->s_fullname ?? '') }}" required>
                                    <span class="invalid-feedback">required</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="s_company" id="s_company" class="form-control" value="{{ old('s_company', $order->s_company ?? '') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Complete Address</label>
                                    <textarea name="s_complete_address" id="s_complete_address" class="form-control" rows="2" required>{{ old('s_complete_address', $order->s_complete_address ?? '') }}</textarea>
                                    <span class="invalid-feedback">required</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Landmark</label>
                                    <input type="text" name="s_landmark" id="s_landmark" class="form-control" value="{{ old('s_landmark', $order->s_landmark ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="s_phone" id="s_phone" class="form-control"  maxlength="10" value="{{ old('s_phone', $order->s_phone ?? '') }}" required>
                                    <span class="invalid-feedback">required</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Zipcode</label>
                                    <input type="text" name="s_zipcode" id="s_zipcode" class="form-control"  maxlength="6" value="{{ old('s_zipcode', $order->s_zipcode ?? '') }}" required>
                                    <span class="invalid-feedback">required</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <input type="text" name="s_city" id="s_city" class="form-control"  maxlength="100" value="{{ old('s_city', $order->s_city ?? '') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Shipping Country</label>
                                    <select class="form-select" id="s_country_code" name="s_country_code"
                                            required
                                            onchange="fetchStates(this.value, '{{ old('s_state_code', $order->s_state_code ?? '') }}', 's_state_code')">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->country_code }}"
                                                {{ old('s_country_code', $order->s_country_code ?? '') == $country->country_code ? 'selected' : '' }}>
                                                {{ $country->country_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                     <span class="invalid-feedback">required</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Shipping State</label>
                                    <select class="form-select" id="s_state_code" name="s_state_code" required>
                                        <option value="">Select State</option>
                                    </select>
                                     <span class="invalid-feedback">required</span>
                                </div>
                                <div class="col-md-12 form-check ps-5">
                                    @php
                                        $checked = (!isset($is_edit) || !$is_edit) && old('copyShippingToBilling', true);
                                    @endphp
                                    <input class="form-check-input" type="checkbox" id="copyShippingToBilling"  {{ $checked ? 'checked' : '' }} >
                                    <label class="form-check-label small" for="copyShippingToBilling">Billing address same as shipping address</label>
                                </div>
                            </div>
                            <div class="row g-3 {{ $checked ? 'd-none' : '' }}" id="b_billing_address">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{ __('message.order_create.b_address') }}</h4>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="b_fullname" id="b_fullname" class="form-control" value="{{ old('b_fullname', $order->b_fullname ?? '') }}">
                                    <span class="invalid-feedback">required</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="b_company" id="b_company" class="form-control" value="{{ old('b_company', $order->b_company ?? '') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Complete Address</label>
                                    <textarea name="b_complete_address" id="b_complete_address" class="form-control" rows="2">{{ old('b_complete_address', $order->b_complete_address ?? '') }}</textarea>
                                     <span class="invalid-feedback">required</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Landmark</label>
                                    <input type="text" name="b_landmark" id="b_landmark" class="form-control" value="{{ old('b_landmark', $order->b_landmark ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="b_phone" id="b_phone" class="form-control"  maxlength="10" value="{{ old('b_phone', $order->b_phone ?? '') }}" required>
                                    <span class="invalid-feedback">required</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Zipcode</label>
                                    <input type="text" name="b_zipcode" id="b_zipcode" class="form-control"  maxlength="6" value="{{ old('b_zipcode', $order->b_zipcode ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <input type="text" name="b_city" id="b_city" class="form-control" maxlength="100" value="{{ old('b_city', $order->b_city ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Billing Country</label>
                                    <select class="form-select" id="b_country_code" name="b_country_code"
                                            required
                                            onchange="fetchStates(this.value, '{{ old('b_state_code', $order->b_state_code ?? '') }}', 'b_state_code')">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->country_code }}"
                                                {{ old('b_country_code', $order->b_country_code ?? '') == $country->country_code ? 'selected' : '' }}>
                                                {{ $country->country_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                     <span class="invalid-feedback">required</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Billing State</label>
                                    <select class="form-select" id="b_state_code" name="b_state_code" required>
                                        <option value="">Select State</option>
                                    </select>
                                     <span class="invalid-feedback">required</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Submit -->
                    <div class="text-end mt-4 d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">{{ isset($is_edit) && $is_edit ? 'Update Order' : __('message.order_create.create_order_btn') }}</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- JS: product rows and calculations -->
        <script>
            const isEdit = {{ isset($is_edit) && $is_edit ? 1 : 0 }};
            // start productIndex from number of old products (if any)
            let productIndex = {{ max(count($oldProducts), 1) }};

            document.querySelector('#addProductRow').addEventListener('click', () => {
                const tableBody = document.querySelector('#productsTable tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td><input type="hidden" name="products[${productIndex}][order_product_id]" value="0"><input type="text" name="products[${productIndex}][product_name]" class="form-control" required></td>
                    <td><input type="text" name="products[${productIndex}][sku]" class="form-control" required></td>
                    <td><input type="number" name="products[${productIndex}][quantity]" class="form-control" min="1" value="1" required></td>
                    <td><input type="number" name="products[${productIndex}][unit_price]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="number" name="products[${productIndex}][tax_rate]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="number" name="products[${productIndex}][tax_amount]" class="form-control" min="0" step="0.01" readonly></td>
                    <td><input type="text" name="products[${productIndex}][total_price]" class="form-control" readonly value="0.00"></td>
                    <td><button type="button" class="btn btn-sm btn-danger removeProduct"><i class="bi bi-trash"></i></button></td>
                `;
                tableBody.appendChild(newRow);
                productIndex++;
                // rebind events & recalc
                setTimeout(() => {
                    rebindProductEventsAndRecalc();
                }, 50);
            });

            document.addEventListener('click', (e) => {
                if (e.target.closest('.removeProduct')) {
                    e.target.closest('tr').remove();
                    setTimeout(() => {
                        rebindProductEventsAndRecalc();
                    }, 50);
                }
            });
        </script>

        <script>
            // helper to parse numbers
            const parseNum = (v) => {
                const n = parseFloat(v);
                return isFinite(n) ? n : 0;
            };

            // calculate order totals from product rows + shipping + discount + cod + giftwrap
            function calculateOrderTotals() {
                let subtotal = 0;
                let totalTax = 0;

                // iterate each product row in the products table
                document.querySelectorAll('#productsTable tbody tr').forEach(row => {
                    const unit_priceEl = row.querySelector('input[name$="[unit_price]"]');
                    const quantityEl = row.querySelector('input[name$="[quantity]"]');
                    const taxEl = row.querySelector('input[name$="[tax_rate]"]');
                    const taxAmountEl = row.querySelector('input[name$="[tax_amount]"]');
                    const totalEl = row.querySelector('input[name$="[total_price]"]');

                    const unit_price = parseNum(unit_priceEl?.value);
                    const quantity = parseNum(quantityEl?.value);
                    const taxRate = parseNum(taxEl?.value);

                    const inclusiveTotal = unit_price * quantity;

                    // ✅ Extract tax-inclusive base price and tax
                    const basePrice = inclusiveTotal / (1 + ((taxRate>=1)?taxRate/100:taxRate));
                    const taxAmount = inclusiveTotal - basePrice;
                    if (taxAmountEl) taxAmountEl.value = taxAmount.toFixed(2);
                    // set per-row total_price input (2 decimals)
                    if (totalEl) totalEl.value = inclusiveTotal.toFixed(2);

                    subtotal += inclusiveTotal;
                    totalTax += taxAmount;
                });

                // read visible inputs (unique ids)
                const shipping = parseNum(document.getElementById('shipping_charges_input')?.value);
                const cod_charges = parseNum(document.getElementById('cod_charges_input')?.value);
                const giftwrap = parseNum(document.getElementById('giftwrap_input')?.value);
                const discountAmt = parseNum(document.getElementById('order_discount_fix')?.value);

                // compute discountAmount (percent of subtotal)
                const discountAmount = discountAmt;

                // grand total: subtotal + tax + shipping + cod + giftwrap - discount
                const grandTotal = subtotal + shipping + cod_charges + giftwrap - discountAmount;

                // update visible texts (2 decimal places)
                document.getElementById('summary_subtotal_text').innerText = subtotal.toFixed(2);
                if(isEdit==0){
                    document.getElementById('summary_tax_text').innerText = totalTax.toFixed(2);
                }
                document.getElementById('summary_shipping_text').innerText = shipping.toFixed(2);
                document.getElementById('summary_cod_text').innerText = cod_charges.toFixed(2);
                document.getElementById('summary_giftwrap_text').innerText = giftwrap.toFixed(2);
                document.getElementById('summary_discount_text').innerText = discountAmount.toFixed(2);
                document.getElementById('summary_grand_text').innerText = grandTotal.toFixed(2);

                // update hidden inputs for submission (server expects these names)
                document.getElementById('sub_total_hidden').value = subtotal.toFixed(2);
                if(isEdit==0){
                    document.getElementById('order_tax_total_hidden').value = totalTax.toFixed(2);
                }
                
                document.getElementById('shipping_charges_hidden').value = shipping.toFixed(2);
                document.getElementById('cod_charges_hidden').value = cod_charges.toFixed(2);
                document.getElementById('giftwrap_hidden').value = giftwrap.toFixed(2);
                // store discount amount (not percent)
                document.getElementById('order_discount_hidden').value = discountAmount.toFixed(2);
                document.getElementById('order_total_hidden').value = grandTotal.toFixed(2);
            }

            // wire events for each product row inputs
            function attachProductRowListeners() {
                document.querySelectorAll('#productsTable tbody tr').forEach(row => {
                    ['input[name$="[unit_price]"]', 'input[name$="[quantity]"]', 'input[name$="[tax_rate]"]'].forEach(sel => {
                        row.querySelectorAll(sel).forEach(el => {
                            // remove previous listener if set (safe noop if not)
                            el.removeEventListener('input', calculateOrderTotals);
                            el.addEventListener('input', calculateOrderTotals);
                        });
                    });
                });
            }

            function rebindProductEventsAndRecalc() {
                attachProductRowListeners();
                calculateOrderTotals();
            }

            document.addEventListener('DOMContentLoaded', () => {
                // attach listeners for visible shipping/discount controls
                ['shipping_charges_input', 'cod_charges_input', 'giftwrap_input', 'order_discount_fix'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('input', calculateOrderTotals);
                });

                // initial bind for product rows
                rebindProductEventsAndRecalc();
            });

            // ensure totals are recalculated just before submit
            document.getElementById('createOrderForm')?.addEventListener('submit', (evt) => {
                calculateOrderTotals();
            });
        </script>

        <!-- JS: copy shipping -> billing when checkbox checked and keep in sync while checked -->
        <script>
            (function () {
                const checkbox = document.getElementById('copyShippingToBilling');

                // Fields to copy except country/state (we handle those separately)
                const simpleFields = [
                    ['s_fullname', 'b_fullname'],
                    ['s_company', 'b_company'],
                    ['s_complete_address', 'b_complete_address'],
                    ['s_landmark', 'b_landmark'],
                    ['s_phone', 'b_phone'],
                    ['s_zipcode', 'b_zipcode'],
                    ['s_city', 'b_city']
                ];

                // helper: wait for #b_state_code to have options then set value
                function waitForBillingOptionsAndSet(stateCode, timeout = 3000) {
                    return new Promise((resolve) => {
                        const sel = document.getElementById('b_state_code');
                        if (!sel) return resolve();
                        if (!stateCode) return resolve();
                        if (sel.options.length > 1) {
                            sel.value = stateCode;
                            return resolve();
                        }
                        const mo = new MutationObserver(() => {
                            if (sel.options.length > 1) {
                                sel.value = stateCode;
                                mo.disconnect();
                                resolve();
                            }
                        });
                        mo.observe(sel, { childList: true, subtree: true });
                        // safety timeout
                        setTimeout(() => {
                            mo.disconnect();
                            resolve();
                        }, timeout);
                    });
                }

                // copy non-country/state fields
                function copySimpleFields() {
                    simpleFields.forEach(([s, b]) => {
                        const sEl = document.getElementById(s);
                        const bEl = document.getElementById(b);
                        if (sEl && bEl) bEl.value = sEl.value;
                    });
                }

                // main copy function: copies country then populates/sets billing state
                async function copyOnce() {
                    copySimpleFields();

                    const sCountryEl = document.getElementById('s_country_code');
                    const sStateEl = document.getElementById('s_state_code');
                    const bCountryEl = document.getElementById('b_country_code');

                    const sCountry = sCountryEl?.value || '';
                    const sState = sStateEl?.value || '';

                    if (bCountryEl && sCountry) {
                        // set billing country
                        bCountryEl.value = sCountry;

                        // If your fetchStates supports (country, selectedState, targetId) use it
                        if (typeof fetchStates === 'function') {
                            try {
                                // call fetchStates to populate billing states and pass selected state
                                fetchStates(sCountry, sState || '', 'b_state_code');
                                // wait until options arrive and selected is applied
                                await waitForBillingOptionsAndSet(sState, 3000);
                            } catch (err) {
                                // fallback: attempt to set value after a short delay
                                await waitForBillingOptionsAndSet(sState, 3000);
                            }
                        } else {
                            // fetchStates not available: try to set after options load
                            await waitForBillingOptionsAndSet(sState, 3000);
                        }
                    } else {
                        // No country found: still copy state directly as best-effort
                        const bStateEl = document.getElementById('b_state_code');
                        if (bStateEl && sState) bStateEl.value = sState;
                    }
                }

                // Keep billing in sync while checkbox checked
                function bindShippingListeners() {
                    // update simple fields live
                    simpleFields.forEach(([s, b]) => {
                        const sEl = document.getElementById(s);
                        const bEl = document.getElementById(b);
                        if (!sEl || !bEl) return;
                        // remove old listener if present
                        if (sEl._shippingListener) sEl.removeEventListener('input', sEl._shippingListener);
                        sEl._shippingListener = () => {
                            if (checkbox.checked) bEl.value = sEl.value;
                        };
                        sEl.addEventListener('input', sEl._shippingListener);
                    });

                    // country change: repopulate billing states immediately
                    const sCountryEl = document.getElementById('s_country_code');
                    if (sCountryEl) {
                        if (sCountryEl._countryListener) sCountryEl.removeEventListener('change', sCountryEl._countryListener);
                        sCountryEl._countryListener = async () => {
                            const val = sCountryEl.value;
                            const bCountryEl = document.getElementById('b_country_code');
                            if (bCountryEl && checkbox.checked) {
                                bCountryEl.value = val;
                                if (typeof fetchStates === 'function') {
                                    // populate billing states and don't select any (we'll copy state separately if needed)
                                    fetchStates(val, '', 'b_state_code');
                                }
                            }
                        };
                        sCountryEl.addEventListener('change', sCountryEl._countryListener);
                    }

                    // shipping state change: copy to billing state (safe: wait if needed)
                    const sStateEl = document.getElementById('s_state_code');
                    if (sStateEl) {
                        if (sStateEl._stateListener) sStateEl.removeEventListener('change', sStateEl._stateListener);
                        sStateEl._stateListener = async () => {
                            const val = sStateEl.value;
                            if (!checkbox.checked) return;
                            // try setting billing state (billing options may change after country change)
                            const bStateEl = document.getElementById('b_state_code');
                            if (bStateEl) {
                                // if options present, set immediately
                                if (bStateEl.options.length > 1) {
                                    bStateEl.value = val;
                                } else {
                                    // wait until options are populated
                                    await waitForBillingOptionsAndSet(val, 3000);
                                }
                            }
                        };
                        sStateEl.addEventListener('change', sStateEl._stateListener);
                    }
                }

                function unbindShippingListeners() {
                    simpleFields.forEach(([s]) => {
                        const sEl = document.getElementById(s);
                        if (sEl && sEl._shippingListener) {
                            sEl.removeEventListener('input', sEl._shippingListener);
                            delete sEl._shippingListener;
                        }
                    });
                    const sCountryEl = document.getElementById('s_country_code');
                    if (sCountryEl && sCountryEl._countryListener) {
                        sCountryEl.removeEventListener('change', sCountryEl._countryListener);
                        delete sCountryEl._countryListener;
                    }
                    const sStateEl = document.getElementById('s_state_code');
                    if (sStateEl && sStateEl._stateListener) {
                        sStateEl.removeEventListener('change', sStateEl._stateListener);
                        delete sStateEl._stateListener;
                    }
                }

                function toggleCopyMode() {
                    const billingSection = document.getElementById("b_billing_address");
                    if (checkbox.checked) {
                        billingSection.classList.add("d-none");
                        copyOnce().then(() => {
                            bindShippingListeners();
                        });
                    } else {
                        unbindShippingListeners();
                        billingSection.classList.remove("d-none");
                    }
                }

                if (checkbox) {
                    checkbox.addEventListener('change', toggleCopyMode);
                    // apply on load if already checked (old input)
                    if (checkbox.checked) toggleCopyMode();
                }
            })();
        </script>


        <script>
            const routes = {
                states:  "{{ route('states', ['country_code' => ':country_code']) }}"
            }
            window.addEventListener('DOMContentLoaded', (event) => {
                const oldShippingCountry = '{{ old('s_country_code', $order->s_country_code ?? '') }}';
                const oldShippingState = '{{ old('s_state_code', $order->s_state_code ?? '') }}';
                if (oldShippingCountry) {
                    fetchStates(oldShippingCountry, oldShippingState, 's_state_code');
                }
                const oldBillingCountry = '{{ old('b_country_code', $order->b_country_code ?? '') }}';
                const oldBillingState = '{{ old('b_state_code', $order->b_state_code ?? '') }}';
                if (oldBillingCountry) {
                    fetchStates(oldBillingCountry, oldBillingState, 'b_state_code');
                }
            });
        </script>
    </x-slot>
</x-layout>

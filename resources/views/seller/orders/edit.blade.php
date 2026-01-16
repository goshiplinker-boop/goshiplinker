<x-layout>
    @if (session('success'))
    <div class="alert alert-soft-success alert-dismissible" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-soft-danger alert-dismissible" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <x-slot name="title">Order View </x-slot>
    <x-slot name="breadcrumbs">{{ Breadcrumbs::render('order_edit',$order) }}</x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{ $order->vendor_order_number }}</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
                <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> Back</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <!-- Shipping (left) -->
            <div class="col-12 col-md-6 mb-3">
                <div class="card">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">Shipping address</h4>
                    </div>                     
                    <div class="card-body">
                        <form id="shipping-form" class="needs-validation" action="{{ route('order_update', $order->id) }}" method="POST" novalidate>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="form_type" value="shipping_address">

                            <div class="mb-3">
                                <label for="shippingPhoneLabel" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="s_phone" id="shippingPhoneLabel" placeholder="Your shipping phone" aria-label="Your s_phone" value="{{ $order->s_phone }}" required>
                                <div class="invalid-feedback">Please Enter your Phone.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shippingAddressLabel" class="form-label">Address</label>
                                <input type="text" class="form-control" name="s_complete_address" id="shippingAddressLabel" placeholder="Your address" aria-label="Your address" value="{{ $order->s_complete_address }}" required>
                                <div class="invalid-feedback">Please Enter your Address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shippingCityLabel" class="form-label">City</label>
                                <input type="text" class="form-control" name="s_city" id="shippingCityLabel" placeholder="City" aria-label="Your city" value="{{ $order->s_city }}" required>
                                <div class="invalid-feedback">Please Enter your City.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shippingStateLabel" class="form-label">Shipping State</label>
                                <select class="form-control" name="s_state_code" id="shippingStateLabel" aria-label="Shipping State" required>
                                    <option value="" disabled>Select a state</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->state_code }}" {{ $order->s_state_code == $state->state_code ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please Select your State.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shippingLandmarkLabel" class="form-label">Land Marks</label>
                                <input type="text" class="form-control" name="s_landmark" id="shippingLandmarkLabel" placeholder="Land Marks" aria-label="Your landmark" value="{{ old('s_landmark', $order->s_landmark) }}" required>
                                <div class="invalid-feedback">Please Enter your Land Marks.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shippingZipCodeLabel" class="form-label">Zip code</label>
                                <input type="text" class="js-input-mask form-control" name="s_zipcode" id="shippingZipCodeLabel" placeholder="Your zip code" value="{{ $order->s_zipcode }}" required>
                                @error('s_zipcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please enter a valid 6-digit Zip Code.</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Billing (right) -->
            <div class="col-12 col-md-6 mb-3">
                <div class="card">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">Billing address</h4>
                    </div>                    
                    <div class="card-body">
                        <form id="billing-form" class="needs-validation" action="{{ route('order_update', $order->id) }}" method="POST" novalidate>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="form_type" value="billing_address">

                            <div class="mb-3">
                                <label for="billingPhoneLabel" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="b_phone" id="billingPhoneLabel" placeholder="Your billing phone" value="{{ $order->b_phone }}" required>
                                <div class="invalid-feedback">Please Enter your billing phone</div>
                            </div>

                            <div class="mb-3">
                                <label for="billingAddressLabel" class="form-label">Address</label>
                                <input type="text" class="form-control" name="b_complete_address" id="billingAddressLabel" placeholder="Your address" value="{{ $order->b_complete_address }}" required>
                                <div class="invalid-feedback">Please Enter your Address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="billingCityLabel" class="form-label">City</label>
                                <input type="text" class="form-control" name="b_city" id="billingCityLabel" placeholder="City" aria-label="Your city" value="{{ $order->b_city }}" required>
                                <div class="invalid-feedback">Please Enter your City.</div>
                            </div>

                            <div class="mb-3">
                                <label for="billingStateLabel" class="form-label">Billing State</label>
                                <select class="form-control" name="b_state_code" id="billingStateLabel" aria-label="Billing State" required>
                                    <option value="" disabled>Select a state</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->state_code }}" {{ $order->b_state_code == $state->state_code ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please Select your State.</div>
                            </div>

                            <div class="mb-3">
                                <label for="billingLandmarkLabel" class="form-label">Land Marks</label>
                                <input type="text" class="form-control" name="b_landmark" id="billingLandmarkLabel" placeholder="Land Marks" aria-label="Your landmark" value="{{ old('b_landmark', $order->b_landmark) }}" required>
                                <div class="invalid-feedback">Please Enter your Land Marks.</div>
                            </div>

                            <div class="mb-3">
                                <label for="billingZipCodeLabel" class="form-label">Zip code</label>
                                <input type="text" class="js-input-mask form-control" name="b_zipcode" id="billingZipCodeLabel" placeholder="Your zip code" value="{{ $order->b_zipcode }}" required>
                                @error('b_zipcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please enter a valid 6-digit Zip Code.</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
<script>
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
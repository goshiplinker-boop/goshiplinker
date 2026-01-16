<x-layout>
    {{-- PAGE TITLE --}}
    <x-slot name="title">Shipping Rate Calculator</x-slot>

    {{-- BREADCRUMBS --}}
    <x-slot name="breadcrumbs">
        Rate Calculater
    </x-slot>

    {{-- PAGE HEADER TITLE --}}
    <x-slot name="page_header_title">
        <h1 class="page-header-title">Shipping Rate Calculator</h1>
    </x-slot>

    {{-- HEADER BUTTONS --}}
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                <i class="bi bi-chevron-left"></i> Back
            </a>
        </div>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <x-slot name="main">
        <div class="card">
            <div class="card-body">

                {{-- FORM START --}}
                <form class="js-validate needs-validation" id="rateCalcForm" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Origin Pincode</label>
                                <input type="text"  class="form-control" id="origin_pincode" value="{{ old('origin_pincode', '') }}"   name="origin_pincode" placeholder="Enter origin pincode" required>
                                <span class="invalid-feedback">Please enter origin pincode</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Destination Pincode</label>
                                <input type="text" class="form-control"  id="destination_pincode" value="{{ old('destination_pincode', '') }}"   name="destination_pincode"  placeholder="Enter destination pincode"  required>
                                <span class="invalid-feedback">Please enter destination pincode</span>
                            </div>
                        </div>
                    </div>

                    {{-- WEIGHT + DIMENSIONS --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Weight (Kg)</label>
                                <input type="number" step="0.01" class="form-control" id="weight" value="{{ old('weight', '') }}"  name="weight"  placeholder="1.5" required>
                                <span class="invalid-feedback">Please enter weight</span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label">Length (cm)</label>
                                <input type="number" class="form-control" id="length" value="{{ old('length', '') }}"  name="length" placeholder="10" required>
                                <span class="text-danger" id="volweight"></span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label">Breadth (cm)</label>
                                <input type="number" class="form-control" id="breadth" Value="{{ old('breadth', '') }}" name="breadth" placeholder="10" required>
                                <span class="text-danger" id="apliweight"></span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" class="form-control" id="height" value="{{ old('height', '') }}"  name="height" placeholder="10" required>
                            </div>
                        </div>
                        
                    </div>

                    {{-- PAYMENT MODE + COMPANY --}}
                    <div class="row">

                        {{-- PAYMENT MODE --}}
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Payment Mode</label>
                                <select class="form-select" id="payment_mode" name="is_cod" required>

                                    <option value="0" {{ old('is_cod') == "0" ? 'selected' : '' }}>Prepaid</option>

                                    <option value="1" {{ old('is_cod') == "1" ? 'selected' : '' }}>COD</option>

                                </select>
                                <span class="invalid-feedback">Please choose payment mode</span>
                            </div>
                        </div>

                         {{-- Amount --}}
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Amount</label>
                                <input type="number"  class="form-control" id="amount" value="{{ old('amount', '') }}" name="amount" placeholder="10" required>
                            </div>                            
                        </div>
                      
                    </div>
                     {{-- COMPANY AND AMOUNT --}}
                    <div class="row">                       
                        {{-- COURIER OPTIONAL --}}
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Courier (Optional)</label>
                                <select class="form-select" id="courier_id" name="courier_id">
                                    <option value="">Any Courier</option>
                                    @foreach($couriers as $courier)
                                        <option value="{{ $courier->id }}" {{ old('courier_id') == $courier->id ? 'selected' : '' }}>{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                         {{-- COMPANY OPTIONAL --}}
                        @if($role_id==1 && $companies)
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Sellers (Optional)</label>
                                    <select class="form-select" id="seller_company_id" name="seller_company_id">
                                        <option value="">Select Seller</option>

                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('seller_company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->brand_name ?? $company->user['name'] }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @endif                     
                    </div>
                    {{-- SUBMIT --}}
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit"  id="shipping_calculate" class="btn btn-primary btn-sm">
                                Calculate Rates
                            </button>
                        </div>
                    </div>
                </form>
                {{-- FORM END --}}

                {{-- COURIER RESULT BOX --}}
                <div class="mt-5" id="courierResultsWrapper" style="display:none;">
                    <h5 class="mb-3">Courier Comparison</h5>
                    <div id="courierResults"></div>
                </div>

            </div>
        </div>

        {{-- JS SECTION --}}
        <script>
            const compareUrl = "{{ route(panelPrefix().'.shipping.rate_comparison') }}"; 

            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('rateCalcForm');

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                    }
                    $('#shipping_calculate').html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Calculating....</span></div>');
                    

                    const payload = Object.fromEntries(new FormData(form).entries());

                    fetch(compareUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(list => {
                        const wrapper = document.getElementById('courierResultsWrapper');
                        const container = document.getElementById('courierResults');

                        container.innerHTML = '';
                        wrapper.style.display = 'block';
                        $('#shipping_calculate').text("Calculate Rates");
                        if (!Array.isArray(list) || list.length === 0) {
                            container.innerHTML = `
                                <div class="alert alert-soft-danger alert-dismissible fade show" role="alert">                               
                                    <ul class="mb-0">
                                        <li> No courier rates found.</li>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                            return;
                        }

                        list.forEach((courier, index) => {
                            const bestBadge = index === 0
                                ? `<span class="badge bg-success ms-2">Best Price</span>`
                                : '';
                            const logo = courier.logo
                                ? `<img src="${courier.logo}" class="me-3" style="height:32px;">`
                                : '';

                            container.innerHTML += `
                                <div class="card mb-3">
                                    <div class="card-body d-flex justify-content-between align-items-center">

                                        <div class="d-flex align-items-center">
                                            ${logo}
                                            <div>
                                                <h6 class="mb-1">
                                                    ${courier.courier_name}
                                                    ${bestBadge}
                                                </h6>
                                                
                                                <div class="small text-muted">
                                                    Chargeable Weight: ${courier.chargeable_weight ?? '-'} Kg
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <div class="h4 text-primary mb-1">₹${courier.cost}</div>
                                        </div>

                                    </div>
                                </div>
                            `;
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        $('#shipping_calculate').text("Calculate Rates");
                        wrapper.style.display = 'block';
                        container.innerHTML = `
                            <div class="alert alert-soft-danger alert-dismissible fade show" role="alert">                               
                                <ul class="mb-0">
                                    <li> Something went wrong. Please try again.</li>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                    });
                });
            });
        </script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lengthInput  = document.getElementById('length');
            const breadthInput = document.getElementById('breadth');
            const heightInput  = document.getElementById('height');
            const weightInput  = document.getElementById('weight');

            const dimensionRow = heightInput.closest('.row');

            function calculateVolumetricWeight() {
                const length  = parseFloat(lengthInput.value)  || 0;
                const breadth = parseFloat(breadthInput.value) || 0;
                const height  = parseFloat(heightInput.value)  || 0;
                const actualWeight = parseFloat(weightInput.value) || 0;

                if (!length || !breadth || !height) return;

                const volumetricWeight = (length * breadth * height) / 5000;
                const applicableWeight = Math.max(actualWeight, volumetricWeight);

                let resultDiv = document.getElementById('volumetricWeightResult');

                if (!resultDiv) {
                    resultDiv = document.createElement('div');
                    resultDiv.id = 'volumetricWeightResult';
                    resultDiv.style.marginLeft = '51%';
                    dimensionRow.parentNode.insertBefore(resultDiv, dimensionRow.nextSibling);
                }

                resultDiv.innerHTML = `
                    <small class="fw-semibold text-danger">
                        Volumetric Weight: ${volumetricWeight.toFixed(2)} kg and Applicable Weight: ${applicableWeight.toFixed(2)} kg
                    </small>
                `;
            }
            lengthInput.addEventListener('input', calculateVolumetricWeight);
            breadthInput.addEventListener('input', calculateVolumetricWeight);
            heightInput.addEventListener('input', calculateVolumetricWeight);
            weightInput.addEventListener('input', calculateVolumetricWeight);
        });
        </script>


    </x-slot>
</x-layout>

<x-layout>
    <x-slot name="title">Edit Location</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('pickup_locations.edit', $pickupLocation) }}</x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.pickup_location.edit_page_header_title')}}</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route('pickup_locations.index') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> Back</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="card">
            <div class="card-body">
                <form id="editLocationForm" class="js-validate needs-validation" action="{{ route('pickup_locations.update', $pickupLocation->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="location_title" class="form-label">{{__('message.add_location.title')}}</label>
                                <input type="text" class="form-control" name="location_title" placeholder="{{__('message.add_location.title_placeholder')}}" value="{{ old('location_title', $pickupLocation->location_title) }}" required>
                                @error('location_title')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.title_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="brand_name" class="form-label">{{__('message.add_location.brand_name')}}</label>
                                <input type="text" class="form-control" name="brand_name" placeholder="{{__('message.add_location.brand_name_placeholder')}}" value="{{ old('brand_name', $pickupLocation->brand_name) }}" required>
                                @error('brand_name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.brand_name_error')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="contact_person_name" class="form-label">{{__('message.add_location.person_name')}}</label>
                                <input type="text" class="form-control" name="contact_person_name" placeholder="{{__('message.add_location.person_name_placeholder')}}" value="{{ old('contact_person_name', $pickupLocation->contact_person_name) }}" required>
                                @error('contact_person_name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.person_name_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="email" class="form-label">{{__('message.add_location.email')}}</label>
                                <input type="email" class="form-control" name="email" placeholder="{{__('message.add_location.email_placeholder')}}" value="{{ old('email', $pickupLocation->email) }}" required>
                                @error('email')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.email_error')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="phone" class="form-label">{{__('message.add_location.phone')}}</label>
                                <input type="text" class="form-control" name="phone" placeholder="{{__('message.add_location.phone_placeholder')}}" value="{{ old('phone', $pickupLocation->phone) }}" required>
                                @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.phone_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="alternate_phone" class="form-label">{{__('message.add_location.alternate_phone')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                <input type="text" class="form-control" name="alternate_phone" placeholder="{{__('message.add_location.alternate_phone_placeholder')}}" value="{{ old('alternate_phone', $pickupLocation->alternate_phone) }}">
                                @error('alternate_phone')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="address" class="form-label">{{__('message.add_location.address')}}</label>
                                <textarea class="form-control" name="address" placeholder="{{__('message.add_location.address_placeholder')}}" required>{{ old('address', $pickupLocation->address) }}</textarea>
                                @error('address')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.address_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="landmark" class="form-label">{{__('message.add_location.landmark')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                <input type="text" class="form-control" name="landmark" placeholder="{{__('message.add_location.landmark_placeholder')}}" value="{{ old('landmark', $pickupLocation->landmark) }}">
                                @error('landmark')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="zipcode" class="form-label">{{__('message.add_location.pincode')}}</label>
                                <input type="text" class="form-control" name="zipcode" placeholder="{{__('message.add_location.pincode_placeholder')}}" value="{{ old('zipcode', $pickupLocation->zipcode) }}" required>
                                @error('zipcode')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.pincode_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="city" class="form-label">{{__('message.add_location.city')}}</label>
                                <input type="text" class="form-control" name="city" placeholder="{{__('message.add_location.city_placeholder')}}" value="{{ old('city', $pickupLocation->city) }}" required>
                                @error('city')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.city_error')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-4">
                                <label class="form-label" for="country">{{__('message.add_location.country')}}</label>
                                <select class="form-select" id="country" name="country_code" onchange="fetchStates(this.value,$pickupLocation->state_code)" required>
                                    <option value="">{{__('message.add_location.country_option')}}</option>
                                    @foreach($countries as $country)
                                    <option value="{{ $country->country_code }}" {{ old('country_code', $pickupLocation->country_code) == $country->country_code ? 'selected' : '' }}> {{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('country_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.country_error')}}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-4">
                                <label class="form-label" for="state">{{__('message.add_location.state')}}</label>
                                <select class="form-select" id="state" name="state_code" required>
                                    <option value="">{{__('message.add_location.state_option')}}</option>
                                    <!-- Assume state options will be loaded via JS based on the selected country -->
                                </select>
                                @error('state_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.state_error')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="gstin" class="form-label">{{__('message.add_location.company_GSTIN')}}</label>
                                <input type="text" class="form-control" name="gstin" placeholder="{{__('message.add_location.company_GSTIN_placeholder')}}" value="{{ old('gstin', $pickupLocation->gstin) }}" required>
                                @error('gstin')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.company_GSTIN_error')}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="location_type" class="form-label">{{__('message.add_location.location_type')}}</label>
                                <select class="form-select" name="location_type" required>
                                    <option value="home" {{ old('location_type', $pickupLocation->location_type) == 'home' ? 'selected' : '' }}> {{__('message.add_location.location_type_home')}}</option>
                                    <option value="office" {{ old('location_type', $pickupLocation->location_type) == 'office' ? 'selected' : '' }}> {{__('message.add_location.location_type_office')}}</option>
                                    <option value="other" {{ old('location_type', $pickupLocation->location_type) == 'other' ? 'selected' : '' }}> {{__('message.add_location.location_type_other')}}</option>
                                </select>
                                @error('location_type')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.add_location_location_type_error')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="default_location" class="form-label">Default Location</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="default_location_yes">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="default" {{ old('default', $pickupLocation->default) == '1' ? 'checked' : '' }} id="default_location_yes">
                                            <span class="form-check-label">Yes</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="default_location_no">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="default" {{ old('default', $pickupLocation->default) == '0' ? 'checked' : '' }} id="default_location_no">
                                            <span class="form-check-label">No</span>
                                        </span>
                                    </label>
                                </div>
                                @error('default')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">Please Selected your Default Location.</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="courier_warehouse_id" class="form-label">{{__('message.add_location.courier_warehouse_id')}}</label>
                                <input type="text" class="form-control" name="courier_warehouse_id" placeholder="{{__('message.add_location.courier_warehouse_id_placeholder')}}" value="{{ old('courier_warehouse_id', $pickupLocation->courier_warehouse_id) }}" >
                                @error('courier_warehouse_id')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.add_location.courier_warehouse_id_error')}}</span>
                            </div>                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="pickup_day" class="form-label">{{ __('message.add_location.pickup_day') }}</label>
                                <select id="pickup_day" class="form-select" name="pickup_day" required>
                                    <option value="1" {{ old('pickup_day', $pickupLocation->pickup_day ?? '') == 1 ? 'selected' : '' }}>Same Day</option>
                                    <option value="2" {{ old('pickup_day', $pickupLocation->pickup_day ?? '') == 2 ? 'selected' : 
                                    '' }}>Next Day</option>
                                </select>
                                <span class="invalid-feedback">{{ __('message.add_location.pickup_day_error') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                           <div class="mb-4">
                                <label for="location_statuses" class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="location_status">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', '1') == '1' ? 'checked' : '' }} id="location_status">
                                            <span class="form-check-label">{{__('message.active')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="location_status_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status" {{ old('status') == '0' ? 'checked' : '' }} id="location_status_inactive">
                                            <span class="form-check-label">{{__('message.inactive')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.error_status')}}</span>
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{__('message.update')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            const routes = {
                states:  "{{ route('states', ['country_code' => ':country_code']) }}"
            }
        
        // Call fetchStates when the page loads
        window.addEventListener('DOMContentLoaded', (event) => {
            let countrySelect = document.getElementById('country'); // Get the country select element
            let selectedCountryCode = countrySelect.value; // Get the selected country code
            let selectedStateCode = "{{ $pickupLocation->state_code }}"; // Get the state code from the database
            if (selectedCountryCode) {
                fetchStates(selectedCountryCode,
                    selectedStateCode); // Fetch states for the selected country and pre-select the state
            }
            // Fetch new states when the country changes
            countrySelect.addEventListener('change', (event) => {
                fetchStates(event.target.value); // Fetch states when the country changes
            });
        });
        </script>
    </x-slot>
</x-layout>
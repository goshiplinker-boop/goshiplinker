<x-layout>
    <x-slot name="title">Magento</x-slot>
    <x-slot name="breadcrumbs">  </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">Create</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="#" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{__('message.woocommerce.guidelines')}}</p>
                        <div id="integrateStepsData" class="integrateStepsData descColor">
                            <div class="genInfoList">
                                <p>{!!__('message.woocommerce.guidelines_description')!!} </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    <form class="needs-validation" action="{{ route('sellfy.store') }}" id="woocommerce-form"
                        method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="channel_code" value="woocommerce">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="channel_title" class="form-label">{{__('message.woocommerce.channel_title')}}</label>
                                <input type="text" id="channel_title" name="channel_title" class="form-control" value="{{ old('channel_title') }}" placeholder="{{__('message.woocommerce.channel_title_placeholder')}}" required>
                                @error('channel_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_channel_title')}}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="brand_name" class="form-label">{{__('message.woocommerce.brand_name')}}</label>
                                <input type="text" id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name') }}" placeholder="{{__('message.woocommerce.brand_name_placeholder')}}" required>
                                @error('brand_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_brand_name')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="channel_url" class="form-label">{{__('message.woocommerce.store_url')}}</label>
                                <input type="text" id="channel_url" name="channel_url" class="form-control" value="{{ old('channel_url') }}"placeholder="{{__('message.woocommerce.store_url_placeholder')}}" required>
                                @error('channel_url')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_store_url')}}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="client_id" class="form-label">{{__('message.woocommerce.client_id')}}</label>
                                <input type="text" id="client_id" name="client_id" class="form-control" value="{{ old('client_id') }}" placeholder="{{__('message.woocommerce.client_id_placeholder')}}" required>
                                @error('client_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_client_id')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="secret_key" class="form-label">{{__('message.woocommerce.key')}}</label>
                                <input type="text" id="secret_key" name="secret_key" class="form-control" value="{{ old('secret_key') }}" placeholder="{{__('message.woocommerce.key_placeholder')}}" required>
                                @error('secret_key')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_key')}}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="brand_logo" class="form-label">{{__('message.woocommerce.logo')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                @error('brand_logo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="email" class="form-label">{{__('message.woocommerce.email')}}
                                    <span class="form-label-secondary">({{__('message.optional')}}) </span>
                                </label>
                                <input type="text" id="email" name="other_details[email]" class="form-control" value="{{ old('other_details.email') }}" placeholder="{{__('message.woocommerce.email_placeholder')}}">
                                @error('other_details.email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="gstin" class="form-label">{{__('message.woocommerce.gstin')}}</label>
                                <input type="text" id="gstin" name="other_details[gstin]" class="form-control" value="{{ old('other_details.gstin') }}" placeholder="{{__('message.woocommerce.gstin_placeholder')}}">
                                @error('other_details.gstin')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_gstin')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label" for="sync_status">{{__('message.woocommerce.status')}}</label><br>
                                <input type="text" id="fetch_status" name="other_details[fetch_status]" class="form-control" value="{{ old('other_details.fetch_status','pending,processing') }}" placeholder="processing" required>
                                @error('other_details.fetch_status')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.error_status')}}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="is_address_same" class="form-label">{{__('message.woocommerce.is_billing_address')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="is_address_same_yes">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="other_details[is_address_same]" {{ old('other_details.is_address_same', '1') == '1' ? 'checked' : '' }} id="is_address_same_yes" required>
                                            <span class="form-check-label">{{__('message.yes')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="is_address_same_no">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="other_details[is_address_same]" {{ old('other_details.is_address_same') == '0' ? 'checked' : '' }} id="is_address_same_no" required>
                                            <span class="form-check-label">{{__('message.no')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('other_details.is_address_same')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">{{__('message.woocommerce.error_billing')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <label class="form-label" for="order_tags">Order Tags
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label><br>
                                <input type="text" id="order_tags" name="other_details[order_tags]" class="form-control" value="{{ old('other_details.order_tags') }}" placeholder="COD, COD Confirmed etc.">
                                @error('other_details.order_tags')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="statuses" class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', '1') == '1' ? 'checked' : '' }} id="status">
                                            <span class="form-check-label">{{__('message.active')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="status_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status" {{ old('status') == '0' ? 'checked' : '' }} id="status_inactive">
                                            <span class="form-check-label">{{__('message.inactive')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">{{__('message.error_status')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{__('message.save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        document.getElementById('sync_status').addEventListener('change', function() {
            const inputContainer = document.getElementById('syncstatusContainer');
            if (this.checked) {
                if (!document.getElementById('fetch_status')) {
                    const newInput = document.createElement('input');
                    newInput.type = 'text';
                    newInput.name = 'other_details[fetch_status]';
                    newInput.placeholder = 'Enter status';
                    newInput.className = 'form-control';
                    newInput.id = 'fetch_status';
                    inputContainer.appendChild(newInput);
                }
            } else {
                const existingInput = document.getElementById('fetch_status');
                if (existingInput) {
                    inputContainer.removeChild(existingInput);
                }
            }
        });
        </script>
    </x-slot>
</x-layout>

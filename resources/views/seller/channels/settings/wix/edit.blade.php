<x-layout>
    <x-slot name="title">Edit</x-slot>

    <x-slot name="breadcrumbs">
       Wix Edit
    </x-slot>

    <x-slot name="page_header_title">
        <h1 class="page-header-title">{{ __('message.wix_create.edit_page_header_title') }}</h1>
    </x-slot>

    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="#" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{ __('message.back') }}</a>
        </div>
    </x-slot>

    <x-slot name="main">
        @if ($errors->any())
            <div class="alert alert-soft-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-sm-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        {{ __('message.wix_create.existing_data') }}
                    </div>
                </div>
            </div>

            <div class="card col-sm-8">
                <div class="card-body">
                    <!-- Nav -->
                    <div class="">
                        <ul class="nav nav-segment mb-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="nav-one-eg1-tab" href="#nav-one-eg1" data-bs-toggle="pill" data-bs-target="#nav-one-eg1" role="tab" aria-controls="nav-one-eg1" aria-selected="true">Basic Configuration</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link" id="nav-two-eg1-tab" href="#nav-two-eg1" data-bs-toggle="pill" data-bs-target="#nav-two-eg1" role="tab" aria-controls="nav-two-eg1" aria-selected="false">Advance Configuration</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="nav-three-eg1-tab" href="#nav-three-eg1" data-bs-toggle="pill" data-bs-target="#nav-three-eg1" role="tab" aria-controls="nav-three-eg1" aria-selected="false">Payment Mapping</a>
                            </li>
                        </ul>
                    </div>
                    <!-- End Nav -->
                    <form class="needs-validation" action="{{ route('wix.update', $wix->channel_id) }}" id="wix-form" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <!-- This is important for the update request -->
                        <input type="hidden" name="channel_code" value="wix">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="nav-one-eg1" role="tabpanel" aria-labelledby="nav-one-eg1-tab">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="brand_name" class="form-label">{{ __('message.wix_create.Communication_Brand_Name') }}</label>
                                        <input type="text" id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name', $wix->brand_name) }}" placeholder="Enter company name" required>
                                        @error('brand_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.enter_brand_name') }}</span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label for="channel_title" class="form-label">{{ __('message.wix_create.channel_name') }}</label>
                                        <input type="text" id="channel_title" name="channel_title" class="form-control" value="{{ old('channel_title', $wix->channel_title) }}" placeholder="Enter channel title" required>
                                        @error('channel_title')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.your_channal_name') }}</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="channel_url" class="form-label">{{ __('message.wix_create.channel_URL') }}</label>
                                        <input type="text" id="channel_url" name="channel_url" class="form-control" value="{{ old('channel_url', $wix->channel_url) }}" placeholder="Enter channel URL" required>
                                        @error('channel_url')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.your_channel_URL') }}</span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label for="client_id" class="form-label">{{ __('message.wix_create.client_id') }}</label>
                                        <input type="text" id="client_id" name="client_id" class="form-control" value="{{ old('client_id', $wix->client_id) }}" placeholder="Enter client Id" disabled>
                                        @error('client_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.client_id_error') }}</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- <div class="col-sm-6 mb-3">
                                        <label for="secret_key" class="form-label">{{ __('message.wix_create.api_password') }}</label>
                                        <input type="text" id="secret_key" name="secret_key" class="form-control" value="{{ old('secret_key', $wix->secret_key) }}" placeholder="Enter secret key">
                                        @error('secret_key')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.enter_your_API_password') }}</span>
                                    </div> -->
                                    <div class="col-sm-6 mb-3">
                                        <label for="is_address_same" class="form-label">{{ __('message.wix_create.billing_and_shipping_address') }}</label>
                                        <div class="input-group input-group-sm-vertical">
                                            <label class="form-control" for="is_address_same_yes">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="1" name="other_details[is_address_same]" required {{ old('other_details.is_address_same', isset($wix->other_details['is_address_same']) ? $wix->other_details['is_address_same'] : '1') == '1' ? 'checked' : '' }} id="is_address_same_yes">
                                                    <span class="form-check-label">{{ __('message.yes') }}</span>
                                                </span>
                                            </label>

                                            <label class="form-control" for="is_address_same_no">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="0" name="other_details[is_address_same]" required {{ old('other_details.is_address_same', isset($wix->other_details['is_address_same']) ? $wix->other_details['is_address_same'] : '1') == '0' ? 'checked' : '' }} id="is_address_same_no">
                                                    <span class="form-check-label">{{ __('message.no') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                        @error('other_details.is_address_same')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.enter_your_address') }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="brand_logo" class="form-label">{{ __('message.wix_create.channel_logo') }}
                                            <span class="form-label-secondary">({{ __('message.optional') }})</span>
                                        </label>
                                        @if (!is_null($wix->brand_logo))
                                            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/logos/' . $wix->brand_logo) }}" style="width:25px;">
                                        @endif
                                        <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                        @error('brand_logo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="fetch_status">{{ __('message.wix_create.status_to_fetch') }}</label><br>
                                        <input type="text" id="fetch_status" name="other_details[fetch_status]" class="form-control" value="{{ old('other_details.fetch_status', $wix->other_details['fetch_status'] ?? 'NOT_FULFILLED') }}" placeholder="Enter fetch status">
                                        @error('other_details.fetch_status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.enter_your_status') }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="pullUpdatedOrders" class="form-label">{{ __('message.wix_create.pull_updated_order') }}</label>
                                        <div class="input-group input-group-sm-vertical">
                                            <label class="form-control" for="pull_updated_active">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="1" name="other_details[pull_update_orders]" {{ old('pull_update_orders', $wix->other_details['pull_update_orders'] ?? '') == '1' ? 'checked' : '' }} id="pull_updated_active">
                                                    <span class="form-check-label">{{ __('message.yes') }}</span>
                                                </span>
                                            </label>

                                            <label class="form-control" for="pull_updated_inactive">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="0" name="other_details[pull_update_orders]" required {{ old('pull_update_orders', isset($wix->other_details['pull_update_orders']) ? $wix->other_details['pull_update_orders'] : '0') == '0' ? 'checked' : '' }} id="pull_updated_inactive">
                                                    <span class="form-check-label">{{ __('message.no') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                        @error('other_details.pull_update_orders')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <span class="invalid-feedback">{{ __('message.wix_create.your_pull_updated_orders') }}</span>
                                    </div>
                                </div>
                               
                                <div class="row">                                    

                                    <div class="col-sm-6 mb-3">
                                        <label for="statuses" class="form-label">{{ __('message.status') }}</label>
                                        <div class="input-group input-group-sm-vertical">
                                            <label class="form-control" for="status">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', $wix->status) == '1' ? 'checked' : '' }} id="status">
                                                    <span class="form-check-label">{{ __('message.active') }}</span>
                                                </span>
                                            </label>

                                            <label class="form-control" required for="status_inactive">
                                                <span class="form-check">
                                                    <input type="radio" class="form-check-input" value="0" name="status" {{ old('status', $wix->status) == '0' ? 'checked' : '' }} id="status_inactive">
                                                    <span class="form-check-label">{{ __('message.inactive') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                        @error('status')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback">{{ __('message.wix_create.enter_your_status') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-two-eg1" role="tabpanel" aria-labelledby="nav-two-eg1-tab">
                                <div class="col-sm-12 mb-3">
                                    <label for="pullUpdatedOrders" class="form-label">
                                       Auto Sync Orders
                                    </label>
                                    <div class="input-group input-group-sm-vertical">
                                        <label class="form-control d-flex align-items-center" style="cursor: pointer;">
                                            <input type="radio" class="form-check-input me-2" value="1" name="other_details[auto_sync]"
                                                {{ old('other_details.auto_sync', $wix->other_details['auto_sync'] ?? '0') == '1' ? 'checked' : '' }}
                                                required>
                                            <span class="form-check-label">Enable</span>
                                        </label>

                                        <label class="form-control d-flex align-items-center" style="cursor: pointer;">
                                            <input type="radio" class="form-check-input me-2" value="0" name="other_details[auto_sync]"
                                                {{ old('other_details.auto_sync', $wix->other_details['auto_sync'] ?? '0') == '0' ? 'checked' : '' }}
                                                required>
                                            <span class="form-check-label">Disable</span>
                                        </label>
                                    </div>
                                </div>
                             </div>
                            <div class="tab-pane fade" id="nav-three-eg1" role="tabpanel" aria-labelledby="nav-three-eg1-tab">
                             @if ($payment_types->isNotEmpty())
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-header-title">Payment Gateway Mapping</h4>
                                                </div>
                                                <table class="table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Payment Gateway Names</th>
                                                            <th>Payment Mode</th>
                                                            <th>Payment Mode</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($payment_types as $payment_type)
                                                            <tr>
                                                                <td>{{ $payment_type->gateway_name }}</td>
                                                                <td>
                                                                    <input type="radio" id="cod{{ $payment_type->id }}" class="form-check-input" name="payment_mapping[{{ $payment_type->id }}]" value="cod" @if (old('payment_mapping.' . $payment_type->id) == 'cod' || $payment_type->payment_mode == 'cod') checked @endif>
                                                                    <label class="form-check-label" for="cod{{ $payment_type->id }}">{{ __('message.woocommerce.cod') }}</label>
                                                                </td>
                                                                <td>
                                                                    <input type="radio" id="prepaid{{ $payment_type->id }}" class="form-check-input" name="payment_mapping[{{ $payment_type->id }}]" value="prepaid" @if (old('payment_mapping.' . $payment_type->id) == 'prepaid' || $payment_type->payment_mode == 'prepaid') checked @endif>
                                                                    <label class="form-check-label" for="prepaid{{ $payment_type->id }}">{{ __('message.woocommerce.prepaid') }}</label>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('message.update') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>


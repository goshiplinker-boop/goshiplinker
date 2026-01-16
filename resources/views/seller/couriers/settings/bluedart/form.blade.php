@csrf
@isset($bluedart)
    @method('PUT')
@endisset

<input type="hidden" name="courier_code" value="bluedart">

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="courier_title" class="form-label">{{ __('message.bluedart.set_tittle') }}</label>
        <input type="text" name="courier_title" id="courier_title" @isset($bluedart) readonly @endisset 
            class="form-control"
            value="{{ old('courier_title', $bluedart->courier_title ?? '') }}"
            placeholder="Enter Courier Title" required>
        @error('courier_title')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.set_tittle') }}</span>
    </div>

    <div class="col-sm-6 mb-3">
        <label for="login_id" class="form-label">{{ __('message.bluedart.login_id') }}</label>
        <input type="text" name="login_id" id="login_id"
            class="form-control"
            value="{{ old('login_id', $bluedart->login_id ?? '') }}"
            placeholder="Enter Login ID" required>
        @error('login_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_login_id') }}</span>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="licence_key" class="form-label">{{ __('message.bluedart.licence_key') }}</label>
        <input type="text" name="licence_key" id="licence_key"
            class="form-control"
            value="{{ old('licence_key', $bluedart->licence_key ?? '') }}"
            placeholder="Enter Licence Key" required>
        @error('licence_key')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_licence_key') }}</span>
    </div>

    <div class="col-sm-6 mb-3">
        <label for="tracking_key" class="form-label">{{ __('message.bluedart.tracking_key') }}</label>
        <input type="text" name="tracking_key" id="tracking_key"
            class="form-control"
            value="{{ old('tracking_key', $bluedart->tracking_key ?? '') }}"
            placeholder="{{ __('message.bluedart.tracking_key') }}" required>
        @error('tracking_key')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_tracking_key') }}</span>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="client_id" class="form-label">{{ __('message.bluedart.client_id') }}</label>
        <input type="text" name="client_id" id="client_id"
            class="form-control"
            value="{{ old('client_id', $bluedart->client_id ?? '') }}"
            placeholder="Enter client id" required>
        @error('client_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_client_id') }}</span>
    </div>

    <div class="col-sm-6 mb-3">
        <label for="client_secret" class="form-label">{{ __('message.bluedart.client_secret') }}</label>
        <input type="text" name="client_secret" id="client_secret"
            class="form-control"
            value="{{ old('client_secret', $bluedart->client_secret ?? '') }}"
            placeholder="Enter client secret" required>
        @error('client_secret')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_client_secret') }}</span>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="origin_area" class="form-label">{{ __('message.bluedart.origin_area') }}</label>
        <input type="text" name="origin_area" id="origin_area"
            class="form-control"
            value="{{ old('origin_area', $bluedart->origin_area ?? '') }}"
            placeholder="Enter Origin Area" required>
        @error('origin_area')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_origin_area') }}</span>
    </div>

    <div class="col-sm-6 mb-3">
        @php
            $selectedServiceType = old('service_type', $bluedart->service_type ?? 'A');
        @endphp
        <label for="service_type" class="form-label">{{ __('message.bluedart.service_type') }}</label>
        <select class="form-select" id="service_type" name="service_type" required>
            <option value="A" {{ $selectedServiceType == 'A' ? 'selected' : '' }}>Apex</option>
            <option value="AB2B" {{ $selectedServiceType == 'AB2B' ? 'selected' : '' }}>Apex (B2B)</option>
            <option value="AD" {{ $selectedServiceType == 'AD' ? 'selected' : '' }}>Bharat Dart</option>
            <option value="DB2B" {{ $selectedServiceType == 'DB2B' ? 'selected' : '' }}>Domestic Priority (B2B)</option>
            <option value="E" {{ $selectedServiceType == 'E' ? 'selected' : '' }}>Surface</option>
            <option value="EB2B" {{ $selectedServiceType == 'EB2B' ? 'selected' : '' }}>Surface (B2B)</option>
        </select>
        @error('service_type')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_service_type') }}</span>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="customer_code" class="form-label">{{ __('message.bluedart.customer_code') }}</label>
        <input type="text" name="customer_code" id="customer_code"
            class="form-control"
            value="{{ old('customer_code', $bluedart->customer_code ?? '') }}"
            placeholder="Enter Customer Code" required>
        @error('customer_code')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.bluedart.error_customer_code') }}</span>
    </div>

     <div class="col-sm-6 mb-3">
        <label class="form-label">{{ __('message.environment_type') }}</label>
        @php
            $env = old('env_type', $bluedart->env_type ?? 'dev'); // default dev
        @endphp
        <div class="input-group input-group-sm-vertical">
            <label class="form-control" for="env_type_dev">
                <span class="form-check">
                    <input  type="radio"
                            id="env_type_dev"
                            name="env_type"
                            value="dev"
                            class="form-check-input"
                            {{ $env === 'dev' ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.dev') }}</span>
                </span>
            </label>
            <label class="form-control" for="env_type_live">
                <span class="form-check">
                    <input  type="radio"
                            id="env_type_live"
                            name="env_type"
                            value="live"
                            class="form-check-input"
                            {{ $env === 'live' ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.live') }}</span>
                </span>
            </label>
        </div>
        @error('env_type')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.environment_type_error') }}</span>
    </div>

    <div class="col-sm-6 mb-3">
        <label class="form-label">{{ __('message.status') }}</label>
        <div class="input-group input-group-sm-vertical">
            <label class="form-control" for="status_yes">
                <span class="form-check">
                    <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                           {{ old('status', $bluedart->status ?? 1) == 1 ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.active') }}</span>
                </span>
            </label>
            <label class="form-control" for="status_no">
                <span class="form-check">
                    <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                           {{ old('status', $bluedart->status ?? '') == 0 ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.inactive') }}</span>
                </span>
            </label>
         </div>
        @error('status')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">Enter status</span>
    </div>
</div>

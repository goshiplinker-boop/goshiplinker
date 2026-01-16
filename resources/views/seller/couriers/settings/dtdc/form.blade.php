@csrf
 @isset($dtdc)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="dtdc">
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="courier_title" class="form-label">Set Title</label>
        <input  type="text" name="courier_title" id="courier_title" class="form-control"  value="{{ old('courier_title', $dtdc->courier_title ?? '') }}"  @isset($dtdc) readonly @endisset required  placeholder="{{ __('courier_title_placeholder') }}">
        @error('courier_title')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.set_tittle_error') }}</span>
    </div>
    <div class="col-sm-6 mb-3">
        <label for="shipment_mode" class="form-label">Shipping Mode</label>
        <select name="shipment_mode" id="shipment_mode" class="form-select" required>
            @php
                $selectedMode = old('shipment_mode', $dtdc->shipment_mode ?? 'B2C PRIORITY'); // default B2C PRIORITY
            @endphp
            <option value="B2C PRIORITY" {{ $selectedMode === 'B2C PRIORITY' ? 'selected' : '' }}>B2C PRIORITY</option>
            <option value="B2C SMART EXPRESS" {{ $selectedMode === 'B2C SMART EXPRESS' ? 'selected' : '' }}>B2C SMART EXPRESS</option>
            <option value="B2C PREMIUM" {{ $selectedMode === 'B2C PREMIUM' ? 'selected' : '' }}>B2C PREMIUM</option>
            <option value="B2C GROUND ECONOMY" {{ $selectedMode === 'B2C GROUND ECONOMY' ? 'selected' : '' }}>B2C GROUND ECONOMY</option>
            <option value="PRIORITY" {{ $selectedMode === 'PRIORITY' ? 'selected' : '' }}>PRIORITY</option>
            <option value="GROUND EXPRESS" {{ $selectedMode === 'GROUND EXPRESS' ? 'selected' : '' }}>GROUND EXPRESS</option>
            <option value="PREMIUM" {{ $selectedMode === 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
            <option value="GEC" {{ $selectedMode === 'GEC' ? 'selected' : '' }}>GEC</option>
            <option value="STD EXP-A" {{ $selectedMode === 'STD EXP-A' ? 'selected' : '' }}>STD EXP-A</option>
        </select>
        @error('shipment_mode')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc.error_shipment_mode') }}</span>
    </div>    
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="api_token" class="form-label">Shipment API Key</label>
        <input  type="text" name="api_key" id="api_key" class="form-control" value="{{ old('api_key', $dtdc->api_key ?? '') }}"  required placeholder="{{ __('api_key_placeholder') }}">
        @error('api_key')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc.error_api_key') }}</span>
    </div>   
    <div class="col-sm-6 mb-3">
        <label for="customer_code" class="form-label">Customer Code</label>
        <input  type="text" name="customer_code" id="customer_code" class="form-control"  value="{{ old('customer_code', $dtdc->customer_code ?? '') }}" required  placeholder="{{ __('customer_code_placeholder') }}">
        @error('customer_code')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.error_customer_code') }}</span>
    </div>    
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="tracking_username" class="form-label">Tracking Username</label>
        <input  type="text" name="tracking_username" id="tracking_username" class="form-control" value="{{ old('tracking_username', $dtdc->tracking_username ?? '') }}"  required placeholder="{{ __('tracking_username_placeholder') }}">
        @error('tracking_username')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc.error_tracking_username') }}</span>
    </div>
     <div class="col-sm-6 mb-3">
        <label for="tracking_password" class="form-label">Tracking Password</label>
        <input  type="text" name="tracking_password" id="tracking_password" class="form-control" value="{{ old('tracking_password', $dtdc->tracking_password ?? '') }}"  required placeholder="{{ __('tracking_password_placeholder') }}">
        @error('tracking_password')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc.error_tracking_password') }}</span>
    </div>     
</div>
<div class="row">
     <div class="col-sm-6 mb-3">
        <label class="form-label">{{ __('message.environment_type') }}</label>
        @php
            $env = old('env_type', $dtdc->env_type ?? 'dev'); // default dev
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
        @php
            $status = old('status', $dtdc->status ?? 1); // default active (1)
        @endphp
        <div class="input-group input-group-sm-vertical">
            <label class="form-control" for="status_yes">
                <span class="form-check">
                    <input  type="radio"
                            id="status_yes"
                            name="status"
                            value="1"
                            class="form-check-input"
                            {{ (int)$status === 1 ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.active') }}</span>
                </span>
            </label>
            <label class="form-control" for="status_no">
                <span class="form-check">
                    <input  type="radio"
                            id="status_no"
                            name="status"
                            value="0"
                            class="form-check-input"
                            {{ (int)$status === 0 ? 'checked' : '' }}>
                    <span class="form-check-label">{{ __('message.inactive') }}</span>
                </span>
            </label>
        </div>
        @error('status')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.error_status') }}</span>
    </div>
</div>
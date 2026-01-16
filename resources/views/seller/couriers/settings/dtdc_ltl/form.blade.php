@csrf
 @isset($dtdc_ltl)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="dtdc_ltl">
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="courier_title" class="form-label">Set Title</label>
        <input  type="text" name="courier_title" id="courier_title" class="form-control"  value="{{ old('courier_title', $dtdc_ltl->courier_title ?? '') }}" @isset($dtdc_ltl) readonly @endisset required  placeholder="{{ __('courier_title_placeholder') }}">
        @error('courier_title')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.set_tittle_error') }}</span>
    </div>
    <div class="col-sm-6 mb-3">
        <label for="shipment_mode" class="form-label">Shipping Mode</label>
        <select name="shipment_mode" id="shipment_mode" class="form-select" required>            
            <option value="SURFACE" selected >SURFACE(LTL)</option>
        </select>
        @error('shipment_mode')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc_ltl.error_shipment_mode') }}</span>
    </div>    
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="api_key" class="form-label">Shipment API Key</label>
        <input  type="text" name="api_key" id="api_key" class="form-control" value="{{ old('api_key', $dtdc_ltl->api_key ?? '') }}"  required placeholder="{{ __('api_key_placeholder') }}">
        @error('api_key')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc_ltl.error_api_key') }}</span>
    </div>   
    <div class="col-sm-6 mb-3">
        <label for="customer_code" class="form-label">Customer Code</label>
        <input  type="text" name="customer_code" id="customer_code" class="form-control"  value="{{ old('customer_code', $dtdc_ltl->customer_code ?? '') }}" required  placeholder="{{ __('customer_code_placeholder') }}">
        @error('customer_code')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.error_customer_code') }}</span>
    </div>    
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="api_token" class="form-label">Authentication Token</label>
        <input  type="text" name="api_token" id="api_token" class="form-control" value="{{ old('api_token', $dtdc_ltl->api_token ?? '') }}"  required placeholder="{{ __('auth_token_placeholder') }}">
        @error('api_token')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc_ltl.api_token') }}</span>
    </div>
     <div class="col-sm-6 mb-3">
        <label for="pay_basis" class="form-label">Pay Basis</label>
        <select name="pay_basis" id="pay_basis" class="form-select" required>   
            @php
                $selectedMode = old('shipment_mode', $dtdc->shipment_mode ?? ''); 
            @endphp
            <option value="TBB" {{ $selectedMode === 'TBB' ? 'selected' : '' }}>TBB</option>
            <option value="TO_PAY" {{ $selectedMode === 'TO_PAY' ? 'selected' : '' }}>BTO_PAY</option>         
            <option value="PAID" {{ $selectedMode === 'PAID' ? 'selected' : '' }} >PAID</option>
        </select>
        @error('pay_basis')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.dtdc_ltl.error_pay_basis') }}</span>
    </div> 
     
</div>
<div class="row">
     <div class="col-sm-6 mb-3">
        <label class="form-label">{{ __('message.environment_type') }}</label>
        @php
            $env = old('env_type', $dtdc_ltl->env_type ?? 'dev'); // default dev
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
            $status = old('status', $dtdc_ltl->status ?? 1); // default active (1)
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
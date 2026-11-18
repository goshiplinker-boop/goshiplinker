@csrf
 @isset($shadowfax)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="shadowfax">
<input type="hidden" name="courier_shipping_label" value="1">
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="courier_title" class="form-label">Set Title</label>
        <input  type="text" name="courier_title" id="courier_title" class="form-control"  value="{{ old('courier_title', $shadowfax->courier_title ?? '') }}" required  placeholder="{{ __('courier_title_placeholder') }}">
        @error('courier_title')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.set_tittle_error') }}</span>
    </div>
    <div class="col-sm-6 mb-3">
        <label for="api_token" class="form-label">Api Token</label>
        <input  type="text" name="api_token" id="api_token" class="form-control" value="{{ old('api_token', $shadowfax->api_token ?? '') }}"   placeholder="{{ __('api_token_placeholder') }}">
        @error('api_token')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{ __('message.shadowfax.error_api_token') }}</span>
    </div>    
</div>
<div class="row">    
     <div class="col-sm-6 mb-3">
        <label class="form-label">{{ __('message.environment_type') }}</label>
        @php
            $env = old('env_type', $shadowfax->env_type ?? 'dev'); // default dev
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
            $status = old('status', $shadowfax->status ?? 1); // default active (1)
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
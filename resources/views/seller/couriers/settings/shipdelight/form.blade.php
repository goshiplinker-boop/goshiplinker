@csrf
 @isset($shipdelight)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="shipdelight">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" 
                   required @isset($shipdelight) readonly @endisset placeholder="Enter Title"
                   value="{{ old('courier_title', $shipdelight->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="shipdelight_courier_id" class="form-label">Courier Name</label>
            <input type="text" name="shipdelight_courier_id" id="shipdelight_courier_id" class="form-control" 
                    placeholder="Enter Courier Name"
                   value="{{ old('shipdelight_courier_id', $shipdelight->shipdelight_courier_id ?? '') }}">
            @error('shipdelight_courier_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Courier Name</span>
        </div>
        
       
    </div>
    <div class="row">  
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Api Key</label>
            <input type="text" name="api_key" id="api_key" class="form-control" 
                   required placeholder="Enter Api Key"
                   value="{{ old('api_key', $shipdelight->api_key ?? '') }}">
            @error('api_key')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Key</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="vendor_code" class="form-label">Vendor Code</label>
            <input type="text" name="vendor_code" id="vendor_code" class="form-control" 
                   required placeholder="Enter Vendor Code"
                   value="{{ old('vendor_code', $shipdelight->vendor_code ?? '') }}">
            @error('vendor_code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Vendor Code</span>
        </div>
        
    </div>
    <div class="row">  
        <div class="col-sm-6 mb-3">
            <label class="form-label">Shipping Label</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="courier_shipping_label_yes">
                    <span class="form-check">
                        <input type="radio" id="courier_shipping_label_yes" name="courier_shipping_label" value="0" class="form-check-input"
                               {{ old('courier_shipping_label', $shipdelight->courier_shipping_label ?? 0) == 0 ? 'checked' : '' }}>
                        <span class="form-check-label">Parcelmind Shipping Label</span>
                    </span>
                </label>
                <label class="form-control" for="courier_shipping_label_no">
                    <span class="form-check">
                        <input type="radio" id="courier_shipping_label_no" name="courier_shipping_label" value="1" class="form-check-input"
                               {{ old('courier_shipping_label', $shipdelight->courier_shipping_label ?? '') == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">shipdelight Shipping Label</span>
                    </span>
                </label>
            </div>
            @error('courier_shipping_label')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">shipdelight Shipping Label</span>
        </div> 
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.environment_type') }}</label>
            @php
                $env = old('env_type', $shipdelight->env_type ?? 'dev'); // default dev
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
       
        
    </div>
        
    <div clas="row">
        
        
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                            {{ old('status', $shipdelight->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                            {{ old('status', $shipdelight->status ?? '') == 0 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.inactive') }}</span>
                    </span>
                </label>
            </div>
        </div>      
       
    </div>
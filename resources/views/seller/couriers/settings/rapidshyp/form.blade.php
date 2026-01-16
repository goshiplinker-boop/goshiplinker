@csrf
 @isset($rapidshyp)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="rapidshyp">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" 
                   required @isset($rapidshyp) readonly @endisset placeholder="Enter Title"
                   value="{{ old('courier_title', $rapidshyp->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Api Key</label>
            <input type="text" name="api_key" id="api_key" class="form-control" 
                   required placeholder="Enter Api Key"
                   value="{{ old('api_key', $rapidshyp->api_key ?? '') }}">
            @error('api_key')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Key</span>
        </div>
       
    </div>
    <div class="row">  
        <div class="col-sm-6 mb-3">
            <label class="form-label">Shipping Label</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="courier_shipping_label_yes">
                    <span class="form-check">
                        <input type="radio" id="courier_shipping_label_yes" name="courier_shipping_label" value="0" class="form-check-input"
                               {{ old('courier_shipping_label', $rapidshyp->courier_shipping_label ?? 0) == 0 ? 'checked' : '' }}>
                        <span class="form-check-label">Parcelmind Shipping Label</span>
                    </span>
                </label>
                <label class="form-control" for="courier_shipping_label_no">
                    <span class="form-check">
                        <input type="radio" id="courier_shipping_label_no" name="courier_shipping_label" value="1" class="form-check-input"
                               {{ old('courier_shipping_label', $rapidshyp->courier_shipping_label ?? '') == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">RapidShyp Shipping Label</span>
                    </span>
                </label>
            </div>
            @error('courier_shipping_label')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">RapidShyp Shipping Label</span>
        </div>       
        <input type="hidden" name="env_type" value="live" >
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $rapidshyp->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $rapidshyp->status ?? '') == 0 ? 'checked' : '' }}>
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

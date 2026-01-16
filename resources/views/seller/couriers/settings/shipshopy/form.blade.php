@csrf
 @isset($shipshopy)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="shipshopy">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" 
                   required @isset($shipshopy) readonly @endisset placeholder="Enter Title"
                   value="{{ old('courier_title', $shipshopy->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="shipshopy_courier_id" class="form-label">Courier Id</label>
            <input type="text" name="shipshopy_courier_id" id="shipshopy_courier_id" class="form-control"  placeholder="Enter shipshopy Courier id"  value="{{ old('shipshopy_courier_id', $shipshopy->shipshopy_courier_id ?? '') }}">   
            @error('shipshopy_courier_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror         
            <span class="invalid-feedback">Enter shipshopy courier id</span>
        </div>  
       
    </div>
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="api_public_key" class="form-label">Api Public Key</label>
            <input type="text" name="api_public_key" id="api_public_key" class="form-control" 
                   required placeholder="Enter api_public_key"
                   value="{{ old('api_public_key', $shipshopy->api_public_key ?? '') }}">
            @error('api_public_key')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Public key</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="api_private_key" class="form-label">Api Private Key</label>
            <input type="text" name="api_private_key" id="api_private_key" class="form-control" 
                   required placeholder="Enter Api Private Key"
                   value="{{ old('api_private_key', $shipshopy->api_private_key ?? '') }}">
            @error('api_private_key')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Private Key</span>
        </div>
    </div>
    <div class="row">  
        <div class="col-sm-6 mb-3">
            <label for="order_type" class="form-label">Order Type</label>
            <select name="order_type" id="order_type" class="form-select" required>
                <option value="NON ESSENTIALS" {{ old('order_type', $shipshopy->order_type ?? '') == 'NON ESSENTIALS' ? 'selected' : '' }}>NON ESSENTIALS</option>
                <option value="ESSENTIALS" {{ old('order_type', $shipshopy->order_type ?? '') == 'ESSENTIALS' ? 'selected' : '' }}>ESSENTIALS</option>                
            </select>
            <span class="invalid-feedback">Enter Order Type</span>
        </div>
        <input type="hidden" name="env_type" value="live" >
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $shipshopy->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $shipshopy->status ?? '') == 0 ? 'checked' : '' }}>
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

@csrf
 @isset($xpressbees_prepaid)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="xpressbees_prepaid">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" @isset($xpressbees_prepaid) readonly @endisset
                   required placeholder="Enter Title"
                   value="{{ old('courier_title', $xpressbees_prepaid->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>

        <div class="col-sm-6 mb-3">
            <label for="shipment_mode" class="form-label">Shipment mode</label>
            <select name="shipment_mode" id="shipment_mode" class="form-select" required>
                <option value="surface" {{ old('shipment_mode', $xpressbees_prepaid->shipment_mode ?? '') == 'surface' ? 'selected' : '' }}>Surface</option>
                <option value="express" {{ old('shipment_mode', $xpressbees_prepaid->shipment_mode ?? '') == 'express' ? 'selected' : '' }}>Express</option>
            </select>
            <span class="invalid-feedback">Enter Shipment Mode</span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   required placeholder="Enter username"
                   value="{{ old('username', $xpressbees_prepaid->username ?? '') }}">
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Username</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="text" name="password" id="password" class="form-control" 
                   required placeholder="Enter password"
                   value="{{ old('password', $xpressbees_prepaid->password ?? '') }}">
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Password</span>
        </div>
    </div>
    <div class="row">    
        <div class="col-sm-6 mb-3">
            <label for="xb_courier_id" class="form-label">Courier Id</label>
            <select name="xb_courier_id" id="xb_courier_id" class="form-select" required>
                <option value="" >Courier id</option>
                <option value="6" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '6' ? 'selected' : '' }}>Air Xpressbees 0.5 K.G</option>
                <option value="1" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '1' ? 'selected' : '' }}>Same Day DeliverySurface Xpressbees 0.5 K.G</option>
                <option value="12298" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '12298' ? 'selected' : '' }}>Xpressbees 1 K.G</option>
                <option value="2" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '2' ? 'selected' : '' }}>Xpressbees 2 K.G</option>
                <option value="3" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '3' ? 'selected' : '' }}>Xpressbees 5 K.G</option>
                <option value="4" {{ old('xb_courier_id', $xpressbees_prepaid->xb_courier_id ?? '') == '4' ? 'selected' : '' }}>Xpressbees 10 K.G</option>                
            </select>
            <span class="invalid-feedback">Enter Xpressbees courier id</span>
        </div>    
        <input type="hidden" name="env_type" value="live" >
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $xpressbees_prepaid->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $xpressbees_prepaid->status ?? '') == 0 ? 'checked' : '' }}>
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

@csrf
 @isset($xpressbees_postpaid)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="xpressbees_postpaid">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" @isset($xpressbees_postpaid) readonly @endisset
                   required placeholder="Enter Title"
                   value="{{ old('courier_title', $xpressbees_postpaid->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>

        <div class="col-sm-6 mb-3">
            <label for="shipment_mode" class="form-label">Shipment mode</label>
            <select name="shipment_mode" id="shipment_mode" class="form-select" required>
                <option value="surface" {{ old('shipment_mode', $xpressbees_postpaid->shipment_mode ?? '') == 'surface' ? 'selected' : '' }}>Surface</option>
                <option value="express" {{ old('shipment_mode', $xpressbees_postpaid->shipment_mode ?? '') == 'express' ? 'selected' : '' }}>Express</option>
            </select>
            <span class="invalid-feedback">Enter Shipment Mode</span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   required placeholder="Enter username"
                   value="{{ old('username', $xpressbees_postpaid->username ?? '') }}">
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Username</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="text" name="password" id="password" class="form-control" 
                   required placeholder="Enter password"
                   value="{{ old('password', $xpressbees_postpaid->password ?? '') }}">
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Password</span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="secret_key" class="form-label">Secret Key</label>
            <input type="text" name="secret_key" id="secret_key" class="form-control" 
                   required placeholder="Enter secret key"
                   value="{{ old('secret_key', $xpressbees_postpaid->secret_key ?? '') }}">
            @error('secret_key')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Secret Key</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="xbkey" class="form-label">XBKey</label>
            <input type="text" name="xbkey" id="xbkey" class="form-control" 
                   required placeholder="Enter XBKey"
                   value="{{ old('xbkey', $xpressbees_postpaid->xbkey ?? '') }}">
            @error('xbkey')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter XBKey</span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="business_account_name" class="form-label">Business Account Name</label>
            <input type="text" name="business_account_name" id="business_account_name" class="form-control" 
                   required placeholder="Enter business account name"
                   value="{{ old('business_account_name', $xpressbees_postpaid->business_account_name ?? '') }}">
            @error('business_account_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter business account name</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="service_type" class="form-label">Service Type</label>
            <select name="service_type" id="service_type" class="form-select" required>
                <option value="SD" {{ old('service_type', $xpressbees_postpaid->service_type ?? '') == 'SD' ? 'selected' : '' }}>Standard Delivery</option>
                <option value="SDD" {{ old('service_type', $xpressbees_postpaid->service_type ?? '') == 'SDD' ? 'selected' : '' }}>Same Day Delivery</option>
                <option value="NDD" {{ old('service_type', $xpressbees_postpaid->service_type ?? '') == 'NDD' ? 'selected' : '' }}>Next Day Delivery</option>
                <option value="IntraSDD" {{ old('service_type', $xpressbees_postpaid->service_type ?? '') == 'IntraSDD' ? 'selected' : '' }}>IntraCity</option>
            </select>
            <span class="invalid-feedback">Enter Service Type</span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="version" class="form-label">Version</label>
            <select name="version" id="version" class="form-select" required>
                <option value="v1" {{ old('version', $xpressbees_postpaid->version ?? '') == 'v1' ? 'selected' : '' }}>V1</option>
                <option value="v2" {{ old('version', $xpressbees_postpaid->version ?? '') == 'v2' ? 'selected' : '' }}>V2</option>
            </select>
            <span class="invalid-feedback">Enter Version</span>
        </div>

       <input type="hidden" name="env_type" value="live" >
       <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $xpressbees_postpaid->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $xpressbees_postpaid->status ?? '') == 0 ? 'checked' : '' }}>
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

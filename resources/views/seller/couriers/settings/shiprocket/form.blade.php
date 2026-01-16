@csrf
 @isset($shiprocket)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="shiprocket">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control" 
                   required @isset($shiprocket) readonly @endisset placeholder="Enter Title"
                   value="{{ old('courier_title', $shiprocket->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="shiprocket_courier_id" class="form-label">Courier Id</label>
            <input type="text" name="shiprocket_courier_id" id="shiprocket_courier_id" class="form-control"  placeholder="Enter shiprocket Courier id"  value="{{ old('shiprocket_courier_id', $shiprocket->shiprocket_courier_id ?? '') }}">   
            @error('shiprocket_courier_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror         
            <span class="invalid-feedback">Enter shiprocket courier id</span>
        </div>  
       
    </div>
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Api Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   required placeholder="Enter username"
                   value="{{ old('username', $shiprocket->username ?? '') }}">
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Username</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="password" class="form-label">Api Password</label>
            <input type="text" name="password" id="password" class="form-control" 
                   required placeholder="Enter password"
                   value="{{ old('password', $shiprocket->password ?? '') }}">
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Api Password</span>
        </div>
    </div>
    <div class="row">  
        <div class="col-sm-6 mb-3">
            <label for="shipment_mode" class="form-label">Shipment mode</label>
            <select name="shipment_mode" id="shipment_mode" class="form-select" required>
                <option value="surface" {{ old('shipment_mode', $shiprocket->shipment_mode ?? '') == 'surface' ? 'selected' : '' }}>Surface</option>
                <option value="air" {{ old('shipment_mode', $shiprocket->shipment_mode ?? '') == 'air' ? 'selected' : '' }}>Air</option>
            </select>
            <span class="invalid-feedback">Enter Shipment Mode</span>
        </div>
        <input type="hidden" name="env_type" value="live" >
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $shiprocket->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $shiprocket->status ?? '') == 0 ? 'checked' : '' }}>
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

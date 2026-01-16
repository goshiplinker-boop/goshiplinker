@csrf
 @isset($nimbus_post)
    @method('PUT')
 @endisset
<input type="hidden" name="courier_code" value="nimbus_post">

    <!-- Form Fields -->
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="courier_title" class="form-label">Set Title</label>
            <input type="text" name="courier_title" id="courier_title" class="form-control"  @isset($nimbus_post) readonly @endisset 
                   required placeholder="Enter Title"
                   value="{{ old('courier_title', $nimbus_post->courier_title ?? '') }}">
            @error('courier_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter title</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="nimbus_post_courier_id" class="form-label">Courier Id</label>
            <input type="text" name="nimbus_post_courier_id" id="nimbus_post_courier_id" class="form-control"  placeholder="Enter nimbus_post Courier id"  value="{{ old('nimbus_post_courier_id', $nimbus_post->nimbus_post_courier_id ?? '') }}">            
            @error('nimbus_post_courier_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror  
            <span class="invalid-feedback">Enter nimbus_post courier id</span>
        </div>         
    </div>
    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="username" class="form-label">Email</label>
            <input type="text" name="username" id="username" class="form-control" 
                   required placeholder="Enter username"
                   value="{{ old('username', $nimbus_post->username ?? '') }}">
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Email</span>
        </div>
        <div class="col-sm-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="text" name="password" id="password" class="form-control" 
                   required placeholder="Enter password"
                   value="{{ old('password', $nimbus_post->password ?? '') }}">
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">Enter Password</span>
        </div>
    </div>
    <div class="row">
        <input type="hidden" name="env_type" value="live" >
        <div class="col-sm-6 mb-3">
            <label class="form-label">{{ __('message.status') }}</label>
            <div class="input-group input-group-sm-vertical">
                <label class="form-control" for="status_yes">
                    <span class="form-check">
                        <input type="radio" id="status_yes" name="status" value="1" class="form-check-input"
                               {{ old('status', $nimbus_post->status ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('message.active') }}</span>
                    </span>
                </label>
                <label class="form-control" for="status_no">
                    <span class="form-check">
                        <input type="radio" id="status_no" name="status" value="0" class="form-check-input"
                               {{ old('status', $nimbus_post->status ?? '') == 0 ? 'checked' : '' }}>
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

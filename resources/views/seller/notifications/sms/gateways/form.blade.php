@csrf
<div class="row">
    <div class="col-md-6">
        <label class="form-label">{{__('message.gateway_form.gateway_name')}}</label>
        <input type="text" name="gateway_name" value="{{ old('gateway_name', $sms_gateway->gateway_name ?? '') }}" class="form-control" placeholder="Enter gateway name" required>
        @error('gateway_name')
                <span class="text-danger">{{ $message }}</span>
        @enderror
        <span class="invalid-feedback">{{__('message.gateway.gateway_name_error')}}</span>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">HTTP Method</label>
            <select name="http_method" class="form-control" required>
                <option value="POST" {{ old('http_method', $sms_gateway->http_method ?? '') == 'POST' ? 'selected' : '' }}>POST</option>
                <option value="GET" {{ old('http_method', $sms_gateway->http_method ?? '') == 'GET' ? 'selected' : '' }}>GET</option>
            </select>
            @error('http_method')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.http_method_error')}}</span>
        </div>
    </div>

    <div class="col-md-14">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.genrate_url')}}</label>
            <input type="url" name="gateway_url" value="{{ old('gateway_url', $sms_gateway->gateway_url ?? '') }}" class="form-control" placeholder="Enter gateway url" required>
            @error('gateway_url')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.gateway_url_error')}}</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.mobile')}}</label>
            <input type="text" name="mobile" value="{{ old('mobile', $sms_gateway->mobile ?? '') }}" class="form-control"  placeholder="Enter mobile key" required>
            @error('mobile')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.mobile_error')}}</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.mobile_value')}}</label>
            <input type="text" name="mobile"  placeholder="Dynamic mobile number"class="form-control"  disabled>
            @error('mobile')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.mobile_error')}}</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.DLT_header_name')}}</label>
            <input type="text" name="dlt_header_name" value="{{ old('dlt_header_name', $sms_gateway->dlt_header_name ?? '') }}" class="form-control" placeholder="Enter header key" required>
            @error('dlt_header_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.dlt_header_name_error')}}</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{ __('message.gateway_form.dlt_header_ID') }}</label>
            <div class="tom-select-custom">
                <select class="js-select form-select" name="dlt_header_id" id="dlt-header-id" required
                    data-hs-tom-select-options='{
                        "placeholder": "Select header ID",
                        "hideSearch": true
                    }'>
                    <option value="">Select Header ID</option>
                    @foreach ($settings as $setting)
                        <option value="{{ $setting->header_id }}"
                            {{ old('dlt_header_id', $sms_gateway->dlt_header_id ?? '') == $setting->header_id ? 'selected' : '' }}>
                            {{ $setting->header_id }}
                        </option>
                    @endforeach
                </select>
            </div>

            @error('dlt_header_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{ __('message.gateway.dlt_header_id_error') }}</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.dlt_template_name')}}</label>
            <input type="text" name="dlt_template_name"  class="form-control" value="{{ old('dlt_template_name', $sms_gateway->dlt_template_name ?? '') }}"  placeholder="Enter template key parameter" required>
            @error('dlt_template_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.dlt_template_name_error')}}</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.template_name_value')}}</label>
            <input type="text" name="dlt_template_name"  class="form-control" placeholder="Dynamic SMS template content" disabled>
            @error('dlt_template_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.dlt_template_name_error')}}</span>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.dlt_template_id')}}</label>
            <input type="text" name="dlt_template_id" value="{{ old('dlt_template_id', $sms_gateway->dlt_template_id ?? '') }}" class="form-control"  placeholder="Enter template ID key paramter" required>
            @error('dlt_template_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.dlt_template_id_error')}}</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">{{__('message.gateway_form.template_id_value')}}</label>
            <input type="text" name="dlt_template_id" class="form-control" placeholder="Dynamic gateway template id"  disabled>
            @error('dlt_template_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <span class="invalid-feedback">{{__('message.gateway.dlt_template_id_error')}}</span>
        </div>
    </div>
    <div class="col-md-6" id="other-parameters">
        <label class="form-label">{{ __('message.gateway_form.other_parameter') }}</label>

        @php
            $otherParameters = old('other_parameters', $sms_gateway->other_parameters ?? []);
        @endphp

        <div id="keyValueContainer">
            @forelse ($otherParameters as $index => $parameter)
                <div class="row mb-2 key-value-row" data-index="{{ $index }}">
                    <div class="col">
                        <input type="text" name="other_parameters[{{ $index }}][key]" class="form-control" placeholder="Key"
                            value="{{ $parameter['key'] ?? '' }}" required>
                    </div>
                    <div class="col">
                        <input type="text" name="other_parameters[{{ $index }}][value]" class="form-control" placeholder="Value"
                            value="{{ $parameter['value'] ?? '' }}" required>
                    </div>
                    <div class="col-auto">
                        @if ($loop->first)
                            <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+</button>
                        @else
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">-</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="row mb-2 key-value-row" data-index="0">
                    <div class="col">
                        <input type="text" name="other_parameters[0][key]" class="form-control" placeholder="Key" required>
                    </div>
                    <div class="col">
                        <input type="text" name="other_parameters[0][value]" class="form-control" placeholder="Value" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+</button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
      <div class="col-sm-6 mb-3">
        <label for="statuses" class="form-label">{{ __('message.status') }}</label>

        <div class="input-group input-group-sm-vertical">
            <label class="form-control">
                <span class="form-check">
                    <input 
                        type="radio" 
                        class="form-check-input" 
                        value="1" 
                        name="status"
                        id="status_active"
                         {{ old('status', $sms_gateway->status ?? '1') == '1' ? 'checked' : '' }}
                        required>
                    <span class="form-check-label">{{ __('message.active') }}</span>
                </span>
            </label>

            <label class="form-control">
                <span class="form-check">
                    <input 
                        type="radio" 
                        class="form-check-input" 
                        value="0" 
                        name="status"
                        id="status_inactive"
                        {{ old('status', $sms_gateway->status ?? '1') == '0' ? 'checked' : '' }}
                        required>
                    <span class="form-check-label">{{ __('message.inactive') }}</span>
                </span>
            </label>
        </div>

        @error('status')
            <div class="text-danger">{{ $message }}</div>
        @enderror
        <div class="invalid-feedback">{{ __('message.error_status') }}</div>
    </div>
</div>
<script>
    let rowIndex = document.querySelectorAll('#keyValueContainer .key-value-row').length;

    function addRow() {
        const container = document.getElementById("keyValueContainer");

        const div = document.createElement("div");
        div.classList.add("row", "mb-2", "key-value-row");
        div.setAttribute("data-index", rowIndex);

        div.innerHTML = `
            <div class="col">
                <input type="text" name="other_parameters[${rowIndex}][key]" class="form-control" placeholder="Key" required>
            </div>
            <div class="col">
                <input type="text" name="other_parameters[${rowIndex}][value]" class="form-control" placeholder="Value" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">-</button>
            </div>
        `;

        container.appendChild(div);
        rowIndex++;
    }

    function removeRow(button) {
        const row = button.closest('.key-value-row');
        row.remove();
        reindexRows();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#keyValueContainer .key-value-row');
        rowIndex = 0;
        rows.forEach(row => {
            row.setAttribute('data-index', rowIndex);
            const inputs = row.querySelectorAll('input');
            inputs[0].setAttribute('name', `other_parameters[${rowIndex}][key]`);
            inputs[1].setAttribute('name', `other_parameters[${rowIndex}][value]`);
            rowIndex++;
        });
    }
</script>

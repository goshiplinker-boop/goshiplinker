<x-layout>
    <x-slot name="title">{{__('message.template_create.tittle')}}</x-slot>
    <x-slot name="breadcrumbs">Notifications</x-slot>
    <x-slot name="page_header_title">
        <h1 class="page-header-title">{{__('message.template_create.page_tittle')}}</h1>
    </x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('sms_templates') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
            </div>
        </div>
    </x-slot>

    <x-slot name="main">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('sms_template_store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">{{ __('message.template_create.order_status') }}</label>
                                <div class="tom-select-custom">
                                    <select name="order_status" class="js-select form-select" required autocomplete="off"
                                        data-hs-tom-select-options='{
                                            "placeholder": "Select Status",
                                            "hideSearch": true
                                        }'>
                                        <option value="">Select Status</option>
                                        @foreach($shipment_statuses as $shipment_status)
                                            <option value="{{ $shipment_status->code }}" {{ old('order_status') == $shipment_status->code ? 'selected' : '' }}>
                                                {{ $shipment_status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                       <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">{{__('message.template_create.registration_ID')}}</label>
                                <input type="text" name="template_registration_id" class="form-control" value="{{ old('template_registration_id') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-4">
                                <label class="form-label">{{__('message.template_create.message_content')}}</label>
                                <textarea name="message_content" class="form-control" rows="4" required>{{ old('message_content') }}</textarea>
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
                                            id="status"
                                            {{ old('status', '1') == '1' ? 'checked' : '' }} 
                                            required
                                        >
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
                                            {{ old('status') == '0' ? 'checked' : '' }} 
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
                        <div class="col-sm-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('message.save') }}</button>
                        </div>
                </form>
            </div>
        </div>
    </x-slot>
</x-layout>

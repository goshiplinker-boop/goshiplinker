<x-layout>
    <x-slot name="title"> Edit </x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('delhivery.edit', $delhivery) }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.delhivery.edit_page_header_title')}}</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route(panelPrefix().'.couriers_list') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> Back</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{__('message.delhivery.integration_steps')}}</p>
                        <div id="integrateStepsData" class="integrateStepsData descColor">
                            <div class="genInfoList">
                                <p>{!!__('message.delhivery.guidelines_details')!!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-soft-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif
                    <form class="needs-validation" method="POST"
                        action="{{ route(panelPrefix().'.delhivery.update', $delhivery->courier_id) }}" id="delhivery-form" novalidate>
                        @csrf
                        @method('PUT')
                        <!-- This is important for the update method -->
                        <input type="hidden" name="courier_code" value="delhivery">
                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_title" class="form-label">{{ __('Set Title') }}</label>
                                <input type="text" name="courier_title" id="courier_title" class="form-control" value="{{ old('courier_title', $delhivery->courier_title) }}" readonly required placeholder="Enter the courier title">
                                @error('courier_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.set_tittle_error')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="shipment_mode" class="form-label">{{__('message.delhivery.add_shipment_mode')}}</label>
                                <select name="shipment_mode" id="form-select" class="form-select" required>
                                    <option value="surface" {{ old('shipment_mode', $delhivery->shipment_mode) == 'surface' ? 'selected' : '' }}> Surface</option>
                                    <option value="express" {{ old('shipment_mode', $delhivery->shipment_mode) == 'express' ? 'selected' : '' }}> Express</option>
                                </select>
                                @error('shipment_mode')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.delhivery.error_add_shipment_mode')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="api_token" class="form-label">{{ __('API Token') }}</label>
                                <input type="text" name="api_token" id="api_token" class="form-control" value="{{ old('api_token', $delhivery->api_token) }}" required placeholder="{{ __('Enter API Token') }}">
                                @error('api_token')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.delhivery.error_api_token')}}</span>
                            </div>
                            <!-- Radio buttons for status -->
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">{{__('message.environment_type')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="env_type_dev">
                                        <span class="form-check">
                                            <input type="radio" id="env_type_dev" name="env_type" value="dev" class="form-check-input"{{ $delhivery->env_type === 'dev' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.dev')}}</label>
                                        </span>
                                    </label>
                                    <label class="form-control" for="env_type_live">
                                        <span class="form-check">
                                            <input type="radio" id="env_type_live" name="env_type" value="live" class="form-check-input" {{ $delhivery->env_type === 'live' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.live')}}</label>
                                        </span>
                                    </label>
                                </div>
                                @error('env_type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.environment_type_error')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status_yes">
                                        <span class="form-check">
                                            <input type="radio" id="status_yes" name="status" value="1" class="form-check-input" {{ $delhivery->status ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.active')}}</label>
                                    </span>
                                    </label>
                                    <label class="form-control" for="status_no">
                                        <span class="form-check">
                                            <input type="radio" id="status_no" name="status" value="0" class="form-check-input" {{ !$delhivery->status ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.inactive')}}</label>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.error_status')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
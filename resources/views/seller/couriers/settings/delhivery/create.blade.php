<x-layout>
    <x-slot name="title">{{__('message.delhivery.heading_title')}}</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('delhivery.create') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.delhivery.page_header_title')}}</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route(panelPrefix().'.couriers_list') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i>{{__('message.back')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{__('message.delhivery.guidelines')}}</p>
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
                    <form class="needs-validation" method="POST" action="{{ route(panelPrefix().'.delhivery.store') }}" id="delhivery-form" novalidate>
                        @csrf
                        <input type="hidden" name="courier_code" value="delhivery">
                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_title" class="form-label">{{ __('message.delhivery.set_tittle')}}</label>
                                <input type="text" name="courier_title" id="courier_title" class="form-control" required placeholder="{{__('message.delhivery.set_tittle_placeholder')}}">
                                @error('courier_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.delhivery.error_title')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="client_secret_key" class="form-label">{{ __('message.delhivery.shipment_mode') }}</label>
                                <select name="shipment_mode" id="form-select" class="form-select" required>
                                    <option value="surface"> Surface </option>
                                    <option value="express">Express</option>
                                </select>
                                <span class="invalid-feedback">{{ __('message.delhivery.error_shipment_mode') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="api_token" class="form-label">{{ __('message.delhivery.api_token') }}</label>
                                <input type="text" name="api_token" id="api_token" class="form-control" required placeholder="{{__('message.delhivery.api_token_placeholder')}}">
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
                                            <input type="radio" id="env_type_dev" name="env_type" value="dev" class="form-check-input" checked>
                                            <span class="form-check-label">{{__('message.dev')}}</label>
                                        </span>
                                    </label>
                                    <label class="form-control" for="env_type_live">
                                        <span class="form-check">
                                            <input type="radio" id="env_type_live" name="env_type" value="live" class="form-check-input">
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
                        <!-- Radio buttons for environment type -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status_yes">
                                        <span class="form-check">
                                            <input type="radio" id="status_yes" name="status" value="1" class="form-check-input" checked>
                                            <span class="form-check-label">{{__('message.active')}}</label>
                                        </span>
                                    </label>
                                    <label class="form-control" for="status_no">
                                        <span class="form-check">
                                            <input type="radio" id="status_no" name="status" value="0" class="form-check-input">
                                            <span class="form-check-label">{{__('message.inactive')}}</label>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.set_tittle_error')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
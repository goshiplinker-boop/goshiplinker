<x-layout>
    <x-slot name="title"> Edit </x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('ekart.edit', $ekart) }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.ekart.edit_page_header_title')}}</h1></x-slot>
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
                        <p class="link">{{__('message.ekart.integration_steps')}}</p>
                        <div id="integrateStepsData" class="integrateStepsData descColor">
                            <div class="genInfoList">
                                <p>{!!__('message.ekart.guidelines_details')!!}</p>
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
                        action="{{ route(panelPrefix().'.ekart.update', $ekart->courier_id) }}" id="ekart-form" novalidate>
                        @csrf
                        @method('PUT')
                        <!-- This is important for the update method -->
                        <input type="hidden" name="courier_code" value="ekart">
                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_title" class="form-label">{{ __('Set Title') }}</label>
                                <input type="text" name="courier_title" id="courier_title" class="form-control" value="{{ old('courier_title', $ekart->courier_title) }}" readonly required placeholder="Enter the courier title">
                                @error('courier_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.set_tittle_error')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="merchant_code" class="form-label">{{ __('message.ekart.merchant_code')}}</label>
                                <input type="text" name="merchant_code" id="merchant_code" class="form-control" value="{{ old('merchant_code', $ekart->merchant_code) }}" required placeholder="{{__('message.ekart.merchant_code_placeholder')}}">
                                @error('merchant_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_merchant_code')}}</span>
                            </div>                                                    
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="authorization_code" class="form-label">{{ __('message.ekart.authorization_code')}}</label>
                                <input type="text" name="authorization_code" id="authorization_code" class="form-control" value="{{ old('authorization_code', $ekart->authorization_code) }}" required placeholder="{{__('message.ekart.authorization_code_placeholder')}}">
                                @error('authorization_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_authorization_code')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="goods_category" class="form-label">{{ __('message.ekart.goods_category') }}</label>
                                <select name="goods_category" id="goods_category" class="form-select">
                                    <option value="NON_ESSENTIAL"  {{ old('goods_category', $ekart->goods_category) == 'NON_ESSENTIAL' ? 'selected' : '' }} >NON_ESSENTIAL</option>
                                    <option value="ESSENTIAL"  {{ old('goods_category', $ekart->goods_category) == 'ESSENTIAL' ? 'selected' : '' }} >ESSENTIAL</option>
                                </select>
                                @error('goods_category')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_goods_category') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="service_code" class="form-label">{{ __('message.ekart.service_code') }}</label>
                                <select name="service_code" id="service_code" class="form-select" required>
                                    <option value="REGULAR"  {{ old('service_code', $ekart->service_code) == 'REGULAR' ? 'selected' : '' }}>REGULAR</option>
                                    <option value="ECONOMY"  {{ old('service_code', $ekart->service_code) == 'ECONOMY' ? 'selected' : '' }}>ECONOMY</option>
                                    <option value="NDD"  {{ old('service_code', $ekart->service_code) == 'NDD' ? 'selected' : '' }}>NDD</option>
                                </select>
                                @error('service_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_service_code') }}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="delivery_type" class="form-label">{{ __('message.ekart.delivery_type') }}</label>
                                <select name="delivery_type" id="delivery_type" class="form-select">
                                    <option value="SMALL" {{ old('delivery_type', $ekart->delivery_type) == 'SMALL' ? 'selected' : '' }}>SMALL</option>
                                    <option value="MEDIUM" {{ old('delivery_type', $ekart->delivery_type) == 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
                                    <option value="LARGE" {{ old('delivery_type', $ekart->delivery_type) == 'LARGE' ? 'selected' : '' }}>LARGE</option>
                                </select>
                                @error('delivery_type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_delivery_type') }}</span>
                            </div>  
                        </div>
                        <div class="row">                           
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">{{__('message.environment_type')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="env_type_dev">
                                        <span class="form-check">
                                            <input type="radio" id="env_type_dev" name="env_type" value="dev" class="form-check-input"{{ $ekart->env_type === 'dev' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.dev')}}</label>
                                        </span>
                                    </label>
                                    <label class="form-control" for="env_type_live">
                                        <span class="form-check">
                                            <input type="radio" id="env_type_live" name="env_type" value="live" class="form-check-input" {{ $ekart->env_type === 'live' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.live')}}</label>
                                        </span>
                                    </label>
                                </div>
                                @error('env_type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.environment_type_error')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status_yes">
                                        <span class="form-check">
                                            <input type="radio" id="status_yes" name="status" value="1" class="form-check-input" {{ $ekart->status ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.active')}}</label>
                                    </span>
                                    </label>
                                    <label class="form-control" for="status_no">
                                        <span class="form-check">
                                            <input type="radio" id="status_no" name="status" value="0" class="form-check-input" {{ !$ekart->status ? 'checked' : '' }}>
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
<x-layout>
    <x-slot name="title">{{__('message.ekart.heading_title')}}</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('ekart.create') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.ekart.page_header_title')}}</h1></x-slot>
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
                        <p class="link">{{__('message.ekart.guidelines')}}</p>
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
                    <form class="needs-validation" method="POST" action="{{ route(panelPrefix().'.ekart.store') }}" id="ekart-form" novalidate>
                        @csrf
                        <input type="hidden" name="courier_code" value="ekart">
                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_title" class="form-label">{{ __('message.ekart.set_tittle')}}</label>
                                <input type="text" name="courier_title" id="courier_title" class="form-control" required placeholder="{{__('message.ekart.set_tittle_placeholder')}}">
                                @error('courier_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_title')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="merchant_code" class="form-label">{{ __('message.ekart.merchant_code')}}</label>
                                <input type="text" name="merchant_code" id="merchant_code" class="form-control" required placeholder="{{__('message.ekart.merchant_code_placeholder')}}">
                                @error('merchant_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_merchant_code')}}</span>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-sm-6 mb-3">
                                <label for="authorization_code" class="form-label">{{ __('message.ekart.authorization_code')}}</label>
                                <input type="text" name="authorization_code" id="authorization_code" class="form-control" required placeholder="{{__('message.ekart.authorization_code_placeholder')}}">
                                @error('authorization_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_authorization_code')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="goods_category" class="form-label">{{ __('message.ekart.goods_category') }}</label>
                                <select name="goods_category" id="goods_category" class="form-select">
                                    <option value="NON_ESSENTIAL">NON_ESSENTIAL</option>
                                    <option value="ESSENTIAL">ESSENTIAL</option>
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
                                    <option value="REGULAR">REGULAR</option>
                                    <option value="ECONOMY">ECONOMY</option>
                                    <option value="NDD">NDD</option>
                                </select>
                                @error('service_code')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_service_code') }}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="delivery_type" class="form-label">{{ __('message.ekart.delivery_type') }}</label>
                                <select name="delivery_type" id="delivery_type" class="form-select">
                                    <option value="SMALL">SMALL</option>
                                    <option value="MEDIUM">MEDIUM</option>
                                    <option value="LARGE">LARGE</option>
                                </select>
                                @error('delivery_type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.ekart.error_delivery_type') }}</span>
                            </div>  
                        </div>
                        <div class="row">                            
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
                            </div> 
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
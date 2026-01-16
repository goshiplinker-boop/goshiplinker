<x-layout>
    <x-slot name="title">{{__('message.selfship.page_title')}}</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('selfship.create') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.selfship.courier_create')}}</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route(panelPrefix().'.couriers_list') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i>{{__('message.back')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{__('message.selfship.guidelines')}}</p>
                        <div id="integrateStepsData" class="integrateStepsData descColor">
                            <div class="genInfoList">
                                <p>Instruction is missing</p>
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
                    <form class="needs-validation" method="POST" action="{{ route(panelPrefix().'.selfship.store') }}"
                        id="delhivery-form" novalidate>
                        @csrf
                        <input type="hidden" name="courier_code" value="selfship">
                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_title" class="form-label">{{ __('message.selfship.set_tittle')}}</label>
                                <input type="text" name="courier_title" id="courier_title" class="form-control" required placeholder="{{__('message.selfship.set_tittle_placeholder')}}">
                                @error('courier_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{ __('message.set_tittle_error')}}</span>
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
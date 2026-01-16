<x-layout>
    <x-slot name="title">Shipshopy</x-slot>
    <x-slot name="breadcrumbs">Shipshopy</x-slot>   
    <x-slot name="page_header_title"><h1 class="page-header-title">Shipshopy</h1></x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route(panelPrefix().'.couriers_list') }}" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i>{{ __('message.back') }}</a>
        </div>
    </x-slot>
    <x-slot name="main">       
        @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{__('message.shipshopy.guidelines')}}</p>
                        <div id="integrateStepsData" class="integrateStepsData descColor">
                            <div class="genInfoList">
                                <p>{!!__('message.shipshopy.guidelines_details')!!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    <form class="needs-validation" method="POST" action="{{ route(panelPrefix().'.'.$shipshopy->courier_code.'.update', $shipshopy->courier_id) }}" id="shipshopy-form" novalidate>
                        @include('seller.couriers.settings.shipshopy.form')
                        <div class="col-sm-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('message.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>

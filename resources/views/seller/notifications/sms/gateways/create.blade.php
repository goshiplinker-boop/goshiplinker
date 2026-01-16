<x-layout>
    <x-slot name="title">{{__('message.gateway.tittle')}}</x-slot>
    <x-slot name="breadcrumbs"> Gateways</x-slot>
    <x-slot name="page_header_title">
      <h1 class="page-header-title">{{__('message.gateway.page_tittle')}}</h1>
    </x-slot>    
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
                <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
            </div>
        </div>
    </x-slot>
    <x-slot name="main">       
        <div class="card overflow-hidden">
            <div class="card-body">
                <form class="js-validate needs-validation" action="{{ route('gateway_store') }}" method="POST">
                    @include('seller.notifications.sms.gateways.form')
                    <div class="col-sm-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('message.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </x-slot>
</x-layout>

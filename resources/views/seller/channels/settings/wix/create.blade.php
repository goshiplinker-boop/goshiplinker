<x-layout>
    <x-slot name="title">{{__('message.wix_create.connect_to_wix')}} </x-slot>    
    <x-slot name="breadcrumbs"> Wix </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.connect')}}</h1></x-slot>     
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="#" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link"> {{__('message.wix_create.integration_steps')}}</p>
                        <p class="link">{!!__('message.wix_create.admin_url')!!}</p>
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                   <a target='__blank' href="{{ route('wix.install') }}"  id="connectwixBtn" class="btn btn-primary btn-sm">{{__('message.wix_create.connect_to_wix')}}</a>                  
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
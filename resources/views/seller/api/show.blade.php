<x-layout>
    <x-slot name="title">{{__('message.api.tittle')}}</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('api.credentials.show') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.api.header')}}</h1></x-slot>
    <x-slot name="headerbuttons">
    </x-slot>
    <x-slot name="main">
    @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
        {{ session('success') }}<br>
            <strong>Api Key:</strong> {{ session('api_key') }}<br>
            <strong>Api Secret:</strong> {{ session('api_secret') }}<br>
            <small><b>Note:</b> Save your secret now — it won’t be shown again!</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
        {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="card">
            <div class="card-body">
            @if(!$credentials)
                <form method="POST" action="{{ route('api.credentials.generate') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{__('message.api.genrate_api')}}</button>
                        </div>
                    </div>
                </form>
            @else
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                        <strong>Api Key:</strong> {{ $credentials->api_key }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                        <strong>Api Secret:</strong> <em>hidden for security</em>
                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>
       
    </x-slot>
</x-layout>
<x-layout>
    <x-slot name="title">{{__('message.gateway_list.sms_gateway')}}</x-slot>
    <x-slot name="breadcrumbs">Gateways</x-slot>
    <x-slot name="page_header_title">
        <h1 class="page-header-title">{{__('message.gateway_list.sms_gateway')}}</h1>
    </x-slot>

    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="{{ route('gateway_create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{__('message.add_new')}}
            </a>
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
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__('message.gateway_list.gateway_name')}}</th>
                            <th>{{__('message.gateway_list.HTTP_method')}}</th>
                            <th>{{__('message.gateway_list.mobile_param')}}</th>
                            <th>{{__('message.status')}}</th>
                            <th class ="text-end">{{__('message.action')}}</th>
                        </tr>
                    </thead>
                   <tbody>
                        @forelse($sms_gateways as $index => $gateway)
                            <tr>
                                <td>{{ $gateway->gateway_name }}</td>
                                <td>{{ $gateway->http_method }}</td>
                                <td>{{ $gateway->mobile }}</td>
                                <td>
                                    <span class="badge bg-{{ $gateway->status ? 'success' : 'secondary' }}">
                                        {{ $gateway->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('gateway_edit', ['gateway_id' => $gateway->id]) }}" class="btn btn-white btn-sm">
                                        <i class="bi-pencil-fill me-1"></i>{{ __('message.edit') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('message.gateway_list.not_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
           </div>
        </div>
    </x-slot>
</x-layout>

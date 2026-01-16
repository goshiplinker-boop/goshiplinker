
    <x-layout>
        <x-slot name="title">Template List</x-slot>
        <x-slot name="breadcrumbs"></x-slot>
        @php
            $segment = request()->segment(1);
      @endphp
   <x-slot name="breadcrumbs">
        @if($segment == 'admin')
            {{ Breadcrumbs::render('seller_notification_list') }} 
        @elseif($segment == 'admin')
            {{ Breadcrumbs::render('notification_list') }}
        @endif
    </x-slot>
        
        <x-slot name="page_header_title">
            <h1 class="page-header-title">Manage Template</h1>
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
        <div class="table-responsive datatable-custom position-relative">
            <!-- Tabs -->
            <ul class="nav nav-segment">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'email' ? 'active' : '' }}"
                        href="{{ route($notification_route, ['tab' => 'email']) }}">
                        Email <span class="badge bg-secondary rounded-pill">{{ $counts['email'] ?? '0' }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'sms' ? 'active' : '' }}"
                        href="{{ route($notification_route, ['tab' => 'sms']) }}">
                        SMS <span class="badge bg-secondary rounded-pill">{{ $counts['sms'] ?? '0' }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'Rcs' ? 'active' : '' }}"
                        href="{{ route($notification_route, ['tab' => 'Rcs']) }}">
                        RCS <span class="badge bg-secondary rounded-pill">{{ $counts['Rcs'] ?? '0' }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'whatsapp' ? 'active' : '' }}"
                        href="{{ route($notification_route, ['tab' => 'whatsapp']) }}">
                        Whatsapp <span class="badge bg-secondary rounded-pill">{{ $counts['whatsapp'] ?? '0' }}</span>
                    </a>
                </li>
              
               
            </ul>

            <!-- Table -->
         <div class="card">
            <div class="table-responsive datatable-custom position-relative">
                <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                    "columnDefs": [{
                        "targets": [0, 7],
                        "orderable": false
                    }],
                    "order": [],
                    "info": {
                        "totalQty": "#datatableWithPaginationInfoTotalQty"
                    },
                    "search": "#datatableSearch",
                    "entries": "#datatableEntries",
                    "pageLength": 15,
                    "isResponsive": false,
                    "isShowPaging": false,
                    "pagination": "datatablePagination"
                }'>
                <thead class="thead-light">
                        <tr>
                            <th>Template Type</th>
                            <th>User Type</th>
                            <th>Event Name</th>
                            <th>Template</th>
                            <th class="text-end">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                            <tr>
                                <td>{{ucwords($template->channel ?? 'N/A')}}</td>
                                <td>{{ucwords($template->user_type ?? 'N/A') }}</td>
                                <td>{{ $template->event_type ?? 'N/A' }}</td>
                                <td>{!! html_entity_decode($template->body) !!}</td>

                                <td class="text-end"><span class="legend-indicator @if($template->status == 1) bg-success @else bg-warning @endif "></span>@if($template->status == 1) Active @else Disabled @endif</td>
                                <td class="text-end">
                                    <a href="{{ request()->segment(1) == 'admin' 
                                                ? route('seller_notification_edit', ['notification_id' => $template->id]) 
                                                : route('notification_edit', ['notification_id' => $template->id]) }}" 
                                       class="btn btn-white btn-sm">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>   
        </x-slot>
    </x-layout>


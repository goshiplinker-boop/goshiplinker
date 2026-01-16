<x-layout>
   <x-slot name="title">{{__('message.couriers.page_header_title')}}</x-slot>
   <x-slot name="breadcrumbs">{{ Breadcrumbs::render('couriers_list') }}</x-slot>
   <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.couriers.page_header_title')}}</h1></x-slot>
   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <div class="d-grid gap-3 gap-lg-5">
         @if($companyCouriers->isNotEmpty())
         <div class="card">
            <div class="card-header">
               <h4 class="card-header-title">{{__('message.couriers.connected_couriers')}}</h4>
            </div>
            <!-- Table -->
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
                        <th>Courier Name</th>
                        <th>Logo</th>
                        <th> API Mode</th>
                        <th>{{__('message.status')}}</th>
                        <th class="text-end">{{__('message.action')}}</th>
                       
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($companyCouriers as $companycourier)
                     <tr>
                        <td>{{ $companycourier->courier_title }}<br><small>ID: {{ $companycourier->id }}</small></td>
                        <td class="pm-store-img"><img class="avatar-img" src="{{ asset(env('PUBLIC_ASSETS') . '/' . $companycourier->image_url) }}" alt="{{$companycourier->courier_code}}"></td>
                        <td>{{ucfirst($companycourier->env_type) }}</td>
                        <td><span class="legend-indicator @if($companycourier->status==1) bg-success @else bg-warning @endif "></span>@if($companycourier->status==1) {{__('message.active')}} @else Disabled @endif</td>
                        <td class="text-end"><a href="{{ route(panelPrefix().".{$companycourier->courier_code}.edit", $companycourier->id) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil-square me-1"></i>{{__('message.edit')}}</a></td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
         <div></div>
      </div>
      @endif
      @if(session('role_id')==1)
      <div class="card">
         <div class="card-header">
            <h4 class="card-header-title">{{__('message.couriers.all_couriers')}}</h4>
         </div>
         <div class="row p-3">
            @foreach($defaultCouriers as $courier)
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
               <div class="card">
                  <div class="card-body">
                     <div>
                        <img src="{{ asset(env('PUBLIC_ASSETS') . '/' . $courier->image_url) }}" class="img-fluid" alt="Your Image">
                     </div>
                     <div class="text-section mt-2">
                        @php
                        $courier_code = str_replace(' ', '_', strtolower($courier->name)); 
                        @endphp
                        <a href="{{ route(panelPrefix().".{$courier_code}.create") }}" class="btn btn-light btn-sm mt-3">{{__('message.connect')}}</a> 
                     </div>
                  </div>
               </div>
            </div>
            @endforeach 
         </div>
      </div>
      @endif
   </x-slot>
</x-layout>
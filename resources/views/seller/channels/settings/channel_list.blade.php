<x-layout>
   <x-slot name="title">{{__('message.channels_list.heading_title')}}</x-slot>
   <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('channels_list') }}</x-slot>
   <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.channels_list.page_header_title')}}</h1></x-slot>
   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if ($errors->any())
         <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {!! $errors->first() !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      
      @endif
      <div class="d-grid gap-3 gap-lg-5">
         @if($companyChannels->isNotEmpty())
         <div class="card">
            <div class="card-header">
               <h4 class="card-header-title">{{__('message.channels_list.connected_channels')}}</h4>
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
                        <th>{{__('message.channels_list.store_id')}}</th>
                        <th>{{__('message.channels_list.store_logo')}}</th>
                        <th class="text-end">{{__('message.status')}}</th>
                        <th class="text-end">{{__('message.action')}}</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($companyChannels as $companyChannel)
                     <tr>
                        <td>{{$companyChannel->channel_title}}
                           @if($companyChannel->channel_code !== 'custom')
                           <br><small>URL: {{ $companyChannel->channel_url }}</small>
                           @endif 
                           <br><small>ID: {{$companyChannel->id}}</small>
                        </td>
                        <td class="pm-store-img"><img class="avatar-img" src="{{ asset(env('PUBLIC_ASSETS') . '/' . $companyChannel->image_url) }}" alt="Image Description"></td>
                        <td class="text-end"><span class="legend-indicator @if($companyChannel->status==1) bg-success @else bg-warning @endif "></span>@if($companyChannel->status==1) Active @else Disabled @endif</td>
                        <td class="text-end"><a href="{{ route("{$companyChannel->channel_code}.edit", $companyChannel->id) }}" class="btn btn-white btn-sm"><i class="bi bi-pencil-square me-1"></i>{{__('message.edit')}}</a></td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
         @endif
         <div class="card">
            <div class="card-header">
               <h4 class="card-header-title">{{__('message.channels_list.all_channels')}}</h4>
            </div>
            <div class="row p-3">
               @foreach($defaultChannels as $channel)
               <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5 ">
                  <!-- Card -->
                  <div class="card">
                     <div class="card-body">
                        <img src="{{ asset(env('PUBLIC_ASSETS') . '/' . $channel->image_url) }}" class="img-fluid" alt="Your Image">
                        <div class="text-section mt-2">
                           @php
                           $channel_code = str_replace(' ', '_', strtolower($channel->name)); 
                           @endphp
                           <a href="{{ route("{$channel_code}.create") }}" class="btn btn-light btn-sm mt-3">{{__('message.connect')}}</a>
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach 
            </div>
         </div>
      </div>
   </x-slot>
</x-layout>
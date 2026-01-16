<x-layout>
   <x-slot name="title">{{__('message.pickup_location.heading_title')}}</x-slot>
   <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('pickup_locations.index') }}</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">{{__('message.pickup_location.page_header_title')}}</h1>
   </x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a class="btn btn-primary btn-sm" href="{{ route('pickup_locations.create') }}"><i class="bi bi-plus-circle me-1"></i>{{__('message.pickup_location.add')}}</a>
      </div>
   </x-slot>
   <x-slot name="main">
      @if(session('success'))
      <div class="alert alert-soft-success" role="alert">
         {{ session('success') }}
      </div>
      @endif
      <div class="card">
         @if($pickupLocations->isEmpty())
         <p class="text-center my-2">{{__('message.pickup_location.not_found')}}</p>
         @else
         <div class="card-header">
            <h4 class="card-header-title">All Locations </h4>
         </div>
         <div class="table-responsive datatable-custom">
            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-align-middle card-table" data-hs-datatables-options='{
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
                     <Th>{{__('message.pickup_location.th')}}              
                     <th>{{__('message.pickup_location.th1')}}</th>
                     <th>{{__('message.pickup_location.th2')}}</th>
                     <th>{{__('message.status')}}</th>
                     <th class="text-end">{{__('message.action')}}</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($pickupLocations as $pickupLocation)
                  <tr>
                     <td>{{$pickupLocation->id}}</td>
                     <td>{{$pickupLocation->location_title}}</td>
                     <td class="w-50">
                        {{$pickupLocation->address}},{{$pickupLocation->landmark}},
                        <span  class="d-block fs-5">{{$pickupLocation->city}},{{$pickupLocation->state_code}},{{$pickupLocation->country_code}}</span>
                     </td>
                     <td>
                        <span class="legend-indicator @if($pickupLocation->status==1) bg-success @else bg-warning @endif "></span>@if($pickupLocation->status==1) Active @else Disabled @endif
                     </td>
                     <td class="text-end">
                        <a href="{{ route('pickup_locations.edit', $pickupLocation->id) }}" class="btn btn-white btn-sm"><i class="bi-pencil-fill me-1"></i> Edit</a>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
            @endif
         </div>
         <div class="card-footer">
            {{ $pickupLocations->links('pagination::bootstrap-5') }}
         </div>
      </div>
   </x-slot>
</x-layout>
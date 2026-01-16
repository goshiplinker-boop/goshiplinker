<x-layout>
   <x-slot name="title">Activities Listing</x-slot>
   <x-slot name="breadcrumbs">
    @if($remarks->isNotEmpty())
        {{ Breadcrumbs::render('followup_activities.show', $remarks->first()) }}
    @endif
   </x-slot>
   <x-slot name="page_header_title"><h1> {{__('message.company.page_header_title')}}</h1> </x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> Back</a>
      </div>
   </x-slot>
   <x-slot name="main">
      <div class="card">
         <div class="table-responsive datatable-custom position-relative">
            <div class="card-header card-header-content-md-between">
               <div>
                  <button id="js-daterangepicker-predefined" class="btn btn-white btn-sm">
                    <i class="bi-calendar-week me-1"></i>
                    <span class="js-daterangepicker-predefined-preview"></span>
                  </button>
                  <button class="btn btn-white btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                     <i class="bi bi-funnel me-1"></i>{{ __('message.orders.filters') }}
                  </button>
                  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                     <div class="offcanvas-header">
                        <h5 id="offcanvasRightLabel">Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                     </div>
                     <div class="offcanvas-body">
                        ...
                     </div>
                  </div>
               </div>
            </div>
            @if ($remarks->isEmpty())
            <div class="d-flex justify-content-center align-items-center">
               <p class="text-center my-5">No data Found</p>
            </div>
            @else
            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
               "columnDefs": [{"targets": [0], "orderable": false}],
               "order": [],
               "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
               "search": "#datatableSearch",
               "entries": "#datatableEntries",
               "pageLength": 12,
               "isResponsive": false,
               "isShowPaging": false,
               "pagination": "datatablePagination"
               }'>
               <thead class="thead-light">
                  <tr>
                     <th>Lead Status</th>
                     <th>Last Remarks</th>
                     <th>Follow-Up Date</th>
                     <th>Completed</th>
                     <th class="text-end">Created Time</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($remarks as $activity)
                  <tr>
                     <td>{{ $activity->leadStatus->status_name ?? 'Status not found' }}</td>
                     <td>{{ $activity->last_remarks }}</td>
                     <td>{{ $activity->followup_date ? \Carbon\Carbon::parse($activity->followup_date)->format('d M, Y H:i') : 'Not set' }}</td>
                     <td>{{ $activity->is_followup_completed ? 'Yes' : 'No' }}</td>
                     <td class="text-end">{{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y H:i') }}</td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
            @endif
            <!-- Pagination Links -->
            <div class="card-footer">
               {{ $remarks->links('pagination::bootstrap-5') }}
            </div>
         </div>
      </div>
   </x-slot>
</x-layout>
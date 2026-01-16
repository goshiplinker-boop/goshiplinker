<x-layout>
   <x-slot name="title">admin</x-slot>
   <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('vendors_list') }}</x-slot>
   <x-slot name="page_header_title"><h1 class="page-header-title">Seller Listing</h1></x-slot>
   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error'))
         <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif 
      <div class="row justify-content-end mb-3">
         <div class="col-lg">
         </div>
      </div>
      <div class="row justify-content-end mb-3">
         <div class="col-lg"></div>
      </div>

      <!-- Filter Tabs -->
       <ul class="nav nav-segment mb-2">
         <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'fresh_lead' || request()->get('tab') === null ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'fresh_lead']) }}">
                  New 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->fresh_lead }}</span>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'qualified' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'qualified']) }}">
                  Qualified 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->qualified }}</span>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'todaysFollowUpCount' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'todaysFollowUpCount']) }}">
                  Today's Follow-up 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->todaysFollowUpCount }}</span>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'overdueFollowUpCount' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'overdueFollowUpCount']) }}">
                  Overdue Follow-up 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->overdueFollowUpCount }}</span>
            </a>
         </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'unqualified' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'unqualified']) }}">
                  UnQualified 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->unqualified }}</span>
            </a>
         </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'lost' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'lost']) }}">
                  Lost 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->lost }}</span>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link {{ request()->get('tab') === 'allLeadsCount' ? 'active' : '' }}"
               href="{{ route('vendors_list', ['tab' => 'allLeadsCount']) }}">
                  All 
                  <span class="badge bg-secondary rounded-pill">{{ $leadCounts->allLeadsCount }}</span>
            </a>
         </li>
      </ul>
     
      <div class="card">
         <div class="table-responsive datatable-custom position-relative">
            <div class="card-header card-header-content-md-between">
               <div>
                  <!-- Date Range Picker -->
                     <button id="js-daterangepicker-predefined" class="btn btn-white btn-sm">
                     <i class="bi-calendar-week me-1"></i>
                     <span class="js-daterangepicker-predefined-preview"></span>
                     </button>
                     <!-- End Date Range Picker -->
                  <button class="btn btn-white btn-sm" type="button" data-bs-toggle="offcanvas"
                     data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                     <i class="bi bi-funnel me-1"></i>Filters
                  </button>
                  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
                     aria-labelledby="offcanvasRightLabel">
                     <div class="offcanvas-header">
                        <h5 id="offcanvasRightLabel">Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                     </div>
                     <div class="offcanvas-body">
                        <form class="needs-validation" action="{{route('vendors_list')}}" id="fiter-form" method="GET" enctype="multipart/form-data" novalidate>
                           <input type="hidden" name="tab" value="{{$tab}}" >
                           <div class="row">
                              <div class="col-sm-12 mb-3">
                                 <label for="company_name" class="form-label">Company</label>
                                 <input type="text" id="company_name"  name="company_name" class="form-control"  value="@if(isset($filters['company_name'])) {{$filters['company_name']}} @endif" placeholder="Company name">
                              </div>
                              <div class="col-sm-12 mb-3">
                                 <label for="lead_status_id" class="form-label">Lead Status</label>
                                 <div class="tom-select-custom tom-select-custom-with-tags">
                                    <select class="js-select form-select" name="lead_status_id" autocomplete="off" data-hs-tom-select-options='{"placeholder": "Select Order Status", "hideSearch": true }'>
                                       <option value="">Select Lead Staus</option>
                                          @foreach ($leadStatuses as $leadStatus)
                                             @if($leadStatus->status_mapping==$tab)
                                                <option value="{{ $leadStatus->id }}"  @if(isset($filters['lead_status_id']) && $filters['lead_status_id']== $leadStatus->id) selected @endif>{{ $leadStatus->status_name }}</option>
                                             @endif
                                             @if($tab =='overdueFollowUpCount' || $tab=='todaysFollowUpCount' || $tab=='allLeadsCount')
                                                <option value="{{ $leadStatus->id }}"  @if(isset($filters['lead_status_id']) && $filters['lead_status_id']== $leadStatus->id) selected @endif>{{ $leadStatus->status_name }}</option>
                                             @endif
                                          @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-sm-12 mb-3">
                                 <label for="subscription_plan" class="form-label">Subscription Plan</label>
                                 <div class="tom-select-custom tom-select-custom-with-tags">
                                    <select class="js-select form-select" name="subscription_plan" autocomplete="off" data-hs-tom-select-options='{"placeholder": "Select Subscription Plan", "hideSearch": true }'>
                                       <option value=""> Select Plan</option>
                                          @foreach ($plans as $plan)
                                             <option value="{{ $plan->name }}"  @if(isset($filters['subscription_plan']) && $filters['subscription_plan']== $plan->name) selected @endif>{{ $plan->name }}</option>
                                          @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-sm-12 mb-3">
                                 <label for="subscription_plan" class="form-label">Subscription Status</label>
                                 <div class="tom-select-custom tom-select-custom-with-tags">
                                    <select class="js-select form-select" name="subscription_status" autocomplete="off" data-hs-tom-select-options='{"placeholder": "Select Subscription status", "hideSearch": true }'>
                                       <option value=""> Select subscription status</option>                                        
                                          <option value="1"  @if(isset($filters['subscription_status']) && $filters['subscription_status']== 1) selected @endif>Paid</option>
                                          <option value="0"  @if(isset($filters['subscription_status']) && $filters['subscription_status']== 0) selected @endif>Expired</option>                                          
                                    </select>
                                 </div>
                              </div>                             
                           </div>                   
                        </form>
                     </div>
                      <div class="offcanvas-footer">
                        <div class="col">
                            <div class="d-grid">
                                <button type="submit" id="filter_buttonxx" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </div>
                     </div> 
                  </div>
               </div>
            </div>

            <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
               <thead class="thead-light">
                  <tr>
                     <th>Registered Info</th>
                     <th>Company Info</th>
                     <th>Other Info</th>
                     <th>Follow-Up Activity</th>
                     <th class="text-end">Action</th>
                  </tr>
               </thead>
               <tbody class="border-3">
                 @if($vendors->isNotEmpty())
                     @foreach($vendors as $vendor)
                        <tr>
                           <!-- Registered Info -->
                           <td class="table-column-ps-0">
                              <a class="d-flex align-items-center">
                                 <div class="ms-3">
                                    <span class="d-block h5 text-inherit mb-0">{{ $vendor->user->name }}
                                    <i class="bi-patch-check-fill text-primary" data-bs-toggle="tooltip"
                                       data-bs-placement="top" title="Top endorsed"></i>
                                    </span>
                                    <span class="d-block fs-5 text-body">{{ $vendor->phone_number }}</span>
                                    <span class="d-block fs-5 text-body">{{ $vendor->user->email }}</span>
                                    <span class="d-block fs-5 text-body">{{ $vendor->user->brand_name }}</span>
                                    <span class="d-block fs-5 text-body">{{ \Carbon\Carbon::parse($vendor->created_at)->format('d-m-Y H:i:s') }}</span>
                                    @php
                                       $utmdata = json_decode($vendor->utm_data, true);  // Decode the JSON
                                    @endphp
                                    @if($utmdata)
                                    <span class="d-block fs-5 text-body">utm source:{{ $utmdata['utm_source']??'' }}</span>
                                    <span class="d-block fs-5 text-body">utm medium:{{ $utmdata['utm_medium']??'' }}</span> 
                                    <span class="d-block fs-5 text-body">utm campaign:{{ $utmdata['utm_campaign']??'' }}</span>
                                    @endif
                                 </div>
                              </a>
                           </td>

                           <!-- Company Info -->
                           <td>
                              <span class="d-block fs-5 text-body">{{ $vendor->legal_registered_name }}</span>
                              <span class="d-block fs-5 text-body">{{ $vendor->address }}</span>
                              <span class="d-block fs-5 text-body">{{ $vendor->city }}</span>
                              <span class="d-block fs-5 text-body">{{ $vendor->state_code }}</span>
                              @if($vendor->subscription_plan)
                                 <span class="d-block fs-5 text-body"><b>Plan:</b> {{ $vendor->subscription_plan }}</span>
                              @endif
                              @if($vendor->subscription_plan !=null && ($vendor->subscription_status==0 || $vendor->subscription_status==1))
                                 <span class="d-block fs-5 text-body">
                                 <b>Subscription:</b>
                                 @if($vendor->subscription_status == 1 && ($vendor->subscription_plan == 'Free' || $vendor->subscription_plan == 'Trial'))
                                    <span class="badge bg-info rounded-pill">Active</span>

                                 @elseif($vendor->subscription_status == 0 && ($vendor->subscription_plan == 'Free' || $vendor->subscription_plan == 'Trial'))
                                    <span class="badge bg-warning rounded-pill">Expired</span>

                                 @elseif($vendor->subscription_status == 1)
                                    <span class="badge bg-success rounded-pill">Paid</span>

                                 @elseif($vendor->subscription_status == 0)
                                    <span class="badge bg-danger rounded-pill">Expired</span>
                                 @endif

                              </span>
                           @endif
                              
                           </td>

                           <!-- Other Info -->
                           <td>
                              <span class="d-block fs-5 text-body">{{ $vendor->channel_name }}</span>
                              <span class="d-block fs-5 text-body">{{ $vendor->courier_using }}</span>
                              <span class="d-block fs-5 text-body">{{ $vendor->product_category }}</span>
                           </td>

                           <!-- Follow-Up Activity -->
                           <td>
                              @php
                                 $activity = $vendor->leadActivities->isNotEmpty()?$vendor->leadActivities->first():[];                       
                              @endphp
                              <span class="d-block fs-5 text-body">Status:
                              {{ $vendor->leadStatus->status_name??'' }}</span>
                              <span class="d-block fs-5 text-body">Last Remarks:
                              {{ $activity->last_remarks??'' }}</span>
                              <span class="d-block fs-5 text-body">Next Follow-up:
                              {{ $activity->followup_date??'' }}</span>
                              <!-- Follow-Up Activity Buttons -->
                              <div class="d-flex">
                                 <button type="button"class="btn btn-white" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal" data-company-id="{{ $vendor->id }}"
                                    data-last-remarks="{{ $activity->last_remarks ?? 'No remarks available' }}"
                                    data-username=""
                                    data-status-name="{{ $vendor->leadStatus->status_name ?? 'Status not found' }}">
                                 Activity
                                 </button>
                                 <!-- Button for All Remarks (Redirect to the show route) -->
                                 <form
                                    action="{{ route('followup_activities.show', ['company_id' => $vendor->id]) }}"
                                    method="GET">
                                    <button type="submit" class="btn btn-white ms-1">All
                                    Remarks</button>
                                 </form>
                              </div>
                           </td>

                           <!-- Action -->
                           <td class="text-end">
                              <a class="btn btn-primary btn-sm" href="{{ route('admin.loginAsVendor', ['user_id' => $vendor->user->id??0]) }}" target="_blank">
                              Seller Login
                              </a>
                           </td>
                        </tr>
                     @endforeach
                  @else
                  <tr>
                     <td colspan="4" class="table-column-ps-0 text-center">No Leads Found</td>
                  </tr>
                  @endif

               </tbody>
            </table>

            <!-- Modal -->
            <!---------------------------------------------------------------------------->
            <!-- Modal Structure -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
               aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h2 class="modal-title" id="exampleModalLabel">Activity
                           ( ID: N/A )
                        </h2>
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                     </div>
                     <div class="modal-body">
                        <form id="myForm" class="js-validate needs-validation" action="{{ route('lead-activities.store') }}" method="POST" novalidate>
                           @csrf
                           <input type="hidden" name="user_id" value="{{ auth()->guard('admin')->user()->id }}">
                           <input type="hidden" name="company_id" id="company_id"
                              value="{{ $vendor->id??'' }}">
                           <div class="mb-3">
                              <label for="statusName" id="statusName" class="form-label font-weight-bold">Current Status:
                              </label>
                           </div>
                           <!-- Last Remarks -->
                           <div class="mb-3">
                              <label for="lastRemarks" class="form-label font-weight-bold" id="lastRemarks">Last Remarks: </label>
                           </div>
                           <div class="d-flex justify-content-between align-items-center mb-3">
                              <label class="form-check form-switch mb-0">
                              <span class="me-3">Set new Follow-up</span>
                              <input type="checkbox" class="form-check-input" id="newFollowUp"
                                 name="set_followup">
                              </label>
                              <label class="form-check form-switch mb-0">
                              <span class="me-3">Is follow-up Completed?</span>
                              <input type="checkbox" class="form-check-input" id="isFollowUpCompleted"
                                 name="is_followup_completed">
                              </label>
                           </div>
                           <div id="followUpDateContainer" class="mb-3" style="display: none;">
                              <label for="followUpDate">Select Follow-up Date and Time:</label>
                              <input type="datetime-local" class="flatpickr-custom-form-control form-control"
                                 name="followup_date" id="followUpDate">
                           </div>
                           <input type="hidden" id="followUpCompletedValue" name="is_followup_completed"
                              value="0">
                           <div class="mb-3">
                              <label class="form-label font-weight-bold" for="status">Status:</label>
                              <select id="status" name="status" class="form-control" required>
                                 <option value="">Choose an option</option>
                                 @foreach ($leadStatuses as $leadStatus)
                                 <option value="{{ $leadStatus->id }}">
                                    {{ $leadStatus->status_name }}
                                 </option>
                                 @endforeach
                              </select>
                               <span class="invalid-feedback">Please Select Status</span>
                           </div>
                           <!-- Remarks -->
                           <div class="mb-3">
                              <label class="form-label font-weight-bold" for="remarks">Remarks:</label>
                              <textarea id="remarks" class="form-control" name="remarks"
                                 placeholder="Enter remarks" rows="4"></textarea>
                           </div>
                           <div class="d-flex justify-content-between mt-3">
                              <!-- Add the company_id as a data attribute on the button -->
                              <a class="btn btn-white" href="#" id="allRemarksBtn" target="_blank"
                                 data-company-id="{{ $vendor->id??'' }}">
                              All Remarks <i class="bi-box-arrow-up-right ms-1"></i>
                              </a>
                              <button type="submit" class="btn btn-primary">Submit</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
            <!-- End Table -->
            <!-- Footer -->
            <div class="card-footer">
            {{ $vendors->appends(request()->only('tab'))->links('pagination::bootstrap-5') }}
            </div>
         </div>
      </div>
   </x-slot>
</x-layout>
<script>
   $(document).ready(function() {
       $('#exampleModal').on('show.bs.modal', function(event) {
           var button = $(event.relatedTarget); // Button that triggered the modal
           var companyId = button.data('company-id'); // Get company_id from the button
           var lastRemarks = button.data('last-remarks'); // Get the last remarks
           var username = button.data('username'); // Get the username
           var statusName = button.data('status-name'); // Get the status name
   
           // Set the company_id dynamically on the "All Remarks" button
           var allRemarksBtn = $('#allRemarksBtn');
           allRemarksBtn.data('company-id', companyId); // Set the company_id data on the button
   
           // Dynamically set the last remarks, username, and status name in the modal
           $('#lastRemarks').text('Last Remarks: ' + (lastRemarks ? lastRemarks : 'No remarks available'));
           $('#statusName').text('Current Status: ' + (statusName ? statusName : 'Status not found'));
   
           // Click event for "All Remarks" button
           allRemarksBtn.on('click', function(e) {
               e.preventDefault(); // Prevent the default link behavior
   
               var companyId = $(this).data(
                   'company-id'); // Get company_id dynamically from the button
   
               if (companyId) {
                   // Generate the URL with the company_id
                   var url =
                      "{{ route('followup_activities.show', ['company_id' => '__company_id__']) }}";
                      url = url.replace('__company_id__',
                       companyId); // Replace the placeholder with the actual company_id
   
                   // Redirect to the generated URL
                   window.location.href = url;
               } else {
                   console.log('Company ID is missing');
               }
           });
       });
   });  
   document.getElementById('newFollowUp').addEventListener('change', function() {
       const followUpDateContainer = document.getElementById(
           'followUpDateContainer');
       if (this.checked) {
           followUpDateContainer.style.display = 'block'; // Show the date input
       } else {
           followUpDateContainer.style.display = 'none'; // Hide the date input
       }
   });
   document.getElementById('isFollowUpCompleted').addEventListener('change', function() {
       const hiddenInput = document.getElementById('followUpCompletedValue');
       hiddenInput.value = this.checked ? '1' : '0'; // Update hidden input value
       console.log('Hidden Input Value:', hiddenInput.value); // Log updated value
   });
   $(document).ready(function() {
       $('#exampleModal').on('show.bs.modal', function(event) {
           var button = $(event.relatedTarget); // Button that triggered the modal
           var companyId = button.data('company-id'); // Get company_id
   
           // Set the company_id dynamically in the modal title
           $('#exampleModalLabel').text('Activity ( ID: ' + (companyId ? companyId : 'N/A') + ')');
       });
   });
   $('#exampleModal').on('show.bs.modal', function(event) {
       var button = $(event.relatedTarget); // Button that triggered the modal
       var companyId = button.data('company-id'); // Get company_id from the button
   
       // Set the company_id dynamically in the hidden input field
       $('#company_id').val(companyId);
   });
   document.getElementById('filter_buttonxx').addEventListener('click', function(event) {
      document.getElementById('fiter-form').submit(); 
   });  
    $(function() {
        $('#js-daterangepicker-predefined').on('apply.daterangepicker', function(ev, picker) {
            const startDate = picker.startDate.format('YYYY-MM-DD');
            const endDate = picker.endDate.format('YYYY-MM-DD');
            // Get the current query string
            const urlParams = new URLSearchParams(window.location.search);
            // Update or add startDate and endDate parameters
            urlParams.set('startDate', startDate);
            urlParams.set('endDate', endDate);
            // Navigate to the updated URL
            window.location.href = `vendors?${urlParams.toString()}`;
        });
    });

</script>

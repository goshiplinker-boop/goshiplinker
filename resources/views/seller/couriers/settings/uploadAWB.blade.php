<x-layout>
   <x-slot name="title">Upload Tracking Number</x-slot>
   <x-slot name="breadcrumbs">{{ Breadcrumbs::render('courier.uploadAWB') }}</x-slot>
   <x-slot name="page_header_title"><h1 class="page-header-title">Upload Tracking</h1></x-slot>
   <x-slot name="main">
      {{-- Display success message --}}
      @if(session('success'))
      <div class="alert alert-soft-success alert-dismissible fade show" role="alert">
         {{ session('success') }}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      {{-- Display error message --}}
      @if(session('error'))
      <div class="alert alert-soft-danger alert-dismissible fade show" role="alert">
         {{ session('error') }}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      <div class="card">
         <div class="card-header">
            <h4 class="card-header-title">Manage Courier Tracking Numbers</h4>
         </div>
         <div class="table-responsive datatable-custom position-relative">
            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table" 
               data-hs-datatables-options='{
               "columnDefs": [{"targets": [0, 7], "orderable": false}],
               "order": [],
               "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
               "search": "#datatableSearch",
               "entries": "#datatableEntries",
               "pageLength": 15,
               "isResponsive": false,
               "isShowPaging": false,
               "pagination": "datatablePagination"
               }'>
               <thead class="thead-light">
                  <tr>
                     <th>Courie Details</th>
                     <th>Count</th>
                     <th>Select</th>
                     <th class="text-end">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($couriers as $courier)
                  <tr>
                     <td>{{ $courier->name }}<br><small>ID: {{ $courier->id }}</small></td>
                     <td>
                        @if($courier->tracking_number_count_cod > 0 || $courier->tracking_number_count_prepaid > 0)
                            <small>COD ({{ $courier->tracking_number_count_cod }})</small>
                            @if($courier->tracking_number_count_cod > 0)
                                <a type="button" class="btn-ghost-secondary btn-sm m-1" onclick="exportTrackingNumbers('{{ $courier->id }}','C')"><i class="bi bi-download"></i></a>
                                <a type="button" class="btn-ghost-danger btn-sm" onclick="deleteTrackingNumbers('{{ $courier->id }}','C')"><i class="bi bi-trash3"></i></a>
                            @endif
                            </br>
                            <small> Prepaid ({{ $courier->tracking_number_count_prepaid }})</small>
                            @if($courier->tracking_number_count_prepaid > 0)
                                <a type="button" class="btn-ghost-secondary btn-sm m-1" onclick="exportTrackingNumbers('{{ $courier->id }}','P')"><i class="bi bi-download"></i></a>
                                <a type="button" class="btn-ghost-danger btn-sm" onclick="deleteTrackingNumbers('{{ $courier->id }}','P')"><i class="bi bi-trash3"></i></a>
                            @endif
                        @else
                            <small>{{ $courier->tracking_number_count }}</small>
                            @if($courier->tracking_number_count > 0)
                            <a type="button" class="btn-ghost-secondary btn-sm m-1" onclick="exportTrackingNumbers('{{ $courier->id }}','')"><i class="bi bi-download"></i></a>
                            <a type="button" class="btn-ghost-danger btn-sm" onclick="deleteTrackingNumbers('{{ $courier->id }}','')"><i class="bi bi-trash3"></i></a>
                            @endif
                        @endif
                       
                     </td>
                    @if(in_array($courier->courier_code, config('app.auto_tracking_number_fetch', [])))
                        <td>
                            <a type="button" class="btn-ghost-primary btn-sm m-1" onclick="fetchTrackingNumbers('{{ $courier->id }}','C')"><i class="bi bi-download"></i> Sync COD Tracking Numbers </a>
                            <a type="button" class="btn-ghost-primary btn-sm m-1" onclick="fetchTrackingNumbers('{{ $courier->id }}','P')"><i class="bi bi-download"></i> Sync Prepaid Tracking Numbers </a>                            
                        </td>
                        <td class="text-end"></td>
                    @else
                        <td>
                            <input type="file" name="csv_file" id="csv_file_{{ $courier->id }}" class="form-control form-control-sm" accept=".csv">
                            <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/SelfShip_tracking_numbers.csv') }}"  class="btn-ghost-primary btn-sm"><i class="bi bi-download"></i> Download Template </a>
                        </td>
                        <td class="text-end">
                        <button type="button" class="btn btn-light" id="uploadButton_{{ $courier->id }}" 
                        onclick="uploadAWB({{ $courier->id }}, {{ session('company_id') }})">
                        <i class="bi bi-upload"></i> Upload
                            </button>

                        </td>
                    @endif
                     
                  </tr>
                  @empty
                  <tr>
                     <td colspan="4" class="text-center">No couriers found.</td>
                  </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </x-slot>
</x-layout>
<script>
   // CSRF token setup for AJAX
   $.ajaxSetup({
       headers: {
           'X-CSRF-TOKEN': '{{ csrf_token() }}'
       }
   });
   
   // CSRF token setup for AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

function uploadAWB(courierId, companyId) {
    const fileInput = document.getElementById(`csv_file_${courierId}`);
    const file = fileInput.files[0];

    if (!file) {
        alert('Please select a CSV file to upload.');
        return;
    }


    const uploadButton = $(`#uploadButton_${courierId}`);
    const originalButtonText = uploadButton.html(); // Save the original button text
    
    // Change the button text and show spinner
       // Show only the spinner and hide the text
       uploadButton.html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Uploading...</span></div>');
    uploadButton.prop('disabled', true);  // Disable button to prevent multiple clicks

    const formData = new FormData();
    formData.append('csv_file', file);
    formData.append('courier_id', courierId);
    formData.append('company_id', companyId);

    $.ajax({
        url: '{{ route("courier.uploadAWB") }}',
        method: 'POST',
        processData: false, // Prevent jQuery from automatically transforming the data
        contentType: false, // Let the browser set the content type
        data: formData,
        success: function (response) {
            // If the upload is successful, reload the page or show a success message
            location.reload();
        },
        error: function (xhr) {
            // If an error occurs, show an alert
            alert('Error uploading file: ' + (xhr.responseText || 'Unknown error.'));
        },
        complete: function () {
            // After the request completes, revert the button text back to original
            uploadButton.html(originalButtonText);
            uploadButton.prop('disabled', false); // Re-enable the button
        }
    });
}

   // Export Tracking Numbers
   function exportTrackingNumbers(courierId,payment_type='') {
       $.ajax({
           url: '{{ route("courier.export.csv") }}',
           method: 'POST',
           data: { courier_id: courierId,payment_type:payment_type },
           xhrFields: {
               responseType: 'blob'
           },
           success: function (response, status, xhr) {
               const filename = xhr.getResponseHeader('Content-Disposition').split('filename=')[1];
               const blob = new Blob([response], { type: 'text/csv' });
               const link = document.createElement('a');
               link.href = window.URL.createObjectURL(blob);
               link.download = filename;
               link.click();
           },
           error: function (error) {
               alert('Error exporting tracking numbers.');
           }
       });
   }
   
   // Delete Courier
   function deleteTrackingNumbers(courierId,payment_type='') {
       if (!confirm('Are you sure you want to delete this Tracking Number?')) return;
   
       $('#loader').show();
       $.ajax({
           url: '{{ route("courier.delete") }}',
           method: 'POST',
           data: { courier_id: courierId,payment_type:payment_type },
           success: function(response) {
               $('#loader').hide();
               // On success, reload the page
               location.reload();
           },
           error: function(xhr, status, error) {
               alert('Failed to delete courier: ' + error);
           }
       });
   }
    // Export Tracking Numbers
   function fetchTrackingNumbers(courierId,payment_type='') {
       $.ajax({
            url: '{{ route("courier.fetch_awb") }}',
            method: 'POST',
            data: { courier_id: courierId,payment_type:payment_type },           
            success: function(response) {
                console.log(response);
               $('#loader').hide();
               // On success, reload the page
               location.reload();
           },
           error: function(xhr, status, error) {
               alert('Failed to delete courier: ' + error);
           }
       });
   }
</script>
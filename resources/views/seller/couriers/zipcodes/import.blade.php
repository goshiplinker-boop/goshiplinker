<x-layout>
    <x-slot name="title">Import Zipcodes</x-slot>
    <x-slot name="breadcrumbs">{{ Breadcrumbs::render('pincode_list') }}</x-slot>
    <x-slot name="page_header_title">
        <h1 class="page-header-title">Import Zipcodes</h1>
    </x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="javascript:history.back()" class="btn btn-light btn-sm">
                <i class="bi bi-chevron-left"></i> {{ __('message.back') }}
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

        @if ($errors->any())
            <div class="alert alert-soft-danger alert-dismissible" role="alert">
                {!! $errors->first() !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        
        @endif
        <div class="row">    
            <div class="card col-sm-12">
                <div class="card-body">                    
                    <form class="needs-validation" action="{{ route('import_pincode') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="courier_id" class="form-label">Courier</label>
                                <select name="courier_id" id="courier_id" class="form-control" required>
                                    <option value="">Select Courier</option>
                                    @foreach ($couriers as $courier)
                                        <option value="{{ $courier->courier_id }}" {{ old('courier_id') == $courier->courier_id ? 'selected' : '' }}>
                                            {{ $courier->courier_title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('courier_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">Courier is required</div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="payment_type" class="form-label">Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-control" required>
                                    <option value="">Select Payment Type</option>
                                    <option value="C" {{ old('payment_type') == 'C' ? 'selected' : '' }}>COD</option>
                                    <option value="P" {{ old('payment_type') == 'P' ? 'selected' : '' }}>Prepaid</option>
                                </select>
                                @error('payment_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">Payment type is required</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="csv_file" class="form-label">CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,text/csv" required>
                                <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/pincode_sample.csv') }}" class="btn-ghost-primary btn-sm">
                                    <i class="bi bi-download"></i> Download Template
                                </a>
                                @error('csv_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">The CSV file must have a "Pincodes" column.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('message.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row mt-4">    
            <div class="card col-sm-12">
                <div class="card-body"> 
                    @if($zipcodes->isEmpty())
                        <p class="text-center my-2">No Couriers have for upload pincodes</p>
                    @else
                        <div class="card-header">
                            <h4 class="card-header-title">All Pincodes</h4>
                        </div>
                        <div class="table-responsive datatable-custom">
                            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-align-middle card-table" data-hs-datatables-options='{
                                "columnDefs": [{"targets": [0, 7],"orderable": false}],
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
                                        <th>Courier Name</th>              
                                        <th>COD Pincode Count</th>
                                        <th>Prepaid Pincode Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($zipcodes as $zipcode)
                                        <tr>
                                            <td>{{ $zipcode->courier_title }}</td>
                                            <td>                                              
                                                <a type="button" class="btn-ghost-primary btn-sm m-1" onclick="exportPincodesNumbers('{{ $zipcode->courier_id }}','C')">
                                                    COD Pincodes ({{ $zipcode->cod_count }}) <i class="bi bi-download"></i>
                                                </a>
                                            </td>  
                                            <td>                                                
                                                <a type="button" class="btn-ghost-primary btn-sm m-1" onclick="exportPincodesNumbers('{{ $zipcode->courier_id }}','P')">
                                                    Prepaid Pincodes ({{ $zipcode->prepaid_count }}) <i class="bi bi-download"></i>
                                                </a>
                                            </td>                     
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $zipcodes->links('pagination::bootstrap-5') }}
                        </div> 
                    @endif
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>

<script>
    // Export Pincode Numbers
    function exportPincodesNumbers(courierId, payment_type = '') {
        $.ajax({
            url: '{{ route("pincodeExport") }}',
            method: 'POST',
            data: { courier_id: courierId, payment_type: payment_type },
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
                alert('Error exporting pincode numbers.');
            }
        });
    }
</script>
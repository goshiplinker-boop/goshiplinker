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
                    <form class="needs-validation" action="{{ route('pincode.upload') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                                <label for="courier_id" class="form-label">Courier</label>
                                <select name="courier_id" id="courier_id" class="form-control" required>
                                    <option value="">Select Courier</option>
                                    @foreach ($couriers as $courier)
                                        <option value="{{ $courier->id }}" {{ old('courier_id') == $courier->id ? 'selected' : '' }}>
                                            {{ $courier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('courier_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">Courier is required</div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="csv_file" class="form-label">CSV File</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".csv,text/csv" required>
                                <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/pincode_master_sample.csv') }}" class="btn-ghost-primary btn-sm">
                                    <i class="bi bi-download"></i> Download Template
                                </a>
                                @error('file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">The CSV file must have a pincodes, city, state, route_code, forward_pickup,	forward_delivery,	reverse_pickup, cod, prepaid column.</div>
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
                    @if($pincodes->isEmpty())
                        <p class="text-center my-2">No Couriers have for upload pincodes</p>
                    @else
                        <div class="card-header">
                            <h4 class="card-header-title">All Pincodes</h4>
                        </div>
                        <div class="table-responsive datatable-custom">
                            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Courier Name</th>              
                                        <th class="text-end">Pincode Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pincodes as $pincode)
                                        <tr>
                                            <td>{{ $pincode->courier['name'] }}<br>ID:{{ $pincode->courier_id }}</td>
                                            <td class="text-end">                                              
                                                <a type="button" class="btn-ghost-primary btn-sm m-1" onclick="exportMasterPincodesNumbers('{{ $pincode->courier_id }}')">
                                                   ({{ $pincode->total_pincodes }}) <i class="bi bi-download"></i>
                                                </a>
                                            </td>                                                              
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>                       
                    @endif
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
<script>
    function exportMasterPincodesNumbers(courierId) {
        $.ajax({
            url: '{{ route(panelPrefix().".masterPincodeExport") }}',
            method: 'GET',
            data: { courier_id: courierId },
            xhrFields: { responseType: 'blob' },

            success: function (response, status, xhr) {

                let disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = "pincodes.csv";

                if (disposition && disposition.indexOf('filename=') !== -1) {
                    let match = disposition.match(/filename="?([^"]+)"?/);
                    if (match && match[1]) filename = match[1];
                }

                const blob = new Blob([response]);
                const link = document.createElement('a');

                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                link.remove();
            },

            error: function () {
                alert('Error exporting pincode numbers.');
            }
        });
    }
</script>
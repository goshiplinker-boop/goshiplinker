<x-layout>

    {{-- Page title --}}
    <x-slot name="title">Upload Courier Weight Sheet</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">
        Upload Courier Weight Sheet
    </x-slot>

    {{-- Page header --}}
    <x-slot name="page_header_title">
        <h1 class="page-header-title">Upload Courier Weight Sheet</h1>
    </x-slot>

    {{-- Main content --}}
    <x-slot name="main">

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-soft-success alert-dismissible" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Error messages --}}
        @if ($errors->any())
            <div class="alert alert-soft-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Courier Weight Reconciliation</h4>
            </div>

            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.weight-discrepancies.upload') }}"
                      enctype="multipart/form-data">

                    @csrf
                    {{-- File upload --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Courier</label>
                            <select name="courier_id" class="form-control" required>
                                <option value="">Select Courier</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}"
                                        {{ old('courier_id') == $courier->id ? 'selected' : '' }}>
                                        {{ $courier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Upload Courier Sheet
                                <small class="text-muted">(CSV / XLSX)</small>
                            </label>
                            <input type="file"  name="file" class="form-control" accept=".csv,.xlsx"  required>
                            <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/courier_weight_discripancies.csv') }}" class="btn-ghost-primary btn-sm">
                                        <i class="bi bi-download"></i> Download Template
                            </a>
                            @error('csv_file')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <div class="invalid-feedback">The CSV file must have a Tracking Number,Courier Weight(Kg) column.</div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            Upload & Process
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </x-slot>

</x-layout>

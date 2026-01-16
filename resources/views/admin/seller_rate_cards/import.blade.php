<x-layout>
    <x-slot name="title">Import Seller Rate Card</x-slot>
    <x-slot name="breadcrumbs"> Import RateCard </x-slot>

    <x-slot name="page_header_title">
        <h1 class="page-header-title">Import Seller Rate Card (CSV)</h1>
    </x-slot>
     <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="javascript:history.back()" class="btn btn-light btn-sm"> <i class="bi bi-chevron-left"></i> {{ __('message.back') }} </a>
        </div>
    </x-slot>
    <x-slot name="main">
    @if($errors->any())
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('import_errors'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach(session('import_errors') as $failure)
                    <li>
                        Row {{ $failure->row() }}:
                        @foreach($failure->errors() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </li>
                @endforeach
            </ul>
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h4 class="card-header-title">Import Seller Rate card </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('manage_seller_rate_card.import') }}"
                method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row">
                  <div class="col-sm-6 mb-3">
                     <label for="seller_company_id" class="form-label">Sellers</label>
                     <select name="seller_company_id" id="sellerSelect" class="form-control" required>
                        <option value="">Select Seller</option>
                        @foreach ($sellers as $seller)
                              <option value="{{ $seller->id }}"
                                {{ request('seller_company_id') == $seller->id ? 'selected' : '' }}>
                                 {{ $seller->brand_name ?? $seller->user['name'] }}
                              </option>
                        @endforeach
                     </select>
                     @error('seller_company_id')
                        <span class="text-danger">{{ $message }}</span>
                     @enderror
                     <div class="invalid-feedback">Seller is required</div>
                  </div>
                  <div class="col-md-6  mb-3">
                        <label class="form-label">CSV File</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/courier_rate_card_sample.csv') }}" class="btn-ghost-primary btn-sm">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a href="{{ route('manage_seller_rate_card.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
    </x-slot>
</x-layout>

<x-layout>
    <x-slot name="title">Add Courier Rate</x-slot>
    <x-slot name="breadcrumbs"> RateCard Create </x-slot>

    <x-slot name="page_header_title">
        <h1 class="page-header-title">Add Courier Rate</h1>
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
    <div class="card">
        <div class="card-header">
            <h4 class="card-header-title">Create New Courier Rate Card </h4>
        </div>
        <div class="card-body">

            <form action="{{ route('manage_rate_card.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-12">
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
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Zone</label>
                        <select name="zone_name" class="form-control" required>
                            <option value="">Select Zone</option>
                            @foreach(['A','B','C','D','E','F'] as $zone)
                                <option value="{{ $zone }}" {{ old('zone_name') == $zone ? 'selected' : '' }}>
                                    {{ $zone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weight Slab (Kg)</label>
                        <select name="weight_slab_kg" class="form-select" required>
                            <option value="">Select weight</option>
                            @foreach ($weight_slabs as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('weight_slab_kg') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Base Freight (₹)</label>
                        <input type="number" step="0.01" name="base_freight_forward"
                            class="form-control"
                            value="{{ old('base_freight_forward') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Additional Freight (₹)</label>
                        <input type="number" step="0.01" name="additional_freight"
                            class="form-control"
                            value="{{ old('additional_freight') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                     <div class="col-md-6">
                        <label class="form-label">RTO Freight (₹)</label>
                        <input type="number" step="0.01" name="rto_freight"
                            class="form-control"
                            value="{{ old('rto_freight') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">COD Charge (₹)</label>
                        <input type="number" step="0.01" name="cod_charge"
                            class="form-control"
                            value="{{ old('cod_charge') }}" required>
                    </div>                    
                </div>
                <div class="row mb-3">                     
                    <div class="col-md-6">
                        <label class="form-label">COD Percentage</label>
                        <input type="number" step="0.01" name="cod_percentage"
                            class="form-control"
                            value="{{ old('cod_percentage') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delivery SLA</label>
                        <select name="delivery_sla" class="form-select" required>
                            <option value="">Select duration</option>
                            @foreach ($slas as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('delivery_sla') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>                   
                </div>
                <div class="row mb-3"> 
                    <div class="col-md-6">
                        <label class="form-label d-block">COD Allowed</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                name="cod_allowed" id="cod_yes" value="1"
                                {{ old('cod_allowed', 1) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="cod_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                name="cod_allowed" id="cod_no" value="0"
                                {{ old('cod_allowed') === '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="cod_no">No</label>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <label class="form-label">Sorting</label>
                        <input type="numeric" name="sort_order"  class="form-control" value="{{ old('sort_order') }}" required>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('manage_rate_card.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-slot>
</x-layout>

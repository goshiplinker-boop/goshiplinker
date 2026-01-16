<x-layout>
    <x-slot name="title">{{__('message.buyer_invoice.title')}}</x-slot>
    <x-slot name="breadcrumbs">{{ Breadcrumbs::render('order_invoice') }}</x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.buyer_invoice.page_header_title')}}</h1></x-slot>
    <x-slot name="main">
        @if (session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-soft-success" role="alert">
            {{ session('error') }}
        </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form action="{{ route('store_order_invoice') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="number_type" class="form-label">{{__('message.buyer_invoice.number_type')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="order_number">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" id="order_number" name="number_type" value="order_number" {{ old('number_type', $buyerInvoiceSettings->number_type ?? 'order_number') === 'order_number' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.buyer_invoice.order_number')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="custom_number">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" id="custom_number" name="number_type" value="custom_number" {{ old('number_type', $buyerInvoiceSettings->number_type ?? 'order_number') === 'custom_number' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.buyer_invoice.custom_number')}}</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="invoice_type" class="form-label">{{__('message.buyer_invoice.invoice_type')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="thermal_4x6">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" id="thermal_4x6" name="invoice_type"  value="thermal_4x6" {{ old('invoice_type', $buyerInvoiceSettings->invoice_type ?? 'thermal_4x6') === 'thermal_4x6' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.buyer_invoice.size')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="classic_a4">
                                        <span class="form-check">
                                            <input type="radio" disabled class="form-check-input" id="classic_a4" name="invoice_type" value="classic_a4" {{ old('invoice_type', $buyerInvoiceSettings->invoice_type ?? 'thermal_4x6') === 'classic_a4' ? 'checked' : '' }}>
                                            <span class="form-check-label">{{__('message.buyer_invoice.A4_size')}}</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" data-dependent="custom_number">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="prefix" class="form-label">{{__('message.buyer_invoice.invoice_prefix')}}</label>
                                    <input type="text" name="prefix" id="prefix" class="form-control" value="{{ old('prefix', $buyerInvoiceSettings->prefix ?? '') }}" placeholder="Enter prefix">
                                    @error('prefix')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <span class="invalid-feedback">{{__('message.buyer_invoice.error_invoice_prefix')}}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="start_from" class="form-label">{{__('message.buyer_invoice.invoice_series')}}</label>
                                    <input type="number" name="start_from" id="start_from" class="form-control" value="{{ old('start_from', $buyerInvoiceSettings->start_from ?? '') }}" placeholder="e.g., 119461">
                                    @error('start_from')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <span class="invalid-feedback">{{__('message.buyer_invoice.error_invoice_series')}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="preview" class="form-label">{{__('message.buyer_invoice.preview_of_invoice')}}</label>
                                    <input type="text" id="preview" class="form-control" value="{{ old('prefix', $buyerInvoiceSettings->prefix ?? '') . old('start_from', $buyerInvoiceSettings->start_from ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{__('message.save')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-slot>
</x-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const numberTypeInputs = document.querySelectorAll('input[name="number_type"]');
        const customNumberFields = document.querySelectorAll('[data-dependent="custom_number"]');
        const prefixInput = document.getElementById('prefix');
        const startFromInput = document.getElementById('start_from');
        const previewInput = document.getElementById('preview');

        function toggleCustomFields() {
            const isCustomNumber = document.getElementById('custom_number').checked;
            customNumberFields.forEach(field => {
                field.style.display = isCustomNumber ? 'block' : 'none';
                field.querySelector('input').required = isCustomNumber;
            });
        }

        function updatePreview() {
            const prefix = prefixInput.value || '';
            const startFrom = startFromInput.value || '';
            previewInput.value = `${prefix}${startFrom}`;
        }

        numberTypeInputs.forEach(input => input.addEventListener('change', toggleCustomFields));
        prefixInput.addEventListener('input', updatePreview);
        startFromInput.addEventListener('input', updatePreview);

        toggleCustomFields(); // Initialize on page load
    });
</script>
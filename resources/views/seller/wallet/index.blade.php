<x-layout>

    {{-- Page Title --}}
    <x-slot name="title">Wallet Transactions</x-slot>

    {{-- Breadcrumbs --}}
    <x-slot name="breadcrumbs">Wallet
    </x-slot>

    {{-- Page Header --}}
    <x-slot name="page_header_title">
        <h1 class="page-header-title">Wallet Transactions</h1>
    </x-slot>

    {{-- Header Buttons --}}
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="javascript:history.back()" class="btn btn-light btn-sm"> <i class="bi bi-chevron-left"></i> {{ __('message.back') }} </a>
        </div>
    </x-slot>

    {{-- Main Content --}}
    <x-slot name="main">

        <div class="row">

            {{-- LEFT SIDE : TRANSACTIONS TABLE --}}
            <div class="col-lg-9">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Transaction History</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-lg table-borderless table-thead-bordered table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Order</th>
                                    <!-- <th>Shipment</th> -->
                                    <th>Tracking</th>
                                    <!-- <th>Cod Charges</th> -->
                                    <th class="text-end">Debit (₹)</th>
                                    <th class="text-end">Credit (₹)</th>
                                    <th class="text-end">Balance (₹)</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($transactions as $index => $txn)
                                    <tr class="{{ $txn->transaction_type === 'freight_reversal' ? 'bg-soft-warning' : '' }}">
                                        <td>
                                            {{ $txn->created_at->format('d M Y') }}
                                            <span class="d-block fs-6 text-muted">
                                                {{ $txn->created_at->format('H:i') }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $txn->description }}
                                            <span class="d-block fs-6 text-muted">
                                                {{ ucfirst(str_replace('_',' ', $txn->transaction_type)) }}
                                            </span>
                                        </td>

                                        <td>{{ $txn->order_id ?? '-' }}</td>
                                        <!-- <td>{{ $txn->shipment_id ?? '-' }}</td> -->
                                        <td>{{ $txn->tracking_number ?? '-' }}</td>
                                        <!-- <td>{{ $txn->cod_charges ?? '-' }}</td> -->

                                        <td class="text-end text-danger">
                                            {{ $txn->direction === 'debit' ? number_format($txn->amount,2) : '-' }}
                                        </td>

                                        <td class="text-end text-success">
                                            {{ $txn->direction === 'credit' ? number_format($txn->amount,2) : '-' }}
                                        </td>

                                        <td class="text-end fw-bold">
                                            {{ number_format($txn->closing_balance,2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No transactions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDE : FILTERS --}}
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Filters</h4>
                    </div>

                    <div class="card-body">
                        <form method="GET">

                            {{-- Date Range --}}
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <input type="text"
                                       id="daterange"
                                       class="form-control"
                                       placeholder="Select date range">
                                <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                                <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                            </div>

                            {{-- Transaction Type --}}
                            <div class="mb-3">
                                <label class="form-label">Transaction Type</label>
                                <select name="transaction_type" class="form-select">
                                    <option value="">All</option>
                                    <option value="freight_charge" @selected(request('transaction_type')=='freight_charge')>
                                        Freight Charge
                                    </option>
                                    <option value="freight_reversal" @selected(request('transaction_type')=='freight_reversal')>
                                        Freight Reversal
                                    </option>
                                    <option value="cod_reversal" @selected(request('transaction_type')=='cod_reversal')>
                                        Cod Reversal
                                    </option>
                                    <option value="additional_weight_charge" @selected(request('transaction_type')=='additional_weight_charge')>
                                        Additional Weight Charge
                                    </option>
                                    <option value="wallet_topup" @selected(request('transaction_type')=='wallet_topup')>
                                        Wallet Topup
                                    </option>
                                    <option value="adjustment" @selected(request('transaction_type')=='adjustment')>
                                        Adjustment
                                    </option>
                                </select>
                            </div>

                            {{-- Debit / Credit --}}
                            <div class="mb-3">
                                <label class="form-label">Debit / Credit</label>
                                <select name="direction" class="form-select">
                                    <option value="">All</option>
                                    <option value="debit" @selected(request('direction')=='debit')>Debit</option>
                                    <option value="credit" @selected(request('direction')=='credit')>Credit</option>
                                </select>
                            </div>

                            {{-- Buttons --}}
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-funnel me-1"></i> Apply Filters
                                </button>

                                <a href="{{ route('seller.wallet.index') }}" class="btn btn-white btn-sm">
                                    Reset
                                </a>
                               
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>

    </x-slot>
</x-layout>

<script>
    $(function () {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format('DD/MM/YYYY') +
                ' - ' +
                picker.endDate.format('DD/MM/YYYY')
            );

            $('#date_from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#date_to').val(picker.endDate.format('YYYY-MM-DD'));
        });

        $('#daterange').on('cancel.daterangepicker', function () {
            $(this).val('');
            $('#date_from').val('');
            $('#date_to').val('');
        });
    });
</script>

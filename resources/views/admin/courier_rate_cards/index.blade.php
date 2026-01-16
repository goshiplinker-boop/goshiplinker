<x-layout>
    <x-slot name="title">Courier Rate Cards</x-slot>
    <x-slot name="breadcrumbs">
       RateCard List
    </x-slot>

    <x-slot name="page_header_title">
        <h1 class="page-header-title">Courier Rate Cards</h1>
    </x-slot>
     <x-slot name="headerbuttons">        
      <div class="col-sm-auto">
         <a class="btn btn-primary btn-sm" href="{{ route('manage_rate_card.create') }}"><i class="bi bi-plus-circle me-1"></i>Add New Rate</a>
          <a class="btn btn-primary btn-sm" href="{{ route('manage_rate_card.import.form') }}"><i class="bi bi-plus-circle me-1"></i>Import CSV</a>
      </div>
   </x-slot>
   
    <x-slot name="main">
        @if(session('success'))
            <div class="alert alert-soft-success alert-dismissible" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card">
             @if($rateCards->isEmpty())
            <p class="text-center my-2">No Rate card Found</p>
            @else
            <div class="card-header">
                <h4 class="card-header-title">All Courier Rates </h4>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Courier</th>
                            <th>Zone</th>
                            <th>Weight Slab (Kg)</th>
                            <th>Base Freight (₹)</th>
                            <th>Additional Freight (₹)</th>
                            <th>RTO Freight (₹)</th>
                            <th>COD Charge (₹)</th>
                            <th>COD %</th>
                            <th>Delivery SLA</th>
                            <th>COD Allowed</th>
                            <th>Sorting</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rateCards as $rateCard)
                            <tr>
                                <td>{{ $loop->iteration + ($rateCards->currentPage() - 1) * $rateCards->perPage() }}</td>
                                <td>{{ $rateCard->courier->name ?? 'N/A' }}<br>ID:{{ $rateCard->courier->id}}</td>
                                <td>{{ $rateCard->zone_name }}</td>
                                <td>{{ $rateCard->weight_slab_kg }}</td>
                                <td>{{ $rateCard->base_freight_forward }}</td>
                                <td>{{ $rateCard->additional_freight }}</td>
                                <td>{{ $rateCard->rto_freight }}</td>
                                <td>{{ $rateCard->cod_charge }}</td>
                                <td>{{ $rateCard->cod_percentage }}</td>
                                <td>{{ $rateCard->delivery_sla }}</td>
                                <td>
                                    @if($rateCard->cod_allowed)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                                <td>{{ $rateCard->sort_order }}</td>
                                <td style="width:152px;">
                                    <a href="{{ route('manage_rate_card.edit', $rateCard->id) }}"
                                    class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <!-- <form action="{{ route('manage_rate_card.destroy', $rateCard->id) }}"
                                        method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this rate?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form> -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">No rate cards found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $rateCards->links() }}
            </div>
            @endif
        <div>
    </x-slot>
</x-layout>

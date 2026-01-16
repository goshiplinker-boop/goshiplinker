<x-layout>
   <x-slot name="title">{{__('message.subscriptions.heading_title')}}</x-slot>
   <x-slot name="breadcrumbs">Payment</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">Payment History</h1>
   </x-slot>
   <x-slot name="headerbuttons">    
   </x-slot>
   <x-slot name="main">      
      <div class="card">
         @if($subscriptions->isEmpty())
         <p class="text-center my-2">{{__('message.subscriptions.not_found')}}</p>
         @else
         <div class="table-responsive datatable-custom">
            <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-align-middle card-table" data-hs-datatables-options='{
               "columnDefs": [{
               "targets": [0, 7],
               "orderable": false
               }],
               "order": [],
               "info": {
               "totalQty": "#datatableWithPaginationInfoTotalQty"
               },
               "search": "#datatableSearch",
               "entries": "#datatableEntries",
               "pageLength": 15,
               "isResponsive": false,
               "isShowPaging": false,
               "pagination": "datatablePagination"
               }'>
               <thead class="thead-light">
                  <tr>          
                    <th>Payment ID</th>
                    <th>Plan Name</th>
                    <th>Paid Amount</th>
                    <th>Purchased Credits</th>
                    <th>Total Credits</th>
                    <th>Purchase Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                @php
                    $i = 0;
                @endphp
                @foreach($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->payment_order_id }}</td>
                        <td>{{ $subscription->plan->name ?? 'N/A' }}</td>
                        <td>{{ $subscription->paid_amount }}</td>
                        <td>{{ $subscription->purchased_credits }}</td>
                        <td>{{ $subscription->total_credits }}</td>                        
                        <td>{{ \Carbon\Carbon::parse($subscription->created_at)->format('d M Y H:i:s') }}</td>
                        <td>{{ \Carbon\Carbon::parse($subscription->expiry_date)->format('d M Y') }}</td>
                        <td>
                            @if($subscription->payment_status !== null && $subscription->payment_status == '1')
                                <span class="badge bg-success rounded-pill">Active</span>
                            @elseif($subscription->payment_status !== null && $subscription->payment_status == '0')
                                <span class="badge bg-danger rounded-pill">Expired</span>
                            @endif
                        </td>
                        <td><a href="{{route('seller_invoice',[$subscription->id])}}" target="__blank">Invoice</a></td>
                    </tr>
                @endforeach
               </tbody>
            </table>
            @endif
         </div>
         <div class="card-footer">
            {{ $subscriptions->links('pagination::bootstrap-5') }}
         </div>
      </div>
   </x-slot>
</x-layout>
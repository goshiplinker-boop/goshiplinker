<x-layout>
   <x-slot name="title">Unfulfilled</x-slot>
   <x-slot name="breadcrumbs"> </x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">Unfulfilled</h1>
   </x-slot>
   <x-slot name="main">
      <div class="card">
         <div class="card-header">
            <h4 class="card-header-title">Unfulfilled Orders</h4>
         </div>
         @if($orders->isNotEmpty())
         <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
               <thead class="thead-light">
                  <tr>
                     <th>
                        <div class="form-check">
                           <input class="form-check-input" type="checkbox" value="" id="datatableCheckAll">
                        </div>
                     </th>
                     <th class="table-column-ps-0">Order Number</th>
                     <th>Sale Channel</th>
                     <th>Request Data</th>
                     <th>Response Data</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($orders as $order)
                  <tr>
                     <td class="table-column-pe-0">
                        <div class="form-check">
                           <input type="checkbox" class="form-check-input ordersCheck1" id="ordersCheck1_{{ $order->id }}" value="{{ $order->id }}">
                           <label class="form-check-label" for="ordersCheck1_{{ $order->id }}"></label>
                        </div>
                     </td>
                     <td>{{ $order->id }}</td>
                     <td>{{ $order->channelSetting->channel_title ?? 'Not available' }}</td>
                     <td>{{ is_array(optional($order->orderLogs)->response) ? implode(', ', optional($order->orderLogs)->response) : optional($order->orderLogs)->response ?? 'Null' }}</td>
                     <td>{{ is_array(optional($order->orderLogs)->payload) ? implode(', ', optional($order->orderLogs)->payload) : optional($order->orderLogs)->payload ?? 'Null' }}</td>
                     <td><a href="#" class="btn btn-primary btn-sm">Fulfill</a></td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         @else
         <div class="card-header text-center">No data found</div>
         @endif
      </div>
   </x-slot>
</x-layout>
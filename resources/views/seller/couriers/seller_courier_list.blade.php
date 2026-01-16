<x-layout>
   <x-slot name="title">{{__('message.couriers.page_header_title')}}</x-slot>
   <x-slot name="breadcrumbs">{{ Breadcrumbs::render('couriers_list') }}</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">{{ __('message.couriers.page_header_title') }}</h1>
   </x-slot>

   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div id="connectedAccountsSection" class="card">
         <div class="card-header">
            <h4 class="card-title">@if((Request::segment(1) =='admin') && session()->has('role_id') && session('role_id')==1) Activate Seller couriers @else Manage Couriers @endif</h4>
         </div>

         <div class="card-body">
            <form method="POST" action="@if(Request::segment(1) =='admin'){{ route(panelPrefix().'.admin_couriers_update') }} @else {{ route(panelPrefix().'.seller_couriers_update') }}@endif">
               @csrf
               @if((Request::segment(1) =='admin') && session()->has('role_id') && session('role_id')==1)

                <div class="row">
                  <div class="col-sm-12 mb-3">
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
               </div>
               @endif
               <div class="row">
                  <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table" >
                     <thead class="thead-light">
                        <tr>
                           <th>Courier Name</th>
                           <th>Logo</th>
                           <th class="text-end">{{__('message.action')}}</th>
                        
                        </tr>
                     </thead>
                     
                     <tbody>
                        @if($companyCouriers->isNotEmpty())
                           @foreach($companyCouriers as $companycourier)
                           <tr>
                              <td>{{ $companycourier->courier_title }}<br><small>ID: {{ $companycourier->id }}</small></td>
                              <td class="pm-store-img"><img class="avatar-img" src="{{ asset(env('PUBLIC_ASSETS') . '/' . $companycourier->image_url) }}" alt="{{$companycourier->courier_code}}"></td>                             
                              <td class="text-end">
                                 <div class="form-check form-switch float-end"><input type="hidden" name="seller_couriers[{{ $companycourier->id }}]" value="0">
                                    <input class="form-check-input" type="checkbox" id="seller_courier_id_{{ $companycourier->id }}" name="seller_couriers[{{ $companycourier->id }}]" value="1" @if(Request::segment(1) =='seller'){{ (($companycourier->seller_courier_status==1) ? 1 : 0) === 1 ? 'checked' : '' }} @else {{ (($companycourier->main_courier_status==1) ? 1 : 0) === 1 ? 'checked' : '' }} @endif>
                                    <label class="form-check-label" for="seller_courier_id_{{ $companycourier->id }}"></label>
                                 </div>
                              </td>
                           </tr>
                           @endforeach
                        @else
                        <tr>
                           <td colspan='2' class="text-center">No Couriers found </td>
                        </tr>
                        @endif
                     </tbody>
                      
                  </table>
               </div>
               @if($companyCouriers->isNotEmpty())
               <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                           {{ __('message.save') }}
                        </button>
                    </div>
                </div>
               @endif
            </form>
         </div>
      </div>
      <script>
         document.getElementById('sellerSelect').addEventListener('change', function () {
            let seller = this.value;
            let url = new URL(window.location.href);

            // Set query parameter seller_company_id
            if (seller) {
               url.searchParams.set('seller_company_id', seller);
            } else {
               url.searchParams.delete('seller_company_id');
            }

            // Reload page
            window.location.href = url.toString();
         });
      </script>

   </x-slot>
</x-layout>

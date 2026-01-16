<x-layout>
   <x-slot name="title">Edit</x-slot>

   <x-slot name="breadcrumbs">
      {{ Breadcrumbs::render('shopbase.create') }}
   </x-slot>

   <x-slot name="page_header_title">
      <h1 class="page-header-title">{{ __('message.shopbase_create.page_header_title') }}</h1>
   </x-slot>

   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="#" class="btn btn-light btn-sm">
            <i class="bi bi-chevron-left"></i> {{ __('message.back') }}
         </a>
      </div>
   </x-slot>

   <x-slot name="main">
      @if ($errors->any())
         <div class="alert alert-soft-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <div class="row">
         <div class="col-sm-4 mb-3">
            <div class="card h-100">
               <div class="card-body">
                  {{ __('message.shopbase_create.existing_data') }}
               </div>
            </div>
         </div>

         <div class="card col-sm-8">
            <div class="card-body">
               <!-- Nav -->
               <ul class="nav nav-segment mb-2" role="tablist">
                  <li class="nav-item">
                     <a class="nav-link active" id="nav-one-eg1-tab" data-bs-toggle="pill"
                        href="#nav-one-eg1" role="tab" aria-controls="nav-one-eg1" aria-selected="true">
                        Basic Configuration
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" id="nav-two-eg1-tab" data-bs-toggle="pill"
                        href="#nav-two-eg1" role="tab" aria-controls="nav-two-eg1" aria-selected="false">
                        Payment Mapping
                     </a>
                  </li>
               </ul>

               <form class="needs-validation" id="opencart-form" method="POST"
                  action="{{ route('opencart.update', $opencart->channel_id) }}"
                  enctype="multipart/form-data" novalidate>
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="channel_code" value="{{ old('channel_code', 'opencart') }}">

                  <div class="tab-content">
                     <div class="tab-pane fade show active" id="nav-one-eg1" role="tabpanel"
                        aria-labelledby="nav-one-eg1-tab">
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Channel Name</label>
                              <input type="text" name="channel_title" class="form-control"
                                 value="{{ old('channel_title', $opencart->channel_title ?? '') }}"
                                 placeholder="Enter Channel Name" required>
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Channel URL</label>
                              <input type="text" name="channel_url" class="form-control"
                                 value="{{ old('channel_url', $opencart->channel_url ?? '') }}"
                                 placeholder="https://yourstore.com" required>
                           </div>
                        </div>

                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Client ID</label>
                              <input type="text" name="client_id" class="form-control"
                                 value="{{ old('client_id', $opencart->client_id ?? '') }}"
                                 placeholder="Enter Client ID">
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Client Secret Key</label>
                              <input type="text" name="secret_key" class="form-control"
                                 value="{{ old('secret_key', $opencart->secret_key ?? '') }}"
                                 placeholder="Enter Secret Key">
                           </div>
                        </div>

                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Fetch Status</label>
                              <input type="text" name="other_details[fetch_status]" class="form-control"
                                 value="{{ old('other_details.fetch_status', $opencart->other_details['fetch_status'] ?? 'pending,processing') }}"
                                 placeholder="pending,processing">
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label class="form-label">Order Tags (optional)</label>
                              <input type="text" name="other_details[order_tags]" class="form-control"
                                 value="{{ old('other_details.order_tags', $opencart->other_details['order_tags'] ?? '') }}"
                                 placeholder="COD, Express, etc.">
                           </div>
                        </div>

                        <div class="col-sm-12 mb-3">
                           <label for="statuses" class="form-label">{{ __('message.status') }}</label>
                           <div class="input-group input-group-sm-vertical">
                              <label class="form-control" for="status">
                                 <span class="form-check">
                                    <input type="radio" class="form-check-input" value="1" name="status"
                                       {{ old('status', $opencart->status ?? '1') == '1' ? 'checked' : '' }} id="status">
                                    <span class="form-check-label">{{ __('message.active') }}</span>
                                 </span>
                              </label>
                              <label class="form-control" for="status_inactive">
                                 <span class="form-check">
                                    <input type="radio" class="form-check-input" value="0" name="status"
                                       {{ old('status', $opencart->status ?? '') == '0' ? 'checked' : '' }}
                                       id="status_inactive">
                                    <span class="form-check-label">{{ __('message.inactive') }}</span>
                                 </span>
                              </label>
                           </div>
                           @error('status')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                           <div class="invalid-feedback">{{ __('message.error_status') }}</div>
                        </div>
                     </div>

                     <div class="tab-pane fade" id="nav-two-eg1" role="tabpanel" aria-labelledby="nav-two-eg1-tab">
                        @if ($payment_types->isNotEmpty())
                           <div class="row">
                              <div class="col-sm-12">
                                 <div class="card">
                                    <div class="card-header">
                                       <h4 class="card-header-title">Payment Gateway Mapping</h4>
                                    </div>
                                    <table class="table">
                                       <thead class="thead-light">
                                          <tr>
                                             <th>Payment Gateway Names</th>
                                             <th>Payment Mode</th>
                                             <th>Payment Mode</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          @foreach ($payment_types as $payment_type)
                                             <tr>
                                                <td>{{ $payment_type->gateway_name }}</td>
                                                <td>
                                                   <input type="radio" id="cod{{ $payment_type->id }}" class="form-check-input"
                                                      name="payment_mapping[{{ $payment_type->id }}]" value="cod"
                                                      @if (old('payment_mapping.' . $payment_type->id) == 'cod' || $payment_type->payment_mode == 'cod') checked @endif>
                                                   <label class="form-check-label" for="cod{{ $payment_type->id }}">{{ __('message.woocommerce.cod') }}</label>
                                                </td>
                                                <td>
                                                   <input type="radio" id="prepaid{{ $payment_type->id }}" class="form-check-input"
                                                      name="payment_mapping[{{ $payment_type->id }}]" value="prepaid"
                                                      @if (old('payment_mapping.' . $payment_type->id) == 'prepaid' || $payment_type->payment_mode == 'prepaid') checked @endif>
                                                   <label class="form-check-label" for="prepaid{{ $payment_type->id }}">{{ __('message.woocommerce.prepaid') }}</label>
                                                </td>
                                             </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        @endif
                     </div>

                     <div class="row mt-3">
                        <div class="col-sm-12 text-end">
                           <button type="submit" class="btn btn-primary">{{ __('message.update') }}</button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </x-slot>
</x-layout>
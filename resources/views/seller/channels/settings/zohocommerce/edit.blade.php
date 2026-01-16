<x-layout>
   <x-slot name="title"> {{__('message.edit')}} </x-slot>
   <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('woocommerce.edit', $woocommerce) }} </x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">{{__('message.woocommerce.edit_page_header_title')}}</h1>
   </x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="#" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
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
         <div class="col-sm-4">
            <div class="card h-100">
               <div class="card-body">
                  <p class="link">{{__('message.woocommerce.guidelines')}}</p>
                  <div id="integrateStepsData" class="integrateStepsData descColor">
                     <div class="genInfoList">
                        <p>{!!__('message.woocommerce.guidelines_description')!!} </p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="card col-sm-8">
            <div class="card-body">
               <!-- Nav -->
               <div class="">
                  <ul class="nav nav-segment mb-2" role="tablist">
                     <li class="nav-item">
                        <a class="nav-link active" id="nav-one-eg1-tab" href="#nav-one-eg1" data-bs-toggle="pill" data-bs-target="#nav-one-eg1" role="tab" aria-controls="nav-one-eg1" aria-selected="true">Basic Configuration</a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link" id="nav-two-eg1-tab" href="#nav-two-eg1" data-bs-toggle="pill" data-bs-target="#nav-two-eg1" role="tab" aria-controls="nav-two-eg1" aria-selected="false">Payment Mapping</a>
                     </li>
                  </ul>
               </div>
               <!-- End Nav -->
               <form class="needs-validation" action="{{ route('woocommerce.update', $woocommerce->channel_id) }}" id="woocommerce-form" method="POST" enctype="multipart/form-data" novalidate>
                  @csrf
                  @method('PUT')

                  <!-- This is important for the update request -->
                  <input type="hidden" name="channel_code" value="woocommerce">
                  <div class="tab-content">
                     <div class="tab-pane fade show active" id="nav-one-eg1" role="tabpanel" aria-labelledby="nav-one-eg1-tab">
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label for="channel_title" class="form-label">{{__('message.woocommerce.channel_title')}}</label>
                              <input type="text" id="channel_title" name="channel_title" class="form-control" value="{{ old('channel_title', $woocommerce->channel_title) }}" placeholder="Enter channel title" required>
                              @error('channel_title')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.channel_title_placeholder')}}</div>
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label for="brand_name" class="form-label">{{__('message.woocommerce.brand_name')}}</label>
                              <input type="text" id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name', $woocommerce->brand_name) }}" placeholder="Enter company name" required>
                              @error('brand_name')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.brand_name_placeholder')}}</div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label for="channel_url" class="form-label">{{__('message.woocommerce.channel_URL')}}</label>
                              <input type="text" id="channel_url" name="channel_url" class="form-control" value="{{ old('channel_url', $woocommerce->channel_url) }}" placeholder="Enter channel URL" required>
                              @error('channel_url')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.channel_URL')}}</div>
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label for="client_id" class="form-label">{{__('message.woocommerce.client_id')}}</label>
                              <input type="text" id="client_id" name="client_id" class="form-control" value="{{ old('client_id', $woocommerce->client_id) }}" placeholder="Enter client Id" required>
                              @error('client_id')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.client_id_placeholder')}}</div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label for="secret_key" class="form-label">{{__('message.woocommerce.key')}}</label>
                              <input type="text" id="secret_key" name="secret_key" class="form-control" value="{{ old('secret_key', $woocommerce->secret_key) }}" placeholder="Enter secret key" required>
                              @error('secret_key')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.key_placeholder')}}</div>
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label for="brand_logo" class="form-label">{{__('message.woocommerce.channel_logo')}}
                                 <span class="form-label-secondary">({{__('message.optional')}})</span>
                              </label>
                              @if(!is_null($woocommerce->brand_logo))
                              <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/logos/' . $woocommerce->brand_logo) }}" style="width:25px;">
                              @endif
                              <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                              @error('brand_logo')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label for="email" class="form-label">{{__('message.woocommerce.emails')}}
                                 <span class="form-label-secondary">({{__('message.optional')}})</span>
                              </label>
                              <input type="text" id="email" name="other_details[email]" class="form-control" value="{{ old('other_details.email', $woocommerce->other_details['email'] ?? '') }}" placeholder="Enter email">
                              @error('other_details.email')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label for="gstin" class="form-label">{{__('message.woocommerce.gstin')}}</label>
                              <input type="text" id="gstin" name="other_details[gstin]" class="form-control" value="{{ old('other_details.gstin', $woocommerce->other_details['gstin'] ?? '') }}" placeholder="Enter GSTIN" required>
                              @error('other_details.gstin')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.error_gstin')}}</div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label for="is_address_same" class="form-label">{{__('message.woocommerce.is_billing_address')}}</label>
                              <div class="input-group input-group-sm-vertical">
                                 <label class="form-control" for="is_address_same_yes">
                                    <span class="form-check">
                                       <input type="radio" class="form-check-input" value="1"  name="other_details[is_address_same]" required {{ old('other_details.is_address_same', $woocommerce->other_details['is_address_same'] ?? '1') == '1' ? 'checked' : '' }}  id="is_address_same_yes">
                                       <span class="form-check-label">{{__('message.yes')}}</span>
                                    </span>
                                 </label>
                                 <label class="form-control" for="is_address_same_no">
                                    <span class="form-check">
                                       <input type="radio" class="form-check-input" value="0" name="other_details[is_address_same]" required {{ old('other_details.is_address_same') == '0' ? 'checked' : '' }} id="is_address_same_no">
                                       <span class="form-check-label">{{__('message.no')}}</span>
                                    </span>
                                 </label>
                              </div>
                              @error('other_details.is_address_same')
                              <div class="text-danger">{{ $message }}</div>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.error_billing')}}</div>
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label class="form-label" for="fetch_status">{{__('message.woocommerce.status_to_fetch')}}</label><br>
                              <input type="text" id="fetch_status" name="other_details[fetch_status]" class="form-control" value=" {{ old('other_details.fetch_status', $woocommerce->other_details['fetch_status'] ?? 'pending,processing') }}" placeholder="Enter fetch status" required>
                              @error('other_details.fetch_status')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.enter_your_status')}}</div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-sm-6 mb-3">
                              <label class="form-label" for="order_tags">{{__('message.woocommerce.order_tags')}}
                                 <span class="form-label-secondary">({{__('message.optional')}})</span>
                              </label><br>
                              <input type="text" id="order_tags" name="other_details[order_tags]" class="form-control" value="{{ old('other_details.order_tags', $woocommerce->other_details['order_tags']??'') }}" placeholder="COD, COD Confirmed etc.">
                              @error('other_details.order_tags')
                              <span class="text-danger">{{ $message }}</span>
                              @enderror
                           </div>
                           <div class="col-sm-6 mb-3">
                              <label for="statuses" class="form-label">{{__('message.status')}}</label>
                              <div class="input-group input-group-sm-vertical">
                                 <label class="form-control" for="status">
                                    <span class="form-check">
                                       <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', $woocommerce->status) == '1' ? 'checked' : '' }} id="status">
                                       <span class="form-check-label">{{__('message.active')}}</span>
                                    </span>
                                 </label>
                                 <label class="form-control" for="status_inactive">
                                    <span class="form-check">
                                       <input type="radio" required class="form-check-input" value="0" name="status" {{ old('status', $woocommerce->status) == '0' ? 'checked' : '' }} id="status_inactive">
                                       <span class="form-check-label">{{__('message.inactive')}}</span>
                                    </span>
                                 </label>
                              </div>
                              @error('status')
                              <div class="text-danger">{{ $message }}</div>
                              @enderror
                              <div class="invalid-feedback">{{__('message.woocommerce.errorr_status')}}</div>
                           </div>
                        </div>
                     </div>
                     <div class="tab-pane fade my-2" id="nav-two-eg1" role="tabpanel" aria-labelledby="nav-two-eg1-tab">
                        @if($payment_types->isNotEmpty())
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
                                       @foreach($payment_types as $payment_type)
                                       <tr>
                                          <td>{{$payment_type->gateway_name}}</td>
                                          <td>
                                             <!-- Set the value attribute here to 'cod' -->
                                             <input type="radio" id="cod{{$payment_type->id}}"class="form-check-input" name="payment_mapping[{{$payment_type->id}}]" value="cod"  @if(old('payment_mapping.'.$payment_type->id) == 'cod' || $payment_type->payment_mode == 'cod') checked @endif>
                                             <label class="form-check-label" for="cod{{$payment_type->id}}">{{__('message.woocommerce.cod')}}</label>
                                          </td>
                                          <td>
                                             <!-- Set the value attribute here to 'prepaid' -->
                                             <input type="radio" id="prepaid{{$payment_type->id}}"  class="form-check-input" name="payment_mapping[{{$payment_type->id}}]" value="prepaid" @if(old('payment_mapping.'.$payment_type->id) == 'prepaid' || $payment_type->payment_mode == 'prepaid') checked @endif>
                                             <label class="form-check-label" for="prepaid{{$payment_type->id}}">{{__('message.woocommerce.prepaid')}}</label>
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
                  </div>
                  <div class="row">
                     <div class="col-sm-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">{{__('message.update')}}</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <script>
         // Set the initial value if it exists
         const initialFetchStatus =
             '{{ old("other_details.fetch_status", $woocommerce->other_details["fetch_status"] ?? '
         ') }}';         
         document.getElementById('sync_status').addEventListener('change', function() {
             const inputContainer = document.getElementById('syncstatusContainer');         
             if (this.checked) {
                 if (!document.getElementById('fetch_status')) {         
                     const newInput = document.createElement('input');
                     newInput.type = 'text';
                     newInput.id = 'fetch_status';
                     newInput.name = 'other_details[fetch_status]';
                     newInput.className = 'form-control';
                     newInput.placeholder = 'Enter status';
                     newInput.value = initialFetchStatus; // Set the initial value here
                     inputContainer.appendChild(newInput);
                 }
             } else {
                 const existingInput = document.getElementById('fetch_status');
                 if (existingInput) {
                     inputContainer.removeChild(existingInput);
                 }
             }
         });         
         // Set the initial state of the input based on the sync_status checkbox
         document.addEventListener('DOMContentLoaded', function() {
             const syncStatusCheckbox = document.getElementById('sync_status');
             if (syncStatusCheckbox.checked) {
                 const newInput = document.createElement('input');
                 newInput.type = 'text';
                 newInput.id = 'fetch_status';
                 newInput.name = 'other_details[fetch_status]';
                 newInput.className = 'form-control';
                 newInput.placeholder = 'Enter status';
                 newInput.value = initialFetchStatus; // Set the initial value here
                 document.getElementById('syncstatusContainer').appendChild(newInput);
             }
         });
      </script>
   </x-slot>
</x-layout>
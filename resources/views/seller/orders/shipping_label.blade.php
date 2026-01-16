<x-layout>
   <x-slot name="title">Shipping label</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">Shipping Label </h1>
   </x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
      </div>
   </x-slot>
   @php
    $settings = json_decode($buyerShippingSettings->extra_details ?? '{}', true);
   @endphp
   <x-slot name="main">
        @if (session('success'))
            <div class="alert alert-soft-success alert-dismissible" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
         @if (session('error'))
            <div class="alert alert-soft-danger alert-dismissible" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
      <div id="connectedAccountsSection" class="card">
         <div class="card-header">
            <h4 class="card-title">Settings</h4>
         </div>
         <div class="card-body">
            <form method="POST" action="{{ route('shipping_label') }}">
                @csrf
               <div class="list-group list-group-lg list-group-flush list-group-no-gutters">
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide prepaid order amount</h4>
                                 <p class="fs-5 text-body mb-0">By enabling this, the order amount with quantity will be masked by xxx </p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts1" name="hide_order_amount" {{ !empty($settings['hide_order_amount']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts1"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide the buyer's mobile number</h4>
                                 <p class="fs-5 text-body mb-0">By enabling this,  only the buyer's mobile number will be hidden.</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts2" name="hide_buyer_mobile" {{ !empty($settings['hide_buyer_mobile']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts2"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide the shipper's mobile number</h4>
                                 <p class="fs-5 text-body mb-0">By enabling this,  only the shipper's mobile number will be hidden</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts3" name="hide_shipper_mobile" {{ !empty($settings['hide_shipper_mobile']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts3"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide the shipper's return address</h4>
                                 <p class="fs-5 text-body mb-0"> By enabling this,  the shipper's return address will be hidden.</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts4" name="hide_return_address" {{ !empty($settings['hide_return_address']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts4"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide product name</h4>
                                 <p class="fs-5 text-body mb-0">By enenabling this, the product name will be masked by xxx</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts5"
                                    name="hide_product_name" {{ !empty($settings['hide_product_name']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts5"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Hide the product's SKU name</h4>
                                 <p class="fs-5 text-body mb-0">By enenabling this, the product name will be masked by xxx</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="connectedAccounts5" 
                                    name="hide_product_sku" {{ !empty($settings['hide_product_sku']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="connectedAccounts5"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Enable the Order Id Barcode</h4>
                                 <p class="fs-5 text-body mb-0">By enenabling this, order id barcode will show on shipping label</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_id_barcode" 
                                    name="enable_order_id_barcode" {{ !empty($settings['enable_order_id_barcode']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_id_barcode"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Enable the Logo</h4>
                                 <p class="fs-5 text-body mb-0">By enenabling this, Logo will show on shipping label</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_logo" 
                                    name="enable_logo" {{ !empty($settings['enable_logo']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_logo"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Enable the Order Notes</h4>
                                 <p class="fs-5 text-body mb-0">By enenabling this, order notes will show on shipping label</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="order_notes" 
                                    name="order_notes" {{ !empty($settings['order_notes']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_notes"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Disabled the Order Marked Auto Shipped </h4>
                                 <p class="fs-5 text-body mb-0">By disabling this, order will not marked shipped auto</p>
                              </div>
                              <div class="col-auto">
                                 <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_shipped" 
                                    name="auto_shipped" {{ (!isset($settings['auto_shipped']) OR !empty($settings['auto_shipped'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_shipped"></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="list-group-item">
                     <div class="d-flex">
                        <div class="flex-grow-1 ms-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <h4 class="mb-0">Instructions</h4>
                                 <textarea id="notes" name="notes" class="fs-5 text-body mb-0 form-control" placeholder="Instructions" >{{ old('notes', $settings['notes'] ?? '') }}</textarea>

                              </div>                              
                           </div>
                        </div>
                     </div>
                  </div>
               </div>                
               <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            {{ $isupdate ? __('message.update') : __('message.save') }}
                        </button>
                    </div>
                </div>
            </form>
            <!-- End Form -->
         </div>
         <!-- End Body -->
      </div>
   </x-slot>
</x-layout>
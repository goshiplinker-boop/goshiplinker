<x-layout>
    <x-slot name="title">{{__('message.shopify_create.connect_to_shopify')}} </x-slot>    
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('shopify.create') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.connect')}}</h1></x-slot>     
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="#" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
        </div>
    </x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link"> {{__('message.shopify_create.integration_steps')}}</p>
                        <p class="link">{!!__('message.shopify_create.admin_url')!!}</p>
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body pt-10">
                    <div class="col-sm-6 mb-3">
                                <label for="channel_url" class="form-label">{{__('message.shopify_create.channel_title')}}</label>
                                <input type="text" id="channel_url" name="channel_url" class="form-control" value="{{ old('channel_url') }}" placeholder="xyz.myshopify.com" required>
                                @error('channel_url')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.shopify.error_channel_url')}}</div>
                            </div>
                   <div class="text-left"><a target='__blank' href="{{ route('shopify.install') }}"  id="connectShopifyBtn" class="btn btn-primary btn-sm">{{__('message.shopify_create.connect_to_shopify')}}</a></div>
                  
                </div>
            </div>
        </div>
       <script>
            document.addEventListener('DOMContentLoaded', function () {
                const connectBtn = document.getElementById('connectShopifyBtn');
                const shopNameInput = document.getElementById('channel_url');

                connectBtn.addEventListener('click', function (e) {
                    let shopName = shopNameInput.value.trim();

                    if (!shopName) {
                        alert('Please enter a valid Shopify store name');
                        e.preventDefault();
                        return;
                    }

                    // Ensure it ends with .myshopify.com
                    if (!shopName.endsWith('.myshopify.com')) {
                        // Remove protocol if user added like https://abc
                        shopName = shopName.replace(/^https?:\/\//, '');

                        // Extract only the subdomain if user entered full URL
                        shopName = shopName.split('.')[0];

                        shopName = shopName + '.myshopify.com';
                    }

                    const baseUrl = "{{ route('shopify.install') }}";
                    this.href = `${baseUrl}?shop=${encodeURIComponent(shopName)}`;
                });
            });
        </script>
    </x-slot>
</x-layout>
<x-layout>
    <x-slot name="title">Edit</x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('shopbase.create') }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.shopbase_create.page_header_title')}}</h1></x-slot>
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
            <div class="col-sm-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        {{__('message.shopbase_create.existing_data')}}
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    <form class="needs-validation" action="{{ route('shopbase.store') }}"
                        id="shopbase-form" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <!-- This is important for the update request -->
                        <input type="hidden" name="channel_code" value="shopbase">

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="brand_name" class="form-label"> {{__('message.shopbase_create.Communication_Brand_Name')}}</label>
                                <input type="text" id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name') }}" placeholder="Enter company name" required>
                                @error('brand_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.shopbase_create.enter_brand_name')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="channel_title" class="form-label"> {{__('message.shopbase_create.channel_name')}}</label>
                                <input type="text" id="channel_title" name="channel_title" class="form-control" value="{{ old('channel_title') }}" placeholder="Enter channel title" required>
                                @error('channel_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.shopbase_create.your_channal_name')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="channel_url" class="form-label"> {{__('message.shopbase_create.channel_URL')}}</label>
                                <input type="text" id="channel_url" name="channel_url" class="form-control" value="{{ old('channel_url') }}" placeholder="Enter channel URL" required>
                                @error('channel_url')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.shopbase_create.your_channel_URL')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="client_id" class="form-label"> {{__('message.shopbase_create.api_key')}}</label>
                                <input type="text" id="client_id" name="client_id" class="form-control" value="{{ old('client_id') }}" placeholder="Enter client Id">
                                @error('client_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.shopbase_create.your_API_key')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="secret_key" class="form-label"> {{__('message.shopbase_create.api_password')}}</label>
                                <input type="text" id="secret_key" name="secret_key" class="form-control" value="{{ old('secret_key') }}" placeholder="Enter secret key">
                                @error('secret_key')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback"> {{__('message.shopbase_create.enter_your_API_password')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="brand_logo" class="form-label">{{__('message.woocommerce.logo')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                @error('brand_logo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="is_address_same" class="form-label"> {{__('message.shopbase_create.billing_and_shipping_address')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="is_address_same_yes">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="other_details[is_address_same]" required  {{ old('other_details.is_address_same', '1') == '1' ? 'checked' : '' }}  id="is_address_same_yes">
                                            <span class="form-check-label">{{__('message.yes')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="is_address_same_no">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="other_details[is_address_same]" required {{ old('other_details.is_address_same') == '0' ? 'checked' : '' }}  id="is_address_same_no">
                                            <span class="form-check-label">{{__('message.no')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('other_details.is_address_same')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.shopbase_create.enter_your_address')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label" for="fetch_status">{{__('message.shopbase_create.status_to_fetch')}}</label><br>
                                <input type="text" id="fetch_status" name="other_details[fetch_status]" class="form-control" value=" {{ old('other_details.fetch_status','unshipped,unfulfilled') }}" placeholder="Enter fetch status">
                                @error('other_details.fetch_status')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <span class="invalid-feedback">{{__('message.shopbase_create.enter_your_status')}}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <label class="form-label" for="order_tags">{{__('message.shopbase_create.order_tags')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label><br>
                                <input type="text" id="order_tags" name="other_details[order_tags]" class="form-control" value="{{ old('other_details.order_tags') }}"  placeholder="COD, COD Confirmed etc.">
                                @error('other_details.order_tags')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="pullUpdatedOrders" class="form-label">{{__('message.shopbase_create.pull_updated_order')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="pull_updated_active">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="other_details[pull_update_orders]"  {{ old('pull_update_orders', '1') == '1' ? 'checked' : '' }}  id="pull_updated_active">
                                            <span class="form-check-label">{{__('message.yes')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="pull_updated_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="other_details[pull_update_orders]" required {{ old('pull_update_orders') == '0' ? 'checked' : '' }} id="pull_updated_inactive">
                                            <span class="form-check-label">{{__('message.no')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('other_details.pull_update_orders')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <span class="invalid-feedback">{{__('message.shopbase_create.your_pull_updated_orders')}}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="statuses" class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', '1') == '1' ? 'checked' : '' }} id="status">
                                            <span class="form-check-label">{{__('message.active')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="status_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status" {{ old('status') == '0' ? 'checked' : '' }} id="status_inactive">
                                            <span class="form-check-label">{{__('message.inactive')}}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">{{__('message.error_status')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{__('message.save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>

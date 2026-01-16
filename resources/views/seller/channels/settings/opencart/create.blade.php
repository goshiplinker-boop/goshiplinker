<x-layout>
    <x-slot name="title">Edit</x-slot>
    <x-slot name="breadcrumbs"></x-slot>
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
                    <form class="needs-validation" action="{{ route('opencart.store') }}"
                        id="opencart-form" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <!-- This is important for the update request -->
                        <input type="hidden" name="channel_code" value="{{ old('channel_code', 'opencart') }}">

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">Channel Name</label>
                                    <input type="text" name="channel_title" class="form-control" value="{{ old('channel_title') }}" placeholder="Enter Channel Name" required>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">Channel URL</label>
                                    <input type="text" name="channel_url" class="form-control" value="{{ old('channel_url') }}" placeholder="https://yourstore.com" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" name="client_id" class="form-control" value="{{ old('client_id') }}" placeholder="Enter Client ID" required>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label">Client Secret Key</label>
                                    <input type="text" name="secret_key" class="form-control" value="{{ old('secret_key') }}" placeholder="Enter Secret Key" required>
                                </div>
                            </div>
                            <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Fetch Status</label>
                                <input type="text" name="other_details[fetch_status]" class="form-control" value="{{ old('other_details.fetch_status', 'pending,processing') }}" placeholder="pending,processing">
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label">Order Tags (optional)</label>
                                <input type="text" name="other_details[order_tags]" class="form-control" value="{{ old('other_details.order_tags') }}" placeholder="COD, Express, etc.">
                            </div>
                        </div>
                        <div class="row">
                            <!-- Status Field -->
                            <div class="col-sm-6 mb-3">
                                <label for="statuses" class="form-label">{{ __('message.status') }}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status" {{ old('status', '1') == '1' ? 'checked' : '' }} id="status">
                                            <span class="form-check-label">{{ __('message.active') }}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="status_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status" {{ old('status') == '0' ? 'checked' : '' }} id="status_inactive">
                                            <span class="form-check-label">{{ __('message.inactive') }}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">{{ __('message.error_status') }}</div>
                            </div>

                            <!-- Brand Logo Field -->
                            <div class="col-sm-6 mb-3">
                                <label for="brand_logo" class="form-label">
                                    {{ __('message.woocommerce.logo') }}
                                    <span class="form-label-secondary">({{ __('message.optional') }})</span>
                                </label>
                                <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                @error('brand_logo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
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
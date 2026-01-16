<x-layout>
    <x-slot name="title">{{ __('message.custom.heading_title') }}</x-slot>
    <x-slot name="page_header_title">{{ __('message.custom.heading_title') }}</x-slot>
    <x-slot name="breadcrumbs">{{ Breadcrumbs::render('custom.create') }}</x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.custom.page_header_title')}}</h1></x-slot>
    <x-slot name="main">
        <div class="row">
            <div class="col-sm-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="link">{{ __('message.custom.guidelines') }}</p>
                        {{ __('message.custom.guidelines_description') }}
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    <form id="custom-form" class="needs-validation" action="{{ route('custom.store') }}" method="POST"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="channel_code" value="custom">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="channel_title" class="form-label">{{ __('message.custom.channel_title') }}</label>
                                <input type="text" id="channel_title" name="channel_title" class="form-control" value="{{ old('channel_title') }}"placeholder="{{ __('message.custom.channel_title_placeholder') }}" required>
                                @error('channel_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{ __('message.custom.error_channel_tittle') }}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="brand_name" class="form-label">{{ __('message.custom.brand_name') }}</label>
                                <input type="text" id="brand_name" name="brand_name" class="form-control" value="{{ old('brand_name') }}" placeholder="{{ __('message.custom.brand_name_placeholder') }}" required>
                                @error('brand_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{ __('message.custom.error_brand_name') }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="brand_logo" class="form-label">{{ __('message.custom.logo') }}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                @error('brand_logo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="statuses" class="form-label">{{ __('message.status') }}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status"
                                                {{ old('status', '1') == '1' ? 'checked' : '' }} id="status" required>
                                            <span class="form-check-label">{{ __('message.active') }}</span>
                                        </span>
                                    </label>
                                    <label class="form-control">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status"
                                                {{ old('status') == '0' ? 'checked' : '' }} id="status_inactive"
                                                required>
                                            <span class="form-check-label">{{ __('message.inactive') }}</span>
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback"{{ __('message.error_status') }}></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('message.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
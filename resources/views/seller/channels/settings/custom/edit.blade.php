<x-layout>
    <x-slot name="title"> Edit </x-slot>
    <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('custom.edit', $custom) }} </x-slot>
    <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.custom.page_header_title')}}</h1</x-slot>
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
                        <p class="link">{{__('message.custom.guidelines')}}</p>
                        {{__('message.custom.guidelines_description')}}
                    </div>
                </div>
            </div>
            <div class="card col-sm-8">
                <div class="card-body">
                    <form class="needs-validation" action="{{ route('custom.update', $custom->channel_id) }}"
                        id="custom-form" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="channel_code" value="custom">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="channel_title" class="form-label">{{__('message.custom.channel_title')}}</label>
                                <input type="text" id="channel_title" name="channel_title" class="form-control" placeholder="{{__('message.custom.channel_title_placeholder')}}" value="{{ old('channel_title', $custom->channel_title) }}" required>
                                @error('channel_title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.custom.error_channel_title')}}</div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="brand_name" class="form-label">{{__('message.custom.brand_name')}}</label>
                                <input type="text" id="brand_name" name="brand_name" class="form-control"
                                    value="{{ old('brand_name', $custom->brand_name) }}"
                                    placeholder="{{__('message.custom.brand_name_placeholder')}}" required>
                                @error('brand_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="invalid-feedback">{{__('message.custom.error_brand_name')}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="brand_logo" class="form-label">{{__('message.custom.logo')}}
                                    <span class="form-label-secondary">({{__('message.optional')}})</span>
                                </label>
                                @if(!is_null($custom->brand_logo))
                                <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/logos/' . $custom->brand_logo) }}" class="img-fluid w-sm-25">
                                @endif
                                <input type="file" id="brand_logo" name="brand_logo" class="form-control" accept="image/*">
                                @error('brand_logo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="statuses" class="form-label">{{__('message.status')}}</label>
                                <div class="input-group input-group-sm-vertical">
                                    <label class="form-control" for="status">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="1" name="status"
                                                {{ old('status', $custom->status) == '1' ? 'checked' : '' }}
                                                id="status">
                                            <span class="form-check-label">{{__('message.active')}}</span>
                                        </span>
                                    </label>
                                    <label class="form-control" for="status_inactive">
                                        <span class="form-check">
                                            <input type="radio" class="form-check-input" value="0" name="status"
                                                {{ old('status', $custom->status) == '0' ? 'checked' : '' }}
                                                id="status_inactive">
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
                                <button type="submit" class="btn btn-primary btn-sm">{{__('message.update')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>
</x-layout>
<x-layout>
    <x-slot name="title">{{__('message.sms_list.tittle')}}</x-slot>
    <x-slot name="breadcrumbs"> Notifications</x-slot>
    <x-slot name="page_header_title">
      <h1 class="page-header-title">{{__('message.sms_list.tittle')}}</h1>
    </x-slot>    
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dltSettingModal"  onclick="openCreateModal()"><i class="bi bi-plus-circle me-1"></i>{{__('message.add_new')}}</a>
        </div>
        <div class="col-sm-auto">
            <div class="d-flex gap-2">
                <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
            </div>
        </div>
   </x-slot>
    <x-slot name="main">
        @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        {{-- DLT Settings Table --}}
        <div class="card overflow-hidden">
            <div class="table-responsive">  
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__('message.sms_list.company')}}</th>
                            <th>{{__('message.sms_list.header_ID')}}</th>
                            <th>{{__('message.sms_list.reg_ID')}}</th>
                            <th>{{__('message.sms_list.telecom_provider')}}</th>
                            <th>{{__('message.status')}}</th>
                            <th class = "text-end">{{__('message.action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $index => $setting)
                            <tr>
                                <td id="company_legal_name_{{$setting->id}}">{{ $setting->company_legal_name ?? 'N/A' }}</td>
                                <td id="header_id_{{$setting->id}}">{{ $setting->header_id }}</td>
                                <td id="header_registration_id_{{$setting->id}}">{{ $setting->header_registration_id }}</td>
                                <td id="telecom_provider_name_{{$setting->id}}">{{ $setting->telecom_provider_name }}</td>
                                <td id="status_{{$setting->id}}">
                                    <span class="badge bg-{{ $setting->status ? 'success' : 'secondary' }}">
                                        {{ $setting->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class = "text-end">
                                    <a class="btn btn-white btn-sm" onclick="editSetting({{ $setting->id }})"><i class="bi-pencil-fill me-1"></i>{{__('message.edit')}}</a>
                                    
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{__('message.sms_list.not_found')}}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="dltSettingModal" tabindex="-1" aria-labelledby="dltSettingModalLabel"               aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <form method="POST" action="{{ route('sms_store') }}" id="dlt-setting-form">
                    @csrf
                    <input type="hidden" name="id" id="setting-id">

                    <div class="modal-header">
                    <h5 class="modal-title" id="dltSettingModalLabel">{{__('message.sms_list.dlt_settings')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                    <div class="row g-3">                        
                        <div class="col-md-6">
                        <label class="form-label">{{__('message.sms_list.header_ID')}}</label>
                        <input type="text" class="form-control" name="header_id" id="header-id" required maxlength="15">
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">{{__('message.sms_list.reg_ID')}}</label>
                        <input type="number" class="form-control" name="header_registration_id" id="header-registration-id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{__('message.sms_list.telecom_provider_name')}}</label>
                            <div class="tom-select-custom">
                                <select class="js-select form-select" name="telecom_provider_name" id="telecom-provider-name" required
                                    data-hs-tom-select-options='{
                                        "placeholder": "Select Provider",
                                        "hideSearch": true
                                    }'>
                                    <option value="">Select Provider</option>
                                    <option value="Bharat">Bharat Sanchar Nigam Ltd</option>
                                    <option value="Mahanagar">Mahanagar Telephone Nigam Ltd.</option>
                                    <option value="Bharti">Bharti Airtel Limited</option>
                                    <option value="Quadrant">Quadrant Televentures Ltd.</option>
                                    <option value="Vodafone">Vodafone Idea Limited</option>
                                    <option value="Reliance">Reliance Communications Ltd.</option>
                                    <option value="Tata">Tata Teleservices Ltd.</option>
                                    <option value="RelianceJio">Reliance Jio Infocomm Limited</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <input type="hidden" name="telecom_provider_name" id="telecom-provider-hidden">
                            <input type="text" class="form-control mt-2 d-none" id="custom-provider" name="custom_telecom_provider_name" placeholder="Enter other provider">
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">{{__('message.sms_list.company_name')}}</label>
                        <input type="text" class="form-control" name="company_legal_name" id="company-legal-name" required>
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
                    </div>

                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{__('message.cancel')}}</button>
                    <button type="submit" class="btn btn-primary btn-sm">{{__('message.sms_list.save_setting')}}</button>
                    </div>

                </form>
                </div>
            </div>
        </div>
        <script>
            function openCreateModal() {
                // Reset form
                $('#dlt-setting-form').trigger("reset");
                $('#setting-id').val('');
                $('#custom-provider').addClass('d-none'); // hide custom input initially
                $('#custom-provider').val('');
                $('#dltSettingModalLabel').text('Add SMS Header');
            }
            function editSetting(id) {
                var company_legal_name = document.getElementById('company_legal_name_' + id).innerText;
                var header_id = document.getElementById('header_id_' + id).innerText;
                var header_registration_id = document.getElementById('header_registration_id_' + id).innerText;
                var telecom_provider_name = document.getElementById('telecom_provider_name_' + id).innerText;
                var statusText = document.getElementById('status_' + id).innerText.trim();
                document.getElementById('setting-id').value = id;
                document.getElementById('header-id').value = header_id;
                document.getElementById('header-registration-id').value = header_registration_id;
                document.getElementById('company-legal-name').value = company_legal_name;
                const telecomSelect = document.getElementById('telecom-provider-name');
                const customInput = document.getElementById('custom-provider');
                const selectOptions = Array.from(telecomSelect.options).map(o => o.value)
                if (selectOptions.includes(telecom_provider_name)) {
                    telecomSelect.value = telecom_provider_name;
                    customInput.classList.add('d-none');
                    customInput.required = false;
                    customInput.value = '';
                } else {
                    telecomSelect.value = 'Other';
                    customInput.classList.remove('d-none');
                    customInput.required = true;
                    customInput.value = telecom_provider_name;
                }
                
                // Map status text to value
                if (statusText === 'Active') {
                    document.getElementById('status').checked = true;
                } else {
                    document.getElementById('status_inactive').checked = true;
                }
                $('#dltSettingModalLabel').text('Edit DLT Setting');      
                var myModal = new bootstrap.Modal(document.getElementById('dltSettingModal'));
                myModal.show();           
            }

            function TelecomProviderHandlers() {
                const telecomSelect = document.getElementById('telecom-provider-name');
                const customInput = document.getElementById('custom-provider');
                const hiddenInput = document.getElementById('telecom-provider-hidden');
                const form = document.getElementById('dlt-setting-form');

                telecomSelect.addEventListener('change', function () {
                    if (this.value === 'Other') {
                        customInput.classList.remove('d-none');
                        customInput.required = true;
                    } else {
                        customInput.classList.add('d-none');
                        customInput.required = false;
                        customInput.value = '';
                    }
                });

                form.addEventListener('submit', function () {
                  
                    const selectedOption = telecomSelect.options[telecomSelect.selectedIndex];
                    hiddenInput.value = telecomSelect.value === 'Other'
                        ? customInput.value
                        : selectedOption.text.trim();
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                TelecomProviderHandlers();
            });
        </script>
    </x-slot>
</x-layout>


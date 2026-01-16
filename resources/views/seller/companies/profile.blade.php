<x-layout>
  <x-slot name="title"> {{__('message.company.title')}} </x-slot>
  <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('profile',$company) }}</x-slot>
  <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.company.page_header_title')}}</h1></x-slot>
  <x-slot name="main">
    @if(session('success'))
    <div class="alert alert-soft-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
     <div class="alert alert-soft-danger alert-dismissible" role="alert">
        @foreach ($errors->all() as $error)
          {!! $error !!}
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    <div class="content container-fluid">
      <form class="js-step-form" class="needs-validation" enctype="multipart/form-data" data-hs-step-form-options='{
              "progressSelector": "#addUserStepFormProgress",
              "stepsSelector": "#addUserStepFormContent",
              "endSelector": "#addUserFinishBtn",
              "isValidate": false
            }' action="{{ route('companies.update', $company->id) }}" method="POST" name="profileForm" novalidate>
        @csrf
        @method('PUT')
        <div class="row justify-content-lg-center">
          <div class="col-lg-8">
            <ul id="addUserStepFormProgress" class="js-step-progress step step-sm step-icon-sm step step-inline step-item-between mb-3 mb-md-5">
              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector": "#addUserStepProfile"}'>
                  <span class="step-icon step-icon-soft-dark">{{__('message.company.span1')}}</span>
                  <div class="step-content">
                    <span class="step-title">{{__('message.company.profile')}}</span>
                  </div>
                </a>
              </li>
              <li class="step-item @if(is_null($company->state_code) || $company->state_code == '') active focus @endif ">
                <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector": "#addUserStepCompanyDetails"}'>
                  <span class="step-icon step-icon-soft-dark">{{__('message.company.span2')}}</span>
                  <div class="step-content">
                    <span class="step-title">{{__('message.company.company_details')}}</span>
                  </div>
                </a>
              </li>
              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector": "#addUserStepCompanyKycDetails"}'>
                  <span class="step-icon step-icon-soft-dark">{{__('message.company.span3')}}</span>
                  <div class="step-content">
                    <span class="step-title">Company KYC</span>
                  </div>
                </a>
              </li>
              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector": "#addUserStepCompanyBankDetails"}'>
                  <span class="step-icon step-icon-soft-dark">4</span>
                  <div class="step-content">
                    <span class="step-title">Bank Details</span>
                  </div>
                </a>
              </li>
              <li class="step-item">
                <a class="step-content-wrapper" href="javascript:;" data-hs-step-form-next-options='{"targetSelector": "#otherDetailsStepConfirmation"}'>
                  <span class="step-icon step-icon-soft-dark">5</span>
                  <div class="step-content">
                    <span class="step-title">{{__('message.company.other_details')}}</span>
                  </div>
                </a>
              </li>
            </ul>
            <div id="addUserStepFormContent">
              <div id="addUserStepProfile" class="card card-lg @if(!is_null($company->state_code) && $company->state_code != '') active @endif" @if(is_null($company->state_code) || $company->state_code == '') style="display: none;" @endif>
                <div class="card-body">
                  <div class="row mb-4">
                    <label for="firstNameLabel" class="col-sm-3 col-form-label form-label">{{__('message.profile.full_name')}}</label>
                    <div class="col-sm-9">
                      <div class="input-group input-group-sm-vertical">
                        <input type="text" class="form-control" name="fullname" readonly id="firstNameLabel" placeholder="{{__('message.profile.full_name_placeholder')}}" value="{{$company->name}}" aria-label="Clarice">
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="emailLabel" class="col-sm-3 col-form-label form-label">{{__('message.profile.email')}}</label>
                    <div class="col-sm-9">
                      <input type="email" class="form-control" readonly name="company_email_id" id="emailLabel" placeholder="clarice@site.com" value="{{$company->company_email_id}}" aria-label="clarice@site.com">
                      @error('company_email_id')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.profile.error_email')}}</div>
                    </div>
                  </div>
                  <div class="js-add-field row mb-4" data-hs-add-field-options='{
                          "template": "#addPhoneFieldTemplate",
                          "container": "#addPhoneFieldContainer",
                          "defaultCreated": 0
                        }'>
                    <label for="phoneLabel" class="col-sm-3 col-form-label form-label">{{__('message.profile.phone')}} <span class="form-label-secondary"></span></label>
                    <div class="col-sm-9">
                      <div class="input-group input-group-sm-vertical">
                        <input type="text" class="js-input-mask form-control" name="phone_number" id="phoneLabel" readonly placeholder="xxxxxxxxxx" value="{{$company->phone_number}}" aria-label="xxxxxxxxxx">
                        @error('phone_number')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.profile.error_phone')}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer d-flex justify-content-end align-items-center">
                  <button type="button" class="btn btn-primary btn-sm" data-hs-step-form-next-options='{
                            "targetSelector": "#addUserStepCompanyDetails"
                          }'>
                    {{__('message.next')}} <i class="bi-chevron-right"></i>
                  </button>
                </div>
              </div>
              <div id="addUserStepCompanyDetails" class="card card-lg @if(is_null($company->state_code) || $company->state_code == '') active @endif" @if(!is_null($company->state_code) && $company->state_code != '') style="display:none" @endif>
                <div class="card-body">
                  <div class="row mb-4">
                    <label class="col-sm-3 col-form-label form-label">{{__('message.company_details.logo')}}</label>
                    <div class="col-sm-9">
                          @if(isset($company->brand_logo) && !empty($company->brand_logo))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/logo/'.$company->brand_logo)}}">
                          @endif
                        <input type="file" class="form-control" name="brand_logo" accept="image/*" >
                        @error('brand_logo')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.company_details.error_logo')}}</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="legalRegisteredName" class="col-sm-3 col-form-label form-label">{{__('message.company_details.company_name')}}</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="legal_registered_name" id="legalRegisteredName" placeholder="{{__('message.company_details.company_name_placeholder')}}" value="{{ old('legal_registered_name', $company->legal_registered_name) }}" aria-label="Your Registered Company Name" required>
                      @error('legal_registered_name')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_Registered')}}.</div>
                    </div>
                  </div>                
                  <div class="row mb-4">
                    <label for="brandNameLable" class="col-sm-3 col-form-label form-label">{{__('message.company_details.brand_name')}}</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="brand_name" id="brandNameLable" placeholder="{{__('message.company_details.brand_name_placeholder')}}" value="{{ old('brand_name', $company->brand_name) }}" aria-label="Your Brand Name" required>
                      @error('brand_name')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_brand')}}</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="websiteUrl" class="col-sm-3 col-form-label form-label">{{__('message.company_details.website_url')}}</label>
                    <div class="col-sm-9">
                      <input type="url" class="js-input-mask form-control" name="website_url" id="websiteUrl" placeholder="{{__('message.company_details.website_url')}}" value="{{ old('website_url', $company->website_url) }}" aria-label="Your Website Url" required>
                      @error('website_url')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_website_url')}}</div>
                    </div>
                  </div>                  
                  <div class="row mb-4">
                    <label for="addressLine1Label" class="col-sm-3 col-form-label form-label">{{__('message.company_details.company_address')}}</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="address" id="addressLine1Label" placeholder="{{__('message.company_details.company_address_placeholder')}}" value=" {{ old('address', $company->address) }}" aria-label="Your Complete Address" required>
                      @error('address')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_address')}}</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="zipCodeLabel" class="col-sm-3 col-form-label form-label">{{__('message.company_details.pincode')}} <i class="bi-question-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="You can find your code in a postal address."></i></label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="pincode" id="zipCodeLabel" placeholder="{{__('message.company_details.pincode_placeholder')}}" value="{{ old('pincode', $company->pincode) }}" aria-label="Your Pincode" required>
                      @error('pincode')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_pincode')}}</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="cityLabel" class="col-sm-3 col-form-label form-label">{{__('message.company_details.city')}}</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="city" id="cityLabel" placeholder="{{__('message.company_details.city_placeholder')}}" value="{{ old('city', $company->city) }}" required aria-label="City">
                      @error('city')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_city')}}</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="locationLabel" class="col-sm-3 col-form-label form-label">{{__('message.company_details.country')}}</label>
                    <div class="col-sm-9">
                      <div class="tom-select-custom">
                        <select class="form-select" name="country_code" id="country" onchange="fetchStates(this.value,$company->state_code)" required>
                          <option value="">Select Country</option>
                          @foreach($countries as $country)
                          <option value="{{$country->country_code}}" {{ old('country_code', $company->country_code) == $country->country_code ? 'selected' : '' }}
                              data-option-template='<span class="d-flex align-items-center">
                              <img class="avatar avatar-xss avatar-circle me-2" 
                              src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/' . strtolower($country->country_code) . '.svg') }}" 
                              alt="{{$country->country_name}} Flag" />
                              <span class="text-truncate">{{$country->country_name}}</span>
                            </span>'>
                            {{$country->country_name}}
                          </option>
                          @endforeach
                        </select>
                        @error('country_code')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.company_details.error_country')}}</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="state" class="col-sm-3 col-form-label form-label">{{__('message.company_details.state')}}</label>
                    <div class="col-sm-9">
                      <div class="tom-select-custom mb-4">
                        <select class="form-select" id="state" name="state_code" required>
                          <option value="">{{__('message.company_details.state_placeholder')}}</option>
                          <!-- Assume state options will be loaded via JS based on the selected country -->
                        </select>
                        @error('state_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <div class="invalid-feedback">{{__('message.company_details.error_state')}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                  <button type="button" class="btn btn-ghost-secondary" data-hs-step-form-prev-options='{"targetSelector": "#addUserStepProfile"}'>
                    <i class="bi-chevron-left"></i>{{__('message.previous')}}
                  </button>
                  <div class="ms-auto">
                    <button type="button" class="btn btn-primary btn-sm" data-hs-step-form-next-options='{ "targetSelector": "#addUserStepCompanyKycDetails"}'>
                      {{__('message.next')}} <i class="bi-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div id="addUserStepCompanyKycDetails" >
                <div class="card-body">                  
                  <div class="row mb-4">
                    <label for="companyGstinLable" class="col-sm-3 col-form-label form-label">PAN Number</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="pan_number" id="company_pannumber" placeholder="PAN Number" value="{{ old('pan_number', $company->pan_number) }}" aria-label="Your Company Pan Number" required>
                      @error('pan_number')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_pan_number')}}</div>
                    </div>
                  </div>  
                  <div class="row mb-4">
                      <label class="col-sm-3 col-form-label form-label">Upload PAN Card</label>
                      <div class="col-sm-9">
                          @if(isset($company->pan_image) && !empty($company->pan_image))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/pan_docs/'.$company->pan_image)}}">
                          @endif
                          <input type="file" class="form-control" name="pan_image" accept="image/*">
                      </div>
                  </div>
                  <div class="row mb-4">
                    <label for="company_type" class="col-sm-3 col-form-label form-label">Seller Type</label>
                    <div class="col-sm-9">
                      <div class="tom-select-custom">
                        <select class="form-select" name="company_type" id="company_type" required>
                            <option value="">Seller Type{{$selectedType}}</option>
                            @foreach($company_types as $type => $items)
                                <option value="{{ $type }}" @if($type==$selectedType) selected @endif>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('company_type')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.company_type_error')}}.</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4" id="company_sub_type_div" >
                    <label for="company_sub_type" class="col-sm-3 col-form-label form-label">Seller Sub Type</label>
                    <div class="col-sm-9">
                      <div class="tom-select-custom">
                        <select class="form-select" name="company_type_id" id="company_subtype">
                            <option value="">Select Sub Type</option>
                        </select>
                        @error('company_type_id')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.company_type_id_error')}}.</div>
                      </div>
                    </div>
                  </div>  
                  <!-- INDIVIDUAL DOCUMENTS -->                   
                    <div class="row mb-4 d-none" id="document_individual">
                       <label class="col-sm-3 col-form-label form-label">Aadhaar Number</label>
                      <div class="col-sm-9 mt-3">
                          <input type="text" class="form-control" name="aadhaar_number" required value="{{ old('aadhaar_number', $company->doc_urls['aadhaar']['aadhaar_number']??'') }}">
                      </div>
                      <label class="col-sm-3 col-form-label form-label">Aadhaar Front</label>
                      <div class="col-sm-9 mt-3">
                          @if(isset($company->doc_urls['aadhaar']['aadhaar_front']) && !empty($company->doc_urls['aadhaar']['aadhaar_front']))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/aadhaar/'.$company->doc_urls['aadhaar']['aadhaar_front'])}}">
                          @endif
                          <input type="file" class="form-control" name="aadhaar_front" accept="image/*" >
                      </div>

                      <label class="col-sm-3 col-form-label form-label mt-3">Aadhaar Back</label>
                      <div class="col-sm-9 mt-3">
                          @if(isset($company->doc_urls['aadhaar']['aadhaar_back']) && !empty($company->doc_urls['aadhaar']['aadhaar_back']))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/aadhaar/'.$company->doc_urls['aadhaar']['aadhaar_back'])}}">
                          @endif
                          <input type="file" class="form-control" name="aadhaar_back" accept="image/*" >
                      </div>
                    </div>

                    <!-- SOLE PROPRIETOR DOCUMENTS -->                     
                    <div class="row mb-4 d-none" id="document_sole">
                      <label class="col-sm-3 col-form-label form-label">Udyam Number</label>
                      <div class="col-sm-9 mt-3">
                          <input type="text" class="form-control" name="udyam_no" value="{{ old('udyam_no', $company->doc_urls['udyam']['udyam_no']??'') }}" >
                      </div>
                      <label class="col-sm-3 col-form-label form-label">Upload Udyam Certificate</label>
                      <div class="col-sm-9 mt-3">
                          @if(isset($company->doc_urls['udyam']['udyam_cert']) && !empty($company->doc_urls['udyam']['udyam_cert']))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/udyam/'.$company->doc_urls['udyam']['udyam_cert'])}}">
                          @endif
                          <input type="file" class="form-control" name="udyam_cert" accept="image/*,.pdf">
                      </div>
                      <label class="col-sm-3 col-form-label form-label">GSTIN Number</label>
                      <div class="col-sm-9 mt-3">
                          <input type="text" class="form-control" name="gstin_no"  value="{{ old('gstin_no', $company->doc_urls['gstin']['gstin_no']??'') }}">
                      </div>
                      <label class="col-sm-3 col-form-label form-label mt-3">Upload GSTIN Certificate</label>
                      <div class="col-sm-9 mt-3">
                          @if(isset($company->doc_urls['gstin']['gstin_cert']) && !empty($company->doc_urls['gstin']['gstin_cert']))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/gstin_cert/'.$company->doc_urls['gstin']['gstin_cert'])}}">
                          @endif
                          <input type="file" class="form-control" name="gstin_cert" accept="image/*,.pdf">
                      </div>
                    </div>

                    <!-- COMPANY DOCUMENTS -->
                    <div class="row mb-4 d-none" id="document_company">
                      <label class="col-sm-3 col-form-label form-label">GSTIN Number</label>
                      <div class="col-sm-9 mt-3">
                          <input type="text" class="form-control" name="gstin_no" value="{{ old('gstin_no', $company->doc_urls['gstin']['gstin_no']??'') }}" >
                      </div>
                      <label class="col-sm-3 col-form-label form-label">GSTIN Certificate</label>
                      <div class="col-sm-9 mt-3">
                          @if(isset($company->doc_urls['gstin']['gstin_cert']) && !empty($company->doc_urls['gstin']['gstin_cert']))
                            <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/gstin_cert/'.$company->doc_urls['gstin']['gstin_cert'])}}">
                          @endif
                          <input type="file" class="form-control" name="gstin_cert" accept="image/*,.pdf">
                      </div>
                    </div>
               
                </div>
                <div class="card-footer d-flex align-items-center">
                  <button type="button" class="btn btn-ghost-secondary" data-hs-step-form-prev-options='{"targetSelector": "#addUserStepCompanyDetails"}'>
                    <i class="bi-chevron-left"></i>{{__('message.previous')}}
                  </button>
                  <div class="ms-auto">
                    <button type="button" class="btn btn-primary btn-sm" data-hs-step-form-next-options='{ "targetSelector": "#addUserStepCompanyBankDetails"}'>
                      {{__('message.next')}} <i class="bi-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div id="addUserStepCompanyBankDetails" class="card card-lg" style="display: none;">
                <div class="card-body">
                  <div class="row mb-4">
                    <label class="col-sm-3 col-form-label form-label">Upload Cancelled Check Photo</label>
                    <div class="col-sm-9">
                      @if(isset($company->bank_details['cancelled_check']) && !empty($company->bank_details['cancelled_check']))
                        <img width="30" height="auto" src="{{ asset(env('PUBLIC_ASSETS') . '/images/companies/cancelled_checks/'.$company->bank_details['cancelled_check'])}}">
                      @endif
                      <input type="file" class="form-control" name="bank_details[cancelled_check]" accept="image/*">
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="bank_name" class="col-sm-3 col-form-label form-label">Bank Account Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="bank_details[bank_name]" id="bank_name" placeholder="Enter Bank Name" value="{{ old('bank_name', $company->bank_details['bank_name']??'') }}" aria-label="Your Registered Company Bank Name" required>
                      @error('bank_name')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_bank_name')}}.</div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="account_number" class="col-sm-3 col-form-label form-label">Bank Account Number</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="bank_details[account_number]" id="account_number" placeholder="Enter Bank Account Number" value="{{ old('account_number', $company->bank_details['account_number']??'') }}" aria-label="Your Company Account Number" required>
                      @error('account_number')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_account_number')}}</div>
                    </div>
                  </div>                 
                  <div class="row mb-4">
                    <label for="account_holder_name" class="col-sm-3 col-form-label form-label">Account Holder Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="js-input-mask form-control" name="bank_details[account_holder_name]" id="account_holder_name" placeholder="Enter Account Holder Name" value="{{ old('account_holder_name', $company->bank_details['account_holder_name']??'') }}" aria-label="Your Bank Hoder Name" required>
                      @error('account_holder_name')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_account_holder_name')}}</div>
                    </div>
                  </div>                 
                  <div class="row mb-4">
                    <label for="ifsc_code" class="col-sm-3 col-form-label form-label">Bank IFSC Code</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="bank_details[ifsc_code]" id="ifsc_code" placeholder="Enter IFSC Code" value="{{ old('ifsc_code', $company->bank_details['ifsc_code']??'') }}" aria-label="Your Bank IFSC Code" required>
                      @error('ifsc_code')
                      <div class="text-danger">{{ $message }}</div>
                      @enderror
                      <div class="invalid-feedback">{{__('message.company_details.error_ifsc_code')}}</div>
                    </div>
                  </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                  <button type="button" class="btn btn-ghost-secondary" data-hs-step-form-prev-options='{"targetSelector": "#addUserStepCompanyKycDetails"}'>
                    <i class="bi-chevron-left"></i>{{__('message.previous')}}
                  </button>
                  <div class="ms-auto">
                    <button type="button" class="btn btn-primary btn-sm" data-hs-step-form-next-options='{ "targetSelector": "#otherDetailsStepConfirmation"}'>
                      {{__('message.next')}} <i class="bi-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div id="otherDetailsStepConfirmation" class="card card-lg" style="display: none;">
                <div class="card-body">
                  <div class="row mb-4">
                    <label for="weightLabel" class="col-sm-5 col-form-label form-label">{{__('message.other_details.weight_range')}}</label>
                    <div class="col-sm-7">
                      <div class="tom-select-custom">
                        <select class="form-select" name="shipment_weight" id="weightLabel" required>
                          <option value="">{{__('message.other_details.weight_range_placeholder')}}</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0 - 500 Grams' ? 'selected' : '' }} value="0 - 500 Grams">0 - 500 Grams</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0.5 - 1 Kg' ? 'selected' : '' }} value="0.5 - 1 Kg">0.5 - 1 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0.5 - 2 Kg' ? 'selected' : '' }} value="0.5 - 2 Kg">0.5 - 2 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0.5 - 5 Kg' ? 'selected' : '' }} value="0.5 - 5 Kg">0.5 - 5 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0.5 - 10 Kg' ? 'selected' : '' }} value="0.5 - 10 Kg">0.5 - 10 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '0.5 - 20 Kg' ? 'selected' : '' }} value="0.5 - 20 Kg">0.5 - 20 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '20 - 50 Kg' ? 'selected' : '' }} value="20 - 50 Kg">20 - 50 Kg</option>
                          <option {{ old('shipment_weight', $company->shipment_weight) == '50 - 100 Kg' ? 'selected' : '' }} value="50 - 100 Kg">50 - 100 Kg</option>
                        </select>
                        @error('shipment_weight')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.shipment_weight')}}.</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="channelLabel" class="col-sm-5 col-form-label form-label">{{__('message.other_details.channel')}}</label>
                    <div class="col-sm-7">
                      <div class="tom-select-custom">
                        <select class="form-select" id="channelLabel" placeholder="{{__('message.other_details.chennel_placeholder')}}" name="channel_name" aria-label="Select Channel" required>
                          <option value="">Select Channel</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Shopify' ? 'selected' : '' }} value="Shopify">Shopify</option>
                          <option {{ old('channel_name', $company->channel_name) == 'WooCommerce' ? 'selected' : '' }} value="WooCommerce">WooCommerce</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Opencart' ? 'selected' : '' }} value="Opencart">Opencart</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Magento' ? 'selected' : '' }} value="Magento">Magento</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Wix Commerce' ? 'selected' : '' }} value="Wix Commerce">Wix Commerce</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Amazon' ? 'selected' : '' }} value="amazon">Amazon</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Flipkart' ? 'selected' : '' }} value="Flipkart">Flipkart</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Big Commerce' ? 'selected' : '' }} value="Big Commerce">Big Commerce</option>
                          <option {{ old('channel_name', $company->channel_name) == 'Custom Website' ? 'selected' : '' }} value="Custom Website">Custom Website</option>
                        </select>
                        @error('channel_name')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.error_channel')}}</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="courierLabel" class="col-sm-5 col-form-label form-label">{{__('message.other_details.couriers')}}</label>
                    <div class="col-sm-7">
                      <div class="tom-select-custom">
                        <select class="form-select" name="courier_using" id="courierLabel" required>
                          <option value="">{{__('message.other_details.couriers_placeholder')}}</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Bluedart' ? 'selected' : '' }} value="Bluedart">Bluedart</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Delhivery' ? 'selected' : '' }} value="Delhivery">Delhivery</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Ecom Express' ? 'selected' : '' }} value="Ecom Express">Ecom Express</option>
                          <option {{ old('courier_using', $company->courier_using) == 'India Post' ? 'selected' : '' }} value="India Post">India Post</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Parcelled' ? 'selected' : '' }} value="Parcelled">Parcelmind</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Shadowfax' ? 'selected' : '' }} value="Shadowfax">Shadowfax</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Flipkart' ? 'selected' : '' }} value="Flipkart">Flipkart</option>
                          <option {{ old('courier_using', $company->courier_using) == 'The Professional Courier' ? 'selected' : '' }} value="The Professional Courier">The Professional Courier</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Ekart' ? 'selected' : '' }} value="Ekart">Ekart</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Smartr Logistics' ? 'selected' : '' }} value="Smartr Logistics">Smartr Logistics</option>
                          <option {{ old('courier_using', $company->courier_using) == 'Others' ? 'selected' : '' }} value="Others">Other</option>
                        </select>
                        @error('courier_using')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.error_courier')}}</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="productCategoryLabel" class="col-sm-5 col-form-label form-label">{{__('message.other_details.category')}}</label>
                    <div class="col-sm-7">
                      <div class="tom-select-custom">
                        <select class="form-select" name="product_category" id="productCategoryLabel" required>
                          <option value="">{{__('message.other_details.category_placeholder')}}</option>
                          <option {{ old('product_category', $company->product_category) == 'Animals & Pet Supplies' ? 'selected' : '' }} value="Animals & Pet Supplies">Animals & Pet Supplies</option>
                          <option {{ old('product_category', $company->product_category) == 'Apparel & Accessories' ? 'selected' : '' }} value="Apparel & Accessories">Apparel & Accessories</option>
                          <option {{ old('product_category', $company->product_category) == 'Arts & Entertainment' ? 'selected' : '' }} value="Arts & Entertainment">Arts & Entertainment</option>
                          <option {{ old('product_category', $company->product_category) == 'Baby & Toddler' ? 'selected' : '' }} value="Baby & Toddler">Baby & Toddler</option>
                          <option {{ old('product_category', $company->product_category) == 'Business & Industrial' ? 'selected' : '' }} value="Business & Industrial">Business & Industrial</option>
                          <option {{ old('product_category', $company->product_category) == 'Cameras & Optics' ? 'selected' : '' }} value="Cameras & Optics">Cameras & Optics</option>
                          <option {{ old('product_category', $company->product_category) == 'Electronics' ? 'selected' : '' }} value="Electronics">Electronics</option>
                          <option {{ old('product_category', $company->product_category) == 'Food' ? 'selected' : '' }} value="Food">Food</option>
                          <option {{ old('product_category', $company->product_category) == 'Beverages & Tobacco' ? 'selected' : '' }} value="Beverages & Tobacco">Beverages & Tobacco</option>
                          <option {{ old('product_category', $company->product_category) == 'Furniture' ? 'selected' : '' }} value="Furniture">Furniture</option>
                          <option {{ old('product_category', $company->product_category) == 'Gifts & Stationary' ? 'selected' : '' }} value="Gifts & Stationary">Gifts & Stationary</option>
                          <option {{ old('product_category', $company->product_category) == 'Hardware' ? 'selected' : '' }} value="Hardware">Hardware</option>
                          <option {{ old('product_category', $company->product_category) == 'Others' ? 'selected' : '' }} value="Others">Others</option>
                        </select>
                        @error('product_category')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.error_product_category')}}</div>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <label for="monthlyOrderLabel" class="col-sm-5 col-form-label form-label">{{__('message.other_details.monthly_order')}}</label>
                    <div class="col-sm-7">
                      <div class="tom-select-custom">
                        <select class="form-select" name="monthly_orders" id="monthlyOrderLabel" required>
                          <option value="">{{__('message.other_details.monthly_order_placeholder')}}</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == '0-100' ? 'selected' : '' }} value="0-100">0-100</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == "100-500" ? 'selected' : '' }} value="100-500">100-500</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == '500-1000' ? 'selected' : '' }} value="500-1000">500-1000</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == '1000-3000' ? 'selected' : '' }} value="1000-3000">1000-3000</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == '3000-5000' ? 'selected' : '' }} value="3000-5000">3000-5000</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == '5000-10000' ? 'selected' : '' }} value="5000-10000">5000-10000</option>
                          <option {{ old('monthly_orders', $company->monthly_orders) == 'more than 10000' ? 'selected' : '' }} value="more than 10000">more than 10000</option>
                        </select>
                        @error('monthly_orders')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">{{__('message.other_details.error_monthly_orders')}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer d-sm-flex align-items-sm-center">
                  <button type="button" class="btn btn-ghost-secondary mb-2 mb-sm-0" data-hs-step-form-prev-options='{"targetSelector": "#addUserStepCompanyBankDetails"}'>
                    <i class="bi-chevron-left"></i>{{__('message.previous')}}
                  </button>
                  <div class="ms-auto">
                    <input id="" type="submit" class="btn btn-primary btn-sm" value="{{__('message.update')}}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <script>
        const routes = {
                states:  "{{ route('states', ['country_code' => ':country_code']) }}"
            }
        
      // Call fetchStates when the page loads
      window.addEventListener('DOMContentLoaded', (event) => {
        let countrySelect = document.getElementById('country'); // Get the country select element
        let selectedCountryCode = countrySelect.value; // Get the selected country code
        let selectedStateCode = "{{ ($company->state_code) }}"; // Get the state code from the database
        if (selectedCountryCode) {
          fetchStates(selectedCountryCode, selectedStateCode); // Fetch states for the selected country and pre-select the state
        }
        // Fetch new states when the country changes
        countrySelect.addEventListener('change', (event) => {
          fetchStates(event.target.value); // Fetch states when the country changes
        });
      });
    </script>
    <script>
      let companyData = @json($company_types);
      let selectedType = "{{ $selectedType ?? '' }}";
      let selectedSubtype = "{{ $selectedSubtype ?? '' }}";
       let company_type_id = "{{ $company->company_type_id ?? '' }}";
     
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const typeSelect = document.getElementById("company_type");
        const subtypeSelect = document.getElementById("company_subtype");
        const subtypeDiv = document.getElementById("company_sub_type_div");

        // Document upload sections
        const docIndividual = document.getElementById("document_individual");
        const docSole = document.getElementById("document_sole");
        const docCompany = document.getElementById("document_company");

        function handleDocumentDisplay(selectedTypeValue) {
            // Hide all first
            docIndividual.classList.add("d-none");
            docSole.classList.add("d-none");
            docCompany.classList.add("d-none");

            // Show based on type
            if (selectedTypeValue === "Individual") {
                docIndividual.classList.remove("d-none");
            }
            else if (selectedTypeValue === "Sole Proprietor") {
                docSole.classList.remove("d-none");
            }
            else if (selectedTypeValue === "Company") {
                docCompany.classList.remove("d-none");
            }
        }

        // When type changes
        typeSelect.addEventListener("change", function () {
            let selectedTypeValue = this.value;

            // Handle document sections
            handleDocumentDisplay(selectedTypeValue);

            // Handle subtype visibility
            subtypeDiv.classList.toggle('d-none', selectedTypeValue === 'Sole Proprietor');

            // Clear existing options
            subtypeSelect.innerHTML = '<option value="">Select Sub Type</option>';

            // If no subtype array available
            if (!selectedTypeValue || !companyData[selectedTypeValue] || companyData[selectedTypeValue].length === 0) {
                subtypeSelect.disabled = true;
                return;
            }

            subtypeSelect.disabled = false;
            // Load subtype options
            companyData[selectedTypeValue].forEach(item => {
                if (!item.subtype || item.subtype.trim() === "") return;

                let option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.subtype;
                if(item.subtype===selectedSubtype){
                  option.selected = true;

                }
                subtypeSelect.appendChild(option);
            });

            // Auto-select subtype during edit
            if (selectedSubtype) {
              subtypeSelect.value = company_type_id;
            }
        });


        // --- EDIT MODE AUTO SELECT ---
        if (selectedType) {
            typeSelect.value = selectedType;

            // Show correct document section on edit
            handleDocumentDisplay(selectedType);

            // Trigger subtype load
            typeSelect.dispatchEvent(new Event("change"));

            // Select subtype after load
            setTimeout(() => {
                if (selectedSubtype) {
                    subtypeSelect.value = company_type_id;
                }
            }, 200);
        }

    });
    </script>
  </x-slot>
</x-layout>
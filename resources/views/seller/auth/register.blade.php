<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Registration</title>
      <link rel="shortcut icon" href="./favicon.ico">
      <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
      <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/tom-select/dist/css/tom-select.bootstrap5.css') }}">
      <link rel="preload" href="{{ asset(env('PUBLIC_ASSETS') . '/css/theme.min.css') }}" data-hs-appearance="default" as="style">
      <link rel="stylesheet" href="{{ asset('assets/css/custom_website.css') }}">
      <script>
         window.hs_config = {
             "themeAppearance": {
                 "layoutSkin": "default"
             }
         }
      </script>
   </head>
   <body class="d-flex align-items-center min-h-100">
      <!-- ========== MAIN CONTENT ========== -->
      <main id="content" role="main" class="main pt-0">
         <!-- Content -->
         <div class="container-fluid px-3">
            <div class="row">
               <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center min-vh-lg-100 position-relative bg-light px-0">
                  <div class="position-absolute top-0 start-0 end-0 mt-3 mx-3">
                     <div class="d-none d-lg-flex justify-content-between">
                        <a href="{{ route('welcome') }}">
                        <img class="zi-2" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}" alt="Image Description" style="width: 8rem;">
                        </a>
                     </div>
                  </div>
                  <div style="max-width: 23rem;">
                     <div class="text-center mb-5">
                        <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-chatting.svg') }}" alt="Image Description" style="width: 12rem;" data-hs-theme-appearance="default">
                        <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-chatting.svg') }}" alt="Image Description" style="width: 12rem;" data-hs-theme-appearance="dark">
                     </div>
                     <div class="mb-5">
                        <h2 class="display-5">{{__('message.register_discription.heading')}}</h2>
                     </div>
                     <ul class="list-checked list-checked-lg list-checked-primary list-py-2">
                        <li class="list-checked-item">
                           <span class="d-block fw-semibold mb-1">{{__('message.register_discription.sub_heading1')}}</span>
                           {{__('message.register_discription.sub_heading1_dis')}}
                        </li>
                        <li class="list-checked-item">
                           <span class="d-block fw-semibold mb-1">{{__('message.register_discription.sub_heading2')}}</span>
                           {{__('message.register_discription.sub_heading2_dis')}}
                        </li>
                     </ul>
                     <div class="row justify-content-between mt-5 gx-3">
                        <div class="col">
                           <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/gitlab-gray.svg') }}" alt="Logo">
                        </div>
                        <div class="col">
                           <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/fitbit-gray.svg') }}" alt="Logo">
                        </div>
                        <div class="col">
                           <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/flow-xo-gray.svg') }}" alt="Logo">
                        </div>
                        <div class="col">
                           <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/brands/layar-gray.svg') }}" alt="Logo">
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-6 d-flex justify-content-center align-items-center min-vh-lg-100">
                  <div class="w-100 content-space-t-lg-1 content-space-b-1" style="max-width: 25rem;">
                     <form class="js-validate needs-validation" novalidate method="POST" action="{{ route('register',request()->query()) }}">
                        @csrf
                        <div class="text-center">
                           <div class="mb-5">
                              <h1 class="display-5">{{__('message.register.heading_title')}}</h1>
                              <p>{{__('message.register.sub_heading')}} <a class="link" href="{{ route('loginForm') }}">{{__('message.register.sign_in')}}</a></p>
                           </div>
                        </div>
                        <div class="row">
                           <div class="mb-4">
                              <label class="form-label" for="fullNameSrEmail">{{__('message.register.name')}}</label>
                              <input type="text" class="form-control form-control-lg  @error('name') is-invalid @enderror" name="name"  value="{{ old('name') }}" id="fullNameSrEmail" placeholder="{{__('message.register.name_placeholder')}}" aria-label="Mark Williams" required>
                              <span class="invalid-feedback"> @error('name') {{ $message }} @else Please enter your full name. @enderror</span>
                           </div>
                        </div>
                        <div class="mb-4">
                           <label class="form-label" for="phone">{{__('message.register.phone')}}</label>
                           
                           <div class="input-group input-group-sm-vertical align-items-center">
                                 <div class="input-group-prepend dropdown ">
                                    <button id="selectedLanguage" class="dropdown-toggle" type="button" data-bs-toggle="dropdown" style="height:45px;">
                                    <img  id="selectedFlag"  width="20" class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/in.svg') }}" alt="Flag"> India (+91)
                                    </button>
                                    <ul class="dropdown-menu scrollable-dropdown">
                                       @foreach($countries as $country)
                                          <li>
                                             <a class="dropdown-item language-option" data-code="{{ $country->country_code }}" data-lang="{{$country->country_name}} (+{{ $country->dialing_code }})" data-flag="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/'.strtolower($country->country_code).'.svg') }}">
                                                <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/'.strtolower($country->country_code).'.svg') }}" alt="Flag"> {{$country->country_name}} (+{{ $country->dialing_code }})
                                             </a>
                                          </li>
                                       @endforeach                                    
                                    </ul>
                                 </div>
                                 <input type="hidden" name="country_code" id="selectedLanguageInput" value="IN">
                              <input type="text" class="js-input-mask form-control form-control-lg @error('phone_number') is-invalid @enderror" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"  placeholder="9999999999" aria-label="9999999999" required>
                              <span class="invalid-feedback">@error('phone_number') {{ $message }} @else Please enter a phone number @enderror</span>                             
                           </div>
                        </div>
                        <div class="mb-4">
                           <label class="form-label" for="signupSrEmail">{{__('message.register.email')}}</label>
                           <input type="email" class="form-control form-control-lg  @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" id="signupSrEmail" placeholder="Markwilliams@site.com" aria-label="Markwilliams@site.com" required>
                           <span class="invalid-feedback">@error('email') {{ $message }} @else Please enter a valid email address. @enderror</span>                          
                        </div>
                        <div class="mb-4">
                           <label class="form-label" for="signupSrPassword">{{__('message.register.password')}}</label>
                           <div class="input-group input-group-merge" data-hs-validation-validate-class>
                              <input type="password" class="js-toggle-password form-control form-control-lg @error('password') is-invalid @enderror" name="password" id="signupSrPassword" autocomplete="new-password" placeholder="{{__('message.register.password_placehoder')}}" aria-label="8+ characters required" required minlength="8" data-hs-toggle-password-options='{
                                 "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                 "defaultClass": "bi-eye-slash",
                                 "showClass": "bi-eye",
                                 "classChangeTarget": ".js-toggle-password-show-icon-1"
                                 }'>
                              <a class="js-toggle-password-target-1 input-group-append input-group-text" href="javascript:;">
                              <i class="js-toggle-password-show-icon-1 bi-eye"></i>
                              </a>
                           </div>
                           <span class="invalid-feedback">@error('email') {{ $message }} @else Your password is invalid. Please try again. @enderror</span>
                        </div>
                        <div class="mb-4">
                           <label class="form-label" for="monthly_orders">{{__('message.register.orders_volume')}}</label>
                           <select class="form-control form-control-lg @error('monthly_orders') is-invalid @enderror" name="monthly_orders" id="monthly_orders" autocomplete="off" data-hs-tom-select-options='{
                              "searchInDropdown": false,
                              "hideSearch": true,
                              "dropdownWidth": "8rem"
                              }' required>
                              <option value="" disabled selected>{{__('message.register.orders_volume_placeholder')}}</option>
                              <option value="0">New Business Startup</option>
                              <option value="100">0-100 Orders</option>
                              <option value="500">100-500 Orders</option>
                              <option value="1000">500-1000 Orders</option>
                              <option value="3000">1000-3000 Orders</option>
                              <option value="5000">3000-5000 Orders</option>
                              <option value="10000">5000-10,000 Orders</option>
                              <option value="10001">More than 10K Orders</option>
                           </select>
                           <span class="invalid-feedback">Please select your monthly order volume.</span>
                        </div>
                        <div class="form-check mb-4">
                           <input class="form-check-input" type="checkbox" checked id="termsCheckbox" required>
                           <label class="form-check-label" for="termsCheckbox">
                           {{__('message.register.accept')}}<a href="#"> {{__('message.register.t_and_c')}}</a>
                           </label>
                           <span class="invalid-feedback">Please accept our Terms and Conditions.</span  >
                        </div>
                        <div class="d-grid gap-2">
                           <button type="submit" class="btn btn-primary btn-lg"> {{__('message.register.creat_account')}}</button>               
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </main>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/hs.theme-appearance.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/hs-toggle-password/dist/js/hs-toggle-password.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/theme.min.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
      <script>
         document.querySelectorAll('.language-option').forEach(item => {
            item.addEventListener('click', function () {
                  let selectedLang = this.getAttribute('data-lang');
                  let selectedFlag = this.getAttribute('data-flag');
                  let selectedCode = this.getAttribute('data-code');
                  document.getElementById('selectedLanguage').innerHTML = `<img id="selectedFlag" class="avatar avatar-xss avatar-circle me-2" src="${selectedFlag}" width="20"> ${selectedLang}`;
                  document.getElementById('selectedLanguageInput').value = selectedCode;
            });
         });
      </script>
      <script>
         (function() {
           window.onload = function () {
             // INITIALIZATION OF BOOTSTRAP VALIDATION
             // =======================================================
             HSBsValidation.init('.js-validate', {
               onSubmit: data => {
                 data.event.preventDefault()
                 const form = document.querySelector('.js-validate')
                 form.submit()
               }
             })
         
             // INITIALIZATION OF TOGGLE PASSWORD
             // =======================================================
             new HSTogglePassword('.js-toggle-password')
         
             // INITIALIZATION OF SELECT
             // =======================================================
             HSCore.components.HSTomSelect.init('.js-select')
           }
         })()
      </script>
   </body>
</html>
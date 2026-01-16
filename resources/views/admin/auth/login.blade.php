<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Login</title>
      <link rel="shortcut icon" href="./favicon.ico">
      <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
      <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/tom-select/dist/css/tom-select.bootstrap5.css') }}">
      <link rel="preload" href="{{ asset(env('PUBLIC_ASSETS') . '/css/theme.min.css') }}" data-hs-appearance="default" as="style">
      <script>
         window.hs_config = {
             "themeAppearance": {
                 "layoutSkin": "default"
             }
         }
      </script>
   </head>
   <body>
      <main id="content" role="main" class="main">
         <div class="position-fixed top-0 end-0 start-0 bg-img-start" style="height: 32rem; background-image: url({{ asset(env('PUBLIC_ASSETS') . '/svg/components/card-6.svg') }});">
            <div class="shape shape-bottom zi-1">
               <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1921 273">
                  <polygon fill="#fff" points="0,273 1921,273 1921,0 " />
               </svg>
            </div>
         </div>
         <div class="container py-5">
            <a class="d-flex justify-content-center mb-5" href="{{ route('welcome') }}">
            <img class="zi-2" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}" alt="Image Description" style="width: 8rem;">
            </a>
            <div class="mx-auto" style="max-width: 30rem;">
               @if (session('message'))
               <div class="alert alert-soft-success" role="alert">
                  {{ session('message') }}
               </div>
               @endif
               @if ($errors->any())
               <div class="alert alert-soft-danger" role="alert">
                  {{ $errors->first() }}
               </div>
               @endif
               <div class="card card-lg mb-5">
                  <div class="card-body">
                     <form class="js-validate needs-validation" novalidate action="{{ route('admin.login') }}" method="POST">
                        @csrf
                        <div class="text-center">
                           <div class="mb-5">
                              <h1 class="display-5">{{__('message.login.heading_title')}}</h1>                             
                           </div>
                        </div>
                        <div class="mb-4">
                           <label class="form-label" for="signinSrEmail">{{__('message.login.email')}}</label>
                           <input type="email" class="form-control form-control-lg" name="email" id="signinSrEmail" tabindex="1" placeholder="{{__('message.login.email_placeholder')}}" aria-label="Your registered email" required>
                           <span class="invalid-feedback">Please enter a valid email address.</span>
                        </div>
                        <div class="mb-4">
                           <label class="form-label w-100" for="signupSrPassword" tabindex="0">
                           <span class="d-flex justify-content-between align-items-center">
                           <span">{{__('message.login.password')}}</span>
                           </span>
                           </label>
                           <div class="input-group input-group-merge" data-hs-validation-validate-class>
                              <input type="password" class="js-toggle-password form-control form-control-lg" name="password" id="signupSrPassword" placeholder="{{__('message.login.password_placeholder')}}" aria-label="Your password" required data-hs-toggle-password-options='{
                                 "target": "#changePassTarget",
                                 "defaultClass": "bi-eye-slash",
                                 "showClass": "bi-eye",
                                 "classChangeTarget": "#changePassIcon"
                                 }'>
                              <a id="changePassTarget" class="input-group-append input-group-text" href="javascript:;">
                              <i id="changePassIcon" class="bi-eye"></i>
                              </a>
                           </div>
                           <span class="invalid-feedback">Please enter a valid password.</span>
                        </div>
                        <div class="d-grid">
                           <button type="submit" class="btn btn-primary btn-lg">{{__('message.login.sing_in')}}</button>
                        </div>
                     </form>
                  </div>
               </div>
               <div class="position-relative text-center zi-1">
                  <small class="text-cap text-body mb-4">Trusted by the world's best teams</small>
                  <div class="w-85 mx-auto">
                     <div class="row justify-content-between">
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
            </div>
         </div>
      </main>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/hs.theme-appearance.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/hs-toggle-password/dist/js/hs-toggle-password.js') }}"></script>
      <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/theme.min.js') }}"></script>
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
           }
         })()
      </script>
   </body>
</html>
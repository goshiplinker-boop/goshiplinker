<x-layout>
   <x-slot name="title">{{__( 'message.home.heading_title')}}</x-slot>
   <x-slot name="main">
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error'))
         <div class="alert alert-soft-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif 
      <ul class="step step-md step-centered">
         <li class="step-item">
            <div class="step-content-wrapper">
               <span class="step-icon step-icon-soft-primary">1</span>
               <div class="step-content">
                  <h4 class="step-title">{{__('message.home.first_step')}}</h4>
                  <p class="step-text">{{__('message.home.first_step_description')}}</p>
               </div>
            </div>
         </li>
         <li class="step-item">
            <div class="step-content-wrapper">
               <span class="step-icon step-icon-soft-primary">2</span>
               <div class="step-content">
                  <h4 class="step-title">{{__('message.home.second_step')}}</h4>
                  <p class="step-text">{{__('message.home.second_step_description')}}</p>
               </div>
            </div>
         </li>
         <li class="step-item">
            <div class="step-content-wrapper">
               <span class="step-icon step-icon-soft-primary">3</span>
               <div class="step-content">
                  <h4 class="step-title">{{__('message.home.third_step')}}</h4>
                  <p class="step-text">{{__( 'message.home.third_step_description')}}</p>
               </div>
            </div>
         </li>
      </ul>
      <div class="card mt-8">
         <div class="row g-0">
            <div class="col-md-6">
               <div class="card-body">
                  <div class="text-section">
                     <h2>{{__('message.home.company_profile')}}</h2>
                     <p>{{__('message.home.company_profile_description')}}</p>
                     <a href="{{ route('profile', ['company_id' => session('company_id')]) }}" class="btn btn-primary btn-sm mt-3">{{__('message.home.go')}}</a>
                  </div>
               </div>
            </div>
            <div class="w-25 mx-auto my-4">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-collaboration.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-collaboration.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
            </div>
         </div>
      </div>
      <div class="card mt-8">
         <div class="row g-0">
            <div class="col-md-6">
               <div class="card-body">
                  <div class="text-section">
                     <h2>{{__('message.home.channel_config')}}</h2>
                     <p>{{__('message.home.channel_config_description')}}</p>
                     <a href="{{route('channels_list')}}" class="btn btn-primary btn-sm mt-3">{{__('message.home.go')}}</a>
                  </div>
               </div>
            </div>
            <div class="w-25 mx-auto my-4">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-looking-for-answers.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-looking-for-answers.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
            </div>
         </div>
      </div>
      <div class="card mt-8">
         <div class="row g-0">
            <div class="col-md-6">
               <div class="card-body">
                  <div class="text-section">
                     <h2>{{__('message.home.couriers_config')}}</h2>
                     <p>{{__('message.home.couriers_config_description')}}</p>
                     <a href="{{route(panelPrefix().'.couriers_list')}}" class="btn btn-primary btn-sm mt-3">{{__('message.home.go')}}</a>
                  </div>
               </div>
            </div>
            <div class="w-25 mx-auto my-4">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-maintenance.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-maintenance.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
            </div>
         </div>
      </div>
   </x-slot>
</x-layout>
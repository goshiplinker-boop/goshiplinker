<x-layout>
   <x-slot name="title">Error 404 </x-slot>
   <x-slot name="main">
      <div class="row justify-content-center align-items-sm-center py-sm-10">
         <div class="col-9 col-sm-6 col-lg-4">
            <div class="text-center text-sm-end me-sm-4 mb-5 mb-sm-0">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-thinking.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
               <img class="img-fluid" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-thinking.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
            </div>
         </div>
         <div class="col-sm-6 col-lg-4 text-center text-sm-start">
            <h1 class="display-1 mb-0">404</h1>
            @if(isset($error))
            {{ $error }}
            @else
            Sorry, the page you're looking for cannot be found.
            @endif
            <a class="btn btn-primary" href="{{ route('dashboard') }}">Go back to the Dashboard</a>
         </div>
      </div>
   </x-slot>
</x-layout>
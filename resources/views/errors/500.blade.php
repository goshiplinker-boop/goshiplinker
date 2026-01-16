<!DOCTYPE html>
<html lang="en">
  <head>
    @include('website.head')
  </head>
  <body style="margin: 0; padding: 0;">
    <section class="hero" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; flex-direction: column; padding-bottom:50px;">
      <img 
        src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-thinking.svg') }}" 
        alt="404 Illustration" 
        style="max-width: 200px; margin-bottom: 10px;"
      >
      <h1 style="font-size: 48px; font-weight: bold; margin-bottom: 5px;">404</h1>
      <p style="font-size: 16px; color: #6c757d; margin-bottom: 15px;">
        @if(isset($error))
          {{ $error }}
        @else
          Sorry, the page you're looking for cannot be found.
        @endif
      </p>
      <a 
        href="{{ route('welcome') }}" 
        class="btn btn-primary" 
        style="padding: 8px 16px; font-size: 14px; margin-top: 20px; text-decoration: none;"
      >
        Go back to Home
      </a>
    </section>

    @include('website.footer')
  </body>
</html>

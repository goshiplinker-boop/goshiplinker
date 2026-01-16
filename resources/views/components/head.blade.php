<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{{$title}}</title>
  <style data-hs-appearance-onload-styles>
    *
    {
      transition: unset !important;
    }

    body
    {
      opacity: 0;
    }
  </style>
  <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/vendor/tom-select/dist/css/tom-select.bootstrap5.css') }}">
  <link rel="preload" href="{{ asset(env('PUBLIC_ASSETS') . '/css/theme.min.css') }}" data-hs-appearance="default" as="style">
  <link rel="preload" href="{{ asset(env('PUBLIC_ASSETS') . '/css/theme-dark.min.css') }}" data-hs-appearance="dark" as="style">
  <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/css/pm-custom-style.css') }}">
  <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
</head>
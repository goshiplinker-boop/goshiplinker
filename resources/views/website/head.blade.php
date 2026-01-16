<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-93QYGM6VP6"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-93QYGM6VP6');
  </script>
  <!-- Google search console -->
  <meta name="google-site-verification" content="jTpCFObBLRjy-HxC8re4l9bO_eWGQeLV9wvn5hKOXfo" />

  <!-- Bing search console -->
  <meta name="msvalidate.01" content="C84A88B91C5A46E63C3CBBF862FC6BBE" />
  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Automate and optimize your logistics with ParcelMind. Features include courier allocation, shipment tracking, advanced analytics, and more.">
  <meta name="keywords" content="Logistics Automation, Courier Allocation, Shipment Tracking, AI Logistics, ParcelMind">
  <meta name="author" content="ParcelMind">
  <meta property="og:title" content="ParcelMind - Logistics Automation">
  <meta property="og:description" content="Simplify logistics with ParcelMind's automated solutions. Try it for free!">
  <meta property="og:image" content="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="ParcelMind - Logistics Automation">
  <meta name="twitter:description" content="Simplify logistics with ParcelMind's automated solutions. Try it for free!">
  <meta name="twitter:image" content="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}">
  <meta name="robots" content="index, follow">
  <title>ParcelMind - Logistics Automation</title>
  <link rel="stylesheet" href="{{ asset(env('PUBLIC_ASSETS') . '/css/website-styles.css') }}">
  <link rel="canonical" href="{{ route('welcome') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "ParcelMind",
    "url": "{{ route('welcome') }}",
    "logo": "{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "G35J+P6J, Shankar Chowk Rd",
      "addressLocality": "Gurugram",
      "addressRegion": "Haryana",
      "postalCode": "122008",
      "addressCountry": "India"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "telephone": "+91-9650065012",
      "contactType": "Customer service",
      "areaServed": "IN",
      "availableLanguage": ["English", "Hindi"]
    },
    "sameAs": [
      "https://www.facebook.com/ParcelMind",
      "https://twitter.com/ParcelMind",
      "https://www.linkedin.com/company/parcelmind"
    ]
  }
  </script>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="container">
      <div class="nav-content">
        <div class="logo">
        <a href="{{ route('welcome') }}"><span class="logo-text">Parcelmind</span></a>
        </div>
        <button class="mobile-menu-button">
          <i data-lucide="menu"></i>
        </button>
        <div class="nav-links">
          <a href="#features">Features</a>
          <a href="#why-us">Why Us</a>
          <a href="{{ route('register') }}">Pricing</a>
          <a href="{{ route('policy') }}">Privacy</a>
          <a href="{{ route('register') }}" class="nav-link">Register</a>
          <a href="{{ route('loginForm') }}" class="nav-link">Login</a>
          <button class="btn-primary"><a href="{{ route('register') }}" class="nav-link">Get Started</a></button>
        </div>
      </div>
    </div>
  </nav>
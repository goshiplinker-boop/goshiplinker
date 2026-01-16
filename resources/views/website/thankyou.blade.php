@include('website.head')

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1>Thank you for registering!</h1>
        <p class="hero-text">
        We're excited to welcome you. A member of our team will be in touch with you shortly to discuss your needs and answer any questions you may have. <br>We look forward to connecting with you soon!
        </p>
        <div class="hero-buttons">
          <button class="btn-primary"><a href="{{ route('login') }}" class="nav-link">Login dashboard</a></button>
          <!-- <button class="btn-secondary">Watch Demo</button> -->
        </div>
      </div>
    </div>
  </section>
      
<!-- Footer -->
@include('website.footer')
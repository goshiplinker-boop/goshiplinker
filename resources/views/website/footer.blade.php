    <footer class="footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-section">
            <h3>ParcelMind</h3>
            <p>Automate courier selection, tracking, and returns to streamline eCommerce fulfilment.</p>
          </div>
          <div class="footer-section">
            <h4>Product</h4>
            <ul>
              <li><a href="#features">Features</a></li>
              <!-- <li><a href="#pricing">Pricing</a></li>
              <li><a href="#integrations">Integrations</a></li> -->
             <li>
              <a href="https://documenter.getpostman.com/view/44575099/2sB2j4eAsh" target="_blank">
                REST APIs
              </a>
            </li>
           </ul>
          </div>
          <div class="footer-section">
            <h4>Company</h4>
            <ul>
              <li><a href="/about">About</a></li>
              <li><a href="{{ route('policy') }}">Privacy Policy</a></li>
              <li><a href="/terms">Terms of Service</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Contact</h4>
            <address>
              Parcelmind, DLF Cyber City<br>
              DLF Phase 2, Sector 24<br>
              Gurugram, Haryana 122002, India<br>              
            </address>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 ParcelMind. All rights reserved.</p>
        </div>
      </div>
    </footer>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset(env('PUBLIC_ASSETS') . '/js/website-script.js') }}"></script>
   </body>
  </html>
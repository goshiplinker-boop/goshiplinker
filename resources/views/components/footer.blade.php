<div class="footer">
  <div class="row align-items-center">
    <div class="col">
      
    </div>
  </div>
</div>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/jquery-migrate/dist/jquery-migrate.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/tom-select/dist/js/tom-select.complete.min.js')}}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/hs-step-form/dist/hs-step-form.min.js')}}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/dropzone/dist/min/dropzone.min.js')}}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/hs-file-attach/dist/hs-file-attach.min.js')}}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/clipboard/dist/clipboard.min.js')}}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/js/theme.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/js/pm-custom.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/js/hs.theme-appearance.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/chart.js/dist/chart.min.js') }}"></script>
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/chartjs-chart-matrix/dist/chartjs-chart-matrix.min.js') }}"></script> 
<script src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js') }}"></script>
<script>
  (function() {
    // INITIALIZATION OF NAVBAR VERTICAL ASIDE
    // =======================================================
    new HSSideNav('.js-navbar-vertical-aside').init()
  
    // INITIALIZATION OF BOOTSTRAP DROPDOWN
    // =======================================================
    HSBsDropdown.init()  
  })()
</script>

<!-- Theme switcher dark mode light mode -->
<script>
  (function () {
    // STYLE SWITCHER
    // =======================================================
    const $dropdownBtn = document.getElementById('selectThemeDropdown') // Dropdowon trigger
    const $variants = document.querySelectorAll(`[aria-labelledby="selectThemeDropdown"] [data-icon]`) // All items of the dropdown
  
    // Function to set active style in the dorpdown menu and set icon for dropdown trigger
    const setActiveStyle = function () {
      $variants.forEach($item => {
        if ($item.getAttribute('data-value') === HSThemeAppearance.getOriginalAppearance()) {
          $dropdownBtn.innerHTML = `<i class="${$item.getAttribute('data-icon')}" />`
          return $item.classList.add('active')
        }
  
        $item.classList.remove('active')
      })
    }

    // Add a click event to all items of the dropdown to set the style
    $variants.forEach(function ($item) {
      $item.addEventListener('click', function () {
        HSThemeAppearance.setAppearance($item.getAttribute('data-value'))
      })
    })
  
    // Call the setActiveStyle on load page
    setActiveStyle()
  
    // Add event listener on change style to call the setActiveStyle function
    window.addEventListener('on-hs-appearance-change', function () {
      setActiveStyle()
    })
  })()
</script>
<script>
  (function() {
    // INITIALIZATION OF CHARTJS
    // =======================================================
    document.querySelectorAll('.js-chart').forEach(item => {
      HSCore.components.HSChartJS.init(item)
    })
  })();
</script>
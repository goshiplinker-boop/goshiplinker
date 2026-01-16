<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-light bg-light">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->
            @if(Request::segment(1) !='admin')
            <a class="navbar-brand" href="{{ route('dashboard') }}" aria-label="Front">
                <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}"
                    alt="Parcel Mind">
                <!-- <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos/logo.svg') }}" alt="Logo" data-hs-theme-appearance="default"> -->
                <!-- <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos-light/logo.svg') }}" alt="Logo" data-hs-theme-appearance="dark"> -->
                <img class="navbar-brand-logo-mini" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/logo-short.png') }}" alt="Logo" data-hs-theme-appearance="default">
                <!-- <img class="navbar-brand-logo-mini" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos-light/logo-short.svg') }}" alt="Logo" data-hs-theme-appearance="dark"> -->
            </a>
            @else
            <a class="navbar-brand" href="{{ route('vendors_list', ['tab' => 'fresh_lead']) }}" aria-label="Front">
                <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/PM_Logo.png') }}" alt="Parcel Mind">
                <!-- <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos/logo.svg') }}" alt="Logo" data-hs-theme-appearance="default"> -->
                <!-- <img class="navbar-brand-logo" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos-light/logo.svg') }}" alt="Logo" data-hs-theme-appearance="dark"> -->
                <img class="navbar-brand-logo-mini" src="{{ asset(env('PUBLIC_ASSETS') . '/images/logo/logo-short.png') }}" alt="Logo" data-hs-theme-appearance="default">
                <!-- <img class="navbar-brand-logo-mini" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/logos-light/logo-short.svg') }}" alt="Logo" data-hs-theme-appearance="dark"> -->
            </a>
            @endif
            <!-- End Logo -->
            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="bi-arrow-bar-right navbar-toggler-full-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
            </button>
            <!-- End Navbar Vertical Toggle -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">
                    <!-- Collapse -->
                    @if(Request::segment(1) !='admin')
                    <div class="nav-item">
                        <a class="nav-link @if(Request::segment(1) == 'dashboard') active @else collapsed @endif "
                            href="{{ route('dashboard') }}" data-placement="left">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">{{__('message.sidebar.home')}}</span>
                        </a>
                    </div>
                      <div class="nav-item">
                            <a class="nav-link @if(Request::segment(2) == 'analytic') active @else collapsed @endif"
                                href="{{ route('analytic') }}" data-placement="left">
                                <i class="bi bi-graph-up nav-icon"></i>
                                <span class="nav-link-title">Analytics </span>
                            </a>
                        </div> 
                    @endif
                    <!-- End Collapse -->
                    <!-- Collapse -->
                    <div class="navbar-nav nav-compact">
                    </div>
                    <div id="navbarVerticalMenuPagesMenu">                       
                        @if(Request::segment(1)=='seller')
                        <div class="nav-item">
                            <a class="nav-link @if(Request::segment(2) == 'orders') active @else collapsed @endif"
                                href="{{ route('order_list') }}" data-placement="left">
                                <i class="bi-basket nav-icon"></i>
                                <span class="nav-link-title">{{__('message.sidebar.orders')}}</span>
                            </a>
                        </div>
                        <!-- Collapse -->
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle  @if(Request::segment(2) == 'wallet') active @else collapsed @endif "
                                href="#navbarVerticalMenuPagesPayment" role="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarVerticalMenuPagesPaymentMenu" aria-expanded="false"
                                aria-controls="navbarVerticalMenuPagesPaymentMenu">
                                <i class="bi-box nav-icon"></i>
                                <span class="nav-link-title">Wallet</span>
                            </a>
                            <div id="navbarVerticalMenuPagesPaymentMenu" data-bs-parent="#navbarVerticalMenuPagesPaymentMenu" class="nav-collapse collapse  @if(Request::segment(3) == 'subscription' || Request::segment(2) == 'wallet') show @endif">
                                <!-- <a class="nav-link @if(Request::segment(3) == 'subscription') active @else collapsed @endif" href="{{ route('subscription_plans') }}">Plans</a> -->
                                <a class="nav-link @if(Request::segment(2) == 'wallet') active @else collapsed @endif" href="{{ route(panelPrefix().'.wallet.index') }}">Transaction History</a>
                            </div>
                        </div>                      
                        @endif
                        <!-- Collapse -->
                        @if(Request::segment(1)=='seller')
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle  @if(Request::segment(2) == 'locations' || Request::segment(2) == 'couriers'  || Request::segment(2) == 'channels') active @endif "
                                href="#navbarVerticalMenuPagesUsersMenu" role="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarVerticalMenuPagesUsersMenu" aria-expanded="false"
                                aria-controls="navbarVerticalMenuPagesUsersMenu">
                                <i class="bi-gear nav-icon"></i>
                                <span class="nav-link-title">{{__('message.sidebar.settings')}}</span>
                            </a>
                                <div id="navbarVerticalMenuPagesUsersMenu" data-bs-parent="#navbarVerticalMenuPagesMenu" class="nav-collapse collapse  @if(
                                    Request::segment(2) == 'channels'  || 
                                    Request::segment(2) == 'couriers'  || 
                                    Request::segment(2) == 'tracking'  || 
                                    Request::segment(2) == 'locations' || 
                                    Request::segment(2) == 'company'   ||
                                    Request::segment(3) == 'shipping_label'   || 
                                    Request::segment(3) == 'invoice'   ||                                
                                    Request::segment(2) == 'api_credentials' || 
                                    Request::segment(2) == 'pincode' || 
                                    Request::segment(2) == 'seller_rate_card' || 
                                    Request::segment(2) == 'shipping'
                                    ) show @endif">
                                        <a class="nav-link @if(Request::segment(2) == 'channels') active @else collapsed @endif" href="{{ route('channels_list') }}">Channels</a>
                                        <a class="nav-link @if(Request::segment(2) == 'couriers' && Request::segment(3) != 'uploadAWB') active @else collapsed @endif" href="{{ route(panelPrefix().'.couriers_list') }}">Couriers</a>
                                        <a class="nav-link @if(Request::segment(2) == 'shipping' && Request::segment(3) == 'rate_calculator') active @else collapsed @endif" href="{{ route(panelPrefix().'.shipping.rate_calculator') }}">Shipping Rate Calculator</a>
                                        <a class="nav-link @if(Request::segment(2) == 'seller_rate_card') active @else collapsed @endif" href="{{ route(panelPrefix().'.rate_card') }}">Rate Card</a>
                                        <a class="nav-link @if(Request::segment(2) == 'tracking') active @else collapsed @endif" href="{{ route('tracking_create') }}">Tracking Page</a>
                                        <a class="nav-link @if(Request::segment(2) == 'locations') active @else collapsed @endif" href="{{ route('pickup_locations.index') }}">Pickup Locations</a>
                                        <a class="nav-link @if(Request::segment(2) == 'company') active @else collapsed @endif"  href="{{ route('profile') }}">Company</a>
                                        <a class="nav-link @if(Request::segment(3) == 'shipping_label') active @else collapsed @endif" href="{{ route('label') }}">Shipping label</a>
                                        <a class="nav-link @if(Request::segment(3) == 'invoice') active @else collapsed @endif" href="{{ route('order_invoice') }}">Invoice</a> 
                                        <a class="nav-link @if(Request::segment(2) == 'api_credentials') active @else collapsed @endif" href="{{ route('api.credentials.show') }}">Api Setup</a>  
                                
                                </div>                           
                        </div>
                        @endif
                        @if(Request::segment(1)=='admin')
                            <!-- Collapse -->
                            <div class="nav-item">
                                <a class="nav-link dropdown-toggle  @if((Request::segment(2) == 'couriers' && Request::segment(3) == 'manage_courier') || Request::segment(2) == 'manage_pincode'  || Request::segment(2) == 'manage_rate_card') active @endif "
                                    href="#navbarVerticalMenuPagesUsersMenuAdmin" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarVerticalMenuPagesUsersMenuAdmin" aria-expanded="false"
                                    aria-controls="navbarVerticalMenuPagesUsersMenuAdmin">
                                    <i class="bi-gear nav-icon"></i>
                                    <span class="nav-link-title">Courier Master</span>
                                </a>
                                <div id="navbarVerticalMenuPagesUsersMenuAdmin" data-bs-parent="#navbarVerticalMenuPagesMenu" class="nav-collapse collapse  @if(                                                                
                                    Request::segment(3) == 'manage_courier' || 
                                    Request::segment(2) == 'manage_pincode' || 
                                    Request::segment(2) == 'manage_rate_card' 
                                    ) show @endif">                                    
                                    <a class="nav-link @if(Request::segment(2) == 'couriers' && Request::segment(3) == 'manage_courier') active @else collapsed @endif" href="{{ route(panelPrefix().'.couriers_list') }}">Manage Courier</a>
                                    <a class="nav-link @if(Request::segment(2) == 'manage_rate_card') active @else collapsed @endif" href="{{ route('manage_rate_card.index') }}">Manage Rate Card</a>
                                    <a class="nav-link @if(Request::segment(2) == 'manage_pincode') active @else collapsed @endif" href="{{ route('pincode.upload') }}">Manage Pincode</a>                                   
                                </div>
                            </div>
                            <!-- Collapse -->
                            <div class="nav-item">
                                <a class="nav-link dropdown-toggle  @if((Request::segment(2) == 'couriers' && Request::segment(3) == 'manage_seller_courier') || Request::segment(2) == 'manage_seller'  || Request::segment(2) == 'manage_seller_rate_card') active @endif "
                                    href="#navbarVerticalMenuPagesUsersMenuSeller" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarVerticalMenuPagesUsersMenuSeller" aria-expanded="false"
                                    aria-controls="navbarVerticalMenuPagesUsersMenuSeller">
                                    <i class="bi-gear nav-icon"></i>
                                    <span class="nav-link-title">Seller Master</span>
                                </a>
                                <div id="navbarVerticalMenuPagesUsersMenuSeller" data-bs-parent="#navbarVerticalMenuPagesMenu" class="nav-collapse collapse  @if(                                                                
                                    Request::segment(3) == 'manage_seller_courier' || 
                                    Request::segment(2) == 'manage_seller' || 
                                    Request::segment(2) == 'manage_seller_rate_card' || 
                                    Request::segment(2) == 'manage_seller_pincode' || 
                                    Request::segment(2) == 'shipping' 
                                    ) show @endif">   
                                    <a class="nav-link @if(Request::segment(2) == 'manage_seller') active @else collapsed @endif" href="{{ route('vendors_list', ['tab' => 'fresh_lead']) }}">Manage Seller</a>                                  
                                    <a class="nav-link @if(Request::segment(2) == 'couriers' && Request::segment(3) == 'manage_seller_courier') active @else collapsed @endif" href="{{ route(panelPrefix().'.manage_seller_couriers') }}">Manage Courier</a>
                                    <a class="nav-link @if(Request::segment(2) == 'manage_seller_rate_card') active @else collapsed @endif" href="{{ route('manage_seller_rate_card.index') }}">Manage Rate Card</a>
                                    <a class="nav-link @if(Request::segment(2) == 'manage_seller_pincode') active @else collapsed @endif" href="{{ route('seller.pincodes.import.form') }}">Disable Seller Pincode</a>  
                                    <a class="nav-link @if(Request::segment(2) == 'shipping' && Request::segment(3) == 'rate_calculator') active @else collapsed @endif" href="{{ route(panelPrefix().'.shipping.rate_calculator') }}">Shipping Rate Calculator</a>
                                                                      
                                </div>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link @if(Request::segment(2) == 'weight-discrepancies') active @else collapsed @endif"
                                    href="{{ route(panelPrefix().'.weight-discrepancies.index') }}" data-placement="left">
                                    <i class="bi-basket nav-icon"></i>
                                    <span class="nav-link-title">Weight Management</span>
                                </a>
                            </div>
                            <!-- Collapse -->
                            <div class="nav-item">
                                <a class="nav-link dropdown-toggle  @if(Request::segment(3) == 'uploadAWB' || Request::segment(3) == 'pincode') active @endif "
                                    href="#navbarVerticalMenuPagesUsersMenuOther" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarVerticalMenuPagesUsersMenuOther" aria-expanded="false"
                                    aria-controls="navbarVerticalMenuPagesUsersMenuOther">
                                    <i class="bi-gear nav-icon"></i>
                                    <span class="nav-link-title">Other</span>
                                </a>
                                <div id="navbarVerticalMenuPagesUsersMenuOther" data-bs-parent="#navbarVerticalMenuPagesMenu" class="nav-collapse collapse  @if(                                                                
                                    Request::segment(3) == 'uploadAWB' || 
                                    Request::segment(3) == 'pincode'
                                    ) show @endif">   
                                    <a class="nav-link @if(Request::segment(3) == 'uploadAWB') active @else collapsed @endif" href="{{ route('courier.uploadAWB') }}">Upload Tracking</a>
                                    <!-- <a class="nav-link @if(Request::segment(3) == 'pincode') active @else collapsed @endif" href="{{ route('pincode_list') }}">Upload Pincodes</a> -->
                                        
                                                                      
                                </div>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link @if(Request::segment(2) == 'system-logs') active @else collapsed @endif"
                                    href="{{ route('system.logs') }}" data-placement="left">
                                    <i class="bi-basket nav-icon"></i>
                                    <span class="nav-link-title">System Logs</span>
                                </a>
                            </div>
                            
                        @endif
                    </div>
                </div>
                <!-- End Content -->
                <!-- Footer -->
                <div class="navbar-vertical-footer">
                    <ul class="navbar-vertical-footer-list">
                        <li class="navbar-vertical-footer-list-item">
                            <!-- Style Switcher -->
                            <div class="dropdown dropup">
                                <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation></button>
                                <div class="dropdown-menu navbar-dropdown-menu navbar-dropdown-menu-borderless"aria-labelledby="selectThemeDropdown">
                                    <a class="dropdown-item" href="javascript:;" data-icon="bi-moon-stars" data-value="auto">
                                        <i class="bi-moon-stars me-2"></i>
                                        <span class="text-truncate" title="Auto (system default)">Auto (system default)</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;" data-icon="bi-brightness-high" data-value="default">
                                        <i class="bi-brightness-high me-2"></i>
                                        <span class="text-truncate" title="Default (light mode)">Default (light mode)</span>
                                    </a>
                                    <a class="dropdown-item active" href="javascript:;" data-icon="bi-moon" data-value="dark">
                                        <i class="bi-moon me-2"></i>
                                        <span class="text-truncate" title="Dark">Dark</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End Style Switcher -->
                        </li>
                        <li class="navbar-vertical-footer-list-item">
                            <!-- Other Links -->
                            <div class="dropdown dropup">
                                <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="otherLinksDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation><i class="bi-info-circle"></i></button>
                                <div class="dropdown-menu navbar-dropdown-menu-borderless" aria-labelledby="otherLinksDropdown">
                                    <span class="dropdown-header">Contacts</span>
                                    <a class="dropdown-item" href="javascript:;">
                                        <i class="bi-chat-left-dots dropdown-item-icon"></i>
                                        <span class="text-truncate" title="Contact support">hello@parcelmind.com</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End Other Links -->
                        </li>
                        <li class="navbar-vertical-footer-list-item">
                            <!-- Language -->
                            <div class="dropdown dropup">
                                <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle" id="selectLanguageDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
                                    <img class="avatar avatar-xss avatar-circle" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/us.svg') }}" alt="United States Flag">
                                </button>
                                <div class="dropdown-menu navbar-dropdown-menu-borderless"  aria-labelledby="selectLanguageDropdown">
                                    <span class="dropdown-header">Select language</span>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/us.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="English">English (US)</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/gb.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="English">English (UK)</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/de.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="Deutsch">Deutsch</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/dk.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="Dansk">Dansk</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/it.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="Italiano">Italiano</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <img class="avatar avatar-xss avatar-circle me-2" src="{{ asset(env('PUBLIC_ASSETS') . '/vendor/flag-icon-css/flags/1x1/cn.svg') }}" alt="Flag">
                                        <span class="text-truncate" title="中文 (繁體)">中文 (繁體)</span>
                                    </a>
                                </div>
                            </div>
                            <!-- End Language -->
                        </li>
                    </ul>
                </div>
                <!-- End Footer -->
            </div>
        </div>
</aside>
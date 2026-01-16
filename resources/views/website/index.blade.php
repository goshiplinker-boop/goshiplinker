@include('website.head')


  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">       
        @if(session('error'))
          <div style="color: red;font-size: 24px;">
              {!! session('error') !!}           
          </div>
        @endif
        <h1>
          Automate and Optimize Your
          <span class="text-accent">Logistics</span>
        </h1>
        <p class="hero-text">
          Simplify your logistics with automate courier selection, tracking, and returns to streamline eCommerce fulfilment.
        </p>
        <div class="hero-buttons">
          <button class="btn-primary"><a href="{{ route('register') }}" class="nav-link">Start Free Trial</a></button>
          <!-- <button class="btn-secondary">Watch Demo</button> -->
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="features">
    <div class="container">
      <div class="section-header">
        <h2>Powerful Features for Modern Logistics</h2>
        <p>Automate workflows to reduce errors and ensure timely delivery, enhancing customer satisfaction and operational efficiency.</p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="box"></i>
          </div>
          <h3>Order Management System</h3>
          <p>Easily manage customer orders from placement to fulfillment. Track order status, manage returns, update shipping details, and streamline communication—all in one centralized dashboard</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="package-2"></i>
          </div>
          <h3>Smart Courier Allocation</h3>
          <p>Assign the best courier for each order based on cost, speed, destination, and service reliability. Optimize delivery efficiency while reducing shipping costs—no manual effort needed.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="brick-wall"></i>
          </div>
          <h3>Shipping Labels</h3>
          <p>Generate bulk courier or custom shipping labels in one click. Save time, reduce manual errors, and streamline your shipping process with automated label creation for all your orders.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="brick-wall"></i>
          </div>
          <h3>Cutomer Invoices</h3>
          <p>Auto-generate invoice labels with order and customer details for easy packaging, tracking, and documentation. Ensure accurate billing and smooth handovers with clear, ready-to-print labels.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="truck"></i>
          </div>
          <h3>Real-time Tracking</h3>
          <p>TTrack shipments live with real-time updates from dispatch to delivery. Keep customers informed and reduce support queries with transparent, up-to-the-minute tracking info.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="bar-chart-3"></i>
          </div>
          <h3>Advanced Reportings</h3>
          <p>Get actionable insights with real-time dashboards and customizable reports. Track order trends, delivery performance, courier efficiency, and more to make smarter, data-driven decisions.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="refresh-cw"></i>
          </div>
          <h3>Returns Management</h3>
          <p>Simplify and automate the returns process with a centralized system for tracking, approving, and processing return requests. Enhance customer satisfaction while minimizing reverse logistics hassle.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="shield"></i>
          </div>
          <h3>Secure Integration</h3>
          <p>Seamlessly connect with third-party logistis, sale channels, and couriers through secure, encrypted APIs. Ensure safe data exchange and compliance with industry standards—without compromising performance.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="clock"></i>
          </div>
          <h3>Delivery Estimates</h3>
          <p>Get real-time delivery estimates from your logistics providers, dynamically calculated based on your pickup cut-off times.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- channle-integrations Section -->
  <section id="channle-integrations" class="channle-integrations">
    <div class="container">
      <!-- Section Header -->
      <div class="section-header">
        <h2>Sales Channel Integrations</h2>
        <p>Seamlessly sync orders from your favorite sales channels to manage your orders in one place.</p>
      </div>

      <!-- Sales Channels -->
      <div class="integration-subsection">
        <div class="features-grid">
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shopify.png') }}" alt="Shopify sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/woocommerce.png') }}" alt="WooCommerce sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shopbase.png') }}" alt="ShopBase sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/magento.png') }}" alt="Magento sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/bigcommerce.png') }}" alt="ParcelMind BigCommerce sales channel integration" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/dukaan.png') }}" alt="Dukaan sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/ecwid.png') }}" alt="Ecwid sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/instamojo.png') }}" alt="Instamojo sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/opencart.png') }}" alt="OpenCart sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/prestashop.png') }}" alt="PrestaShop sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/sellfy.png') }}" alt="Sellfy sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shift4shop.png') }}" alt="Shift4Shop sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/squareup.png') }}" alt="Squareup sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/squarespace.png') }}" alt="Squarespace sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/wix.png') }}" alt="Wix sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/zoho-commerce.png') }}" alt="Zoho Commerce sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/big-cartel.png') }}" alt="Big Cartel sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/craftcommerce.png') }}" alt="CraftCommerce sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/cdiscount.png') }}" alt="Cdiscount sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/ccvshop.png') }}" alt="CCV Shop sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/businesscentral.png') }}" alt="Business Central sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/boxwise.png') }}" alt="Boxwise sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/bol.png') }}" alt="Bol sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/blokker.png') }}" alt="Blokker sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/basekit.png') }}" alt="Basekit sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/afterbuy.png') }}" alt="Afterbuy sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/decathlon.png') }}" alt="Decathlon sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/ebay.png') }}" alt="eBay sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/ecwidbylightspeed.png') }}" alt="Ecwid by Lightspeed sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/epages.png') }}" alt="ePages sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/equipme.png') }}" alt="Equipme sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/etsy.png') }}" alt="Etsy sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/exactonline.png') }}" alt="Exact Online sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/goedgepickt.png') }}" alt="Goedgepickt sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/holded.png') }}" alt="Holded sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/jouwweb.png') }}" alt="Jouwweb sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/jtlshop.png') }}" alt="JTL Shop sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/kaufland.png') }}" alt="Kaufland sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/lightspeed.png') }}" alt="Lightspeed sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/linnwork.png') }}" alt="Linnworks sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/mijnwebwinkel.png') }}" alt="Mijnwebwinkel sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/mirakl.png') }}" alt="Mirakl sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/netsuite.png') }}" alt="NetSuite sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/nextchapter.png') }}" alt="NextChapter sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/odoo.png') }}" alt="Odoo sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/packcloud.png') }}" alt="PackCloud sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/pccomponentes.png') }}" alt="PcComponentes sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/peoplevox.png') }}" alt="Peoplevox sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/picqer.png') }}" alt="Picqer sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/pixi.png') }}" alt="Pixi sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/plentymarkets.png') }}" alt="Plentymarkets sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/pulpowms.png') }}" alt="Pulpo WMS sales channel integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/salesforce.png') }}" alt="Salesforce sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shiphero.png') }}" alt="ShipHero sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shoppagina.png') }}" alt="Shoppagina sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shoptrader.png') }}" alt="Shoptrader sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/shopware.png') }}" alt="Shopware sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/spoki.png') }}" alt="Spoki sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/stockpilot.png') }}" alt="Stockpilot sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/thirtybees.png') }}" alt="Thirty Bees sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/tiktokshop.png') }}" alt="TikTok Shop sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/webador.png') }}" alt="Webador sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/weclapp.png') }}" alt="Weclapp sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/wics.png') }}" alt="WICS sales channel integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/channels/xentral.png') }}" alt="Xentral sales channel integration in ParcelMind" loading="lazy">
          </div>

        </div>
      </div>
      </div>
      </div>
    </div>
  </section>
      
  <!-- courier-integrations Section -->
  <section id="courier-integrations" class="features">
    <div class="container">
      <!-- Section Header -->
      <div class="section-header">
        <h2>3PL Courier Integrations</h2>
        <p>Integrate your 3pl shipping partners and generate real‑time shipping labels with leading carriers.</p>
      </div>

      <!-- Shipping Carriers -->
      <div class="integration-subsection">
        <div class="features-grid">
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/bluedart.png') }}" alt="Bluedart shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/delhivery.png') }}" alt="Delhivery shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/ekart.png') }}" alt="Ekart shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/xpress.png') }}" alt="XpressBees shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/aramex.png') }}" alt="Aramex shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/indiapost.png') }}" alt="India Post shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/brt.png') }}" alt="BRT shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/canadapost.png') }}" alt="Canada Post shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/dhl.png') }}" alt="DHL Group shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/dpd.png') }}" alt="DPD shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/fedex.png') }}" alt="FedEx shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/gati.png') }}" alt="Gati shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/gls.png') }}" alt="GLS shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/hermes.png') }}" alt="Evri shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/bombino.png') }}" alt="Bombino shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/laposte.png') }}" alt="La Poste shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/UPS.png') }}" alt="UPS shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/royalmail.png') }}" alt="Royal Mail shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/seur.png') }}" alt="SEUR shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/shadowfox.png') }}" alt="Shadowfax shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/swisspost.png') }}" alt="Swiss Post shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/tnt.png') }}" alt="TNT shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/nl.png') }}" alt="PostNL shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/YRC.png') }}" alt="YRC Freight shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/kuehnenagel.png') }}" alt="Kühne & Nagel shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
            <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/selfship.png') }}" alt="Self Ship shipping carrier integration in ParcelMind" loading="lazy">
          </div>
          <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/bigbasket.png') }}" alt="Big Basket shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/blinkit.png') }}" alt="BlinkIt shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/borzo.png') }}" alt="Borzo shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/dunzo.png') }}" alt="Dunzo shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/movers.png') }}" alt="Movers shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/porter.png') }}" alt="Porter shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/swiggy.png') }}" alt="Swiggy shipping carrier integration in ParcelMind" loading="lazy">
        </div>
        <div class="feature-card align-content-md-center">
          <img src="{{ asset(env('PUBLIC_ASSETS') . '/images/couriers/zomoto.png') }}" alt="Zomoto shipping carrier integration in ParcelMind" loading="lazy">
        </div>

        </div>
      </div>
    </div>
  </section>

  <!-- Why Us Section -->
  <section id="why-us" class="why-us">
    <div class="container">
      <div class="section-header">
        <h2>Why Choose ParcelMind?</h2>
        <p>Join thousands of businesses that trust ParcelMind for their logistics automation needs.</p>
      </div>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">99.9%</div>
          <div class="stat-label">Uptime</div>
          <p class="stat-description">Reliable platform that keeps your operations running smoothly</p>
        </div>
        <div class="stat-card">
          <div class="stat-number">25%</div>
          <div class="stat-label">Cost Reduction</div>
          <p class="stat-description">Average savings reported by our customers</p>
        </div>
        <div class="stat-card">
          <div class="stat-number">10M+</div>
          <div class="stat-label">Shipments</div>
          <p class="stat-description">Successfully processed through our platform</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta">
    <div class="container">
      <div class="cta-content">
        <h2>Ready to Transform Your Logistics?</h2>
        <p>Join thousands of businesses that trust ParcelMind for their logistics automation needs.</p>
        <button class="btn-white"><a href="{{ route('register') }}" class="nav-link">Start Free Trial</a></button>

      </div>
    </div>
  </section>
  
  <!-- Footer -->
  @include('website.footer')
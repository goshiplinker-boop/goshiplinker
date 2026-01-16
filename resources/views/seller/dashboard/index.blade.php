<x-layout>
  <x-slot name="title"></x-slot>
  <x-slot name="breadcrumbs"></x-slot>
  <x-slot name="page_header_title">
    <h1 class="page-header-title">Dashboard</h1>
  </x-slot>
  <x-slot name="main">
    <ul class="nav nav-segment" role="tablist">
      <li class="nav-item">
        <a class="nav-link active"
          id="nav-chart-tab"
          data-bs-toggle="pill"
          href="#nav-chart"
          role="tab"
          aria-controls="nav-chart"
          aria-selected="true">
        Overview
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link"
          id="nav-table-tab"
          data-bs-toggle="pill"
          href="#nav-table"
          role="tab"
          aria-controls="nav-table"
          aria-selected="false">
        Insights
        </a>
      </li>
    </ul>
    <!-- Tab Content -->
    <div class="tab-content mt-4">
      <div class="tab-pane fade show active" id="nav-chart"role="tabpanel" aria-labelledby="nav-chart-tab">
        <div class="row">
          <!-- Total Orders -->
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <a class="card card-hover-shadow h-100" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">TOTAL ORDERS</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-6">
                    <h2 class="card-title text-inherit">{{ $orderStats['current'] }}</h2>
                  </div>
                  <div class="col-6">
                    <div class="chartjs-custom" style="height: 3rem;">
                      <div class="chartjs-custom" style="height: 3rem;">
                        <canvas class="js-chart" data-hs-chartjs-options='{
                          "type": "line",
                          "data": {
                          "labels":@json($chartData["labels"]),
                          "datasets": [{
                          "data":@json($chartData["orderData"]),
                          "backgroundColor": ["rgba(55, 125, 255, 0)", "rgba(255, 255, 255, 0)"],
                          "borderColor": "#377dff",
                          "borderWidth": 2,
                          "pointRadius": 0,
                          "pointHoverRadius": 0
                          }]
                          },
                          "options": {
                          "scales": {
                          "y": {
                          "display": false
                          },
                          "x": {
                          "display": false
                          }
                          },
                          "hover": {
                          "mode": "nearest",
                          "intersect": false
                          },
                          "plugins": {
                          "tooltip": {
                          "postfix": "k",
                          "hasIndicator": true,
                          "intersect": false
                          }
                          }
                          }
                          }'>
                        </canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <span class="badge {{ $orderStats['percent'] < 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }}">
                <i class="bi-{{ $orderStats['percent'] < 0 ? 'graph-down' : 'graph-up' }}"></i>
                {{ $orderStats['percent'] }}%
                </span>
                <span class="text-body fs-6 ms-1">from {{ $orderStats['previous'] }}</span>
              </div>
            </a>
          </div>
          <!-- Total Revenue -->
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <a class="card card-hover-shadow h-100" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">TOTAL REVENUE</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-6">
                    <h2 class="card-title text-inherit">₹{{ $salesStats['current'] }}</h2>
                  </div>
                  <div class="col-6">
                    <div class="chartjs-custom" style="height: 3rem;">
                      <canvas class="js-chart" data-hs-chartjs-options='{
                        "type": "line",
                        "data": {
                        "labels":@json($chartData["labels"]),
                        "datasets": [{
                        "data":{!! json_encode($chartData["salesData"]) !!},
                        "backgroundColor": ["rgba(55, 125, 255, 0)", "rgba(255, 255, 255, 0)"],
                        "borderColor": "#377dff",
                        "borderWidth": 2,
                        "pointRadius": 0,
                        "pointHoverRadius": 0
                        }]
                        },
                        "options": {
                        "scales": {
                        "y": {
                        "display": false
                        },
                        "x": {
                        "display": false
                        }
                        },
                        "hover": {
                        "mode": "nearest",
                        "intersect": false
                        },
                        "plugins": {
                        "tooltip": {
                        "postfix": "k",
                        "hasIndicator": true,
                        "intersect": false
                        }
                        }
                        }
                        }'>
                      </canvas>
                    </div>
                  </div>
                </div>
                <span class="badge {{ $salesStats['percent'] < 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }}">
                <i class="bi-{{ $salesStats['percent'] < 0 ? 'graph-down' : 'graph-up' }}"></i>
                {{ $salesStats['percent'] }}%
                </span>
                <span class="text-body fs-6 ms-1">from ₹{{ $salesStats['previous'] }}</span>
              </div>
            </a>
          </div>
          <!-- COD Revenue -->
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <a class="card card-hover-shadow h-100" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">REVENUE COD</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-6">
                    <h2 class="card-title text-inherit">₹{{ $codData['current'] }}</h2>
                  </div>
                  <div class="col-6">
                    <div class="chartjs-custom" style="height: 3rem;">
                      <canvas class="js-chart" data-hs-chartjs-options='{
                        "type": "line",
                        "data": {
                        "labels": @json($codData["labels"]),
                        "datasets": [{
                        "data": @json($codData["chartData"]),
                        "backgroundColor": ["rgba(55, 125, 255, 0)", "rgba(255, 255, 255, 0)"],
                        "borderColor": "#377dff",
                        "borderWidth": 2,
                        "pointRadius": 0,
                        "pointHoverRadius": 0
                        }]
                        },
                        "options": {
                        "scales": {
                        "y": {
                        "display": false
                        },
                        "x": {
                        "display": false
                        }
                        },
                        "hover": {
                        "mode": "nearest",
                        "intersect": false
                        },
                        "plugins": {
                        "tooltip": {
                        "postfix": "k",
                        "hasIndicator": true,
                        "intersect": false
                        }
                        }
                        }
                        }'>
                      </canvas>
                    </div>
                  </div>
                </div>
                <span class="badge {{ $prepaidData['percent'] < 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }}">
                <i class="bi-{{ $prepaidData['percent'] < 0 ? 'graph-down' : 'graph-up' }}"></i>
                {{ $codData['percent'] }}%
                </span>
                <span class="text-body fs-6 ms-1">from ₹{{ $codData['previous'] }}</span>
              </div>
            </a>
          </div>
          <!-- Prepaid Revenue -->
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <a class="card card-hover-shadow h-100" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">REVENUE PREPAID</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-6">
                    <h2 class="card-title text-inherit">₹{{ $prepaidData['current'] }}</h2>
                  </div>
                  <div class="col-6">
                    <div class="chartjs-custom" style="height: 3rem;">
                      <canvas class="js-chart" data-hs-chartjs-options='{
                        "type": "line",
                        "data": {
                        "labels": @json($prepaidData["labels"]),
                        "datasets": [{
                        "data": @json($prepaidData["chartData"]),
                        "backgroundColor": ["rgba(55, 125, 255, 0)", "rgba(255, 255, 255, 0)"],
                        "borderColor": "#377dff",
                        "borderWidth": 2,
                        "pointRadius": 0,
                        "pointHoverRadius": 0
                        }]
                        },
                        "options": {
                        "scales": {
                        "y": {
                        "display": false
                        },
                        "x": {
                        "display": false
                        }
                        },
                        "hover": {
                        "mode": "nearest",
                        "intersect": false
                        },
                        "plugins": {
                        "tooltip": {
                        "postfix": "k",
                        "hasIndicator": true,
                        "intersect": false
                        }
                        }
                        }
                        }'>
                      </canvas>
                    </div>
                  </div>
                </div>
                <span class="badge {{ $prepaidData['percent']< 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }}">
                <i class="bi-{{ $prepaidData['percent'] < 0 ? 'graph-down' : 'graph-up' }}"></i>
                {{ $prepaidData['percent'] }}%
                </span>
                <span class="text-body fs-6 ms-1">from ₹{{ $prepaidData['previous'] }}</span>
              </div>
            </a>
          </div>
        </div>
        <div class="row">
          <!-- Shipment Status Overview -->
          <div class="col-lg-6 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Shipment Status Overview</h4>
              </div>
              <div class="card-body">
                <!-- Tabs Navigation -->
                <div class="d-flex justify-content-end mb-3">
                  <ul class="nav nav-segment" id="shipmentStatusTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" href="javascript:;" data-week="this">This Week</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="javascript:;" data-week="last">Last Week</a>
                    </li>
                  </ul>
                </div>

                <!-- Pie Chart -->
                <div class="chartjs-custom mb-3" style="width: 335px; height: 330px">
                  <canvas id="shipmentStatusPieChart" width="335" height="330"></canvas>
                </div>

                <!-- No Data Found Message -->
               <div id="noDataShipmentMessage"
                    class="position-absolute top-50 start-50 translate-middle text-center d-none">
                  <h3 class="text-muted">No data found.</h3>
              </div>


                <!-- Legend container (below the canvas) -->
                <div id="shipmentLegend" class="d-flex flex-wrap justify-content-center mt-auto pt-3"></div>
              </div>

            </div>
          </div>
          <!-- Courier Status Overview -->
          <div class="col-lg-6 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Courier Status Overview</h4>
              </div>
             <div class="card-body">
  <!-- Tabs Navigation -->
  <div class="d-flex justify-content-end mb-3">
    <ul class="nav nav-segment" id="courierStatusTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="javascript:;" data-bs-toggle="tab">This month</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;" data-bs-toggle="tab">Last month</a>
      </li>
    </ul>
  </div>

  <!-- Pie Chart -->
  <div class="chartjs-custom mb-3 mb-sm-2" style="width: 335px; height: 330px;">
    <canvas id="courierStatusPieChart" width="335" height="330"></canvas>
  </div>

  <!-- No data fallback -->
  <div id="noDataMessage" class="text-center" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <h3 class="text-muted">No data found.</h3>
  </div>

  <!-- Legend container -->
  <div id="courierLegend" class="d-flex flex-wrap justify-content-center"></div>
</div>

            </div>
          </div>
        </div>
        <!-- Shipping and Delivery Chart -->
        <div class="row mt-4">
          <div class="col-lg-12">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Shipping and Delivery</h4>
              </div>
              <div class="card-body">
                <div class="row align-items-sm-center mb-4">
                  <div class="col-sm mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                      <span class="h5 text-muted">Delivered Revenue ₹{{ number_format($deliveredTotals, 0) }}</span>
                    </div>
                  </div>
                  <div class="col-sm-auto">
                    <div class="row font-size-sm">
                      <div class="col-auto"><span class="legend-indicator bg-primary"></span> Shipping</div>
                      <div class="col-auto"><span class="legend-indicator bg-info"></span> Delivery</div>
                    </div>
                  </div>
                </div>
                <div class="chartjs-custom" style="height: 18rem;">
                  <canvas id="project" class="js-chart"
                    data-hs-chartjs-options='{
                    "type": "line",
                    "data": {
                    "labels": {!! json_encode($months) !!},
                    "datasets": [
                    {
                    "data": {!! json_encode($orderCounts) !!},
                    "backgroundColor": "transparent",
                    "borderColor": "#377dff",
                    "borderWidth": 2,
                    "pointRadius": 0,
                    "hoverBorderColor": "#377dff",
                    "pointBackgroundColor": "#377dff",
                    "pointBorderColor": "#fff",
                    "pointHoverRadius": 0,
                    "tension": 0.4
                    },
                    {
                    "data": {!! json_encode($deliveredCounts) !!},
                    "backgroundColor": "transparent",
                    "borderColor": "#00c9db",
                    "borderWidth": 2,
                    "pointRadius": 0,
                    "hoverBorderColor": "#00c9db",
                    "pointBackgroundColor": "#00c9db",
                    "pointBorderColor": "#fff",
                    "pointHoverRadius": 0,
                    "tension": 0.4
                    }
                    ]
                    },
                    "options": {
                    "scales": {
                    "y": {
                    "grid": {
                    "color": "#e7eaf3",
                    "drawBorder": false,
                    "zeroLineColor": "#e7eaf3"
                    },
                    "ticks": {
                    "min": 0,
                    "max": 100,
                    "stepSize": 100,
                    "color": "#97a4af",
                    "font": {
                    "family": "Open Sans, sans-serif"
                    },
                    "padding": 10
                    }
                    },
                    "x": {
                    "grid": {
                    "display": false,
                    "drawBorder": false
                    },
                    "ticks": {
                    "color": "#97a4af",
                    "font": {
                    "size": 12,
                    "family": "Open Sans, sans-serif"
                    },
                    "padding": 5
                    }
                    }
                    },
                    "plugins": {
                    "tooltip": {
                    "postfix": " Count",
                    "hasIndicator": true,
                    "mode": "index",
                    "intersect": false,
                    "lineMode": true,
                    "lineWithLineColor": "rgba(19, 33, 68, 0.075)"
                    }
                    },
                    "hover": {
                    "mode": "nearest",
                    "intersect": true
                    }
                    }
                    }'>
                  </canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="nav-table" role="tabpanel" aria-labelledby="nav-table-tab">
        <div class="row mt-4">
          <div class="col-lg-12 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Order Overview</h4>
              </div>
             <div class="card">
                <div class="table-responsive datatable-custom position-relative">
                  <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                      "columnDefs": [{"targets": [0, 7],"orderable": false}],
                      "order": [],
                      "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
                      "search": "#datatableSearch",
                      "entries": "#datatableEntries",
                      "pageLength": 15,
                      "isResponsive": false,
                      "isShowPaging": false,
                      "pagination": "datatablePagination"
                    }'>
                    <thead class="table-light">
                      <tr>
                        <th>Sales Channel</th>
                        <th>New</th>
                        <th>Ready to Ship</th>
                        <th>Shipped</th>
                        <th>Completed</th>
                        <th>Cancelled</th>
                        <th>Hold</th>
                        <th class="text-end">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(count($groupedData) > 0)
                        @foreach ($groupedData as $channel => $data)
                          <tr>
                            <td>{{ ucfirst($channel) }}</td>
                            <td>{{ $data['N'] ?? 0 }}</td>
                            <td>{{ $data['P'] ?? 0 }}</td>
                            <td>{{ $data['S'] ?? 0 }}</td>
                            <td>{{ $data['C'] ?? 0 }}</td>
                            <td>{{ $data['F'] ?? 0 }}</td>
                            <td>{{ $data['H'] ?? 0 }}</td>
                            <td class="text-end">{{ $data['total'] ?? 0 }}</td>
                          </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="8" class="text-center py-5">
                            No data found
                          </td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-12 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">State Overview</h4>
              </div>
             <div class="card">
                <div class="table-responsive datatable-custom position-relative">
                  <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                      "columnDefs": [{"targets": [0, 7], "orderable": false}],
                      "order": [],
                      "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
                      "search": "#datatableSearch",
                      "entries": "#datatableEntries",
                      "pageLength": 15,
                      "isResponsive": false,
                      "isShowPaging": false,
                      "pagination": "datatablePagination"
                    }'>
                    <thead class="table-light">
                      <tr>
                        <th>States</th>
                        <th>COD</th>
                        <th>Prepaid</th>
                        <th>Orders</th>
                        <th>₹ Revenue</th>
                        <th class="text-end">Revenue%</th>
                      </tr>
                    </thead>
                    <tbody id="stateStatsTableBody">
                      @if($stateStatsCollection->count() > 0)
                        @foreach ($stateStatsCollection->take(10) as $data)
                          <tr class="state-row">
                            <td>{{ $data['state_name'] }}</td>
                            <td>{{ $data['cod'] }}</td>
                            <td>{{ $data['prepaid'] }}</td>
                            <td>{{ $data['total_orders'] }}</td>
                            <td>₹{{ number_format($data['revenue'], 0) }}</td>
                            <td class="text-end">{{ $data['revenue_percent'] }}%</td>
                          </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="6" class="text-center py-5 text-muted">No data found</td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-12 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Top 10 Customers</h4>
              </div>
             <div class="card">
                <div class="table-responsive datatable-custom position-relative">
                  <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                      "columnDefs": [{"targets": [0, 7], "orderable": false}],
                      "order": [],
                      "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
                      "search": "#datatableSearch",
                      "entries": "#datatableEntries",
                      "pageLength": 15,
                      "isResponsive": false,
                      "isShowPaging": false,
                      "pagination": "datatablePagination"
                    }'>
                    <thead class="table-light">
                      <tr>
                        <th>Customer Name</th>
                        <th class="text-end">Order Count</th>
                        <th class="text-end">₹ Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if (count($topProducts) > 0)
                        @foreach ($topCustomers as $data)
                          <tr>
                            <td>{{ $data['customer_name'] }}</td>
                            <td class="text-end">{{ $data['order_count'] }}</td>
                            <td class="text-end">₹{{ number_format($data['revenue'], 0) }}</td>
                          </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="3" class="text-center py-5 text-muted">No data found</td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-12 mb-3 mb-lg-0">
            <div class="card h-100">
              <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">Top 10 Products</h4>
              </div>
             <div class="card">
              <div class="table-responsive datatable-custom position-relative">
                <table id="datatable" class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                  data-hs-datatables-options='{
                    "columnDefs": [{"targets": [0, 7], "orderable": false}],
                    "order": [],
                    "info": {"totalQty": "#datatableWithPaginationInfoTotalQty"},
                    "search": "#datatableSearch",
                    "entries": "#datatableEntries",
                    "pageLength": 15,
                    "isResponsive": false,
                    "isShowPaging": false,
                    "pagination": "datatablePagination"
                  }'>
                  <thead class="table-light">
                    <tr>
                      <th>Product Name</th>
                      <th class="text-end">Unit Sold</th>
                      <th class="text-end">₹Revenue</th>
                    </tr>
                  </thead>
                 <tbody>
                    @if (count($topProducts) > 0)
                      @foreach ($topProducts as $product)
                        <tr>
                          <td>{{ $product['product_name'] }}</td>
                          <td class="text-end">{{ $product['units_sold'] }}</td>
                          <td class="text-end">₹{{ $product['revenue'] }}</td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="3" class="text-center py-5 text-muted">No data found</td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Tab Content -->
  </x-slot>
</x-layout>
<script>
  (function () {
    const labels = {!! json_encode($Piechart['labels']) !!};
    const thisWeekData = {!! json_encode($Piechart['thisWeekData']) !!};
    const lastWeekData = {!! json_encode($Piechart['lastWeekData']) !!};

    const datasets = [thisWeekData, lastWeekData];
    const backgroundColors = [
      '#377dff', '#00c9db', '#ff5733',
      '#4338ca', '#dc3545', '#28a745', '#fd7e14'
    ];

    const ctx = document.getElementById('shipmentStatusPieChart').getContext('2d');
    const noDataShipmentMessage = document.getElementById('noDataShipmentMessage');

    // Check if any data is available
    function checkDataAvailability(data) {
      return data && data.some(value => value > 0);
    }

    // Toggle the "No Data" message visibility
    function toggleNoDataMessage(data) {
      if (checkDataAvailability(data)) {
        noDataShipmentMessage.classList.add('d-none');
      } else {
        noDataShipmentMessage.classList.remove('d-none');
      }
    }

    // Initial chart and message setup
    toggleNoDataMessage(thisWeekData);

    const shipmentChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: checkDataAvailability(thisWeekData) ? thisWeekData : [],
          backgroundColor: backgroundColors,
          borderWidth: 5,
          hoverBorderColor: "#fff"
        }]
      },
      options: {
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || '';
                const value = context.raw || 0;
                return `${label}: ${value} shipments`;
              }
            }
          },
          legend: {
            display: false
          }
        }
      }
    });

    // Custom legend renderer
    function renderCustomLegend(labels, colors, data) {
      const legendContainer = document.getElementById('shipmentLegend');
      legendContainer.innerHTML = '';

      if (checkDataAvailability(data)) {
        labels.forEach((label, i) => {
          const value = data[i];
          const color = colors[i];

          const item = document.createElement('div');
          item.className = 'd-flex align-items-center me-4 mb-2';
          item.innerHTML = `
            <span class="rounded-circle me-2" style="width:10px; height:10px; background-color:${color}; display:inline-block;"></span>
            <span>${label}</span>
          `;
          legendContainer.appendChild(item);
        });
      } else {
        legendContainer.innerHTML = '';
      }

      toggleNoDataMessage(data);
    }

    // Render legend for initial data
    renderCustomLegend(labels, backgroundColors, thisWeekData);

    // Handle tab switching
    const weekTabs = document.querySelectorAll('#shipmentStatusTab .nav-link');
    weekTabs.forEach(tab => {
      tab.addEventListener('click', function () {
        weekTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const week = this.getAttribute('data-week');
        const newData = week === 'this' ? thisWeekData : lastWeekData;

        // Update chart data
        shipmentChart.data.datasets[0].data = checkDataAvailability(newData) ? newData : [];
        shipmentChart.update();

        // Update legend and no data message
        renderCustomLegend(labels, backgroundColors, newData);
      });
    });
  })();
</script>

<script>
(function () {
  const thisMonthData = {!! json_encode($thisMonth) !!};
  const lastMonthData = {!! json_encode($lastMonth) !!};
  const labels = thisMonthData.map(item => 
  item.courier_code.charAt(0).toUpperCase() + item.courier_code.slice(1)
);

  const backgroundColors = [
    '#377dff', '#00c9db', '#ff5733',
    '#4338ca', '#dc3545', '#28a745', '#fd7e14'
  ];

  let currentData = thisMonthData;

  const ctx = document.getElementById('courierStatusPieChart').getContext('2d');
  const noDataText = document.getElementById('noDataMessage');
  const chartWrapper = document.getElementById('courierStatusPieChart').parentElement;
  const legendContainer = document.getElementById('courierLegend');

  let courierChart;

  function renderChart(dataSet) {
    if (courierChart) {
      courierChart.destroy();
    }

    courierChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: dataSet.map(item => item.delivered_orders),
          backgroundColor: backgroundColors,
          borderWidth: 5,
          hoverBorderColor: "#fff"
        }]
      },
      options: {
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                const index = context.dataIndex;
                const label = labels[index];
                const totalOrders = dataSet[index].total_orders;
                const deliveryPercent = Math.floor(dataSet[index].delivery_percentage);

                return ` ${label}: ${totalOrders} orders (${deliveryPercent}% delivered)`;
              }
            }
          },
          legend: {
            display: false
          }
        }
      }
    });
  }

  function renderCustomLegend(labels, colors, data) {
    legendContainer.innerHTML = '';

    if (!data.length) return;

    labels.forEach((label, i) => {
      const value = data[i].delivered_orders;
      const color = colors[i];

      const item = document.createElement('div');
      item.className = 'd-flex align-items-center me-4 mb-2';
      item.innerHTML = `
        <span class="rounded-circle me-2" style="width:12px; height:12px; background-color:${color}; display:inline-block;"></span>
        <span>${label}</span>
      `;
      legendContainer.appendChild(item);
    });
  }

  function updateChartAndLegend(dataSet) {
    if (!dataSet.length) {
      chartWrapper.style.display = 'none';
      noDataText.style.display = 'block';
      legendContainer.innerHTML = '';
    } else {
      chartWrapper.style.display = 'block';
      noDataText.style.display = 'none';
      renderChart(dataSet);
      renderCustomLegend(labels, backgroundColors, dataSet);
    }
  }

  // Initial Load
  updateChartAndLegend(thisMonthData);

  // Handle Tab Switching
  const monthTabs = document.querySelectorAll('#courierStatusTab .nav-link');
  monthTabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault();

      const isThisMonth = this.textContent.trim() === 'This month';
      currentData = isThisMonth ? thisMonthData : lastMonthData;

      updateChartAndLegend(currentData);

      monthTabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
})();
</script>



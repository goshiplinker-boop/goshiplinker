
    $(function() {
        $('#js-daterangepicker-predefined').on('apply.daterangepicker', function(ev, picker) {
            const startDate = picker.startDate.format('YYYY-MM-DD');
            const endDate = picker.endDate.format('YYYY-MM-DD');
            // Get the current query string
            const urlParams = new URLSearchParams(window.location.search);
            // Update or add startDate and endDate parameters
            urlParams.set('startDate', startDate);
            urlParams.set('endDate', endDate);
            // Navigate to the updated URL
            window.location.href = `orders?${urlParams.toString()}`;
        });
    });

    $('#viewManifest').on('click', function(e) {
        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedOrders.length === 0) {
            alert('Please select at least one manifest');
            return;
        }
        // Encode order IDs to avoid issues with special characters
        const encodedOrderIds = encodeURIComponent(selectedOrders.join(','));
        window.location.href = `manifest/view_manifest?manifest_ids=${encodedOrderIds}`;
    });

    $('#downloadShippingLabel').on('click', function(e) {
        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedOrders.length === 0) {
            alert('Please select at least one order');
            return;
        }
        // Encode order IDs to avoid issues with special characters
        const encodedOrderIds = encodeURIComponent(selectedOrders.join(','));
        window.location.href = `orders/shipping?order_ids=${encodedOrderIds}`;
    });

    $('#downloadCombinedPdf').on('click', function(e) {
        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedOrders.length === 0) {
            alert('Please select at least one order');
            return;
        }
        const encodedOrderIds = encodeURIComponent(selectedOrders.join(','));
        window.location.href = `orders/labelInvoice?order_ids=${encodedOrderIds}`;
    });

    $('#downloadInvoice').on('click', function(e) {
        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();
        if (selectedOrders.length === 0) {
            alert('Please select at least one order');
            return;
        }
        console.log('Selected Orders:', selectedOrders);
        const encodedOrderIds = encodeURIComponent(selectedOrders.join(','));
        window.location.href = `orders/invoice?order_ids=${encodedOrderIds}`;
    });

    $('.pickup_create').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const manifest_id = $(this).attr('manifest_id');
        const courier_id = $(this).attr('courier_id');
        const pickup_location_id = $(this).attr('pickup_location_id');
        $.ajax({
            url: routes.pickup_create,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                manifest_id: manifest_id,
                courier_id: courier_id,
                pickup_location_id: pickup_location_id,
                _token: csrfToken
            }),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
            },
        });
    });

    $('#is_return_pickup_location').on('change', function () {
        const isChecked = $(this).is(':checked');
        $(this).val(isChecked ? "1" : "0");
        
        if (isChecked) {
            $('.return_pickup').hide();
        } else {
            $('.return_pickup').show();
        }
    });
    $('#syncOrders').on('click',function(e){
        $('#syncOrders').html(
            '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Syncing Order....</span></div>');

    });
   
    $('#assign_tracking_number,#print_response').on('click', function(e) {
        e.preventDefault(); // Prevent default behavior if the button is inside a form.
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        // Collect selected orders
        var print_response = $(this).attr('response') || ''; 
        var pickup_location_id = $('#pickup_location_id').val();
        if (!pickup_location_id) {
            alert('Please select pickup location.');
            return;
        }

        var return_pickup_location_id = $('#return_pickup_location_id').val();        

        if (!return_pickup_location_id) {
            alert('Please select return pickup location.');
            return;
        }

        var courier_id = $('#courier_id').val();
        if (!courier_id) {
            alert('Please select courier.');
            return;
        }

        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedOrders.length === 0) {
            alert('Please select at least one order before submitting.');
            return;
        }
        if(print_response==''){
            $('#assign_tracking_number').html(
                '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Booking Order....</span></div>');
        }else{
            $('#print_response').html(
                '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Booking Order....</span></div>');
        }
       
        // AJAX request to submit the selected orders
        $.ajax({
            url: routes.shiporders,
            method: 'POST',
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: {
                order_ids: selectedOrders,
                courier_id: courier_id,
                pickup_location_id: pickup_location_id,
                return_pickup_location_id: return_pickup_location_id,
                print_response: print_response,
                _token: csrfToken
            },
            success: function(response) {
                if (response.print_response) {
                    $('#print_response').html('<i class="bi bi-truck"></i>Print Request Response');
                    var offcanvasElement = document.querySelector('#BulkShipOrders'); // Replace with your offcanvas ID
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement); // Get the Bootstrap instance

                    if (offcanvas) {
                        offcanvas.hide(); // This will close the offcanvas
                    }
                    
                    // Convert JSON object to string with indentation
                    const jsonString = JSON.stringify(response.print_response, null, 4);
            
                    // Display it inside the modal <pre> element
                    document.getElementById('jsonContent').textContent = jsonString;
            
                    // Show the Bootstrap modal
                    var myModal = new bootstrap.Modal(document.getElementById('jsonModal'));
                    myModal.show();
                } else {
                    location.reload();
                }

            },
            error: function(xhr) {
                $('#assign_tracking_number').html('<i class="bi bi-truck"></i>Ship Now');
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr); // Optional: Log error for debugging
            },
        });
    });
    $('#calculate_shipping').on('click', function(e) {
        e.preventDefault(); // Prevent default behavior if the button is inside a form.
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        // Collect selected orders
        var print_response = $(this).attr('response') || ''; 
        var pickup_location_id = $('#pickup_location_id').val();
        if (!pickup_location_id) {
            alert('Please select pickup location.');
            return;
        }

        var return_pickup_location_id = $('#return_pickup_location_id').val();        

        if (!return_pickup_location_id) {
            alert('Please select return pickup location.');
            return;
        }

        var courier_id = $('#courier_id').val();
        if (!courier_id) {
            alert('Please select courier.');
            return;
        }

        const selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedOrders.length === 0) {
            alert('Please select at least one order before submitting.');
            return;
        }
        
        $('#calculate_shipping').html(
                '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Calculating Shipping....</span></div>');
        
        var html='';
        // AJAX request to submit the selected orders
        $.ajax({
            url: routes.calculate_shipping,
            method: 'POST',
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: {
                order_ids: selectedOrders,
                courier_id: courier_id,
                pickup_location_id: pickup_location_id,
                return_pickup_location_id: return_pickup_location_id,
                _token: csrfToken
            },
            success: function(response) {   
                if(response.success){
                    html='<div class="text-start alert alert-soft-danger alert-dismissible mt-3" role="alert">'+response.message+'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $('#calculate_shipping').after(html);
                }else if(response.error){
                    html='<div class="text-start alert alert-warning alert-dismissible mt-3" role="alert">'+response.error+'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $('#calculate_shipping').after(html);
                } 
                $('#calculate_shipping').html('<i class="bi bi-truck"></i>Calculate Shipping');             

            },
            error: function(xhr) {
                $('#calculate_shipping').html('<i class="bi bi-truck"></i>Calculate Shipping');
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr); // Optional: Log error for debugging
            },
        });
    });
    $('#bulk_manifest_create,.manifest_create').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        const order_id = $(this).attr('order_id');   
        
        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }

        $.ajax({
            url: routes.create_manifest,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: selectedOrders,
                _token: csrfToken
            }),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
            },
        });
    });
    $('#bulk_unassign_orders,.unassign_orders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        const order_id = $(this).attr('order_id');   
        
        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }
        if (confirm('Are you sure, Do you want to proceed?')) {
            $.ajax({
                url: routes.unassign_order,
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                data: JSON.stringify({
                    order_ids: selectedOrders,
                    _token: csrfToken
                }),
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Failed to submit orders.');
                },
            });
        }
    });

    $('#cancelOrders,.cancelOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();
        const order_id = $(this).attr('order_id');   
        
        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }
        $.ajax({
            url: routes.cancelOrders,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: selectedOrders,
                status_code: 'C',
                _token: csrfToken
            }),

            success: function(response) {
                // console.log(response);
                location.reload();
            },
            error: function(xhr) {
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr); // Optional: Log error for debugging
            },
        });
    });

    $('#archiveOrders,.archiveOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        const order_id = $(this).attr('order_id');   
        
        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }
        $.ajax({
            url:routes.archiveOrders,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: selectedOrders,
                status_code: 'A',
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });
    $('#completedOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();
        const order_id = $(this).attr('order_id');   

        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }
        $.ajax({
            url: routes.completedOrders,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: selectedOrders,
                status_code: 'F',
                _token: csrfToken
            }),

            success: function(response) {
                // console.log(response);
                location.reload();
            },
            error: function(xhr) {
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr); // Optional: Log error for debugging
            },
        });
    });
    $('#shippedOrders,.shippedOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();
        const manifest_id = $(this).attr('data-manifest-id');   
        
        if ((!manifest_id || manifest_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one manifest.');
            return;
        }
        if(selectedOrders.length ===0 && manifest_id){
            selectedOrders = manifest_id;
        }
        $.ajax({
            url: routes.shippedOrders,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                manifest_ids: selectedOrders,
                status_code: 'S',
                _token: csrfToken
            }),

            success: function(response) {
                // console.log(response);
                location.reload();
            },
            error: function(xhr) {
                // Handle error response
                alert('Failed to submit orders.');
                console.error(xhr); // Optional: Log error for debugging
            },
        });
    });
    $('#onholdOrders,.onholdOrders').on('click', function(e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        var selectedOrders = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        const order_id = $(this).attr('order_id');   
        
        if ((!order_id || order_id.trim() === '') && selectedOrders.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        if(selectedOrders.length ===0 && order_id){
            selectedOrders = order_id;
        }
        $.ajax({
            url:routes.onholdOrders,
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            data: JSON.stringify({
                order_ids: selectedOrders,
                status_code: 'H',
                _token: csrfToken
            }),

            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to submit orders.');
                console.error(xhr);
            },
        });
    });

    function downloadCSV() {
        // Collect selected order IDs
        const downloadButton = document.getElementById('downloadCSVButton');
        downloadButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Downloading CSV...</span></div>';

        const selectedOrderIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
            .map(checkbox => checkbox.value); // Assuming checkboxes have order IDs as their value
    
        if (selectedOrderIds.length === 0) {
            alert('No orders selected.');
            downloadButton.innerHTML = '<i class="bi bi-download"></i>';
            return;
        }
    
        // Retrieve the value of selectAllInput
        const allSelect = document.getElementById('selectAllInput')?.value;
    
        // Prepare the data to be sent in the POST request
        let postData = {};

        function isEmpty(obj) {
            return Object.keys(obj).length === 0;
        }

        if (allSelect === 'true') {
            if (typeof orderFilters !== 'undefined') {
                const { token, ...orderFiltersWithoutToken } = orderFilters;
                postData = orderFiltersWithoutToken;
                if (isEmpty(postData)) {
                    postData.tab = 'new';
                }
            } else {
                console.error('orderFilters is not defined.');
                return;
            }
        } else {
            postData.selectedOrderIds = selectedOrderIds;
        }
    
        // Send a POST request to the server to export the selected orders
        fetch(routes.export_orders, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(postData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Failed to export orders.");
                }
                const contentDisposition = response.headers.get("Content-Disposition");
                let fileName = "orders.csv"; // Default filename
                if (contentDisposition) {
                    const match = contentDisposition.match(/filename="?([^"]+)"?/);
                    if (match && match[1]) {
                        fileName = match[1];
                    }
                }
    
                return response.blob().then(blob => ({
                    blob,
                    fileName
                }));
            })
            .then(({
                blob,
                fileName
            }) => {
                // Create a downloadable link for the file
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.style.display = "none";
                a.href = url;
                a.download = fileName; // Use dynamic filename
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                downloadButton.innerHTML = '<i class="bi bi-download"></i>';
            })
            .catch(error => {
                console.error("Error exporting orders:", error);
                alert("There was an error exporting the selected orders.");
                downloadButton.innerHTML = '<i class="bi bi-download"></i>';

            });
    }
    

    // Event listener for delete button click
$(document).on('click', '#deleteBtn', function (e) {
        e.preventDefault();
        // Fetch the manifest ID from the button's data attribute
        var manifestId = $(this).data('manifest-id');
        var token = document.querySelector('meta[name="csrf-token"]').content;

        // Confirm delete action
        if (confirm('Are you sure you want to delete this manifest?')) {
            // AJAX call to delete manifest
            $.ajax({
                url: routes.manifest_delete, // Backend route
                method: 'POST',
                data: {
                    _token: token, // CSRF token
                    manifest_id: manifestId // Manifest ID
                },
                success: function(response) {
                    // On success, reload the page
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Failed to delete manifest: ' + error);
                }
            });
        }
     });
    

// Use event delegation for dynamically created delete buttons
$(document).off('click', '.manifestOrderDelete').on('click', '.manifestOrderDelete', function (e) {
    e.preventDefault();

    // Get attributes directly from the clicked button
    var manifestId = $(this).data('manifest-id');
    var orderId = $(this).data('order-id');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Confirmation before deletion
    if (confirm('Are you sure you want to delete this manifest order?')) {
        $.ajax({
            url: routes.delete_manifest_order,
            method: 'POST',
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: {
                manifest_id: manifestId,
                order_id: orderId,
                _token: csrfToken
            },
            success: function (response) {
                // Reload the page after successful deletion
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('Failed to delete manifest: ' + error);
            }
        });
    }
});

$(document).ready(function() {
    // On click of 'Create Order' dropdown item
    $('#createOrderBtn').click(function(e) {
        e.preventDefault(); // Prevent default action

        // Show loader while waiting for response
        $('#loader').show();

        $.ajax({
            url:routes.create_order,  // Endpoint to handle the request
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').content,
            },
            success: function(response) {
                // Hide loader when response is received
                $('#loader').hide();
                location.reload();
                // Handle any additional UI updates here if needed
                //console.log(response);  // Optionally log the response
            },
            error: function(xhr, status, error) {
                // Hide loader in case of error
                $('#loader').hide();

                alert('Error occurred: ' + error);  // Show error message
            }
        });
    });
});
$('.view_manifest_orders').on('click', function(e) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const manifestId = this.getAttribute('data-manifest-id');
    const modalBody = document.getElementById('ordersModalBody');
    modalBody.innerHTML = ''; // Clear existing rows
    $('#ordersModalLabel').text("Manifest # "+manifestId);
    $.ajax({
        url: routes.manifest_orders,
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        data: JSON.stringify({
            manifest_id: manifestId,
            _token: csrfToken
        }),
        success: function(response) {
            // Populate the table dynamically
            if (response && response.length > 0) {
                response.forEach(function(manifestOrder, index) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${manifestOrder.vendor_order_number}</td>
                        <td>${manifestOrder.tracking_number || 'N/A'}</td>
                         <td>  ${manifestOrder.payment_mode.toLowerCase() =='cod'?
                                    `<span class="badge bg-primary rounded-pill">COD</span>`
                                    :
                                    `<span class="badge bg-success rounded-pill">Prepaid</span>` 
                                }
                        </td>     
                        <td>
                            ${manifestOrder.current_status === null
                                    ? `<span class="badge btn-soft-dark text-body">
                                        <span class="legend-indicator bg-dark"></span>${manifestOrder.status_name}
                                      </span>`
                                    :  `<span class="badge btn-soft-dark text-body">
                                    <span class="legend-indicator bg-dark"></span>${manifestOrder.shipment_status}
                                  </span>`
                            }
                        </td>
                        <td class="text-center">
                            ${
                                manifestOrder.pickup_created === 0
                                    ? `<button class="btn btn-soft-danger btn-sm manifestOrderDelete" 
                                            data-manifest-id="${manifestId}"
                                            data-order-id="${manifestOrder.order_id}">
                                            <i class="bi bi-trash"></i>
                                       </button>`
                                    : 'N/A'
                            }
                        </td>
                    `;
                    modalBody.appendChild(row);
                });
            } else {
                modalBody.innerHTML = '<tr><td colspan="5">No orders available for this manifest.</td></tr>';
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            alert('Failed to load manifest orders. Please try again.');
        },
    });
    
});

(function($){
    // Helper to escape values
    function esc(v){ return v == null ? '' : String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;'); }

    var modalEl = document.getElementById('updatePackageModal');
    if (!modalEl) { console.error('Modal not found: #updatePackageModal'); return; }
    var bsModal = new bootstrap.Modal(modalEl);
    var packageIdx = 0;

    function addPackageRow(pkg) {
        var tbody = $('#packageTable tbody');

        // auto-generate default code if not provided
        var nextIndex = tbody.find('tr').length + 1;
        var defaultCode = 1;

        var code = esc(pkg && pkg.package_count ? pkg.package_count : defaultCode);
        var length = esc(pkg && typeof pkg.length !== 'undefined' ? pkg.length : '');
        var breadth = esc(pkg && typeof pkg.breadth !== 'undefined' ? pkg.breadth : '');
        var height = esc(pkg && typeof pkg.height !== 'undefined' ? pkg.height : '');
        var dead_weight = esc(pkg && typeof pkg.dead_weight !== 'undefined' ? pkg.dead_weight : '');

        var tr = $('<tr>');
        tr.html(
            '<td><input type="text" class="form-control form-control-sm package_count" name="package_count[]" value="'+code+'"></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm length" name="length[]" value="'+length+'"></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm breadth" name="breadth[]" value="'+breadth+'"></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm height" name="height[]" value="'+height+'"></td>' +
            '<td><input type="number" step="0.001" class="form-control form-control-sm dead_weight" name="dead_weight[]" value="'+dead_weight+'"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow" title="Remove package"><i class="bi bi-trash"></i></button></td>'
        );
        tbody.append(tr);
    }


    function clearRows() {
        $('#packageTable tbody').empty();
        packageIdx = 0;
        $('#packageErrors').hide().text('');
    }

    // Open modal for specific order
    $(document).on('click', '.update-packages-btn', function(){
        var orderId = $(this).data('order-id');
        var jsonUrl = $(this).data('packages-url');      // endpoint to fetch packages
        var saveUrl = $(this).data('packages-save-url'); // endpoint to save packages

        if (!orderId) { alert('Order ID missing'); return; }

        $('#orderId').val(orderId);
        $('#savePackagesBtn').data('save-url', saveUrl);

        clearRows();
        addPackageRow(null); // placeholder row while fetching

        $.ajax({
            url: jsonUrl,
            method: 'GET',
            dataType: 'json',
            headers: {'Accept': 'application/json'},
            success: function(res){
                clearRows();
                var pkgs = Array.isArray(res.packages) ? res.packages : [];
                if (pkgs.length === 0){
                    addPackageRow(null);
                } else {
                    pkgs.forEach(function(p){ addPackageRow(p); });
                }
                $('#updatePackageModalTitle').text('Update Packages for Order #' + orderId);
                bsModal.show();
            },
            error: function(xhr){
                console.error('Failed to load packages', xhr.status, xhr.responseText ? xhr.responseText.slice(0,200) : '');
                clearRows();
                addPackageRow(null);
                $('#updatePackageModalTitle').text('Update Packages for Order #' + orderId);
                bsModal.show();
            }
        });
    });

    // Add new package row
    $(document).on('click', '#addPackageBtn', function(){
        addPackageRow(null);
    });

    // Remove row (keep at least one)
    $(document).on('click', '.removeRow', function(){
        var tbody = $('#packageTable tbody');
        var rowCount = tbody.find('tr').length;

        if (rowCount <= 1) {
            alert('At least one package row must remain.');
            return;
        }

        $(this).closest('tr').remove();
    });

    // Save packages
    $(document).on('click', '#savePackagesBtn', function(){
        $('#packageErrors').hide().text('');
        var saveUrl = $(this).data('save-url');
        var orderId = $('#orderId').val();
        if (!orderId || !saveUrl) { $('#packageErrors').show().text('Missing order id or save URL'); return; }

        var packages = [];
        var hasError = false;

        $('#packageTable tbody tr').each(function(){
            var $tr = $(this);
            var pkg = {
                package_count: $tr.find('.package_count').val().trim(),
                length: $tr.find('.length').val().trim(),
                breadth: $tr.find('.breadth').val().trim(),
                height: $tr.find('.height').val().trim(),
                dead_weight: $tr.find('.dead_weight').val().trim()
            };

            // Validation
            if (pkg.length === '' || isNaN(pkg.length) || Number(pkg.length) < 0) { $tr.find('.length').addClass('is-invalid'); hasError=true; } else $tr.find('.length').removeClass('is-invalid');
            if (pkg.breadth === '' || isNaN(pkg.breadth) || Number(pkg.breadth) < 0) { $tr.find('.breadth').addClass('is-invalid'); hasError=true; } else $tr.find('.breadth').removeClass('is-invalid');
            if (pkg.height === '' || isNaN(pkg.height) || Number(pkg.height) < 0) { $tr.find('.height').addClass('is-invalid'); hasError=true; } else $tr.find('.height').removeClass('is-invalid');
            if (pkg.dead_weight === '' || isNaN(pkg.dead_weight) || Number(pkg.dead_weight) < 0) { $tr.find('.dead_weight').addClass('is-invalid'); hasError=true; } else $tr.find('.dead_weight').removeClass('is-invalid');

            packages.push(pkg);
        });

        if (hasError) { $('#packageErrors').show().text('Please correct highlighted fields'); return; }

        var token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: saveUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ packages: packages }),
            headers: { 'X-CSRF-TOKEN': token || '' },
            success: function(res){ location.reload(); },
            error: function(xhr){
                console.error('Save error', xhr.status, xhr.responseText ? xhr.responseText.slice(0,500) : '');
                $('#packageErrors').show().text('Failed to save packages');
            }
        });
    });

})(jQuery);
<?php
// track-order.php - Simplified Customer Order Tracking
session_start();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - Solar Power Energy</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Keeping your existing variables and core styles */
        :root {
            --primary-color: #f39c12;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
        }

        body { background-color: #f8f9fa; }
        .track-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        .track-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 30px; margin: 30px 0; }
        
        /* New Tab Styling to match your reference image */
        .nav-tabs { 
            border-bottom: 2px solid #eee; 
            margin-bottom: 0; 
        }
        
        .nav-link { 
            color: #666 !important; /* Force gray for inactive */
            font-weight: 600; 
            border: none !important; 
            padding: 15px 20px; 
            transition: 0.3s; 
        }
        
        /* Fix Hover Color */
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        /* Fix Active Tab Color and Underline */
        .nav-link.active { 
            color: var(--primary-color) !important; 
            border-bottom: 3px solid var(--primary-color) !important; 
            background: none !important; 
        }
        
        .status-badge { 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            color: #f39c12; 
            background: rgba(243, 156, 18, 0.1); /* Adds a light orange background */
            padding: 4px 10px;
            border-radius: 50px;
        }
        
        .search-inner-bar { background: #f1f1f1; padding: 10px 20px; border-bottom: 1px solid #eee; }
        .order-item-row { border-bottom: 1px solid #eee; padding: 20px; transition: 0.2s; cursor: pointer; }
        .order-item-row:hover { background: #fcfcfc; }
        .status-badge { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #f39c12; }
        
        .btn-track { background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 10px; width: 100%; font-weight: 600; }
        .loading, .error-message { text-align: center; padding: 20px; display: none; }
    </style>
</head>
<body>
    
        <?php include "includes/header.php" ?>

    <section style="margin: 120px 0;">
    <div class="track-container">
        <div class="track-card" id="searchCard">
            <div class="text-center mb-4">
                <h1 class="fw-bold"><i class="fas fa-shipping-fast text-warning"></i> Track My Orders</h1>
                <p class="text-muted">Enter your cellphone number to view your order history</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-2">Cellphone Number</label>
                        <input type="tel" id="customerPhone" class="form-control form-control-lg text-center" placeholder="e.g. +639805926760">
                    </div>
                    <button class="btn-track" onclick="trackOrders()"><i class="fas fa-search"></i> TRACK MY ORDERS</button>
                </div>
            </div>
        </div>

        <div id="resultSection" style="display:none;">
            <div class="track-card p-0 overflow-hidden">
                <ul class="nav nav-tabs nav-fill" id="orderTabs">
                    <li class="nav-item"><a class="nav-link active" href="#" onclick="filterOrders('all')">All</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="filterOrders('pending')">To Pay</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="filterOrders('confirmed')">To Ship</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="filterOrders('in_transit')">To Receive</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="filterOrders('delivered')">Completed</a></li>
                </ul>

                <div class="search-inner-bar">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-0 bg-transparent" placeholder="Search by Order ID...">
                    </div>
                </div>

                <div id="ordersList">
                    </div>
            </div>

            <div class="text-center">
                <button class="btn btn-sm btn-link text-muted" onclick="location.reload()">Track another number</button>
            </div>
        </div>

        <div id="loadingState" class="loading"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div id="errorMessage" class="error-message alert alert-danger"></div>
    </div>
    </section>

    <script>
        let ordersData = [];

        function trackOrders() {
            const phone = document.getElementById('customerPhone').value.trim();
            if(!phone) return alert("Please enter your cellphone number");

            document.getElementById('searchCard').style.display = 'none';
            document.getElementById('loadingState').style.display = 'block';

            fetch(`controllers/customer_track_order.php?phone=${encodeURIComponent(phone)}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('loadingState').style.display = 'none';
                    if(data.success) {
                        ordersData = data.orders;
                        document.getElementById('resultSection').style.display = 'block';
                        filterOrders('all');
                    } else {
                        document.getElementById('searchCard').style.display = 'block';
                        alert(data.message || "No orders found.");
                    }
                });
        }
        
        function getStatusLabel(status) {
        if (!status) return 'Pending';
        
        const statusMap = {
            'maya_initial': 'Initial Payment',
            'maya_full': 'Full Payment',
            'down_payment': 'Down Payment',
            'pending': 'Pending',
            'confirmed': 'To Ship',
            'in_transit': 'To Receive',
            'delivered': 'Completed'
        };
        // Returns the mapped name, or capitalizes the original if not found
        return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
    }

        function filterOrders(status) {
            // Update Active Tab logic remains same...
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if(link.textContent.toLowerCase().includes(status.replace('_',' '))) link.classList.add('active');
                if(status === 'all' && link.textContent === 'All') link.classList.add('active');
            });
        
            const list = document.getElementById('ordersList');
            const filtered = status === 'all' ? ordersData : ordersData.filter(o => o.order_status === status);
        
            if(filtered.length === 0) {
                list.innerHTML = `<div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" style="width:80px; opacity:0.4">
                        <p class="text-muted mt-3">No orders yet</p>
                    </div>`;
                return;
            }
        
            list.innerHTML = filtered.map(order => `
                <div class="order-item-row">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold">${order.order_reference}</span>
                        <span class="status-badge">${getStatusLabel(order.order_status)}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">${order.items_ordered || 'Solar Product'}</h6>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i> ${order.current_location || 'Warehouse'}
                            </p>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">₱${parseFloat(order.total_amount).toLocaleString()}</div>
                            <div class="small text-muted">${getStatusLabel(order.payment_method)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>
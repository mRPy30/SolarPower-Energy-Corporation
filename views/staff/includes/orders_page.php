<div id="orders" class="page-content">
    <div class="orders-container">

        <!-- HEADER -->
        <div class="orders-header">
            <div class="orders-title">
                <h3><i class="fas fa-shopping-cart"></i> Order Check</h3>
            </div>

            <div style="display: flex; gap: 10px;">
                <button class="btn-primary" style="background-color: #3498db;" onclick="exportOrdersPDF()">
                    <i class="fas fa-file-pdf"></i> Export to PDF
                </button>
                <button class="btn-primary" style="background-color: #217346;" onclick="exportOrdersExcel()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
                <button class="btn-refresh" onclick="OrdersModule.loadOrders()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="orders-filters">
            <div class="orders-filter-group">
                <label>Search</label>
                <input type="text" id="orderSearch" placeholder="Search by customer, email or order ref">
            </div>

            <div class="orders-filter-group">
                <label>Order Status</label>
                <select id="orderStatusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="orders-filter-group">
                <label>Payment Method</label>
                <select id="paymentFilter">
                    <option value="">All Payments</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="maya_full">Maya (Full)</option>
                    <option value="unionbank">UnionBank</option>
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order Ref</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:#888;">
                            <i class="fas fa-spinner fa-spin"></i> Loading orders...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Orders PDF Export (Print Preview) -->
<script>
function exportOrdersPDF() {
    fetch('dashboard.php?ajax=1&action=fetch_orders')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No order data available to export.');
                return;
            }
            generateOrdersPrintPreview(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch order data for export.');
        });
}

function generateOrdersPrintPreview(orders) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    const totalRevenue = orders.reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
    const paidOrders = orders.filter(o => o.order_status.toUpperCase() === 'PAID').length;
    const pendingOrders = orders.filter(o => o.order_status.toUpperCase() === 'PENDING').length;
    const cancelledOrders = orders.filter(o => o.order_status.toUpperCase() === 'CANCELLED').length;

    function statusColor(status) {
        switch (status.toUpperCase()) {
            case 'PAID': return '#27ae60';
            case 'PENDING': return '#f39c12';
            case 'CANCELLED': return '#e74c3c';
            default: return '#333';
        }
    }

    let tableRows = orders.map((order, i) => {
        const status = order.order_status.toUpperCase();
        const date = new Date(order.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        const amount = 'PHP ' + parseFloat(order.total_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        return `<tr style="${i % 2 === 1 ? 'background:#f8f9fa;' : ''}">
            <td style="text-align:center;">${i + 1}</td>
            <td style="text-align:center;">${order.order_reference}</td>
            <td style="font-weight:600;">${order.customer_name}</td>
            <td style="text-align:right;">${amount}</td>
            <td style="text-align:center;">${date}</td>
            <td style="text-align:center;">${order.payment_method}</td>
            <td style="text-align:center;font-weight:700;color:${statusColor(status)}">${status}</td>
        </tr>`;
    }).join('');

    const html = `<!DOCTYPE html>
<html><head><title>SolarPower Orders Report</title>
<style>
    @page { size: landscape; margin: 10mm 14mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; font-size: 10px; }
    .accent-bar { height: 6px; background: #ffc107; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; padding: 14px 0 10px; }
    .header-left h1 { font-size: 20px; color: #2c3e50; margin-bottom: 2px; }
    .header-left p { font-size: 12px; color: #888; }
    .header-right { text-align: right; font-size: 10px; color: #888; }
    .divider { border: none; border-top: 2px solid #ffc107; margin: 8px 0 14px; }
    .summary { display: flex; gap: 10px; margin-bottom: 16px; }
    .summary-box { flex: 1; border-radius: 6px; padding: 10px 8px; text-align: center; color: #fff; }
    .summary-box .label { font-size: 8px; letter-spacing: 0.5px; opacity: 0.85; text-transform: uppercase; }
    .summary-box .value { font-size: 16px; font-weight: 700; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    thead th { background: #2c3e50; color: #fff; font-size: 10px; font-weight: 600; padding: 8px 6px; text-align: center; }
    tbody td { padding: 6px; border-bottom: 1px solid #e0e0e0; }
    .footer { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 6px; display: flex; justify-content: space-between; font-size: 8px; color: #999; }
    @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style></head><body>
<div class="accent-bar"></div>
<div class="header">
    <div class="header-left">
        <h1>SolarPower Energy Corporation</h1>
        <p>Orders Report</p>
    </div>
    <div class="header-right">
        Generated: ${dateStr} at ${timeStr}<br>
        Total Orders: ${orders.length}
    </div>
</div>
<hr class="divider">
<div class="summary">
    <div class="summary-box" style="background:#2c3e50;">
        <div class="label">Total Revenue</div>
        <div class="value">PHP ${totalRevenue.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</div>
    </div>
    <div class="summary-box" style="background:#27ae60;">
        <div class="label">Paid</div>
        <div class="value">${paidOrders}</div>
    </div>
    <div class="summary-box" style="background:#f39c12;">
        <div class="label">Pending</div>
        <div class="value">${pendingOrders}</div>
    </div>
    <div class="summary-box" style="background:#e74c3c;">
        <div class="label">Cancelled</div>
        <div class="value">${cancelledOrders}</div>
    </div>
</div>
<table>
    <thead><tr>
        <th>#</th><th>Order Ref</th><th>Customer</th><th>Amount</th><th>Date</th><th>Payment</th><th>Status</th>
    </tr></thead>
    <tbody>${tableRows}</tbody>
</table>
<div class="footer">
    <span>SolarPower Energy Corporation — Confidential</span>
</div>
</body></html>`;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
    };
}
</script>

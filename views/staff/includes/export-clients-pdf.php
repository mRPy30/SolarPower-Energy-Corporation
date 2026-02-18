<!-- Clients PDF Export (Print Preview) -->
<script>
function exportClientsPDF() {
    fetch('dashboard.php?ajax=1&action=fetch_clients')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No client data available to export.');
                return;
            }
            generateClientsPrintPreview(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch client data for export.');
        });
}

function generateClientsPrintPreview(clients) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    let tableRows = clients.map((client, i) => {
        return `<tr style="${i % 2 === 1 ? 'background:#f8f9fa;' : ''}">
            <td style="text-align:center;">${i + 1}</td>
            <td style="font-weight:600;">${client.customer_name}</td>
            <td>${client.customer_email}</td>
            <td style="text-align:center;">${client.customer_phone}</td>
            <td>${client.customer_address}</td>
            <td style="text-align:center;">${client.total_orders}</td>
        </tr>`;
    }).join('');

    const html = `<!DOCTYPE html>
<html><head><title>SolarPower Client Directory Report</title>
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
        <p>Client Directory Report</p>
    </div>
    <div class="header-right">
        Generated: ${dateStr} at ${timeStr}<br>
        Total Clients: ${clients.length}
    </div>
</div>
<hr class="divider">
<table>
    <thead><tr>
        <th>#</th><th>Full Name</th><th>Email Address</th><th>Contact Number</th><th>Delivery Address</th><th>Total Orders</th>
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

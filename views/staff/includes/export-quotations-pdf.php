<!-- Quotations PDF Export (Print Preview) -->
<script>
function exportQuotationsPDF() {
    fetch('quotation_api.php?action=fetch')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No quotation data available to export.');
                return;
            }
            generateQuotationsPrintPreview(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch quotation data for export.');
        });
}

function generateQuotationsPrintPreview(quotations) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

    const hybridCount = quotations.filter(q => q.system_type === 'HYBRID').length;
    const supplyCount = quotations.filter(q => q.system_type === 'SUPPLY ONLY').length;
    const gridTieCount = quotations.filter(q => q.system_type === 'GRID-TIE-HYBRID').length;

    function statusColor(status) {
        switch ((status || '').toUpperCase()) {
            case 'APPROVED': return '#27ae60';
            case 'SENT': return '#2980b9';
            case 'ONGOING': return '#f39c12';
            case 'CLOSED': return '#7f8c8d';
            case 'LOSS': return '#e74c3c';
            default: return '#333';
        }
    }

    let tableRows = quotations.map((q, i) => {
        const status = (q.status || '').toUpperCase();
        return `<tr style="${i % 2 === 1 ? 'background:#f8f9fa;' : ''}">
            <td style="text-align:center;">${i + 1}</td>
            <td style="font-weight:600;">${q.quotation_number || ''}</td>
            <td style="font-weight:600;">${q.client_name || ''}</td>
            <td>${q.email || ''}</td>
            <td style="text-align:center;">${q.contact || ''}</td>
            <td>${q.location || ''}</td>
            <td style="text-align:center;">${q.system_type || ''}</td>
            <td style="text-align:center;">${q.kw || '-'}</td>
            <td style="text-align:center;">${q.officer_display_name || q.officer || ''}</td>
            <td style="text-align:center;font-weight:700;color:${statusColor(status)}">${status}</td>
            <td>${q.remarks || ''}</td>
        </tr>`;
    }).join('');

    const html = `<!DOCTYPE html>
<html><head><title>SolarPower Quotations Report</title>
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
    table { width: 100%; border-collapse: collapse; font-size: 8px; }
    thead th { background: #2c3e50; color: #fff; font-size: 8px; font-weight: 600; padding: 6px 4px; text-align: center; }
    tbody td { padding: 5px 4px; border-bottom: 1px solid #e0e0e0; }
    .footer { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 6px; display: flex; justify-content: space-between; font-size: 8px; color: #999; }
    @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style></head><body>
<div class="accent-bar"></div>
<div class="header">
    <div class="header-left">
        <h1>SolarPower Energy Corporation</h1>
        <p>Quotations Report</p>
    </div>
    <div class="header-right">
        Generated: ${dateStr} at ${timeStr}<br>
        Total Quotations: ${quotations.length}
    </div>
</div>
<hr class="divider">
<div class="summary">
    <div class="summary-box" style="background:#2c3e50;">
        <div class="label">Total Quotations</div>
        <div class="value">${quotations.length}</div>
    </div>
    <div class="summary-box" style="background:#2980b9;">
        <div class="label">Hybrid</div>
        <div class="value">${hybridCount}</div>
    </div>
    <div class="summary-box" style="background:#27ae60;">
        <div class="label">Supply Only</div>
        <div class="value">${supplyCount}</div>
    </div>
    <div class="summary-box" style="background:#f39c12;">
        <div class="label">Grid-Tie Hybrid</div>
        <div class="value">${gridTieCount}</div>
    </div>
</div>
<table>
    <thead><tr>
        <th>#</th><th>Quotation #</th><th>Client Name</th><th>Email</th><th>Contact</th><th>Location</th><th>System Type</th><th>kW</th><th>Officer</th><th>Status</th><th>Remarks</th>
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

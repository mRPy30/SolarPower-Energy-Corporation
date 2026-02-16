<!-- ExcelJS for Clients Excel Export -->
<script>
function exportClientsExcel() {
    fetch('dashboard.php?ajax=1&action=fetch_clients')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No client data available to export.');
                return;
            }
            generateClientsExcel(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch client data for export.');
        });
}

function generateClientsExcel(clients) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('en-PH', {
        hour: '2-digit', minute: '2-digit'
    });

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'SolarPower Energy Corporation';
    workbook.created = now;

    const ws = workbook.addWorksheet('Clients', {
        views: [{ showGridLines: true }]
    });

    // Column definitions
    ws.columns = [
        { key: 'num', width: 6 },
        { key: 'name', width: 30 },
        { key: 'email', width: 35 },
        { key: 'phone', width: 20 },
        { key: 'address', width: 40 },
        { key: 'orders', width: 14 }
    ];

    // === HEADER ROWS ===
    // Row 1: Company name
    const companyRow = ws.addRow(['SolarPower Energy Corporation']);
    ws.mergeCells('A1:F1');
    companyRow.getCell(1).font = { bold: true, size: 16, color: { argb: 'FF2C3E50' } };
    companyRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };
    companyRow.getCell(1).border = { top: { style: 'thick', color: { argb: 'FFFFC107' } } };
    companyRow.height = 28;

    // Row 2: Report title
    const titleRow = ws.addRow(['Client Directory Report']);
    ws.mergeCells('A2:F2');
    titleRow.getCell(1).font = { size: 12, color: { argb: 'FF646464' } };
    titleRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 3: Generated date
    const dateRow = ws.addRow([`Generated: ${dateStr} at ${timeStr}`]);
    ws.mergeCells('A3:F3');
    dateRow.getCell(1).font = { size: 10, italic: true, color: { argb: 'FF999999' } };
    dateRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 4: blank
    ws.addRow([]);

    // Row 5: Summary
    const summRow = ws.addRow(['Total Clients', clients.length]);
    summRow.getCell(1).font = { bold: true, size: 10, color: { argb: 'FF2C3E50' } };
    summRow.getCell(2).font = { bold: true, size: 10 };
    summRow.getCell(1).alignment = { vertical: 'middle' };
    summRow.getCell(2).alignment = { vertical: 'middle' };

    // Row 6: blank
    ws.addRow([]);

    // Row 7: Table header
    const headerRow = ws.addRow(['#', 'Full Name', 'Email Address', 'Contact Number', 'Delivery Address', 'Total Orders']);
    headerRow.height = 22;
    headerRow.eachCell((cell) => {
        cell.font = { bold: true, size: 10, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF2C3E50' } };
        cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        cell.border = {
            top: { style: 'thin', color: { argb: 'FFCCCCCC' } },
            bottom: { style: 'thin', color: { argb: 'FFCCCCCC' } },
            left: { style: 'thin', color: { argb: 'FFCCCCCC' } },
            right: { style: 'thin', color: { argb: 'FFCCCCCC' } }
        };
    });

    // Data rows
    clients.forEach((client, index) => {
        const row = ws.addRow([
            index + 1,
            client.customer_name,
            client.customer_email,
            client.customer_phone,
            client.customer_address,
            client.total_orders
        ]);

        const fillColor = index % 2 === 0 ? 'FFFFFFFF' : 'FFF8F9FA';

        row.eachCell((cell, colNumber) => {
            cell.alignment = { wrapText: true, vertical: 'middle' };
            cell.font = { size: 9 };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: fillColor } };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFE0E0E0' } },
                bottom: { style: 'thin', color: { argb: 'FFE0E0E0' } },
                left: { style: 'thin', color: { argb: 'FFE0E0E0' } },
                right: { style: 'thin', color: { argb: 'FFE0E0E0' } }
            };

            // Center # column
            if (colNumber === 1) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Bold name
            if (colNumber === 2) cell.font = { size: 9, bold: true };
            // Center phone
            if (colNumber === 4) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Center total orders
            if (colNumber === 6) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        });
    });

    // Write and save
    workbook.xlsx.writeBuffer().then(buffer => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `SolarPower_Clients_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.xlsx`;
        a.click();
        URL.revokeObjectURL(url);
    });
}
</script>

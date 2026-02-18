<!-- ExcelJS Library for Orders Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

<script>
function exportOrdersExcel() {
    fetch('dashboard.php?ajax=1&action=fetch_orders')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No order data available to export.');
                return;
            }
            generateOrdersExcel(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch order data for export.');
        });
}

function generateOrdersExcel(orders) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('en-PH', {
        hour: '2-digit', minute: '2-digit'
    });

    // Calculate summary
    const totalRevenue = orders.reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
    const paidOrders = orders.filter(o => o.order_status.toUpperCase() === 'PAID').length;
    const pendingOrders = orders.filter(o => o.order_status.toUpperCase() === 'PENDING').length;
    const cancelledOrders = orders.filter(o => o.order_status.toUpperCase() === 'CANCELLED').length;

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'SolarPower Energy Corporation';
    workbook.created = now;

    const ws = workbook.addWorksheet('Orders', {
        views: [{ showGridLines: true }]
    });

    // Column definitions with widths
    ws.columns = [
        { key: 'num', width: 6 },
        { key: 'ref', width: 28 },
        { key: 'customer', width: 30 },
        { key: 'amount', width: 18 },
        { key: 'date', width: 18 },
        { key: 'payment', width: 18 },
        { key: 'status', width: 14 }
    ];

    // === HEADER ROWS ===
    // Row 1: Company name
    const companyRow = ws.addRow(['SolarPower Energy Corporation']);
    ws.mergeCells('A1:G1');
    companyRow.getCell(1).font = { bold: true, size: 16, color: { argb: 'FF2C3E50' } };
    companyRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };
    companyRow.height = 28;

    // Row 2: Report title
    const titleRow = ws.addRow(['Orders Report']);
    ws.mergeCells('A2:G2');
    titleRow.getCell(1).font = { size: 12, color: { argb: 'FF646464' } };
    titleRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 3: Generated date
    const dateRow = ws.addRow([`Generated: ${dateStr} at ${timeStr}`]);
    ws.mergeCells('A3:G3');
    dateRow.getCell(1).font = { size: 10, italic: true, color: { argb: 'FF999999' } };
    dateRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 4: blank
    ws.addRow([]);

    // Row 5: Summary - Total Orders & Revenue
    const summRow1 = ws.addRow(['Total Orders', orders.length, '', 'Total Revenue', totalRevenue]);
    summRow1.getCell(1).font = { bold: true, size: 10, color: { argb: 'FF2C3E50' } };
    summRow1.getCell(2).font = { bold: true, size: 10 };
    summRow1.getCell(4).font = { bold: true, size: 10, color: { argb: 'FF2C3E50' } };
    summRow1.getCell(5).font = { bold: true, size: 10 };
    summRow1.getCell(5).numFmt = '#,##0.00';
    summRow1.eachCell({ includeEmpty: false }, cell => {
        cell.alignment = { vertical: 'middle' };
    });

    // Row 6: Summary - Paid / Pending / Cancelled
    const summRow2 = ws.addRow(['Paid', paidOrders, '', 'Pending', pendingOrders, '', 'Cancelled', cancelledOrders]);
    summRow2.getCell(1).font = { bold: true, size: 10, color: { argb: 'FF27AE60' } };
    summRow2.getCell(2).font = { bold: true, size: 10, color: { argb: 'FF27AE60' } };
    summRow2.getCell(4).font = { bold: true, size: 10, color: { argb: 'FFF39C12' } };
    summRow2.getCell(5).font = { bold: true, size: 10, color: { argb: 'FFF39C12' } };
    // Column G & H for cancelled
    if (summRow2.getCell(7)) summRow2.getCell(7).font = { bold: true, size: 10, color: { argb: 'FFE74C3C' } };
    if (summRow2.getCell(8)) summRow2.getCell(8).font = { bold: true, size: 10, color: { argb: 'FFE74C3C' } };
    summRow2.eachCell({ includeEmpty: false }, cell => {
        cell.alignment = { vertical: 'middle' };
    });

    // Row 7: blank
    ws.addRow([]);

    // Row 8: Table header
    const headerRow = ws.addRow(['#', 'Order Reference', 'Customer Name', 'Amount (PHP)', 'Date', 'Payment Method', 'Status']);
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
    orders.forEach((order, index) => {
        const row = ws.addRow([
            index + 1,
            order.order_reference,
            order.customer_name,
            parseFloat(order.total_amount),
            new Date(order.created_at).toLocaleDateString('en-PH', {
                year: 'numeric', month: 'short', day: 'numeric'
            }),
            order.payment_method,
            order.order_status.toUpperCase()
        ]);

        // Alternate row fill
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

            // Center align # column
            if (colNumber === 1) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Bold customer name
            if (colNumber === 3) cell.font = { size: 9, bold: true };
            // Right-align & format amount
            if (colNumber === 4) {
                cell.alignment = { horizontal: 'right', vertical: 'middle', wrapText: true };
                cell.numFmt = '#,##0.00';
            }
            // Center date, payment, status
            if (colNumber >= 5) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };

            // Color-code status
            if (colNumber === 7) {
                const status = cell.value;
                if (status === 'PAID') cell.font = { size: 9, bold: true, color: { argb: 'FF27AE60' } };
                else if (status === 'PENDING') cell.font = { size: 9, bold: true, color: { argb: 'FFF39C12' } };
                else if (status === 'CANCELLED') cell.font = { size: 9, bold: true, color: { argb: 'FFE74C3C' } };
            }
        });
    });

    // Yellow accent bar - top border on row 1
    companyRow.eachCell((cell) => {
        cell.border = { top: { style: 'thick', color: { argb: 'FFFFC107' } } };
    });

    // Write and save with Save As dialog
    const defaultFilename = `SolarPower_Orders_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.xlsx`;
    workbook.xlsx.writeBuffer().then(async buffer => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        if (window.showSaveFilePicker) {
            try {
                const handle = await window.showSaveFilePicker({
                    suggestedName: defaultFilename,
                    types: [{
                        description: 'Excel Spreadsheet',
                        accept: { 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx'] }
                    }]
                });
                const writable = await handle.createWritable();
                await writable.write(blob);
                await writable.close();
            } catch (err) {
                if (err.name !== 'AbortError') console.error('Save failed:', err);
            }
        } else {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = defaultFilename;
            a.click();
            URL.revokeObjectURL(url);
        }
    });
}
</script>

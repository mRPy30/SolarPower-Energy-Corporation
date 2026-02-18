<!-- ExcelJS for Quotations Excel Export -->
<script>
function exportQuotationsExcel() {
    fetch('quotation_api.php?action=fetch')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No quotation data available to export.');
                return;
            }
            generateQuotationsExcel(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch quotation data for export.');
        });
}

function generateQuotationsExcel(quotations) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('en-PH', {
        hour: '2-digit', minute: '2-digit'
    });

    // Calculate summary
    const hybridCount = quotations.filter(q => q.system_type === 'HYBRID').length;
    const supplyCount = quotations.filter(q => q.system_type === 'SUPPLY ONLY').length;
    const gridTieCount = quotations.filter(q => q.system_type === 'GRID-TIE-HYBRID').length;

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'SolarPower Energy Corporation';
    workbook.created = now;

    const ws = workbook.addWorksheet('Quotations', {
        views: [{ showGridLines: true }]
    });

    // Column definitions
    ws.columns = [
        { key: 'num', width: 6 },
        { key: 'qnum', width: 20 },
        { key: 'client', width: 25 },
        { key: 'email', width: 30 },
        { key: 'contact', width: 18 },
        { key: 'location', width: 22 },
        { key: 'system', width: 18 },
        { key: 'kw', width: 10 },
        { key: 'officer', width: 16 },
        { key: 'status', width: 14 },
        { key: 'remarks', width: 30 }
    ];

    // === HEADER ROWS ===
    // Row 1: Company name
    const companyRow = ws.addRow(['SolarPower Energy Corporation']);
    ws.mergeCells('A1:K1');
    companyRow.getCell(1).font = { bold: true, size: 16, color: { argb: 'FF2C3E50' } };
    companyRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };
    companyRow.getCell(1).border = { top: { style: 'thick', color: { argb: 'FFFFC107' } } };
    companyRow.height = 28;

    // Row 2: Report title
    const titleRow = ws.addRow(['Quotations Report']);
    ws.mergeCells('A2:K2');
    titleRow.getCell(1).font = { size: 12, color: { argb: 'FF646464' } };
    titleRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 3: Generated date
    const dateRow = ws.addRow([`Generated: ${dateStr} at ${timeStr}`]);
    ws.mergeCells('A3:K3');
    dateRow.getCell(1).font = { size: 10, italic: true, color: { argb: 'FF999999' } };
    dateRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 4: blank
    ws.addRow([]);

    // Row 5: Summary
    const summRow1 = ws.addRow(['Total Quotations', quotations.length, '', 'Hybrid', hybridCount, '', 'Supply Only', supplyCount, '', 'Grid-Tie Hybrid', gridTieCount]);
    summRow1.getCell(1).font = { bold: true, size: 10, color: { argb: 'FF2C3E50' } };
    summRow1.getCell(2).font = { bold: true, size: 10 };
    summRow1.getCell(4).font = { bold: true, size: 10, color: { argb: 'FF2980B9' } };
    summRow1.getCell(5).font = { bold: true, size: 10, color: { argb: 'FF2980B9' } };
    summRow1.getCell(7).font = { bold: true, size: 10, color: { argb: 'FF27AE60' } };
    summRow1.getCell(8).font = { bold: true, size: 10, color: { argb: 'FF27AE60' } };
    summRow1.getCell(10).font = { bold: true, size: 10, color: { argb: 'FFF39C12' } };
    summRow1.getCell(11).font = { bold: true, size: 10, color: { argb: 'FFF39C12' } };
    summRow1.eachCell({ includeEmpty: false }, cell => {
        cell.alignment = { vertical: 'middle' };
    });

    // Row 6: blank
    ws.addRow([]);

    // Row 7: Table header
    const headerRow = ws.addRow(['#', 'Quotation #', 'Client Name', 'Email', 'Contact', 'Location', 'System Type', 'kW', 'Officer', 'Status', 'Remarks']);
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
    quotations.forEach((q, index) => {
        const row = ws.addRow([
            index + 1,
            q.quotation_number || '',
            q.client_name || '',
            q.email || '',
            q.contact || '',
            q.location || '',
            q.system_type || '',
            q.kw || '',
            q.officer_display_name || q.officer || '',
            q.status || '',
            q.remarks || ''
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
            // Bold quotation number
            if (colNumber === 2) cell.font = { size: 9, bold: true };
            // Bold client name
            if (colNumber === 3) cell.font = { size: 9, bold: true };
            // Center contact
            if (colNumber === 5) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Center system type
            if (colNumber === 7) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Center kW
            if (colNumber === 8) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Center officer
            if (colNumber === 9) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Color-code status
            if (colNumber === 10) {
                cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                const status = (cell.value || '').toUpperCase();
                if (status === 'APPROVED') cell.font = { size: 9, bold: true, color: { argb: 'FF27AE60' } };
                else if (status === 'SENT') cell.font = { size: 9, bold: true, color: { argb: 'FF2980B9' } };
                else if (status === 'ONGOING') cell.font = { size: 9, bold: true, color: { argb: 'FFF39C12' } };
                else if (status === 'CLOSED') cell.font = { size: 9, bold: true, color: { argb: 'FF7F8C8D' } };
                else if (status === 'LOSS') cell.font = { size: 9, bold: true, color: { argb: 'FFE74C3C' } };
            }
        });
    });

    // Write and save with Save As dialog
    const defaultFilename = `SolarPower_Quotations_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.xlsx`;
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

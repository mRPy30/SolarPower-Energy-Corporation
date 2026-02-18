<!-- ExcelJS for Suppliers Excel Export -->
<script>
function exportSuppliersExcel() {
    fetch('dashboard.php?ajax=1&action=fetch')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No supplier data available to export.');
                return;
            }
            generateSuppliersExcel(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch supplier data for export.');
        });
}

function generateSuppliersExcel(suppliers) {
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

    const ws = workbook.addWorksheet('Suppliers', {
        views: [{ showGridLines: true }]
    });

    // Column definitions
    ws.columns = [
        { key: 'num', width: 6 },
        { key: 'name', width: 28 },
        { key: 'contact', width: 22 },
        { key: 'email', width: 30 },
        { key: 'phone', width: 18 },
        { key: 'address', width: 30 },
        { key: 'city', width: 16 },
        { key: 'country', width: 16 },
        { key: 'registered', width: 18 }
    ];

    // === HEADER ROWS ===
    // Row 1: Company name
    const companyRow = ws.addRow(['SolarPower Energy Corporation']);
    ws.mergeCells('A1:I1');
    companyRow.getCell(1).font = { bold: true, size: 16, color: { argb: 'FF2C3E50' } };
    companyRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };
    companyRow.getCell(1).border = { top: { style: 'thick', color: { argb: 'FFFFC107' } } };
    companyRow.height = 28;

    // Row 2: Report title
    const titleRow = ws.addRow(['Supplier Directory Report']);
    ws.mergeCells('A2:I2');
    titleRow.getCell(1).font = { size: 12, color: { argb: 'FF646464' } };
    titleRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 3: Generated date
    const dateRow = ws.addRow([`Generated: ${dateStr} at ${timeStr}`]);
    ws.mergeCells('A3:I3');
    dateRow.getCell(1).font = { size: 10, italic: true, color: { argb: 'FF999999' } };
    dateRow.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

    // Row 4: blank
    ws.addRow([]);

    // Row 5: Summary
    const summRow = ws.addRow(['Total Suppliers', suppliers.length]);
    summRow.getCell(1).font = { bold: true, size: 10, color: { argb: 'FF2C3E50' } };
    summRow.getCell(2).font = { bold: true, size: 10 };
    summRow.getCell(1).alignment = { vertical: 'middle' };
    summRow.getCell(2).alignment = { vertical: 'middle' };

    // Row 6: blank
    ws.addRow([]);

    // Row 7: Table header
    const headerRow = ws.addRow(['#', 'Supplier Name', 'Contact Person', 'Email', 'Phone', 'Address', 'City', 'Country', 'Registered']);
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
    suppliers.forEach((s, index) => {
        const row = ws.addRow([
            index + 1,
            s.supplierName || '',
            s.contactPerson || '',
            s.email || '',
            s.phone || '',
            s.address || '',
            s.city || '',
            s.country || '',
            s.registrationDate ? new Date(s.registrationDate).toLocaleDateString('en-PH', {
                year: 'numeric', month: 'short', day: 'numeric'
            }) : ''
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
            // Bold supplier name
            if (colNumber === 2) cell.font = { size: 9, bold: true };
            // Center phone
            if (colNumber === 5) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            // Center registered date
            if (colNumber === 9) cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        });
    });

    // Write and save with Save As dialog
    const defaultFilename = `SolarPower_Suppliers_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.xlsx`;
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

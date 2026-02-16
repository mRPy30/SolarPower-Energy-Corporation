<!-- Quotations PDF Export -->
<script>
function exportQuotationsPDF() {
    fetch('quotation_api.php?action=fetch')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No quotation data available to export.');
                return;
            }
            generateQuotationsPDF(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch quotation data for export.');
        });
}

function generateQuotationsPDF(quotations) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('landscape', 'mm', 'a4');

    const pageWidth = doc.internal.pageSize.getWidth();
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

    // ===== HEADER =====
    doc.setFillColor(255, 193, 7);
    doc.rect(0, 0, pageWidth, 4, 'F');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.setTextColor(44, 62, 80);
    doc.text('SolarPower Energy Corporation', 14, 18);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100);
    doc.text('Quotations Report', 14, 26);

    doc.setFontSize(10);
    doc.text(`Generated: ${dateStr} at ${timeStr}`, pageWidth - 14, 18, { align: 'right' });
    doc.text(`Total Quotations: ${quotations.length}`, pageWidth - 14, 24, { align: 'right' });

    // Divider
    doc.setDrawColor(255, 193, 7);
    doc.setLineWidth(0.5);
    doc.line(14, 30, pageWidth - 14, 30);

    // ===== SUMMARY BOXES =====
    const boxY = 34;
    const boxH = 16;
    const boxW = (pageWidth - 28 - 15) / 4;

    // Total
    doc.setFillColor(44, 62, 80);
    doc.roundedRect(14, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(200, 200, 200);
    doc.text('TOTAL QUOTATIONS', 14 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(quotations.length), 14 + boxW / 2, boxY + 13, { align: 'center' });

    // Hybrid
    doc.setFillColor(41, 128, 185);
    doc.roundedRect(14 + boxW + 5, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(200, 220, 255);
    doc.text('HYBRID', 14 + boxW + 5 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(hybridCount), 14 + boxW + 5 + boxW / 2, boxY + 13, { align: 'center' });

    // Supply Only
    doc.setFillColor(39, 174, 96);
    doc.roundedRect(14 + (boxW + 5) * 2, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(200, 255, 220);
    doc.text('SUPPLY ONLY', 14 + (boxW + 5) * 2 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(supplyCount), 14 + (boxW + 5) * 2 + boxW / 2, boxY + 13, { align: 'center' });

    // Grid-Tie Hybrid
    doc.setFillColor(243, 156, 18);
    doc.roundedRect(14 + (boxW + 5) * 3, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(255, 240, 200);
    doc.text('GRID-TIE HYBRID', 14 + (boxW + 5) * 3 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(gridTieCount), 14 + (boxW + 5) * 3 + boxW / 2, boxY + 13, { align: 'center' });

    // ===== TABLE =====
    const tableData = quotations.map((q, index) => [
        index + 1,
        q.quotation_number || '',
        q.client_name || '',
        q.email || '',
        q.contact || '',
        q.location || '',
        q.system_type || '',
        q.kw || '-',
        q.officer_display_name || q.officer || '',
        q.status || '',
        q.remarks || ''
    ]);

    doc.autoTable({
        startY: boxY + boxH + 6,
        head: [['#', 'Quotation #', 'Client Name', 'Email', 'Contact', 'Location', 'System Type', 'kW', 'Officer', 'Status', 'Remarks']],
        body: tableData,
        theme: 'grid',
        headStyles: {
            fillColor: [44, 62, 80],
            textColor: [255, 255, 255],
            fontSize: 8,
            fontStyle: 'bold',
            halign: 'center',
            cellPadding: 3
        },
        bodyStyles: {
            fontSize: 7,
            cellPadding: 2.5,
            textColor: [50, 50, 50]
        },
        alternateRowStyles: {
            fillColor: [248, 249, 250]
        },
        columnStyles: {
            0: { halign: 'center', cellWidth: 8 },
            1: { fontStyle: 'bold', cellWidth: 28 },
            2: { fontStyle: 'bold', cellWidth: 28 },
            3: { cellWidth: 35 },
            4: { halign: 'center', cellWidth: 22 },
            5: { cellWidth: 22 },
            6: { halign: 'center', cellWidth: 22 },
            7: { halign: 'center', cellWidth: 12 },
            8: { halign: 'center', cellWidth: 18 },
            9: { halign: 'center', cellWidth: 18 },
            10: { cellWidth: 'auto' }
        },
        margin: { left: 14, right: 14 },
        didParseCell: function(data) {
            if (data.section === 'body' && data.column.index === 9) {
                const status = (data.cell.raw || '').toUpperCase();
                if (status === 'APPROVED') {
                    data.cell.styles.textColor = [39, 174, 96];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'SENT') {
                    data.cell.styles.textColor = [41, 128, 185];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'ONGOING') {
                    data.cell.styles.textColor = [243, 156, 18];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'CLOSED') {
                    data.cell.styles.textColor = [127, 140, 141];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'LOSS') {
                    data.cell.styles.textColor = [231, 76, 60];
                    data.cell.styles.fontStyle = 'bold';
                }
            }
        },
        didDrawPage: function(data) {
            const pageHeight = doc.internal.pageSize.getHeight();
            const pageNum = doc.internal.getNumberOfPages();
            const currentPage = doc.internal.getCurrentPageInfo().pageNumber;

            doc.setDrawColor(200, 200, 200);
            doc.setLineWidth(0.3);
            doc.line(14, pageHeight - 15, pageWidth - 14, pageHeight - 15);

            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(150, 150, 150);
            doc.text('SolarPower Energy Corporation — Confidential', 14, pageHeight - 10);
            doc.text(`Page ${currentPage} of ${pageNum}`, pageWidth - 14, pageHeight - 10, { align: 'right' });
        }
    });

    const filename = `SolarPower_Quotations_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.pdf`;
    doc.save(filename);
}
</script>

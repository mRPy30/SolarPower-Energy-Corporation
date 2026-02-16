<!-- Orders PDF Export -->
<script>
function exportOrdersPDF() {
    // Fetch fresh order data from MySQL via existing endpoint
    fetch('dashboard.php?ajax=1&action=fetch_orders')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No order data available to export.');
                return;
            }
            generateOrdersPDF(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch order data for export.');
        });
}

function generateOrdersPDF(orders) {
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
    const totalRevenue = orders.reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
    const paidOrders = orders.filter(o => o.order_status.toUpperCase() === 'PAID').length;
    const pendingOrders = orders.filter(o => o.order_status.toUpperCase() === 'PENDING').length;
    const cancelledOrders = orders.filter(o => o.order_status.toUpperCase() === 'CANCELLED').length;

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
    doc.text('Orders Report', 14, 26);

    doc.setFontSize(10);
    doc.text(`Generated: ${dateStr} at ${timeStr}`, pageWidth - 14, 18, { align: 'right' });
    doc.text(`Total Orders: ${orders.length}`, pageWidth - 14, 24, { align: 'right' });

    // Divider
    doc.setDrawColor(255, 193, 7);
    doc.setLineWidth(0.5);
    doc.line(14, 30, pageWidth - 14, 30);

    // ===== SUMMARY BOXES =====
    const boxY = 34;
    const boxH = 16;
    const boxW = (pageWidth - 28 - 15) / 4; // 4 boxes with gaps

    // Total Revenue
    doc.setFillColor(44, 62, 80);
    doc.roundedRect(14, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(200, 200, 200);
    doc.text('TOTAL REVENUE', 14 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text('PHP ' + totalRevenue.toLocaleString('en-PH', { minimumFractionDigits: 2 }), 14 + boxW / 2, boxY + 13, { align: 'center' });

    // Paid
    doc.setFillColor(39, 174, 96);
    doc.roundedRect(14 + boxW + 5, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(200, 255, 200);
    doc.text('PAID', 14 + boxW + 5 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(paidOrders), 14 + boxW + 5 + boxW / 2, boxY + 13, { align: 'center' });

    // Pending
    doc.setFillColor(243, 156, 18);
    doc.roundedRect(14 + (boxW + 5) * 2, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(255, 240, 200);
    doc.text('PENDING', 14 + (boxW + 5) * 2 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(pendingOrders), 14 + (boxW + 5) * 2 + boxW / 2, boxY + 13, { align: 'center' });

    // Cancelled
    doc.setFillColor(231, 76, 60);
    doc.roundedRect(14 + (boxW + 5) * 3, boxY, boxW, boxH, 2, 2, 'F');
    doc.setFontSize(8);
    doc.setTextColor(255, 200, 200);
    doc.text('CANCELLED', 14 + (boxW + 5) * 3 + boxW / 2, boxY + 5, { align: 'center' });
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text(String(cancelledOrders), 14 + (boxW + 5) * 3 + boxW / 2, boxY + 13, { align: 'center' });

    // ===== TABLE =====
    const tableData = orders.map((order, index) => [
        index + 1,
        order.order_reference,
        order.customer_name,
        'PHP ' + parseFloat(order.total_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
        new Date(order.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }),
        order.payment_method,
        order.order_status.toUpperCase()
    ]);


    doc.autoTable({
        startY: boxY + boxH + 6,
        head: [['#', 'Order Ref', 'Customer', 'Amount', 'Date', 'Payment', 'Status']],
        body: tableData,
        theme: 'grid',
        headStyles: {
            fillColor: [44, 62, 80],
            textColor: [255, 255, 255],
            fontSize: 10,
            fontStyle: 'bold',
            halign: 'center',
            cellPadding: 4
        },
        bodyStyles: {
            fontSize: 9,
            cellPadding: 3,
            textColor: [50, 50, 50]
        },
        alternateRowStyles: {
            fillColor: [248, 249, 250]
        },
        columnStyles: {
            0: { halign: 'center', cellWidth: 12 },
            1: { halign: 'center', cellWidth: 35 },
            2: { fontStyle: 'bold', cellWidth: 50 },
            3: { halign: 'right', cellWidth: 35 },
            4: { halign: 'center', cellWidth: 35 },
            5: { halign: 'center', cellWidth: 30 },
            6: { halign: 'center', cellWidth: 28 }
        },
        margin: { left: 14, right: 14 },
        didParseCell: function(data) {
            // Color-code the Status column
            if (data.section === 'body' && data.column.index === 6) {
                const status = data.cell.raw;
                if (status === 'PAID') {
                    data.cell.styles.textColor = [39, 174, 96];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'PENDING') {
                    data.cell.styles.textColor = [243, 156, 18];
                    data.cell.styles.fontStyle = 'bold';
                } else if (status === 'CANCELLED') {
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

    // Save
    const filename = `SolarPower_Orders_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.pdf`;
    doc.save(filename);
}
</script>

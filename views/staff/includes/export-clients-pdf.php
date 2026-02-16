<!-- jsPDF Libraries for Client PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
function exportClientsPDF() {
    // Fetch fresh client data from MySQL via existing endpoint
    fetch('dashboard.php?ajax=1&action=fetch_clients')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No client data available to export.');
                return;
            }
            generateClientsPDF(res.data);
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Failed to fetch client data for export.');
        });
}

function generateClientsPDF(clients) {
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

    // ===== HEADER =====
    // Yellow accent bar
    doc.setFillColor(255, 193, 7);
    doc.rect(0, 0, pageWidth, 4, 'F');

    // Company name
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.setTextColor(44, 62, 80);
    doc.text('SolarPower Energy Corporation', 14, 18);

    // Report title
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100);
    doc.text('Client Directory Report', 14, 26);

    // Date & time (right-aligned)
    doc.setFontSize(10);
    doc.text(`Generated: ${dateStr} at ${timeStr}`, pageWidth - 14, 18, { align: 'right' });
    doc.text(`Total Clients: ${clients.length}`, pageWidth - 14, 24, { align: 'right' });

    // Divider line
    doc.setDrawColor(255, 193, 7);
    doc.setLineWidth(0.5);
    doc.line(14, 30, pageWidth - 14, 30);

    // ===== TABLE =====
    const tableData = clients.map((client, index) => [
        index + 1,
        client.customer_name,
        client.customer_email,
        client.customer_phone,
        client.customer_address,
        client.total_orders
    ]);

    doc.autoTable({
        startY: 35,
        head: [['#', 'Full Name', 'Email Address', 'Contact Number', 'Delivery Address', 'Total Orders']],
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
            1: { fontStyle: 'bold', cellWidth: 45 },
            2: { cellWidth: 55 },
            3: { halign: 'center', cellWidth: 35 },
            4: { cellWidth: 'auto' },
            5: { halign: 'center', cellWidth: 25 }
        },
        margin: { left: 14, right: 14 },
        didDrawPage: function(data) {
            // Footer on each page
            const pageHeight = doc.internal.pageSize.getHeight();
            const pageNum = doc.internal.getNumberOfPages();
            const currentPage = doc.internal.getCurrentPageInfo().pageNumber;

            // Footer line
            doc.setDrawColor(200, 200, 200);
            doc.setLineWidth(0.3);
            doc.line(14, pageHeight - 15, pageWidth - 14, pageHeight - 15);

            // Footer text
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text('SolarPower Energy Corporation — Confidential', 14, pageHeight - 10);
            doc.text(`Page ${currentPage} of ${pageNum}`, pageWidth - 14, pageHeight - 10, { align: 'right' });
        }
    });

    // Save the PDF
    const filename = `SolarPower_Clients_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}.pdf`;
    doc.save(filename);
}
</script>

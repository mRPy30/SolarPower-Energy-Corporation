function updatePaymentDisplay() {
    // 1. Kunin ang Base Total (halimbawa: 50000)
    let baseTotal = parseFloat(document.getElementById('modalTotalAmount').innerText.replace(/[^\d.]/g, ''));
    
    // 2. Alamin kung ilang percent ang babayaran
    let percentage = 1; // Default is 100%
    const selectedRadio = document.querySelector('input[name="paymentOption"]:checked');
    if (selectedRadio) {
        percentage = parseFloat(selectedRadio.value) / 100;
    }

    // 3. Compute for the final amount to be paid
    let amountToPay = (baseTotal * percentage).toFixed(2);
    
    // I-update ang text sa UI
    document.getElementById('amountToPay').innerText = "₱" + parseFloat(amountToPay).toLocaleString();

    // 4. GENERATE QRPH STRING (Dapat walang error dito para hindi mag-loading)
    const merchantName = "SOLARPOWER ENERGY";
    const accountNum = "002180027200";
    
    // Format the amount length (Tag 54)
    let amtStr = amountToPay.toString();
    let amtLen = amtStr.length.toString().padStart(2, '0');

    // Official QRPh Format
    const qrData = "00020101021230540010COM.UNIONBANK" + accountNum + "52045999530360854" + amtLen + amtStr + "5802PH5916" + merchantName + "6007MANILA6304";

    // 5. I-RENDER ANG QR (Dito mawawala ang loading spinner)
    const qrContainer = document.getElementById("dynamicQrCode");
    qrContainer.innerHTML = ""; // Clear the loading spinner
    
    try {
        new QRCode(qrContainer, {
            text: qrData,
            width: 280,
            height: 280,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });
    } catch (err) {
        console.error("QR Generation Error:", err);
        qrContainer.innerHTML = "<p class='text-danger'>Error generating QR. Please refresh.</p>";
    }
}

// Tawagin ang function kapag nag-load ang page
document.addEventListener('DOMContentLoaded', updatePaymentDisplay);
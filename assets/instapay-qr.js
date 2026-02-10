function generateUnionBankQR(amount) {
    // This is a simplified version of the QRPh/InstaPay string format
    // In a real scenario, you would use a library to build this exact payload
    const merchantName = "SOLARPOWER ENERGY";
    const accountNumber = "002180027200";
    
    // The 'payload' should follow the EMVCo standard used by UnionBank/InstaPay
    const qrPayload = `00020101021130540010COM.UNIONBANK${accountNumber}52045999530360854${amount}5802PH5916${merchantName}6007MANILA6304`;

    // Clear previous QR
    document.getElementById('dynamicQrCode').innerHTML = "";

    // Generate New QR
    new QRCode(document.getElementById("dynamicQrCode"), {
        text: qrPayload,
        width: 280,
        height: 280,
        colorDark : "#004b8d", // UnionBank Blue
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
}

function printQrCode() {
    const invoice = document.getElementById('qr-code-container').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = invoice;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload(); // Restore layout
}

function printBarCode(){
    const invoice = document.getElementById('bar-code-container').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = invoice;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload(); // Restore layout
}


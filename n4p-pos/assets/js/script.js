// N4P POS Script

function confirmDelete() {
    return confirm("Are you sure you want to delete this data?");
}

function autoInvoice() {
    let date = new Date();
    return "INV" + date.getTime();
}

function calculateChange(total, payment) {
    return payment - total;
}// N4P POS Script

function confirmDelete() {
    return confirm("Are you sure you want to delete this data?");
}

function autoInvoice() {
    let date = new Date();
    return "INV" + date.getTime();
}

function calculateChange(total, payment) {
    return payment - total;
}
import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    const toastElement = document.getElementById("successToast");
    if (toastElement) {
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
});

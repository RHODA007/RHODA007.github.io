function showToast(message, type = "success") {
    const toast = document.getElementById("toast");
    toast.innerText = message;
    toast.className = "show " + type;
    setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
}

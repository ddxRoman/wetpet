document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-input');
    const previewImg = document.getElementById('avatar-preview');
    if (!fileInput || !previewImg) return;

    fileInput.addEventListener('change', function() {
        if (this.files[0]) {
            previewImg.src = URL.createObjectURL(this.files[0]);
        }
    });
});

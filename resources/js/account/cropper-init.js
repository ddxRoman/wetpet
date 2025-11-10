import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.min.css';

let cropper = null;

export function initCropper(fileInput, previewImg) {
    const cropperModal = document.getElementById('cropper-modal');
    const cropperImage = document.getElementById('cropper-image');
    const closeCropper = document.getElementById('close-cropper');
    const saveCropped = document.getElementById('save-cropped');

    fileInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e2 => {
            cropperImage.src = e2.target.result;
            cropperModal.style.display = 'flex';

            if (cropper) cropper.destroy();
            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                background: false,
                autoCropArea: 1,
            });
        };
        reader.readAsDataURL(file);
    });

    closeCropper.addEventListener('click', () => {
        cropperModal.style.display = 'none';
        if (cropper) cropper.destroy();
    });

    saveCropped.addEventListener('click', async () => {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 800,
            imageSmoothingQuality: 'high'
        });

        // конвертация в webp
        canvas.toBlob(blob => {
            if (!blob) return;
            const webpFile = new File([blob], 'pet-photo.webp', { type: 'image/webp' });

            // создаем превью
            previewImg.src = URL.createObjectURL(webpFile);
            previewImg.style.display = 'block';

            // создаем виртуальный FileList
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(webpFile);
            fileInput.files = dataTransfer.files;

            cropperModal.style.display = 'none';
            cropper.destroy();
        }, 'image/webp', 0.9);
    });
}

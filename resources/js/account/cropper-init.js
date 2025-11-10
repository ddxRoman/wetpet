import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.min.css';

let cropperInstance = null;

export function initCropper(imageElement, fileInput) {
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            imageElement.src = event.target.result;

            if (cropperInstance) {
                cropperInstance.destroy();
            }

            cropperInstance = new Cropper(imageElement, {
                aspectRatio: 1,
                viewMode: 1,
                background: false,
                autoCropArea: 1,
            });
        };
        reader.readAsDataURL(file);
    });
}

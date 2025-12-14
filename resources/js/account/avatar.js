console.log('avatars.js loaded');

import { initCropper } from './cropper-init';

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-input');
    const previewImg = document.getElementById('avatar-preview');

    if (!fileInput || !previewImg) return;

    // ✅ Инициализация cropper-модалки
    initCropper(fileInput, previewImg);
});

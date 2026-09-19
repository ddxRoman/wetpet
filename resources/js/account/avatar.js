// console.log('avatars.js loaded');

import { initCropper } from './cropper-init';
import { showToast } from './toast';

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-input');
    const previewImg = document.getElementById('avatar-preview');

    if (!fileInput || !previewImg) return;

    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.content || '';

    // ✅ Инициализация cropper-модалки. После обрезки фото сразу
    // загружается на сервер — без необходимости нажимать
    // основную кнопку "Сохранить изменения".
    initCropper(fileInput, previewImg, {
        fileName: 'avatar.webp',
        onCropped: async (file) => {
            const originalOpacity = previewImg.style.opacity;
            previewImg.style.opacity = '0.5';

            const fd = new FormData();
            fd.append('avatar', file);

            try {
                const res = await fetch('/account/avatar', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    body: fd
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    previewImg.src = data.avatar_url;
                    showToast('Фото профиля обновлено', 'success');
                } else {
                    showToast(data.message || 'Не удалось сохранить фото', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Ошибка сети при загрузке фото', 'error');
            } finally {
                previewImg.style.opacity = originalOpacity || '1';
            }
        }
    });
});

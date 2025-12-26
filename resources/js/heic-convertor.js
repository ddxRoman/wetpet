export async function convertImageToWebP(file) {
    const isHeic =
        file.type === 'image/heic' ||
        file.type === 'image/heif' ||
        /\.heic$/i.test(file.name) ||
        /\.heif$/i.test(file.name);

    let bitmapSource = null;

    if (isHeic) {
        try {
            const heic2any = (await import('heic2any')).default;

            bitmapSource = await heic2any({
                blob: file,
                toType: 'image/png',
                quality: 1
            });
        } catch (e) {
            console.warn('HEIC not supported in browser, fallback to upload', e);

            // ❗ НЕ ПЫТАЕМСЯ ДЕЛАТЬ PREVIEW
            return {
                file,
                previewUrl: null
            };
        }
    } else {
        bitmapSource = file;
    }

    return new Promise(resolve => {
        const img = new Image();

        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;

            canvas.getContext('2d').drawImage(img, 0, 0);

            canvas.toBlob(blob => {
                if (!blob) {
                    resolve({ file, previewUrl: null });
                    return;
                }

                const webpFile = new File(
                    [blob],
                    file.name.replace(/\.\w+$/, '.webp'),
                    { type: 'image/webp' }
                );

                resolve({
                    file: webpFile,
                    previewUrl: URL.createObjectURL(webpFile)
                });
            }, 'image/webp', 0.9);
        };

        img.onerror = () => {
            resolve({ file, previewUrl: null });
        };

        img.src = URL.createObjectURL(bitmapSource);
    });
}

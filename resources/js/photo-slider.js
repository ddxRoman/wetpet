console.log('photo-slider.js loaded');

import { Modal } from 'bootstrap';

document.addEventListener("DOMContentLoaded", () => {
    let currentReviewId = null;
    let currentIndex = 0;
    let photos = {};

    // Собираем фото всех отзывов
    document.querySelectorAll(".review-photos").forEach(block => {
        const reviewId = block.dataset.reviewId;
        photos[reviewId] = [];

        block.querySelectorAll(".review-photo").forEach(img => {
            photos[reviewId].push(img.src);
        });
    });

    const modalEl = document.getElementById('photoModal');
    if (!modalEl) return;

    const modal = new Modal(modalEl, {
        backdrop: true,
        keyboard: true
    });

    const modalImg = document.getElementById("modalPhoto");
    const prevBtn = document.getElementById("prevPhoto");
    const nextBtn = document.getElementById("nextPhoto");

    if (!modalImg || !prevBtn || !nextBtn) return;

    // Открытие фото
    document.querySelectorAll(".review-photo").forEach(img => {
        img.addEventListener("click", () => {
            currentReviewId = img.dataset.reviewId;
            currentIndex = parseInt(img.dataset.index, 10);

            modalImg.src = photos[currentReviewId][currentIndex];
            modal.show();
        });
    });

    // Назад
    prevBtn.addEventListener("click", () => {
        if (!photos[currentReviewId]) return;

        currentIndex =
            (currentIndex - 1 + photos[currentReviewId].length) %
            photos[currentReviewId].length;

        modalImg.src = photos[currentReviewId][currentIndex];
    });

    // Вперёд
    nextBtn.addEventListener("click", () => {
        if (!photos[currentReviewId]) return;

        currentIndex =
            (currentIndex + 1) % photos[currentReviewId].length;

        modalImg.src = photos[currentReviewId][currentIndex];
    });

    // Клавиатура
    document.addEventListener("keydown", e => {
        if (e.key === "Escape") modal.hide();
        if (e.key === "ArrowLeft") prevBtn.click();
        if (e.key === "ArrowRight") nextBtn.click();
    });
});

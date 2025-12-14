console.log('slider_doctor.js loaded');

import Swiper from 'swiper';
import { Navigation, Autoplay, Controller } from 'swiper/modules';

Swiper.use([Navigation, Autoplay, Controller]);

document.addEventListener('DOMContentLoaded', () => {

    const mainSliderEl = document.querySelector('.main-slider');
    const navSliderEl  = document.querySelector('.nav-slider');

    // ✅ если слайдера нет — просто ничего не делаем
    if (!mainSliderEl || !navSliderEl) {
        console.log('Swiper sliders not found on this page');
        return; // ← ВАЖНО: теперь return ВНУТРИ функции
    }

    const interleaveOffset = 0.5;

    const mainSlider = new Swiper(mainSliderEl, {
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 3000,
        },
        loopAdditionalSlides: 10,
        grabCursor: true,
        watchSlidesProgress: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        on: {
            init() {
                this.autoplay.stop();
            },
            imagesReady() {
                this.el.classList.remove('loading');
                this.autoplay.start();
            },
            slideChangeTransitionEnd() {
                const captions = this.el.querySelectorAll('.caption');
                captions.forEach(c => c.classList.remove('show'));

                const activeSlide = this.slides[this.activeIndex];
                const caption = activeSlide?.querySelector('.caption');
                if (caption) caption.classList.add('show');
            },
            progress() {
                this.slides.forEach(slide => {
                    const slideProgress = slide.progress;
                    const innerOffset = this.width * interleaveOffset;
                    const innerTranslate = slideProgress * innerOffset;

                    const bg = slide.querySelector('.slide-bgimg');
                    if (bg) {
                        bg.style.transform = `translateX(${innerTranslate}px)`;
                    }
                });
            },
            touchStart() {
                this.slides.forEach(slide => {
                    slide.style.transition = '';
                });
            },
            setTransition(speed) {
                this.slides.forEach(slide => {
                    slide.style.transition = `${speed}ms`;
                    const bg = slide.querySelector('.slide-bgimg');
                    if (bg) bg.style.transition = `${speed}ms`;
                });
            },
        },
    });

    const navSlider = new Swiper(navSliderEl, {
        loop: true,
        loopAdditionalSlides: 10,
        speed: 1000,
        spaceBetween: 5,
        slidesPerView: 5,
        centeredSlides: true,
        touchRatio: 0.2,
        slideToClickedSlide: true,
        direction: 'vertical',
        on: {
            imagesReady() {
                this.el.classList.remove('loading');
            },
            click() {
                mainSlider.autoplay.stop();
            },
        },
    });

    mainSlider.controller.control = navSlider;
    navSlider.controller.control = mainSlider;
});

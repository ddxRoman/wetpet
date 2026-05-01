import $ from 'jquery';
window.$ = window.jQuery = $;

import './heic-convertor'; 
import './bootstrap';
import 'select2';
import './search_script';
import 'select2/dist/css/select2.css';
import './pages/add_organization';
import './pages/add_doctor'; 
import './add'; 
import './slider_doctor';
import './photo-slider';
import './account/account'; 
import './account/cropper-init'; 
import './account/toast'; 
import './reset_password/timer_mail_reset';
import './slider/personal_recommendations';
import './pages/pagination';


// Слушаем событие ошибки на стадии захвата (capture), 
// так как событие 'error' не всплывает.
document.addEventListener('error', function (event) {
    if (event.target.tagName.toLowerCase() === 'img') {
        const fallbackSrc = '/storage/logo/default-placeholder.webp'; // Путь к твоей заглушке
        
        // Предотвращаем бесконечный цикл, если заглушка тоже битая
        if (event.target.src !== window.location.origin + fallbackSrc) {
            event.target.src = fallbackSrc;
        }
    }
}, true);


// console.log('app.js loaded');
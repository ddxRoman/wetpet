/**
 * Всплывающие уведомления (toast) в правом верхнем углу.
 *
 *   import { notify, notifyAfterReload } from './notify';
 *   notify('Специалист успешно добавлен');                 // success по умолчанию
 *   notify('Не удалось сохранить', 'error');
 *   notify('Текст', 'success', { title: 'Готово', duration: 6000 });
 *   notifyAfterReload('Организация успешно добавлена!');   // показать после location.reload()
 *
 * Функции также доступны глобально: window.notify / window.notifyAfterReload.
 * Стили подключаются из самого модуля, отдельный CSS-файл не нужен.
 */

const STORAGE_KEY = 'wp_notify_queue';
const CONTAINER_ID = 'wp-notify-container';
const STYLE_ID = 'wp-notify-styles';

const TYPES = {
    success: {
        title: 'Готово',
        color: '#1a9b6c',
        bg: '#e7f7f0',
        icon: '<path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>',
    },
    error: {
        title: 'Ошибка',
        color: '#d64545',
        bg: '#fdecec',
        icon: '<path d="M7 7l10 10M17 7L7 17" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
    },
    warning: {
        title: 'Внимание',
        color: '#d98a00',
        bg: '#fff4de',
        icon: '<path d="M12 7v6M12 17v.01" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>',
    },
    info: {
        title: 'Информация',
        color: '#2b7de9',
        bg: '#e8f1fd',
        icon: '<path d="M12 11v6M12 7v.01" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>',
    },
};

const CSS = `
#${CONTAINER_ID}{position:fixed;top:20px;right:20px;z-index:11000;display:flex;flex-direction:column;gap:12px;width:360px;max-width:calc(100vw - 24px);pointer-events:none;}
.wp-toast{--wp-c:#1a9b6c;--wp-bg:#e7f7f0;position:relative;display:flex;align-items:flex-start;gap:12px;padding:14px 40px 16px 14px;background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(20,40,80,.16),0 2px 6px rgba(20,40,80,.08);overflow:hidden;pointer-events:auto;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;transform:translateX(120%);opacity:0;transition:transform .35s cubic-bezier(.2,.8,.2,1),opacity .35s ease;}
.wp-toast.is-visible{transform:translateX(0);opacity:1;}
.wp-toast.is-leaving{transform:translateX(120%);opacity:0;}
.wp-toast__icon{flex:0 0 34px;width:34px;height:34px;border-radius:50%;background:var(--wp-bg);color:var(--wp-c);display:flex;align-items:center;justify-content:center;}
.wp-toast__icon svg{width:20px;height:20px;}
.wp-toast__body{min-width:0;padding-top:1px;}
.wp-toast__title{font-size:14px;font-weight:700;color:#1f2937;line-height:1.3;}
.wp-toast__msg{font-size:13px;color:#4b5563;line-height:1.4;margin-top:2px;word-wrap:break-word;}
.wp-toast__close{position:absolute;top:8px;right:8px;width:26px;height:26px;border:0;border-radius:50%;background:transparent;color:#9ca3af;font-size:18px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.wp-toast__close:hover{background:#f3f4f6;color:#4b5563;}
.wp-toast__bar{position:absolute;left:0;bottom:0;height:3px;width:100%;background:var(--wp-c);transform-origin:left;animation:wp-toast-bar linear forwards;}
.wp-toast:hover .wp-toast__bar{animation-play-state:paused;}
@keyframes wp-toast-bar{from{transform:scaleX(1);}to{transform:scaleX(0);}}
@media (max-width:575px){#${CONTAINER_ID}{top:10px;right:12px;left:12px;width:auto;max-width:none;}}
@media (prefers-reduced-motion:reduce){.wp-toast{transition:opacity .2s ease;transform:none;}.wp-toast.is-leaving{transform:none;}}
`;

function ensureContainer() {
    if (!document.getElementById(STYLE_ID)) {
        const style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = CSS;
        document.head.appendChild(style);
    }

    let container = document.getElementById(CONTAINER_ID);
    if (!container) {
        container = document.createElement('div');
        container.id = CONTAINER_ID;
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
    }

    return container;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

export function notify(message, type = 'success', options = {}) {
    const cfg = TYPES[type] || TYPES.success;
    const duration = options.duration ?? (type === 'error' ? 7000 : 5000);
    const container = ensureContainer();

    const toast = document.createElement('div');
    toast.className = 'wp-toast';
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.style.setProperty('--wp-c', cfg.color);
    toast.style.setProperty('--wp-bg', cfg.bg);
    toast.innerHTML = `
        <div class="wp-toast__icon"><svg viewBox="0 0 24 24" aria-hidden="true">${cfg.icon}</svg></div>
        <div class="wp-toast__body">
            <div class="wp-toast__title">${escapeHtml(options.title || cfg.title)}</div>
            <div class="wp-toast__msg">${escapeHtml(message)}</div>
        </div>
        <button type="button" class="wp-toast__close" aria-label="Закрыть">&times;</button>
        <div class="wp-toast__bar" style="animation-duration:${duration}ms"></div>
    `;

    let closed = false;
    const close = () => {
        if (closed) return;
        closed = true;
        toast.classList.remove('is-visible');
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 400);
    };

    toast.querySelector('.wp-toast__close').addEventListener('click', close);
    toast.querySelector('.wp-toast__bar').addEventListener('animationend', close);

    container.appendChild(toast);
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('is-visible')));

    return close;
}

/** Показать уведомление после перезагрузки страницы (location.reload / редирект). */
export function notifyAfterReload(message, type = 'success', options = {}) {
    try {
        const queue = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        queue.push({ message, type, title: options.title });
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
    } catch (e) {
        // sessionStorage недоступен — показываем сразу
        notify(message, type, options);
    }
}

function flushQueue() {
    let queue = [];
    try {
        queue = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        sessionStorage.removeItem(STORAGE_KEY);
    } catch (e) {
        return;
    }
    queue.forEach(item => notify(item.message, item.type, { title: item.title }));
}

window.notify = notify;
window.notifyAfterReload = notifyAfterReload;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', flushQueue);
} else {
    flushQueue();
}

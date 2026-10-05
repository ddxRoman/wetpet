{{--
    Модальные уведомления сайта (создаются в админке Filament → Контент → Уведомления).
    Окно выводится поверх всего содержимого страницы. Какие уведомления и когда показывать, решает
    сервер (расписание, частота, аудитория: питомцы / город / регион), здесь — только отображение.
    Кнопка закрытия ✕ появляется через заданное число секунд (до этого идёт обратный отсчёт).
    Предпросмотр для админа: любая страница сайта с ?preview_notice=ID.
--}}
<script>
(function () {
    if (window.__siteNoticesInit || window.top !== window.self) return;
    window.__siteNoticesInit = true;

    var PENDING_URL = @json(route('site-notices.pending'));
    var SEEN_URL    = @json(url('/api/site-notices'));   // + /{id}/seen

    /* ---------- идентификатор гостя (у вошедших сервер использует id пользователя) ---------- */
    var vid = '';
    try {
        vid = localStorage.getItem('notice_vid') || '';
        if (!vid) {
            vid = (window.crypto && crypto.randomUUID)
                ? crypto.randomUUID()
                : 'v' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
            localStorage.setItem('notice_vid', vid);
        }
    } catch (e) {
        vid = 'v' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
    }

    var preview = new URLSearchParams(location.search).get('preview_notice');
    var csrfEl  = document.querySelector('meta[name="csrf-token"]');
    var queue = [];

    function el(tag, css, text) {
        var node = document.createElement(tag);
        if (css) node.style.cssText = css;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function markSeen(notice) {
        if (notice.preview) return;                      // предпросмотр в статистику не попадает
        try { sessionStorage.setItem('site_notice_s_' + notice.id, '1'); } catch (e) {}

        fetch(SEEN_URL + '/' + notice.id + '/seen', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfEl ? csrfEl.content : ''
            },
            body: JSON.stringify({ vid: vid })
        }).catch(function () {});
    }

    function showNext() {
        var notice = queue.shift();
        if (!notice) return;
        render(notice);
    }

    function render(notice) {
        var delay = Math.max(0, parseInt(notice.close_delay, 10) || 0);
        var canClose = delay === 0;
        var prevOverflow = document.documentElement.style.overflow;

        /* ---------- затемнение поверх всего ---------- */
        var overlay = el('div',
            'position:fixed;top:0;right:0;bottom:0;left:0;background:rgba(17,24,39,.6);z-index:2147483000;' +
            'display:flex;align-items:center;justify-content:center; padding:16px;overflow-y:auto;' +
            'opacity:0;transition:opacity .25s;');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        var box = el('div',
            'position:relative; padding-top:2%; background:#fff;border-radius:16px;width:100%;max-width:520px;max-height:92vh;overflow-y:auto;' +
            'box-shadow:0 20px 60px rgba(0,0,0,.35);transform:translateY(12px);transition:transform .25s;');

        /* ---------- кнопка закрытия с обратным отсчётом ---------- */
        var closeBtn = el('button',
            'position:absolute;top:10px;right:10px;width:34px;height:34px;border-radius:50%;border:none;' +
            'background:rgba(255,255,255,.92);box-shadow:0 1px 6px rgba(0,0,0,.25);font-size:16px;line-height:34px;' +
            'text-align:center;padding:0;z-index:2;');
        closeBtn.type = 'button';

        function paintClose() {
            if (canClose) {
                closeBtn.textContent = '✕';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.color = '#111827';
                closeBtn.setAttribute('aria-label', 'Закрыть');
                closeBtn.disabled = false;
            } else {
                closeBtn.textContent = String(left);
                closeBtn.style.cursor = 'default';
                closeBtn.style.color = '#9ca3af';
                closeBtn.setAttribute('aria-label', 'Закрыть можно через ' + left + ' сек.');
                closeBtn.disabled = true;
            }
        }

        var left = delay;
        paintClose();

        if (!canClose) {
            var timer = setInterval(function () {
                left -= 1;
                if (left <= 0) { clearInterval(timer); canClose = true; }
                paintClose();
            }, 1000);
        }

        /* ---------- содержимое ---------- */
        if (notice.image_url) {
            var img = el('img', 'display:block;width:100%;max-height:50vh;object-fit:contain;background:#f3f4f6;border-radius:16px 16px 0 0;');
            img.src = notice.image_url;
            img.alt = notice.title || 'Уведомление';
            box.appendChild(img);
        }

        var content = el('div', 'padding:20px 24px 22px;');
        if (!notice.image_url) content.style.paddingTop = '44px';   // место под крестик

        if (notice.title) {
            content.appendChild(el('div', 'font-size:20px;font-weight:700;color:#111827;margin-bottom:10px;line-height:1.3;', notice.title));
        }

        if (notice.body) {
            // Текст выводим построчно через textContent — разметка из текста не исполняется
            String(notice.body).split(/\r?\n/).forEach(function (line) {
                content.appendChild(line.trim() === ''
                    ? el('div', 'height:10px;')
                    : el('p', 'margin:0 0 6px;font-size:16px;line-height:1.5;color:#374151;word-break:break-word;', line));
            });
        }

        box.appendChild(content);
        box.appendChild(closeBtn);
        overlay.appendChild(box);

        /* ---------- закрытие ---------- */
        function close() {
            if (!canClose) return;
            document.removeEventListener('keydown', onKey, true);
            overlay.style.opacity = '0';
            setTimeout(function () {
                overlay.remove();
                document.documentElement.style.overflow = prevOverflow;
                setTimeout(showNext, 300);               // следующее уведомление из очереди
            }, 250);
        }

        function onKey(e) { if (e.key === 'Escape') { e.stopPropagation(); close(); } }

        closeBtn.addEventListener('click', close);
        overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', onKey, true);

        document.documentElement.style.overflow = 'hidden';
        document.body.appendChild(overlay);
        requestAnimationFrame(function () {
            overlay.style.opacity = '1';
            box.style.transform = 'translateY(0)';
        });

        markSeen(notice);
    }

    function start() {
        var url = PENDING_URL + '?vid=' + encodeURIComponent(vid) + (preview ? '&preview=' + encodeURIComponent(preview) : '');

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { notices: [] }; })
            .then(function (data) {
                queue = (data.notices || []).filter(function (n) {
                    // «Один раз за визит»: браузер помнит, что уже показывал в этой сессии
                    if (n.frequency === 'session' && !n.preview) {
                        try { return !sessionStorage.getItem('site_notice_s_' + n.id); } catch (e) { return true; }
                    }
                    return true;
                });
                showNext();
            })
            .catch(function () {});
    }

    // Запускаем чуть позже загрузки страницы, чтобы не мешать её отрисовке (и только один раз)
    var started = false;
    function init() {
        if (started) return;
        started = true;
        setTimeout(start, 600);
    }

    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);
})();
</script>

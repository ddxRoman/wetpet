{{--
    Профиль → «Отзывы на обжаловании».
    Показывает споры, в которых участвует пользователь: он владелец карточки, оспоривший отзыв,
    или автор оспоренного отзыва. По клику открывается окно: сверху текст отзыва, ниже —
    диалог пользователя с администратором и форма (текст + файлы).
--}}
@php
    $__disputes = \App\Models\ReviewDispute::involving(auth()->id())
        ->with(['review.user', 'review.reviewable', 'opener', 'messages'])
        ->latest()
        ->get();
@endphp

<style>
    .rd-list { display:flex; flex-direction:column; gap:12px; margin-top:14px; }
    .rd-item { border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px; background:#fff; cursor:pointer; transition:box-shadow .15s; position:relative; }
    .rd-item:hover { box-shadow:0 4px 14px rgba(0,0,0,.08); }
    .rd-item__head { display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:6px; }
    .rd-badge { display:inline-block; font-size:12px; padding:2px 10px; border-radius:20px; background:#f1f5f9; color:#475569; }
    .rd-badge--open { background:#fff4e5; color:#b26a00; }
    .rd-badge--restored { background:#e8f7ee; color:#1c7c43; }
    .rd-badge--removed { background:#fdecea; color:#b3261e; }
    .rd-badge--role { background:#eef2ff; color:#4338ca; }
    .rd-new { display:inline-block; min-width:20px; height:20px; line-height:20px; text-align:center; border-radius:10px; background:#dc3545; color:#fff; font-size:12px; padding:0 6px; }
    .rd-item__text { color:#374151; font-size:14px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .rd-item__meta { color:#6b7280; font-size:12px; margin-top:6px; }

    #rdOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:3000; align-items:center; justify-content:center; padding:16px; }
    #rdOverlay .rd-modal { background:#fff; border-radius:14px; width:100%; max-width:640px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 12px 48px rgba(0,0,0,.3); }
    .rd-modal__head { padding:16px 20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .rd-modal__body { padding:16px 20px; overflow-y:auto; }
    .rd-review { background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:16px; }
    .rd-stars { color:#f5a623; letter-spacing:2px; }
    .rd-chat { display:flex; flex-direction:column; gap:10px; margin:10px 0 14px; max-height:300px; overflow-y:auto; padding-right:4px; }
    .rd-msg { max-width:85%; padding:8px 12px; border-radius:12px; font-size:14px; white-space:pre-wrap; word-break:break-word; }
    .rd-msg--admin { align-self:flex-start; background:#eef2ff; }
    .rd-msg--mine { align-self:flex-end; background:#e8f7ee; }
    .rd-msg__who { font-size:11px; color:#6b7280; margin-bottom:2px; }
    .rd-files a { display:inline-block; margin:4px 6px 0 0; font-size:13px; }
    .rd-files img { max-width:120px; max-height:90px; border-radius:6px; border:1px solid #ddd; }
    .rd-form textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:14px; }
    .rd-form__row { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:8px; flex-wrap:wrap; }
    .rd-filelist { font-size:12px; color:#6b7280; margin-top:6px; }
</style>

<h2>Отзывы на обжаловании</h2>
<p style="color:#555;margin-bottom:0;">
    Здесь отзывы, которые оспорил владелец карточки (они скрыты до выяснения обстоятельств).
    Откройте отзыв, чтобы прочитать ответ администратора и написать ему — можно приложить файлы.
</p>

@if($__disputes->isEmpty())
    <p style="margin-top:20px;color:#6b7280;">У вас пока нет отзывов на обжаловании.</p>
@else
    <div class="rd-list">
        @foreach($__disputes as $__d)
            @php
                $__party  = $__d->partyOf(auth()->user());
                $__unread = $__d->messages->where('is_admin', true)->where('is_read', false)->where('party', $__party)->count();
                $__review = $__d->review;
            @endphp
            <div class="rd-item" data-dispute-id="{{ $__d->id }}" data-url="{{ route('account.review-disputes.show', $__d->id) }}">
                <div class="rd-item__head">
                    <strong>{{ $__review?->reviewable?->name ?? 'Карточка удалена' }}</strong>
                    <span>
                        <span class="rd-badge rd-badge--role">{{ $__party === 'owner' ? 'Вы оспорили отзыв' : 'Оспорен ваш отзыв' }}</span>
                        <span class="rd-badge rd-badge--{{ $__d->status }}">{{ $__d->status_label }}</span>
                        @if($__unread)
                            <span class="rd-new" data-unread title="Новые ответы администратора">{{ $__unread }}</span>
                        @endif
                    </span>
                </div>
                <div class="rd-item__text">{{ \Illuminate\Support\Str::limit($__review?->content ?: ($__review?->liked ?: $__review?->disliked), 220) ?: '—' }}</div>
                <div class="rd-item__meta">
                    Автор отзыва: {{ $__review?->user?->name ?? '—' }} · оспорено {{ $__d->created_at->format('d.m.Y') }}
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="rdOverlay">
    <div class="rd-modal">
        <div class="rd-modal__head">
            <strong id="rdTitle">Обжалование отзыва</strong>
            <button type="button" id="rdClose" style="background:none;border:none;font-size:20px;line-height:1;cursor:pointer;">✕</button>
        </div>
        <div class="rd-modal__body">
            <div class="rd-review" id="rdReview"></div>

            <div style="font-weight:600;" id="rdChatTitle">Диалог с администратором</div>
            <div class="rd-chat" id="rdChat"></div>

            <form class="rd-form" id="rdForm" enctype="multipart/form-data">
                <textarea name="message" id="rdMessage" rows="3" maxlength="3000" placeholder="Напишите сообщение администратору…"></textarea>
                <div class="rd-filelist" id="rdFileList"></div>
                <div class="rd-form__row">
                    <label style="cursor:pointer;color:#2563eb;font-size:14px;margin:0;">
                        📎 Приложить файлы
                        <input type="file" id="rdFiles" name="files[]" multiple style="display:none;"
                               accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    </label>
                    <button type="submit" class="save-btn" id="rdSend" style="margin:0;">Отправить</button>
                </div>
                <div id="rdError" style="display:none;color:#dc3545;font-size:13px;margin-top:8px;"></div>
                <div style="font-size:12px;color:#9ca3af;margin-top:6px;">До 5 файлов, не больше 10 МБ каждый: фото, PDF, Word, Excel, TXT.</div>
            </form>
            <div id="rdClosedNote" style="display:none;color:#6b7280;font-size:14px;margin-top:8px;"></div>
        </div>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('rdOverlay');
    if (!overlay) return;

    var $ = function (id) { return document.getElementById(id); };
    var chat = $('rdChat'), form = $('rdForm'), fileInput = $('rdFiles'), errorBox = $('rdError');
    var currentId = null, currentItem = null;
    var tokenEl = document.querySelector('meta[name="csrf-token"]');

    function el(tag, cls, text) {
        var node = document.createElement(tag);
        if (cls) node.className = cls;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function stars(n) { return '★★★★★'.slice(0, n) + '☆☆☆☆☆'.slice(0, 5 - n); }

    function renderReview(r) {
        var box = $('rdReview');
        box.innerHTML = '';
        box.appendChild(el('div', '', (r.entity_type ? r.entity_type + ': ' : '') + (r.entity || '—')));
        box.firstChild.style.fontWeight = '600';

        var meta = el('div', 'rd-stars', stars(r.rating || 0));
        meta.title = 'Оценка: ' + (r.rating || 0) + ' из 5';
        box.appendChild(meta);

        var who = el('div', '', 'Автор: ' + (r.author || '—') + (r.date ? ' · ' + r.date : ''));
        who.style.cssText = 'font-size:12px;color:#6b7280;margin:2px 0 8px;';
        box.appendChild(who);

        if (r.liked)    { var l = el('div'); var b1 = el('strong', '', 'Понравилось: '); b1.style.color = '#198754'; l.appendChild(b1); l.appendChild(document.createTextNode(r.liked)); box.appendChild(l); }
        if (r.disliked) { var d = el('div'); var b2 = el('strong', '', 'Не понравилось: '); b2.style.color = '#dc3545'; d.appendChild(b2); d.appendChild(document.createTextNode(r.disliked)); box.appendChild(d); }
        if (r.content)  { var c = el('p', '', r.content); c.style.cssText = 'margin:6px 0 0;white-space:pre-wrap;'; box.appendChild(c); }

        if (r.photos && r.photos.length) {
            var ph = el('div'); ph.style.cssText = 'display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;';
            r.photos.forEach(function (u) {
                var a = el('a'); a.href = u; a.target = '_blank'; a.rel = 'noopener';
                var im = el('img'); im.src = u; im.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:6px;border:1px solid #ddd;';
                a.appendChild(im); ph.appendChild(a);
            });
            box.appendChild(ph);
        }
    }

    function messageNode(m) {
        var node = el('div', 'rd-msg ' + (m.mine ? 'rd-msg--mine' : 'rd-msg--admin'));
        node.appendChild(el('div', 'rd-msg__who', m.who + ' · ' + m.time));
        if (m.message) node.appendChild(el('div', '', m.message));

        if (m.files && m.files.length) {
            var files = el('div', 'rd-files');
            m.files.forEach(function (f) {
                var a = el('a'); a.href = f.url; a.target = '_blank'; a.rel = 'noopener';
                if (f.is_image) { var im = el('img'); im.src = f.url; im.alt = f.name; a.appendChild(im); }
                else { a.textContent = '📄 ' + f.name; }
                files.appendChild(a);
            });
            node.appendChild(files);
        }
        return node;
    }

    function renderChat(messages) {
        chat.innerHTML = '';
        if (!messages.length) {
            var empty = el('div', '', 'Сообщений пока нет. Администратор ответит здесь.');
            empty.style.cssText = 'color:#9ca3af;font-size:14px;';
            chat.appendChild(empty);
        } else {
            messages.forEach(function (m) { chat.appendChild(messageNode(m)); });
        }
        chat.scrollTop = chat.scrollHeight;
    }

    function setOpenState(isOpen, label) {
        form.style.display = isOpen ? 'block' : 'none';
        var note = $('rdClosedNote');
        note.style.display = isOpen ? 'none' : 'block';
        note.textContent = isOpen ? '' : 'Обжалование закрыто: ' + (label || '') + '. Новые сообщения отправить нельзя.';
    }

    function showError(text) { errorBox.textContent = text; errorBox.style.display = text ? 'block' : 'none'; }

    function openDispute(item) {
        currentItem = item;
        currentId = item.dataset.disputeId;
        showError('');
        form.reset();
        $('rdFileList').textContent = '';
        $('rdReview').textContent = 'Загрузка…';
        chat.innerHTML = '';
        overlay.style.display = 'flex';

        fetch(item.dataset.url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (data) {
                $('rdChatTitle').textContent = data.party === 'owner'
                    ? 'Ваш диалог с администратором (как владельца карточки)'
                    : 'Ваш диалог с администратором (как автора отзыва)';
                renderReview(data.review);
                renderChat(data.messages);
                setOpenState(data.is_open, data.status_label);

                // Ответы прочитаны — убираем счётчик у отзыва и в шапке
                var badge = item.querySelector('[data-unread]');
                if (badge) {
                    var n = parseInt(badge.textContent, 10) || 0;
                    badge.remove();
                    document.querySelectorAll('[data-rd-unread]').forEach(function (b) {
                        var left = (parseInt(b.textContent, 10) || 0) - n;
                        if (left > 0) b.textContent = left; else b.remove();
                    });
                    if (!document.querySelector('[data-rd-unread]')) {
                        document.querySelectorAll('[data-rd-unread-dot]').forEach(function (d) { d.remove(); });
                    }
                }
            })
            .catch(function () { $('rdReview').textContent = 'Не удалось загрузить обжалование. Обновите страницу.'; });
    }

    function close() { overlay.style.display = 'none'; currentId = null; }

    document.querySelectorAll('.rd-item').forEach(function (item) {
        item.addEventListener('click', function () { openDispute(item); });
    });
    $('rdClose').addEventListener('click', close);
    overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.style.display === 'flex') close(); });

    fileInput.addEventListener('change', function () {
        $('rdFileList').textContent = fileInput.files.length
            ? 'Выбрано файлов: ' + fileInput.files.length + ' — ' + Array.prototype.map.call(fileInput.files, function (f) { return f.name; }).join(', ')
            : '';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!currentId) return;

        var btn = $('rdSend');
        btn.disabled = true;
        showError('');

        fetch('/account/review-disputes/' + currentId + '/messages', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': tokenEl ? tokenEl.content : '' },
            body: new FormData(form)
        })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                btn.disabled = false;
                if (!res.ok) {
                    var msg = res.data.message || 'Не удалось отправить сообщение.';
                    if (res.data.errors) msg = Object.values(res.data.errors)[0][0];
                    showError(msg);
                    return;
                }
                if (chat.children.length === 1 && !chat.firstChild.classList.contains('rd-msg')) chat.innerHTML = '';
                chat.appendChild(messageNode(res.data.message));
                chat.scrollTop = chat.scrollHeight;
                form.reset();
                $('rdFileList').textContent = '';
            })
            .catch(function () { btn.disabled = false; showError('Нет соединения. Попробуйте ещё раз.'); });
    });

    // Переход по ссылке из шапки (#review-disputes), когда страница профиля уже открыта
    window.addEventListener('hashchange', function () {
        if (location.hash === '#review-disputes') {
            var btn = document.querySelector('[data-tab="review-disputes"]');
            if (btn) btn.click();
        }
    });
})();
</script>

@php
    $__isPremiumT = $user->isPremium();
    $__trusted    = $user->trustedUsers()->orderBy('trusted_users.created_at', 'desc')->get();
@endphp

<div class="tab-content" id="trusted" style="display:none;">
    <h2>Доверенные пользователи</h2>

    @unless($__isPremiumT)
        <div class="premium-status">
            ⭐ Доверенные пользователи и лента их отзывов доступны с Премиум-подпиской.
            <a href="#premium" class="js-open-premium">Подробнее о Премиум</a>
            @if($__trusted->isNotEmpty())
                <div class="section-hint" style="margin-top:6px;">Ваш список сохранён и снова заработает после продления подписки.</div>
            @endif
        </div>
    @endunless

    <div class="trusted-switch">
        <button type="button" class="trusted-switch__btn active" data-trusted-view="feed">Лента</button>
        <button type="button" class="trusted-switch__btn" data-trusted-view="list">Мои доверенные ({{ $__trusted->count() }})</button>
    </div>

    {{-- Лента --}}
    <div id="trusted-view-feed">
        @if($__isPremiumT)
            <div id="trusted-feed"></div>
            <button type="button" id="trusted-feed-more" class="save-btn" style="display:none; margin-top:10px;">Показать ещё</button>
        @endif
    </div>

    {{-- Список --}}
    <div id="trusted-view-list" style="display:none;">
        @if($__trusted->isEmpty())
            <p class="empty-message">Список пока пуст. Откройте профиль автора отзыва (клик по имени в отзыве) и нажмите «В доверенные».</p>
        @else
            <ul class="trusted-list">
                @foreach($__trusted as $__t)
                    <li data-trusted-id="{{ $__t->id }}">
                        <a href="{{ route('user.profile', $__t->id) }}" class="trusted-list__user">
                            <img src="{{ $__t->avatar ? asset('storage/' . $__t->avatar) : asset('storage/avatars/default/default_avatar.webp') }}"
                                 alt="" width="40" height="40" style="border-radius:50%; object-fit:cover;">
                            <span>{{ $__t->name }}</span>
                        </a>
                        <button type="button" class="trusted-remove" data-user-id="{{ $__t->id }}">Убрать</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<style>
    .trusted-switch { display: flex; gap: 8px; margin: 14px 0; }
    .trusted-switch__btn { border: 1px solid #e0e4f1; background: #fff; padding: 8px 16px; border-radius: 20px; cursor: pointer; }
    .trusted-switch__btn.active { background: #1ccfc9; border-color: #1ccfc9; color: #fff; }
    .trusted-list { list-style: none; padding: 0; margin: 0; }
    .trusted-list li { display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 0; border-bottom: 1px solid #eef0f6; }
    .trusted-list__user { display: flex; align-items: center; gap: 10px; color: inherit; text-decoration: none; }
    .trusted-remove { border: 1px solid #e0e4f1; background: #fff; padding: 5px 12px; border-radius: 8px; cursor: pointer; }
    .trusted-review { border: 1px solid #e0e4f1; border-radius: 14px; padding: 14px 16px; margin-bottom: 12px; background: #fff; }
    .trusted-review__head { display: flex; justify-content: space-between; gap: 10px; font-size: 14px; color: #666; margin-bottom: 6px; }
    .trusted-review__author { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; color: #222; text-decoration: none; }
    .trusted-review__photos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .trusted-review__photos img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; }
</style>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const feedBox = document.getElementById('trusted-feed');
    const moreBtn = document.getElementById('trusted-feed-more');
    let page = 0, loading = false, loaded = false;

    // Переключатель «Лента / Мои доверенные»
    document.querySelectorAll('[data-trusted-view]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-trusted-view]').forEach(b => b.classList.toggle('active', b === btn));
            document.getElementById('trusted-view-feed').style.display = btn.dataset.trustedView === 'feed' ? 'block' : 'none';
            document.getElementById('trusted-view-list').style.display = btn.dataset.trustedView === 'list' ? 'block' : 'none';
        });
    });

    // Ссылка «Подробнее о Премиум» открывает вкладку «Премиум»
    document.querySelector('.js-open-premium')?.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector('.tab-btn[data-tab="premium"]')?.click();
    });

    // Лента грузится при первом открытии вкладки
    async function loadFeed() {
        if (!feedBox || loading) return;
        loading = true;
        try {
            const res = await fetch(`/account/trusted/feed?page=${page + 1}`, {
                headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'error');
            page += 1;
            loaded = true;
            if (page === 1 && data.total === 0) {
                feedBox.innerHTML = '<p class="empty-message">В ленте пока пусто. Добавьте пользователей в доверенные — и их новые отзывы появятся здесь.</p>';
            } else {
                feedBox.insertAdjacentHTML('beforeend', data.html);
            }
            moreBtn.style.display = data.has_more ? 'inline-block' : 'none';
        } catch (e) {
            feedBox.insertAdjacentHTML('beforeend', '<p class="empty-message">Не удалось загрузить ленту. Попробуйте обновить страницу.</p>');
        } finally {
            loading = false;
        }
    }

    document.querySelector('.tab-btn[data-tab="trusted"]')?.addEventListener('click', () => { if (!loaded) loadFeed(); });
    if (location.hash === '#trusted') loadFeed();
    moreBtn?.addEventListener('click', loadFeed);

    // Убрать из доверенных
    document.querySelectorAll('.trusted-remove').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Убрать пользователя из доверенных?')) return;
            const res = await fetch(`/trusted/${btn.dataset.userId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (res.ok) {
                btn.closest('li')?.remove();
                // Лента могла измениться — загрузим заново при следующем открытии
                if (feedBox) { feedBox.innerHTML = ''; page = 0; loaded = false; moreBtn.style.display = 'none'; }
            }
        });
    });
})();
</script>

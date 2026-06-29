@if($activeTab === 'promotions')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">🏷️ Акции и спецпредложения</h5>
        @php $hasPackage = auth()->user()->hasPromoPackage(); @endphp
        @if($hasPackage)
            <span class="badge bg-success rounded-pill px-3" style="font-size:12px;">✓ Рекламный пакет активен</span>
        @else
            <span class="badge bg-secondary rounded-pill px-3" style="font-size:12px;">Рекламный пакет не активен</span>
        @endif
    </div>

    @if(!$hasPackage)
        {{-- Нет пакета — показываем заглушку --}}
        <div class="text-center py-5">
            <div style="font-size:48px;">🔒</div>
            <h6 class="fw-bold mt-3">Это платная функция</h6>
            <p class="text-muted" style="font-size:14px;max-width:400px;margin:0 auto;">
                Акции и спецпредложения доступны только пользователям с активным рекламным пакетом.
                Ваши акции будут показаны на главной странице и в карточке организации.
            </p>
            <a href="#" class="btn btn-primary rounded-pill px-4 mt-3">Подключить рекламный пакет</a>
        </div>
    @else
        @php $promotions = $entity->promotions()->latest()->get(); @endphp

        {{-- Лимит --}}
        @if($promotions->count() >= 3)
            <div class="alert alert-warning rounded-3 mb-4" style="font-size:13px;">
                ⚠️ Достигнут лимит в 3 акции. Удалите одну, чтобы добавить новую.
            </div>
        @endif

        {{-- Форма добавления --}}
        @if($promotions->count() < 3)
        <div class="border rounded-3 p-4 mb-4 bg-light">
            <h6 class="fw-semibold mb-3">Добавить акцию</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium small">Название акции <span class="text-danger">*</span></label>
                    <input type="text" id="promo-title" class="form-control" placeholder="Комплексная вакцинация" maxlength="100">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium small">Описание (необязательно)</label>
                    <textarea id="promo-description" class="form-control" rows="2" placeholder="Кратко опишите условия акции..."></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Старая цена</label>
                    <input type="number" id="promo-old-price" class="form-control" placeholder="2500" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Новая цена</label>
                    <input type="number" id="promo-new-price" class="form-control" placeholder="1800" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Бейдж (необязательно)</label>
                    <input type="text" id="promo-badge" class="form-control" placeholder="-30% или Топ цена" maxlength="20">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Действует до</label>
                    <input type="date" id="promo-expires" class="form-control" min="{{ now()->toDateString() }}">
                </div>
            </div>
            <button class="btn btn-primary mt-3 rounded-pill px-4" id="promo-save-btn">
                + Добавить акцию
            </button>
            <div id="promo-save-status" class="mt-2 text-muted small"></div>
        </div>
        @endif

        {{-- Список акций --}}
        <div id="promo-list">
            @forelse($promotions as $promo)
            <div class="border rounded-3 p-3 mb-3 bg-white d-flex justify-content-between align-items-start gap-3" id="promo-row-{{ $promo->id }}">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="fw-semibold" style="font-size:14px;">{{ $promo->title }}</span>
                        @if($promo->badge)
                            <span class="badge bg-danger rounded-pill" style="font-size:11px;">{{ $promo->badge }}</span>
                        @endif
                        @if(!$promo->is_active)
                            <span class="badge bg-secondary rounded-pill" style="font-size:11px;">Отключена</span>
                        @endif
                    </div>
                    @if($promo->description)
                        <div class="text-muted mb-1" style="font-size:13px;">{{ $promo->description }}</div>
                    @endif
                    <div class="d-flex gap-3 flex-wrap" style="font-size:13px;">
                        @if($promo->old_price)
                            <span><s class="text-muted">{{ number_format($promo->old_price, 0, '.', ' ') }} ₽</s>
                            → <strong class="text-success">{{ number_format($promo->new_price, 0, '.', ' ') }} ₽</strong></span>
                        @endif
                        @if($promo->expires_at)
                            <span class="text-muted">До {{ $promo->expires_at->format('d.m.Y') }}</span>
                        @endif
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger rounded-pill flex-shrink-0 btn-delete-promo"
                        data-id="{{ $promo->id }}">
                    Удалить
                </button>
            </div>
            @empty
            <div class="text-center text-muted py-4" id="no-promo-row">Акции не добавлены</div>
            @endforelse
        </div>
    @endif
</div>

<script>
(function () {
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content;
    const type     = '{{ $type }}';
    const entityId = {{ $entityId }};

    // ── Сохранение акции ────────────────────────────────
    document.getElementById('promo-save-btn')?.addEventListener('click', function () {
        const title    = document.getElementById('promo-title')?.value.trim();
        const status   = document.getElementById('promo-save-status');

        if (!title) {
            status.textContent = 'Введите название акции.';
            status.className   = 'mt-2 text-danger small';
            return;
        }

        this.disabled    = true;
        status.textContent = 'Сохранение...';
        status.className   = 'mt-2 text-muted small';

        fetch('/owner/promotions/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                entity_type: type,
                entity_id:   entityId,
                title:       title,
                description: document.getElementById('promo-description')?.value.trim() || null,
                old_price:   document.getElementById('promo-old-price')?.value || null,
                new_price:   document.getElementById('promo-new-price')?.value || null,
                badge:       document.getElementById('promo-badge')?.value.trim() || null,
                expires_at:  document.getElementById('promo-expires')?.value || null,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                status.textContent = data.message || 'Ошибка сохранения.';
                status.className   = 'mt-2 text-danger small';
                this.disabled = false;
            }
        })
        .catch(() => {
            status.textContent = 'Ошибка соединения.';
            status.className   = 'mt-2 text-danger small';
            this.disabled = false;
        });
    });

    // ── Удаление акции ───────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-promo');
        if (!btn) return;
        if (!confirm('Удалить акцию?')) return;

        btn.disabled     = true;
        btn.textContent  = '…';

        fetch(`/owner/promotions/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`promo-row-${btn.dataset.id}`)?.remove();
                const list = document.getElementById('promo-list');
                if (list && list.querySelectorAll('[id^="promo-row-"]').length === 0) {
                    list.innerHTML = '<div class="text-center text-muted py-4">Акции не добавлены</div>';
                }
                // Перезагружаем чтобы форма добавления снова появилась
                window.location.reload();
            } else {
                btn.disabled = false;
                btn.textContent = 'Удалить';
            }
        });
    });
})();
</script>
@endif

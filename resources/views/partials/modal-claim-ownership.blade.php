{{--
    Универсальная модалка "Заявить права на объект".
    Подключать на любую публичную карточку:
    @include('partials.modal-claim-ownership', ['entityType' => 'organization', 'entityId' => $organization->id])

    Кнопка-триггер должна иметь:
    data-bs-toggle="modal" data-bs-target="#claimOwnershipModal"
--}}

@auth
<div class="modal fade" id="claimOwnershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Подтверждение владения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <p class="text-muted" style="font-size:14px;">
                    Чтобы получить доступ к управлению этой карточкой, загрузите документ,
                    подтверждающий вашу личность или право владения — например, выписку
                    ЕГРЮЛ/ЕГРИП, диплом, доверенность или трудовой договор.
                </p>

                <div id="claim-success-state" class="d-none text-center py-4">
                    <div style="font-size:48px;">✅</div>
                    <h6 class="fw-bold mt-2">Заявка отправлена</h6>
                    <p class="text-muted small mb-0">
                        Мы проверим документы и подтвердим доступ в течение 1–2 рабочих дней.
                    </p>
                </div>

                <form id="claim-ownership-form">
                    @csrf
                    <input type="hidden" name="entity_type" value="{{ $entityType }}">
                    <input type="hidden" name="entity_id" value="{{ $entityId }}">

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Документы (PDF, JPG, PNG — до 120 МБ каждый)</label>
                        <input type="file" name="documents[]" id="claim-documents-input"
                               class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp"
                               multiple required>
                        <div id="claim-files-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Комментарий (необязательно)</label>
                        <input type="text" name="comment" class="form-control" placeholder="Например: я директор организации">
                    </div>

                    <div id="claim-error" class="alert alert-danger d-none" style="font-size:13px;"></div>

                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2">
                        Отправить на проверку
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form    = document.getElementById('claim-ownership-form');
    const errBox  = document.getElementById('claim-error');
    const success = document.getElementById('claim-success-state');
    const fileInput = document.getElementById('claim-documents-input');
    const preview   = document.getElementById('claim-files-preview');

    if (!form || form.dataset.bound) return;
    form.dataset.bound = '1';

    // Превью выбранных файлов
    fileInput?.addEventListener('change', function () {
        preview.innerHTML = '';
        [...this.files].forEach(file => {
            const badge = document.createElement('span');
            badge.className = 'badge rounded-pill d-flex align-items-center gap-1';
            badge.style.cssText = 'background:#eef3ff;color:#374151;font-size:12px;font-weight:500;border:1px solid #c7dbe7;padding:5px 10px;';
            const icon = file.type === 'application/pdf' ? '📄' : '🖼️';
            const size = (file.size / 1024 / 1024).toFixed(1);
            badge.textContent = icon + ' ' + file.name + ' (' + size + ' МБ)';
            preview.appendChild(badge);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errBox.classList.add('d-none');

        const fd = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка…';

        fetch("{{ route('owner.claim') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: fd,
        })
        .then(async (r) => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => {
            form.classList.add('d-none');
            success.classList.remove('d-none');
        })
        .catch((err) => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Отправить на проверку';

            let message = 'Не удалось отправить заявку. Попробуйте ещё раз.';
            if (err?.errors) {
                message = Object.values(err.errors).flat().join(' ');
            } else if (err?.message) {
                message = err.message;
            }
            errBox.textContent = message;
            errBox.classList.remove('d-none');
        });
    });

    // Сброс формы при повторном открытии модалки
    document.getElementById('claimOwnershipModal')?.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.classList.remove('d-none');
        success.classList.add('d-none');
        errBox.classList.add('d-none');
        preview.innerHTML = '';
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = false;
        btn.textContent = 'Отправить на проверку';
    });
})();
</script>
@endauth
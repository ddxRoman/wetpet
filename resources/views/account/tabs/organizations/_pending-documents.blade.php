{{--
    Форма загрузки документов для организации, ожидающей подтверждения.
    Ожидает: $organizationOwner (модель OrganizationOwner с подгруженными documents и organization)
--}}

<div class="card border-warning shadow-sm">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-warning text-dark">⏳ На проверке</span>
            <h5 class="mb-0">{{ $organizationOwner->organization->name ?? 'Организация' }}</h5>
        </div>

        <p class="text-muted mb-4" style="font-size: 0.9rem;">
            Заявка на управление этой организацией получена. Чтобы ускорить проверку,
            загрузите документ, подтверждающий вашу личность или право владения
            (свидетельство о регистрации, доверенность, паспорт руководителя и т.п.).
        </p>

        @if($organizationOwner->admin_comment)
            <div class="alert alert-danger" style="font-size: 0.9rem;">
                <strong>Комментарий администратора:</strong> {{ $organizationOwner->admin_comment }}
            </div>
        @endif

        {{-- Список уже загруженных документов --}}
        @if($organizationOwner->documents->isNotEmpty())
            <div class="mb-4">
                <div class="fw-bold mb-2" style="font-size: 0.9rem;">Загруженные документы:</div>
                <div class="d-flex flex-column gap-2" id="documents-list-{{ $organizationOwner->id }}">
                    @foreach($organizationOwner->documents as $doc)
                        <div class="d-flex align-items-center justify-content-between border rounded-2 px-3 py-2"
                             id="document-row-{{ $doc->id }}">
                            <a href="{{ Storage::url($doc->path) }}" target="_blank" class="text-decoration-none">
                                📄 {{ $doc->original_name ?: 'Документ' }}
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete-document"
                                    data-id="{{ $doc->id }}">
                                Удалить
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Форма загрузки нового документа --}}
        <form class="document-upload-form" data-owner-row-id="{{ $organizationOwner->id }}">
            @csrf
            <input type="hidden" name="entity_type" value="organization">
            <input type="hidden" name="owner_row_id" value="{{ $organizationOwner->id }}">

            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label fw-medium small">Файл (PDF, JPG, PNG)</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-medium small">Комментарий (необязательно)</label>
                    <input type="text" name="comment" class="form-control" placeholder="Например: выписка ЕГРЮЛ">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3 rounded-pill px-4">
                ⬆️ Загрузить документ
            </button>
            <div class="upload-status mt-2 text-muted small"></div>
        </form>

    </div>
</div>

{{-- JS подключается один раз на странице — см. account.blade.php или общий layout --}}
<script>
(function () {
    if (window.__documentUploadInit) return;
    window.__documentUploadInit = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('document-upload-form')) return;

        e.preventDefault();
        const status = form.querySelector('.upload-status');
        const fd = new FormData(form);
        status.textContent = 'Загрузка…';

        fetch("{{ route('owner.documents.upload') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                status.textContent = 'Документ загружен ✓';
                setTimeout(() => window.location.reload(), 700);
            } else {
                status.textContent = 'Ошибка загрузки';
            }
        })
        .catch(() => { status.textContent = 'Ошибка загрузки'; });
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-document');
        if (!btn) return;

        if (!confirm('Удалить документ?')) return;

        fetch(`/owner/documents/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`document-row-${btn.dataset.id}`)?.remove();
            }
        });
    });
})();
</script>

{{--
    Заглушка вместо формы редактирования, пока владение не подтверждено админом.
    Параметры: $name — название карточки, $url — страница заявки (документы и чат с админом).
--}}
<div class="card border-warning shadow-sm my-3">
    <div class="card-body p-4 text-center">
        <div class="mb-2"><span class="badge bg-warning text-dark" style="font-size:14px;">⏳ На проверке</span></div>
        <h5 class="mb-2">{{ $name }}</h5>
        <p class="text-muted mb-3" style="font-size:.95rem;">
            Заявка на управление получена. Права на редактирование появятся после того, как администратор подтвердит владение.
            Чтобы ускорить проверку, загрузите документы, подтверждающие, что карточка принадлежит вам.
        </p>
        <a href="{{ $url }}" class="btn btn-warning fw-bold" style="border-radius: 10px;">Загрузить документы / открыть заявку</a>
    </div>
</div>

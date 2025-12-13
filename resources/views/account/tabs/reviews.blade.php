<div class="tab-content" id="reviews" style="display:none;">
    <h2>Ваши отзывы {{ $user->name }}</h2>
    <div id="reviews-list" class="reviews-container">
        <p class="empty-message">Загрузка отзывов...</p>
    </div>
</div>

<script>
    // Передаём серверные данные в JS
    window.userId = {{ auth()->id() }};
    window.csrfToken = '{{ csrf_token() }}';
</script>
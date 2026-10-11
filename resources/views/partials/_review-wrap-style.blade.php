{{--
    Перенос длинных слов/строк без пробелов в ТЕКСТЕ отзыва, чтобы он не выходил за рамки карточки.
    Правило намеренно не стоит на самой карточке: иначе имя автора в шапке (flex-строка) сжимается
    и переносится посреди слова.
--}}
<style>
    .review-card {
        min-width: 0;
    }

    .review-card [data-field],
    .review-card p,
    .review-card .review-content,
    .review-card .review-liked,
    .review-card .review-disliked {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
</style>

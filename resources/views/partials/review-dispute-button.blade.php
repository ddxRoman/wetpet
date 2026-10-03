{{--
    Кнопка «Оспорить отзыв» на карточке отзыва. Видна только подтверждённому владельцу карточки,
    которой оставлен отзыв (клиника, организация, профиль врача или специалиста).
    По нажатию — подтверждение «отзыв будет скрыт до выяснения обстоятельств»; после него отзыв
    скрывается и уходит в админку (раздел «Спорные отзывы»).
    Параметр: $review
--}}
@auth
    @if($review->canBeDisputedBy(auth()->user()))
        <div class="mt-3 pt-2 border-top d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-danger js-dispute-open"
                    data-url="{{ route('reviews.dispute.store', $review->id) }}">
                ⚖️ Оспорить отзыв
            </button>
        </div>
    @endif
@endauth

@once
    @auth
        @if(count(array_filter(\App\Models\Review::ownedReviewableMap(auth()->id()))))
            <div id="disputeReviewOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;padding:16px;">
                <div style="background:#fff;border-radius:12px;max-width:480px;width:100%;padding:22px 22px 18px;box-shadow:0 10px 40px rgba(0,0,0,.25);">
                    <div id="disputeStepForm">
                        <h5 style="margin:0 0 10px;">Оспорить отзыв</h5>
                        <p style="margin:0 0 12px;color:#444;">
                            Отзыв будет <strong>скрыт до выяснения обстоятельств</strong>. Мы свяжемся с вами и с автором отзыва,
                            после чего администратор примет решение: вернуть отзыв или удалить его.
                        </p>
                        <label for="disputeReason" class="form-label" style="font-size:13px;color:#555;">Почему вы оспариваете отзыв? (необязательно)</label>
                        <textarea id="disputeReason" class="form-control" rows="4" maxlength="2000"
                                  placeholder="Например: этот человек не был нашим клиентом; в отзыве неправда..."></textarea>
                        <div id="disputeError" style="display:none;color:#dc3545;font-size:13px;margin-top:8px;"></div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light" id="disputeCancel">Отмена</button>
                            <button type="button" class="btn btn-danger" id="disputeConfirm">Оспорить отзыв</button>
                        </div>
                    </div>
                    <div id="disputeStepDone" style="display:none;">
                        <h5 style="margin:0 0 10px;">✓ Отзыв скрыт</h5>
                        <p id="disputeDoneText" style="margin:0 0 14px;color:#444;"></p>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('account') }}#review-disputes" class="btn btn-outline-primary">Перейти к обжалованиям</a>
                            <button type="button" class="btn btn-primary" id="disputeDoneClose">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var overlay   = document.getElementById('disputeReviewOverlay');
                if (!overlay) return;

                // Окно выводится внутри карточки отзыва, а карточка после оспаривания удаляется со страницы —
                // переносим окно в <body>, чтобы оно не исчезло вместе с ней
                document.body.appendChild(overlay);

                var stepForm  = document.getElementById('disputeStepForm');
                var stepDone  = document.getElementById('disputeStepDone');
                var reason    = document.getElementById('disputeReason');
                var errorBox  = document.getElementById('disputeError');
                var confirmBtn = document.getElementById('disputeConfirm');
                var currentBtn = null;

                function open(btn) {
                    currentBtn = btn;
                    reason.value = '';
                    errorBox.style.display = 'none';
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Оспорить отзыв';
                    stepForm.style.display = 'block';
                    stepDone.style.display = 'none';
                    overlay.style.display = 'flex';
                }

                function close() {
                    overlay.style.display = 'none';
                    currentBtn = null;
                }

                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('.js-dispute-open');
                    if (btn) { open(btn); }
                });

                document.getElementById('disputeCancel').addEventListener('click', close);
                document.getElementById('disputeDoneClose').addEventListener('click', close);
                overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.style.display === 'flex') close(); });

                confirmBtn.addEventListener('click', function () {
                    if (!currentBtn) return;

                    var card = currentBtn.closest('.review-card');
                    var token = document.querySelector('meta[name="csrf-token"]');

                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Отправляем…';
                    errorBox.style.display = 'none';

                    fetch(currentBtn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token ? token.content : ''
                        },
                        body: JSON.stringify({ reason: reason.value })
                    })
                        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
                        .then(function (res) {
                            if (!res.ok) {
                                var msg = res.data.message || 'Не удалось оспорить отзыв. Попробуйте ещё раз.';
                                if (res.data.errors) { msg = Object.values(res.data.errors)[0][0]; }
                                errorBox.textContent = msg;
                                errorBox.style.display = 'block';
                                confirmBtn.disabled = false;
                                confirmBtn.textContent = 'Оспорить отзыв';
                                return;
                            }

                            // Отзыв скрыт — убираем его со страницы
                            if (card) {
                                card.style.transition = 'opacity .3s';
                                card.style.opacity = '0';
                                setTimeout(function () { card.remove(); }, 300);
                            }

                            document.getElementById('disputeDoneText').textContent = res.data.message || 'Отзыв скрыт до выяснения обстоятельств.';
                            stepForm.style.display = 'none';
                            stepDone.style.display = 'block';
                        })
                        .catch(function () {
                            errorBox.textContent = 'Нет соединения. Проверьте интернет и попробуйте ещё раз.';
                            errorBox.style.display = 'block';
                            confirmBtn.disabled = false;
                            confirmBtn.textContent = 'Оспорить отзыв';
                        });
                });
            })();
            </script>
        @endif
    @endauth
@endonce

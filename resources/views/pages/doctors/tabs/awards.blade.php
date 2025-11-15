<div class="tab-pane fade" id="awards" role="tabpanel" aria-labelledby="awards-tab">
    <div class="row g-4 mt-3">
        @forelse($clinic->awards as $award)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#awardModal{{ $award->id }}">
                        <img src="{{ asset('storage/' . $award->image) }}" class="card-img-top" alt="{{ $award->title }}">
                    </a>
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">{{ $award->title }}</h6>
                    </div>
                </div>
            </div>

            <!-- Модальное окно -->
            <div class="modal fade" id="awardModal{{ $award->id }}" tabindex="-1" aria-labelledby="awardModalLabel{{ $award->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <h5 class="modal-title" id="awardModalLabel{{ $award->id }}">{{ $award->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <img src="{{ asset('storage/' . $award->image) }}" class="img-fluid rounded" alt="{{ $award->title }}">
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-0">{{ $award->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center">У этой клиники пока нет наград или сертификатов.</p>
        @endforelse
    </div>
</div>

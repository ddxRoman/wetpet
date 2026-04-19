    <div class="tab-pane fade {{ $activeTab === 'awards' ? 'show active' : '' }}" id="awards" role="tabpanel">
        <h4>Награды</h4>
{{-- Награды --}}

    {{-- Модальное окно со слайдером --}}
    @if(($clinic->awards ?? [])->count())
    <div class="modal fade" id="awardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <div id="awardCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @foreach($clinic->awards as $index => $award)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <div class="text-center p-3">
                                        <img src="{{ asset('storage/' . $award->image) }}" 
                                             class="img-fluid rounded mb-3 award-img"
                                             alt="{{ $award->title }}"
                                             style="max-height: 70vh; object-fit: contain;">
                                        <h5 class="fw-bold">{{ $award->title }}</h5>
                                        <p class="text-muted mb-0">{{ $award->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- Стрелки навигации --}}
                        <button class="carousel-control-prev" type="button" data-bs-target="#awardCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#awardCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    @endif
</div>
    </div>
</div>
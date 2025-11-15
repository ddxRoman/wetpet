                        {{-- Услуги --}}
                        <div class="tab-pane fade" id="services" role="tabpanel">
                            @php
                            // Все услуги, связанные с клиникой
                            $services = $clinic->services ?? collect();

                            // Сортировка по специализации и названию
                            $services = $services->sortBy([
                            fn($a, $b) => strcasecmp($a->specialization ?? '', $b->specialization ?? ''),
                            fn($a, $b) => strcasecmp($a->name ?? '', $b->name ?? ''),
                            ]);

                            // Загружаем цены
                            $prices = \App\Models\Price::where('clinic_id', $clinic->id)->get()->keyBy('service_id');

                            // Группировка по специализациям
                            $grouped = $services->groupBy(fn($s) => $s->specialization ?? 'Без специализации');

                            // Алфавит (только используемые буквы)
                            $letters = collect($grouped->keys())
                            ->map(fn($key) => mb_strtoupper(mb_substr($key, 0, 1)))
                            ->unique()
                            ->sort()
                            ->values();
                            @endphp

                            @if($grouped->isNotEmpty())

                            {{-- 🐾 Алфавитный навигатор --}}
                            <div class="mb-4 d-flex flex-wrap gap-2 justify-content-start">
                                @foreach($letters as $letter)
                                <a href="#letter-{{ $letter }}" class="paw-link text-decoration-none" title="Перейти к '{{ $letter }}'">
                                    <div class="paw-circle">
                                        <img src="{{ asset('storage/icon/alphabet/letter_icon.png') }}" class="paw-icon" alt="paw">
                                        <span class="paw-letter">{{ $letter }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                            {{-- Список специализаций --}}
                            @foreach($grouped as $specialization => $group)
                            @php
                            $anchor = mb_strtoupper(mb_substr($specialization, 0, 1));
                            @endphp
                            <div id="letter-{{ $anchor }}" class="mb-5 specialization-block">
                                <h5 class="fw-semibold specialization_block text-primary border-bottom pb-2 mb-3 specialization-header">
                                    {{ $specialization }}
                                </h5>

                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60%">Название услуги</th>
                                            <th style="width: 40%">Стоимость</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group as $service)
                                        @php
                                        $price = $prices->get($service->id);
                                        @endphp
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>
                                                @if($price && $price->price !== null)
                                                {{ number_format($price->price, 0, ',', ' ') }} {{ $price->currency }}
                                                @else
                                                —
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endforeach

                            @else
                            <p class="text-muted">Информация об услугах отсутствует.</p>
                            @endif
                        </div>

                        {{-- 🪄 Плавная прокрутка и подсветка --}}
                        <script>
                            document.querySelectorAll('.paw-link').forEach(link => {
                                link.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const target = document.querySelector(this.getAttribute('href'));
                                    if (target) {
                                        target.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'start'
                                        });

                                        // Подсветка блока на 3 секунды
                                        target.classList.add('highlight-section');
                                        setTimeout(() => {
                                            target.classList.remove('highlight-section');
                                        }, 3000);
                                    }
                                });
                            });
                        </script>
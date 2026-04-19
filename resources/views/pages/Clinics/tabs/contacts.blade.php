<div class="tab-pane fade {{ $tab === 'contacts' ? 'show active' : '' }}" id="contacts" role="tabpanel">
                            {{-- Контакты --}}
                        
                            <div class="row">
                                {{-- Левая часть: контакты --}}
                                <div class="col-md-7">
                                    <div class="text-secondary mb-4">
                                        <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
                                        <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
                                        {{-- Телефоны как ссылки --}}
                                        @if($clinic->phone1)
                                        <div>
                                            📞 <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone1) }}">{{ $clinic->phone1 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон" title="Позвонить"> </a>
                                            @if($clinic->phone2)
                                            , <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone2) }}">{{ $clinic->phone2 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон запасной" title="Позвонить"> </a>
                                            @endif
                                        </div>
                                        @endif
                                        <div>✉️ {{ $clinic->email }}</div>
                                        @if($clinic->telegram)
                                        <div>💬 Telegram: <a href="https://t.me/{{ $clinic->telegram }}" target="_blank">https://t.me/{{ $clinic->telegram }}<img width="24px" src="{{ asset('storage/icon/contacts/telegram.svg') }}" title="Связатся через телеграмм" alt="Телеграмм"></a></div>
                                        @endif
                                        @if($clinic->whatsapp)
                                        <div>💬 WhatsApp: <a href="https://wa.me/{{ $clinic->whatsapp }}" target="_blank">{{ $clinic->whatsapp }}<img width="24px" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" title="Связаться через Вотсапп" alt="Вотсапп"></a></div>
                                        @endif
                                        @if($clinic->website)
                                        <div>💬 <a href="{{ $clinic->website }}" target="_blank" title="Перейти на сайт клиники">Перейти на сайт</a></div>
                                        @endif
                                         @if($clinic->description)
                                         <br>
                                         <label class="label_description_clinic" for="description">О клинике</label>
                                        <div id="#description">{{ $clinic->description }}</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Карта / Доп. информация --}}
                                <!-- <div class="card shadow-sm border-0 p-3" style="max-width: 450px;">
                                    <h5 class="fw-semibold mb-2">Карта / Доп. информация</h5>

                                    {{-- Встраиваемая Яндекс.Карта с геометкой --}}
                                    <div class="bg-light rounded overflow-hidden mb-3" style="height: 300px; width: 100%;">
                                        <iframe
                                            src="https://yandex.ru/map-widget/v1/?text={{ urlencode($clinic->country . ', ' . $clinic->region . ', ' . $clinic->city . ', ' . $clinic->street . ' ' . $clinic->house) }}&z=16&l=map"
                                            width="100%"
                                            height="100%"
                                            frameborder="0"
                                            allowfullscreen
                                            loading="lazy"></iframe>
                                    </div>
                                </div> -->
                            </div>
                        </div>
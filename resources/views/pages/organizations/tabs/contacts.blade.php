<div class="tab-pane fade {{ $tab === 'contacts' ? 'show active' : '' }}" id="contacts" role="tabpanel">
    {{-- Контакты организации --}}
    <div class="row">
        {{-- Левая часть: контакты --}}
        <div class="col-md-7">
            <div class="text-secondary mb-4">
                {{-- Адрес --}}
                <div>
                    📍 {{ implode(', ', array_filter([$organization->country, $organization->region, $organization->city, $organization->street, $organization->house])) }}
                </div>

                {{-- Режим работы --}}
                <div>
                    🕒 {{ $organization->workdays ?? 'Дни не указаны' }} — {{ $organization->schedule ?? 'Время не указано' }}
                </div>

                {{-- Телефоны --}}
                @if($organization->phone1)
                    <div>
                        📞 <a href="tel:{{ preg_replace('/\D/', '', $organization->phone1) }}" class="text-decoration-none">
                            {{ $organization->phone1 }}
                            <img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон" title="Позвонить">
                        </a>
                        @if($organization->phone2)
                            , <a href="tel:{{ preg_replace('/\D/', '', $organization->phone2) }}" class="text-decoration-none">
                                {{ $organization->phone2 }}
                                <img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон запасной" title="Позвонить">
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Почта --}}
                @if($organization->email)
                    <div>✉️ {{ $organization->email }}</div>
                @endif

                {{-- Соцсети и мессенджеры --}}
                @if($organization->telegram)
                    <div>
                        💬 Telegram: 
                        <a href="https://t.me/{{ $organization->telegram }}" target="_blank" class="text-decoration-none">
                            
                            <img width="24px" src="{{ asset('storage/icon/contacts/telegram.svg') }}" title="Связаться через Telegram" alt="Telegram">
                        </a>
                    </div>
                @endif

                @if($organization->whatsapp)
                    <div>
                        💬 WhatsApp: 
                        <a href="{{$organization->whatsapp }}" target="_blank" class="text-decoration-none">
                            
                            <img width="24px" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" title="Связаться через WhatsApp" alt="WhatsApp">
                        </a>
                    </div>
                @endif

                {{-- Сайт --}}
                @if($organization->website)
                    <div class="mt-2">
                        🌐 <a href="{{ $organization->website }}" target="_blank" title="Перейти на сайт организации" class="btn btn-sm btn-outline-primary py-0">
                            Перейти на сайт
                        </a>
                    </div>
                @endif

                {{-- Описание --}}
                @if($organization->description)
                    <div class="mt-4">
                        <label class="fw-bold text-dark mb-1" for="description">Об организации</label>
                        <div id="description" class="text-dark">
                            {{ $organization->description }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
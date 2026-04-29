{{-- Контакты --}}
<div class="tab-pane fade show active" id="contacts" role="tabpanel">
    <div class="row">
        {{-- Левая часть: контакты --}}
        <div class="col-md-7">

            @php
                $contact = $doctor->contacts;
            @endphp

            <div class="text-secondary mb-4">

                {{-- 📞 Телефон --}}
                @if(!empty($contact?->phone))
                    <div>
                        📞 <a href="tel:{{ preg_replace('/\D/', '', $contact->phone) }}" title="Позвонить">
                            {{ $contact->phone }}
                            <img width="24" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон">
                        </a>
                    </div>
                @endif

                {{-- ✉️ Email --}}
                @if(!empty($contact?->email))
                    <div>
                        ✉️ <a href="mailto:{{ $contact->email }}" title="Написать на почту">
                            {{ $contact->email }}
                        </a>
                    </div>
                @endif

                {{-- 💬 Telegram --}}
                @if(!empty($contact?->telegram))
                    <div>
                        💬 Telegram:
                        <a href="https://t.me/{{ $contact->telegram }}" target="_blank" title="Связатся через Телеграмм">
                            https://t.me/{{ $contact->telegram }}
                            <img width="24" src="{{ asset('storage/icon/contacts/telegram.svg') }}" alt="Телеграмм">
                        </a>
                    </div>
                @endif

                {{-- 💬 WhatsApp --}}
                @if(!empty($contact?->whatsapp))
                    <div>
                        💬 WhatsApp:
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank" title="Написать в вотсапп">
                            {{ $contact->whatsapp }}
                            <img width="24" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" alt="Вотсапп">
                        </a>
                    </div>
                @endif

                {{-- 💬 Telegram MAX (структура MAX не уточнена, выводим как есть) --}}
                @if(!empty($contact?->max))
                    <div>
                        💬 MAX:
                                                <a href="https://max.ru/join/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank" title="Связаться через мессенджер МАХ">
                            {{ $contact->max }}
                            <img width="24" src="{{ asset('storage/icon/contacts/max_messendger.svg') }}" alt="МАХ">
                        </a> 
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

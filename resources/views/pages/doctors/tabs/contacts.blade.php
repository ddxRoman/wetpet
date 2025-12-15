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
                        📞 <a href="tel:{{ preg_replace('/\D/', '', $contact->phone) }}">
                            {{ $contact->phone }}
                            <img width="24" src="{{ asset('storage/icon/contacts/phone.svg') }}">
                        </a>
                    </div>
                @endif

                {{-- ✉️ Email --}}
                @if(!empty($contact?->email))
                    <div>
                        ✉️ <a href="mailto:{{ $contact->email }}">
                            {{ $contact->email }}
                        </a>
                    </div>
                @endif

                {{-- 💬 Telegram --}}
                @if(!empty($contact?->telegram))
                    <div>
                        💬 Telegram:
                        <a href="https://t.me/{{ $contact->telegram }}" target="_blank">
                            https://t.me/{{ $contact->telegram }}
                            <img width="24" src="{{ asset('storage/icon/contacts/telegram.svg') }}">
                        </a>
                    </div>
                @endif

                {{-- 💬 WhatsApp --}}
                @if(!empty($contact?->whatsapp))
                    <div>
                        💬 WhatsApp:
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank">
                            {{ $contact->whatsapp }}
                            <img width="24" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}">
                        </a>
                    </div>
                @endif

                {{-- 💬 Telegram MAX (структура MAX не уточнена, выводим как есть) --}}
                @if(!empty($contact?->max))
                    <div>
                        💬 MAX:
                                                <a href="https://max.me/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank">
                            {{ $contact->max }}
                            <img width="24" src="{{ asset('storage/icon/contacts/max_messendger.svg') }}">
                        </a> 
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $contact = $doctor->contacts;
@endphp

@if($contact)
    <div class="text-secondary mb-4">
        {{-- 📞 Телефон --}}
        @if(!empty($contact->phone))
            <div class="mb-2">
                📞 <a href="tel:{{ preg_replace('/\D/', '', $contact->phone) }}" class="text-decoration-none">
                    {{ $contact->phone }}
                    <img width="20" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон">
                </a>
            </div>
        @endif

        {{-- 💬 Telegram --}}
        @if(!empty($contact->telegram))
            @php 
                // Убираем @ если пользователь его ввел
                $tgUser = str_replace('@', '', $contact->telegram); 
            @endphp
            <div class="mb-2">
                💬 Telegram: 
                <a href="https://t.me/{{ $tgUser }}" target="_blank" class="text-decoration-none">
                    {{ $contact->telegram }}
                    <img width="20" src="{{ asset('storage/icon/contacts/telegram.svg') }}" alt="Телеграмм">
                </a>
            </div>
        @endif

        {{-- 💬 WhatsApp --}}
        @if(!empty($contact->whatsapp))
            <div class="mb-2">
                💬 WhatsApp: 
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact->whatsapp) }}" target="_blank" class="text-decoration-none">
                    {{ $contact->whatsapp }}
                    <img width="20" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" alt="Вотсапп">
                </a>
            </div>
        @endif

        {{-- 💬 MAX --}}
        @if(!empty($contact->max))
            <div class="mb-2">
                💬 MAX: 
                <a href="https://max.ru/join/{{ preg_replace('/\D/', '', $contact->max) }}" target="_blank" class="text-decoration-none">
                    {{ $contact->max }}
                    <img width="20" src="{{ asset('storage/icon/contacts/max_messendger.svg') }}" alt="МАХ">
                </a> 
            </div>
        @endif
    </div>
@else
    <p class="text-muted">Контактная информация не указана.</p>
@endif
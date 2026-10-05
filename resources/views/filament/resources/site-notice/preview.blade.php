{{-- Предпросмотр уведомления в админке (без таймера закрытия — это только внешний вид окна) --}}
<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.25);overflow:hidden;position:relative;color:#111827;">
    <div style="position:absolute;top:10px;right:10px;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.92);box-shadow:0 1px 6px rgba(0,0,0,.25);text-align:center;line-height:34px;">
        {{ (int) $notice->close_delay === 0 ? '✕' : (int) $notice->close_delay }}
    </div>

    @if($notice->image_url)
        <img src="{{ $notice->image_url }}" alt="" style="display:block;width:100%;max-height:50vh;object-fit:contain;background:#f3f4f6;">
    @endif

    <div style="padding:{{ $notice->image_url ? '20px' : '44px' }} 24px 22px;">
        @if($notice->title)
            <div style="font-size:20px;font-weight:700;margin-bottom:10px;line-height:1.3;">{{ $notice->title }}</div>
        @endif

        @foreach(preg_split('/\r?\n/', (string) $notice->body) as $line)
            @if(trim($line) === '')
                <div style="height:10px;"></div>
            @else
                <p style="margin:0 0 6px;font-size:16px;line-height:1.5;color:#374151;word-break:break-word;">{{ $line }}</p>
            @endif
        @endforeach
    </div>
</div>

<p style="text-align:center;font-size:12px;color:#6b7280;margin-top:12px;">
    Крестик: {{ (int) $notice->close_delay === 0 ? 'сразу' : 'через ' . (int) $notice->close_delay . ' с (до этого — обратный отсчёт)' }}
</p>

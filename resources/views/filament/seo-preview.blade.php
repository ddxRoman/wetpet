@php
    $title = $record->seo_title;
    $description = $record->seo_description;
    $url = rtrim(config('app.url'), '/') . '/news/' . $record->slug;
@endphp

<div style="font-family: Arial, sans-serif; max-width: 600px;">
    <div style="color: #202124; font-size: 14px; line-height: 1.3;">
        {{ $url }}
    </div>
    <div style="color: #1a0dab; font-size: 18px; line-height: 1.3; margin-top: 2px;">
        {{ $title }}
    </div>
    <div style="color: #4d5156; font-size: 14px; line-height: 1.4; margin-top: 2px;">
        {{ $description }}
    </div>
</div>

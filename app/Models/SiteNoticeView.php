<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Сколько раз и когда уведомление показано конкретному посетителю (для частоты показа и статистики). */
class SiteNoticeView extends Model
{
    protected $fillable = ['site_notice_id', 'visitor_key', 'last_shown_at', 'shown_count'];

    protected $casts = ['last_shown_at' => 'datetime'];

    public function notice()
    {
        return $this->belongsTo(SiteNotice::class, 'site_notice_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewDisputeMessage extends Model
{
    public const PARTY_OWNER  = 'owner';   // диалог админа с владельцем карточки
    public const PARTY_AUTHOR = 'author';  // диалог админа с автором отзыва

    protected $fillable = ['review_dispute_id', 'party', 'user_id', 'is_admin', 'message', 'is_read'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_read'  => 'boolean',
    ];

    public function dispute()
    {
        return $this->belongsTo(ReviewDispute::class, 'review_dispute_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(ReviewDisputeFile::class, 'review_dispute_message_id');
    }

    /** Данные для JSON (модалка пользователя). $viewerIsAdmin — кто смотрит: «мои» сообщения справа. */
    public function toDialogArray(bool $viewerIsAdmin = false): array
    {
        return [
            'id'      => $this->id,
            'mine'    => $this->is_admin === $viewerIsAdmin,
            'who'     => $this->is_admin ? 'Администратор' : ($viewerIsAdmin ? ($this->user?->name ?? 'Пользователь') : 'Вы'),
            'message' => $this->message,
            'time'    => $this->created_at?->format('d.m.Y H:i'),
            'files'   => $this->files->map(fn ($f) => [
                'name'     => $f->original_name ?: basename($f->path),
                'url'      => $f->url,
                'is_image' => $f->is_image,
            ])->values()->all(),
        ];
    }
}

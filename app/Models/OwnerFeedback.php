<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerFeedback extends Model
{
    protected $table = 'owner_feedback';

    protected $fillable = [
        'user_id',
        'user_name',
        'entity_type',
        'entity_id',
        'entity_name',
        'activity_type',
        'region',
        'city',
        'address',
        'snapshot',
        'reason',
        'is_read',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'is_read'  => 'boolean',
    ];

    public const TYPE_LABELS = [
        'clinic'       => 'Клиника',
        'organization' => 'Организация',
        'doctor'       => 'Врач',
        'specialist'   => 'Специалист',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->entity_type] ?? $this->entity_type;
    }
}

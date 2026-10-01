<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_id',
        'reviewable_type',
        'workplace_type',
        'workplace_id',
        'review_date',
        'rating',
        'content',
        'liked',
        'disliked',
        'receipt_path',
        'receipt_verified',
        'pet_id',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    // Отзыв — принадлежит пользователю
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Полиморфная связь (клиника, врач, сервис и т.д.)
    public function reviewable()
    {
        return $this->morphTo();
    }

    // Место работы врача/специалиста НА МОМЕНТ отзыва (Clinic|Organization), если
    // отзыв был оставлен врачу/специалисту. Для отзывов, оставленных напрямую
    // клинике/организации, не используется (null).
    public function workplaceable()
    {
        return $this->morphTo(__FUNCTION__, 'workplace_type', 'workplace_id');
    }

    /**
     * Отзыв «привязан» к чьему-то месту работы — то есть он оставлен врачу или
     * специалисту, а не клинике/организации напрямую. Такие отзывы показываются
     * и на странице самого врача/специалиста, и (с пометкой) на странице того
     * места работы, где он трудился в момент отзыва.
     */
    public function isAboutWorkplaceEmployee(): bool
    {
        return !empty($this->workplace_type) && !empty($this->workplace_id);
    }

    /**
     * Работает ли врач/специалист, которому оставлен отзыв, в зафиксированном
     * на момент отзыва месте работы ПРЯМО СЕЙЧАС (а не сменил ли он его с тех пор).
     * Сравнение всегда идёт с текущими данными врача/специалиста — отдельно
     * хранить «актуальность» не нужно, при смене места работы статус обновится
     * сам, без правки старых отзывов.
     */
    public function specialistStillWorksHere(): bool
    {
        if (!$this->isAboutWorkplaceEmployee()) {
            return false;
        }

        $employee = $this->reviewable;
        if (!$employee) {
            return false;
        }

        // Врач может работать в нескольких клиниках, специалист — в нескольких организациях
        // (сводные таблицы clinic_doctor / organization_specialist); основное место
        // (clinic_id / organization_id) проверяем тоже — для старых записей.
        if ($this->workplace_type === \App\Models\Clinic::class) {
            return (int) $employee->clinic_id === (int) $this->workplace_id
                || (method_exists($employee, 'clinics')
                    && $employee->clinics()->whereKey($this->workplace_id)->exists());
        }

        if ($this->workplace_type === \App\Models\Organization::class) {
            return (int) $employee->organization_id === (int) $this->workplace_id
                || (method_exists($employee, 'organizations')
                    && $employee->organizations()->whereKey($this->workplace_id)->exists());
        }

        return false;
    }

    // Фото прикрепленные
    public function photos()
    {
        return $this->hasMany(ReviewPhoto::class);
    }

    // Чеки
    public function receipts()
    {
        return $this->hasMany(ReviewReceipt::class);
    }

    // Связанный питомец
    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}

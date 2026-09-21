<?php

namespace App\Models\Concerns;

use Carbon\Carbon;


trait HasPracticeExperience
{
    /** Через сколько лет после рождения можно начать практику */
    public const MIN_PRACTICE_AGE = 16;

    /**
     * Самый ранний допустимый месяц начала практики в формате Y-m
     * (дата рождения + 16 лет, а без даты рождения — 1950-01).
     */
    public static function earliestPracticeStart($dateOfBirth = null): string
    {
        if (blank($dateOfBirth)) {
            return '1950-01';
        }

        try {
            return Carbon::parse($dateOfBirth)->addYears(self::MIN_PRACTICE_AGE)->format('Y-m');
        } catch (\Throwable $e) {
            return '1950-01';
        }
    }

    /** Самая поздняя допустимая дата рождения (не моложе 16 лет), формат Y-m-d. */
    public static function latestBirthDate(): string
    {
        return now()->subYears(self::MIN_PRACTICE_AGE)->format('Y-m-d');
    }

    /** Правила валидации поля practice_started_at (значение вида Y-m). */
    public static function practiceStartRules($dateOfBirth = null): array
    {
        return [
            'nullable',
            'date_format:Y-m',
            'before_or_equal:' . now()->format('Y-m'),
            'after_or_equal:' . self::earliestPracticeStart($dateOfBirth),
        ];
    }

    public function initializeHasPracticeExperience(): void
    {
        $this->mergeCasts(['practice_started_at' => 'date:Y-m-d']);
    }

    /**
     * Принимает «YYYY-MM» (из <input type="month">) или обычную дату
     * и всегда сохраняет 1-е число месяца.
     */
    public function setPracticeStartedAtAttribute($value): void
    {
        if (blank($value)) {
            $this->attributes['practice_started_at'] = null;

            return;
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            $value .= '-01';
        }

        $this->attributes['practice_started_at'] = Carbon::parse($value)->startOfMonth()->toDateString();
    }

    public function getExperienceMonthsAttribute(): ?int
    {
        if (! $this->practice_started_at) {
            return null;
        }

        $start = $this->practice_started_at;
        $now   = now();

        return max(0, ($now->year - $start->year) * 12 + ($now->month - $start->month));
    }

    public function getExperienceAttribute(): ?int
    {
        $months = $this->experience_months;

        return $months === null ? null : intdiv($months, 12);
    }

    public function getExperienceLabelAttribute(): ?string
    {
        $months = $this->experience_months;

        if ($months === null) {
            return null;
        }

        $years = intdiv($months, 12);
        $rest  = $months % 12;

        if ($years === 0 && $rest === 0) {
            return 'менее месяца';
        }

        $parts = [];

        if ($years > 0) {
            $mod100 = $years % 100;
            $mod10  = $years % 10;
            $word   = ($mod100 >= 11 && $mod100 <= 14) ? 'лет'
                : (($mod10 === 1) ? 'год' : (($mod10 >= 2 && $mod10 <= 4) ? 'года' : 'лет'));
            $parts[] = $years . ' ' . $word;
        }

        if ($rest > 0) {
            $parts[] = $rest . ' мес.';
        }

        return implode(' ', $parts);
    }
}

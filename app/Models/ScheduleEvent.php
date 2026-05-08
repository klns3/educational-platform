<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    public const TYPE_LESSON = 'lesson';
    public const TYPE_CONSULTATION = 'consultation';
    public const TYPE_EXAM = 'exam';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'teacher_id',
        'class_group_id',
        'course_id',
        'title',
        'description',
        'type',
        'location',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_LESSON,
            self::TYPE_CONSULTATION,
            self::TYPE_EXAM,
            self::TYPE_OTHER,
        ];
    }

    public function scopeForWeek(Builder $query, string $startsAtColumn, $weekStart, $weekEnd): Builder
    {
        return $query
            ->where($startsAtColumn, '>=', $weekStart)
            ->where($startsAtColumn, '<=', $weekEnd);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

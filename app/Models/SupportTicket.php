<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    public const TYPE_TECHNICAL_ISSUE = 'technical_issue';
    public const TYPE_COURSE_QUESTION = 'course_question';
    public const TYPE_TEACHER_REQUEST = 'teacher_request';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'course_id',
        'assigned_teacher_id',
        'type',
        'subject',
        'message',
        'status',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_TECHNICAL_ISSUE,
            self::TYPE_COURSE_QUESTION,
            self::TYPE_TEACHER_REQUEST,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_IN_PROGRESS,
            self::STATUS_CLOSED,
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'teacher') {
            return $query->where(function (Builder $teacherQuery) use ($user) {
                $teacherQuery->where('user_id', $user->id)
                    ->orWhere('assigned_teacher_id', $user->id);
            });
        }

        return $query->where('user_id', $user->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }
}

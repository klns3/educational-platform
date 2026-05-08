<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationCode extends Model
{
    public const ROLE_STUDENT = 'student';
    public const ROLE_TEACHER = 'teacher';

    protected $fillable = [
        'code',
        'role',
        'class_group_id',
        'created_by',
        'is_active',
        'uses_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public static function roleOptions(): array
    {
        return [
            self::ROLE_STUDENT,
            self::ROLE_TEACHER,
        ];
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

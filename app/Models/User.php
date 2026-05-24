<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected ?string $resolvedAvatarUrl = null;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'class_group_id',
        'learning_goal',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
        'initials',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if ($this->resolvedAvatarUrl !== null) {
            return $this->resolvedAvatarUrl;
        }

        $this->resolvedAvatarUrl = Cache::rememberForever(
            $this->avatarUrlCacheKey($this->avatar),
            fn () => Storage::url($this->avatar)
        );

        return $this->resolvedAvatarUrl;
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1));
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    // Курсы, которые ведёт пользователь-преподаватель
    public function teachingCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // Курсы, на которые записан пользователь-студент
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withTimestamps();
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }

    public function scheduleEvents()
    {
        return $this->hasMany(ScheduleEvent::class, 'teacher_id');
    }

    public static function forgetAvatarUrlCache(?string $avatarPath): void
    {
        if (!$avatarPath) {
            return;
        }

        Cache::forget(self::avatarUrlCacheKey($avatarPath));
    }

    private static function avatarUrlCacheKey(string $avatarPath): string
    {
        return 'user_avatar_url:' . md5($avatarPath);
    }
}

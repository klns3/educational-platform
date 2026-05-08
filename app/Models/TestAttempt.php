<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    protected $fillable = [
        'test_id',
        'user_id',
        'score',
        'max_score',
        'started_at',
        'finished_at',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }

    public function scopeOnlyStudentAttempts($query)
    {
        return $query->whereHas('user', function ($userQuery) {
            $userQuery->where('role', 'student');
        });
    }

    public function scopeExcludeAdmins($query)
    {
        return $this->scopeOnlyStudentAttempts($query);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scheduleEvents()
    {
        return $this->hasMany(ScheduleEvent::class);
    }

    public function invitationCodes()
    {
        return $this->hasMany(InvitationCode::class);
    }
}

<?php

namespace App;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function projectMadeByMe(){
        return $this->hasMany(Project::class,"created_by");
    }

    public function projectJoined(){
        return $this->belongsTo(Project::class);
    }
    public function tasks(){
        return $this->belongsToMany(Task::class);
    }
}

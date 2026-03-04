<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_id','status','by_user_id','created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function byUser()
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}

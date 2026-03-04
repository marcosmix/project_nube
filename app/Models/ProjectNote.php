<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
    protected $fillable = ['project_id','content','status','by_user_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function byUser()
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CreatorFollower extends Pivot
{
    protected $table = 'creator_followers';

    protected $fillable = [
        'creator_profile_id',
        'user_id',
    ];

    public $timestamps = true;

    public function followers()
    {
        return $this->belongsToMany(User::class, 'creator_followers', 'creator_profile_id', 'user_id')
            ->using(CreatorFollower::class) // usa a model pivot customizada
            ->withTimestamps();
    }
}

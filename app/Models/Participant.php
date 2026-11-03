<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = ['name', 'email'];
    public function events() 
    {
        return $this->belongsToMany(Event::class, 'registrations')
                ->withPivot('status')->withTimestamps();
    }
}

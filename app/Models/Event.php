<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['venue_id', 'title', 'starts_at', 'description', 'details'];

    protected $casts = ['details' => 'array'];
    public function venue() 
    { 
        return $this->belongsTo(Venue::class); 
    }
    public function participants() 
    {
        return $this->belongsToMany(Participant::class, 'registrations')
                ->withPivot('status')->withTimestamps();
    }
    public function notes() 
    { 
        return $this->morphMany(Note::class, 'noteable'); 
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now());
    }

    public function scopeSoonest($query)
    {
        return $query->orderBy('starts_at');
    }

    public function scopeAtVenue($query, int $venueId)
    {
        return $query->where('venue_id', $venueId);
    }
}   

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class Tournament extends Model
{
    protected $table = 'tournament';

    protected $fillable = [
        'name',
        'slug',
        'dateIni',
        'dateFin',
        'registerDateLimit',
        'sport',
        'promoter',
        'host_organization',
        'technical_assistance',
        'category',
        'rule_id',
        'type',
        'venue_id',
        'level_id',
        'event_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    
}

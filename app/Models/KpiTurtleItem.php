<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KpiTurtleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'love_cost',
        'image_path',
        'attributes',
        'available',
        'required_level',
    ];

    protected $casts = [
        'love_cost' => 'integer',
        'attributes' => 'array',
        'available' => 'boolean',
        'required_level' => 'integer',
    ];

    public function turtles(): BelongsToMany
    {
        return $this->belongsToMany(KpiTurtle::class, 'kpi_user_turtle_items')
            ->withPivot('equipped', 'purchased_at')
            ->withTimestamps();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLevel($query, $level)
    {
        return $query->where('required_level', '<=', $level);
    }
}

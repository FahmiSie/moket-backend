<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'logo_url', 'description', 
        'contact_email', 'contact_phone', 'status', 'created_by'
    ];

    public function members()
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}

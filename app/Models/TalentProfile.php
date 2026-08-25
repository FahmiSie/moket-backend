<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentProfile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'category', 'bio',
        'portfolio_url', 'contact_info',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

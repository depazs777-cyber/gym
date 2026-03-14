<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'third_party_id',
        'status',
        'membership_type',
        'start_date',
        'end_date',
        'qr_token',
        'qr_token_expires_at',
        'birth_date',
        'gender',
        'emergency_contact',
        'terms_accepted',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'birth_date' => 'date',
            'terms_accepted' => 'boolean',
            'qr_token_expires_at' => 'datetime',
        ];
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirdParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'document_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
    ];

    public function member()
    {
        return $this->hasOne(Member::class);
    }
}

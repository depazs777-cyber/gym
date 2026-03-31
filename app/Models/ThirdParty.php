<?php

namespace App\Models;

use App\Core\Model;

class ThirdParty extends Model {
    protected $table = 'third_parties';
    protected $fillable = ['gym_id', 'doc_type', 'doc_number', 'name', 'email', 'phone', 'address', 'city', 'type', 'vat_responsible'];
}

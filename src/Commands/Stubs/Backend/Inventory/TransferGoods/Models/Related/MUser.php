<?php

namespace __defaultNamespace__\Models\Related;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class MUser extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

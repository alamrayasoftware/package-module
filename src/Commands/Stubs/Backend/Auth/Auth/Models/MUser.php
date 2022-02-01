<?php

namespace __defaultNamespace__\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class MUser extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'm_users';

}

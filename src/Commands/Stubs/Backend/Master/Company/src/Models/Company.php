<?php

namespace __defaultNamespace__\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model 
{
    use SoftDeletes;

    protected $table = 'm_companies';
    
}

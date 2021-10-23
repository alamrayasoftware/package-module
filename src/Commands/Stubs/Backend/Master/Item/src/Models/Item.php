<?php

namespace __defaultNamespace__Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model 
{
    use SoftDeletes;

    protected $table = 'm_items';
    
}

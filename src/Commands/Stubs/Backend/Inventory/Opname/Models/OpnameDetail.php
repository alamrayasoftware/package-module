<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    use HasFactory;
    protected $table = 'inv_opname_detail';

    // item
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->withTrashed();
    }
}

<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\MItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    use HasFactory;
    protected $table = 'inv_opname_details';

    // item
    public function item()
    {
        return $this->belongsTo(MItem::class, 'item_id');
    }
}

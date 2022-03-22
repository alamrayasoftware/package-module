<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\MItem;
use __defaultNamespace__\Models\Related\MUnitOfMeasurement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionDetail extends Model
{
    use HasFactory;
    protected $table = 'inv_transaction_details';
    public $timestamps = false;

    // item
    public function item()
    {
        return $this->belongsTo(MItem::class, 'item_id')->withTrashed();
    }

    // unit
    public function unit()
    {
        return $this->belongsTo(MUnitOfMeasurement::class, 'unit_id')->withTrashed();
    }
}

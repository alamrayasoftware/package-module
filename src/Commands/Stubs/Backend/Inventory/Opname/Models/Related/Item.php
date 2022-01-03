<?php

namespace __defaultNamespace__\Models\Related;

use __defaultNamespace__\Models\Related\UnitOfMeasurement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'm_items';
    protected $appends = ['current_stock', 'uri_image','item_prices_per_unit'];

    public function itemUnit()
    {
        return $this->belongsToMany(UnitOfMeasurement::class, 'm_item_m_unit', 'item_id', 'unit_id')->withTrashed()->withPivot('barcode', 'multiplier', 'point_value', 'unit_price_value', 'sales_commission_value');
    }
}

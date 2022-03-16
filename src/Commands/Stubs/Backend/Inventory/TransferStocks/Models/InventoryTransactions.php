<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\MCompany;
use __defaultNamespace__\Models\Related\MUser;
use __defaultNamespace__\Models\Related\MWarehouse;
use __defaultNamespace__\Models\Related\StockMutation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactions extends Model
{
    use HasFactory;
    protected $table = 'inv_transactions';

    // company origin
    public function companyOrigin()
    {
        return $this->belongsTo(MCompany::class, 'company_origin_id')->withTrashed();
    }

    // warehouse origin
    public function warehouseOrigin()
    {
        return $this->belongsTo(MWarehouse::class, 'warehouse_origin_id')->withTrashed();
    }

    // company destination
    public function companyDestination()
    {
        return $this->belongsTo(MCompany::class, 'company_destination_id')->withTrashed();
    }

    // warehouse destination
    public function warehouseDestination()
    {
        return $this->belongsTo(MWarehouse::class, 'warehouse_destination_id')->withTrashed();
    }

    // details
    public function details()
    {
        return $this->hasMany(InventoryTransactionDetails::class, 'inv_transaction_id');
    }

    // created by
    public function createdBy()
    {
        return $this->belongsTo(MUser::class, 'created_by')->withTrashed();
    }

    // updated by
    public function updatedBy()
    {
        return $this->belongsTo(MUser::class, 'updated_by')->withTrashed();
    }

    // stock mutation
    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'mutationable');
    }
}

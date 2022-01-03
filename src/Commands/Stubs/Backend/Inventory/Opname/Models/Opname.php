<?php

namespace __defaultNamespace__\Models;

use __defaultNamespace__\Models\Related\FinanceAccount;
use __defaultNamespace__\Models\Related\StockMutation;
use __defaultNamespace__\Models\Related\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opname extends Model
{
    use HasFactory;
    protected $table = 'inv_opname';

    // finance account
    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    // warehouse position
    public function warehousePosition()
    {
        return $this->belongsTo(Warehouse::class, 'position_id')->withTrashed();
    }

    // opname details
    public function details()
    {
        return $this->hasMany(OpnameDetail::class, 'opname_id');
    }
}

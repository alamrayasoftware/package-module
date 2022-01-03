<?php

namespace __defaultNamespace__\Models\Related;

use __defaultNamespace__\Models\Related\FinanceAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'm_warehouses';
    protected $guarded = [];
    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    /**
     * scope query to only include owned data by selected company
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDataOwner($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

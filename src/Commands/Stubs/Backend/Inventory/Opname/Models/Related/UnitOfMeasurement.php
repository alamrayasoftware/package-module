<?php

namespace __defaultNamespace__\Models\Related;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitOfMeasurement extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'm_unit_of_measurements';

    // --- scopes ---
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

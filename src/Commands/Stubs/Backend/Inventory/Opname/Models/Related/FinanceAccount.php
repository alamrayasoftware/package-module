<?php

namespace __defaultNamespace__\Models\Related;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceAccount extends Model
{
    use HasFactory;
    protected $table = 'dk_akun';
    protected $primaryKey = 'ak_id';
}

<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    /**
     * the attributes that are mass assignable. 
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'asset_id',
        'loan_id',
    ];
    protected $guarded = [];
}

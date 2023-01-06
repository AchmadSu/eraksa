<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    /**
     * the attributes that are mass assignable. 
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'loan_id',
        'loaner_id',
        'recipient_id',
        'date',
        'due_date',
        'deleted_at'
    ];
    protected $guarded = [];
}

<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assets extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;
    /**
     * the attributes that are mass assignable. 
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'category_id',
        'user_id',
        'condition',
        'status',
        'date',
        'placement_id',
        'study_program_id',
        'deleted_at'
    ];
    protected $guarded = [];

    public function lender(){
        return $this->belongsTo('App\Models\User');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assets extends Model
{
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
        'date',
        'condition',
        'status',
        'placement_id'
    ];
}

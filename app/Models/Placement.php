<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Placement extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * the attributes that are mass assignable. 
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];
}
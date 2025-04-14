<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    use HasFactory;
    protected $table='events';
    protected $fillable=[
        'charity_id',
        'charity_name',
        'id',
        'title',
        'description',
        'due_date',
        'priority',
        'category',
        'status'
    ];
    
}

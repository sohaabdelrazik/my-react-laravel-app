<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    use HasFactory;
    protected $table='comments';
    protected $fillable=[
        'user_id',
        'event_id',
        'charity_id',
        'content',
        'charity_name',
        'user_name'
    ];

}

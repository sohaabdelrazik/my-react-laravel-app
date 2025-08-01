<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Charity;

class event extends Model
{
    use HasFactory;
    protected $table='events';
    protected $fillable=[
        'charity_id',
        'charity_name',
        'title',
        'description',
        'due_date',
        'priority',
        'category',
        'status',
        'location'
    ];
    public function charity()
{
    return $this->belongsTo(Charity::class);
}

}

<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'user_id',
        'food_id',
        'quantity',
        'total',
        'payment_url'
      
    ];
    public function user(){
        return $this->hasOne(Food::class, 'id', 'user_id');
    }
    

    public function food(){
        return $this->hasOne(Food::class, 'id', 'food_id');
    }
  

    public function  getCreateAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }
    public function  getUpdateAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }


}

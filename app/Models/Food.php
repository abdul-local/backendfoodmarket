<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Food extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'ingredients',
        'price',
        'rate',
        'types',
        'picturePath'
       
    ];
    public function  getCreateAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }
    public function  getUpdateAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }
    public function toArray(){
        $toArray=parent::toArray();
        $toArray['picurePath']=$this->picturePath;
        return $toArray;
    }
    public function getPictureAttribute(){
        return url('') .Storage::url($this->attributes['picturePath']);
    }
}

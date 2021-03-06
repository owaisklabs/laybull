<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable=[
        'name',
        'image',
    ];
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')->orderBy('id','desc');
    }
    public function categorySize(){
        return $this->hasMany(ProductSize::class,'category_id');
    }
}

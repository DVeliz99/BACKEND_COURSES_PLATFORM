<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'category_id',
        'name',
        'detail',
        'image',
        'url',
        'accordion',
        'price_now',
        'price_before',
        'num_of_sales'
    ];

    //de uno a muchos 

    public function videos()
    {
        return $this->hasMany('App\Models\Video'); //Varios videos pueden pertenecer a un solo curso 
    }

    //de muchos a muchos 

    public function users()
    {
        //muchos cursos pueden pertenecer a muchos usuarios , y cualquier de los cursos pueden pertenecer a varios usuarios
        return $this->belongsToMaany(User::class, 'sales'); //en la tabla sales se guardara el user_id y el course_id(la tabla pivote) 
        //Indica el usuario que compró dicho curso  
    }

    //de muchos a uno 
    public function  categories(){
        return $this->belongsTo('App\Models\Category','category_id'); //muchos cursos pueden pertenecer a una categoría
    }

    public function sales()
{
    return $this->hasMany(Sales::class,'course_id'); //muchas ventas pueden pertenecer a un solo curso 
}
}

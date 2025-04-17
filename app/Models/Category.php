<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';

    protected $fillable = [
        //Permite actualizar varios campos a la vez
        'name'
    ];



    //de uno a muchos

    public function courses(){ //una categoria puede pertenecer a muchos cursos 
        return $this->hasMany('App\Models\Course');

    }

}

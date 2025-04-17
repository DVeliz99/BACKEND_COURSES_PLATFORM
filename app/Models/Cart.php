<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'cart';

    protected $fillable = [
        //Permite actualizar varios campos a la vez
        'quantity'
    ];

    /*Relaciones */


    //De muchos a uno 

    //muchos carritos(carts) pueden pertenecer a un solo usuario
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id'); //user_id de la tabla cart
    }

    //muchos carritos(carts) pueden pertenecer a un solo curso 

    public function course(){
        return $this->belongsTo('App\Models\Course','course_id');
    }

    
}

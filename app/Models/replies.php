<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class replies extends Model
{

    use HasFactory;

    protected $table = 'responses';

    protected $fillable = [
        'response',
        'image'
    ];


    //de muchos a uno 
    public function  comment()
    {
        return $this->belongsTo('App\Models\Comment', 'comment_id'); //muchos respuestas pueden pertenecer a un comentario
    }


    //de muchos a uno 
    public function  user()
    {
        return $this->belongsTo('App\Models\User', 'user_id'); //muchos respuestas pueden pertenecer a un usuario
    }
}

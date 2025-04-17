<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\replies;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';

    protected $fillable = ['title', 'comment', 'image'];



    //de muchos a uno 

    public function video()
    {
        return $this->belongsTo('App\Models\Video', 'video_id'); //Muchos comentarios pueden pertenecer a un video
    }


    //de muchos a uno 

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id'); //Muchos comentarios pueden pertenecer a un usuario
    }


    //de uno a muchos

    public function responses()
    {
        return $this->hasMany(replies::class); //un comentario puede tener muchas respuestas 
    }
}

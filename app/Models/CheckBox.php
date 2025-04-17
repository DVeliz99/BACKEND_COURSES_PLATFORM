<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckBox extends Model
{
    use HasFactory;
    protected $table = 'checkbox';

    protected $fillable = ['user_id', 'course_id', 'video_id', 'checkbox'];


    //No se usara para actualizar 


    //de muchos a uno 
    public function curso()
    {
        return $this->belongsTo('App\Models\Course', 'course_id'); //Muchos checkbox pueden pertenecer a un solo curso 
    }


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id'); //Muchos checkbox pueden pertenecer a un solo usuario 
    }

    public function video()
    {
        return $this->belongsTo('App\Models\Video', 'video_id'); //Muchos checkbox pueden pertenecer a un solo video 
    }
}

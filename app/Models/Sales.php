<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sales extends Model
{
    use HasFactory;


    protected $table = 'sales';

    protected $fillable = [
        'user_id',
        'course_id',
        'video_id',
        'progress',
    ];


    //de muchos a uno 
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id'); //muchas ventas pueden pertenecer a un solo curso
    }


     //de muchos a uno 
     public function user()
     {
         return $this->belongsTo('App\Models\User', 'user_id'); //muchas ventas pueden pertenecer a un solo usuario
     }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;


    protected $table = 'videos';

    protected $fillable = [
        'title',
        'content',
        'url',
        'file',
        'download',
        'section',
        'title_accordion'
    ];


    //de muchos a uno 
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id'); //muchos videos pueden pertenecer a un solo curso
    }


    //de uno a muchos 
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('id', 'desc'); //un video puede tener muchos comentarios
        //cuando se obtengan los comentarios estaran ordenados de form descendiente
    }

    //Muchos videos pueden pertenecer a un solo usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accordion extends Model
{
    use HasFactory;
    protected $table = 'accordion';

    protected $fillable = [
        'section_name',
        'course_id',
        'num_of_section'
    ];


    /*Relaciones */


    //De muchos a uno 



    //muchos accordions pueden pertenecer a un solo curso 

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }
}

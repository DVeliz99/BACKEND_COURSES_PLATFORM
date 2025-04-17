<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CheckBox;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Response;
use App\Models\Sales;
use App\Models\User;
use App\Models\Video;

class TestController extends Controller
{
    public function index()
    {
        $user = User::find(1); // Buscara el numero 1 en la tabla 
        $sales = Sales::all();
        $videos = Video::all();
        $course = Course::find(1); // el curso que tiene tiene el id 1
        
        return view('welcome', compact('user','sales','videos','course'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;

class PruebasController extends Controller{
    public function pruebaOrm(){
        $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<h2>".$post->content."</h2>";
        }
        die();
        
    }
}


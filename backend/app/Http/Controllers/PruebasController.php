<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use App\Models\Category;

class PruebasController extends Controller
{
    public function testOrm(){
        $posts = Post::all();

        // foreach($posts as $post){
        //     echo '<h2>'.$post->title.'</h2>';
        //     echo "<span style='color:blue'>{$post->user->name} - {$post->category->name}</span>";
        //     echo '<p>'.$post->content.'</p>';
        //     echo '<hr>';
        // }

        $categories = Category::all();

        foreach($categories as $category){
            echo "<h1>{$category->name}</h1>";

            foreach($category->posts as $post){
                echo '<h2>'.$post->title.'</h2>';
                echo "<span style='color:blue'>{$post->user->name} - {$post->category->name}</span>";
                echo '<p>'.$post->content.'</p>';
            }
            echo '<hr>';
        }

        die();
    }
}

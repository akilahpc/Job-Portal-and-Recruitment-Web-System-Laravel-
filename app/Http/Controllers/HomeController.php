<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //This method will show our home page
    public function index(){

        //'categories' table eke athi pahatha data tika ganna  (home page eke 'categories' wla penwimata)
        $categories = Category::where('status',1) //'categories' table eke 'status'->1 wana
                      ->orderBy('name','ASC') //'categories' table eke 'name' ek ganna
                      ->take(8)->get(); //'take(8)' -> home page eke category 8k display

        $newCategories = Category::where('status',1) //'categories' table eke 'status'->1 wana
                      ->orderBy('name','ASC') //'categories' table eke 'name' ek ganna
                      ->get(); //'take(8)' -> home page eke category 8k display


        //'jobs' table eke athi pahatha data tika ganna  (home page eke 'Featured Jobs' wla penwimata)
        $featuredJobs = Job::where('status',1) //'jobs' table eke 'status'->1 wana
                        ->orderBy('created_at','DESC') //'jobs' table eke 'discription'eka ganna
                        ->with('jobType') 
                        ->where('isFeatured',1) //'jobs' table eke 'isFeatured'->1 wana
                        ->take(6)->get();  //'take(6)' -> home page eke Featured Jobs 6k display

        $latestJobs = Job::where('status',1)
                        ->with('jobType')
                        ->orderBy('created_at','DESC')
                        ->take(6)->get();

        return view('front.home',[ //ihatha rules wlata anuwa homepage eke 'feelds 3' display kirima
            'categories' => $categories,
            'featuredJobs' => $featuredJobs,
            'latestJobs' => $latestJobs,
            'newCategories' => $newCategories
        ]);
    }
}

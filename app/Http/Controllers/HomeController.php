<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'featured' => MenuItem::query()->where('is_active', true)->orderBy('sort')->take(3)->get(),
        ]);
    }

    public function menu(): View
    {
        return view('menu', [
            'categories' => Category::query()->orderBy('sort')->get(),
            'items' => MenuItem::query()->with('category')->where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function plans(): View
    {
        return view('plans');
    }

    public function how(): View
    {
        return view('how');
    }

    public function catering(): View
    {
        return view('catering');
    }

    public function about(): View
    {
        return view('about');
    }
}

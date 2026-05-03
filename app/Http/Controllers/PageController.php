<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\CmsSetting;

class PageController extends Controller
{
    public function home()
    {
        $heroImage = CmsSetting::get('home_hero_image', 'img/hero-feed.png');
        return view('landing', compact('heroImage'));
    }

    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_visible', true)->firstOrFail();
        return view('page', compact('page'));
    }
}

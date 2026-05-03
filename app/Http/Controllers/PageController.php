<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;

class PageController extends Controller
{
    public function home()
    {
        return view('landing');
    }

    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_visible', true)->firstOrFail();
        return view('page', compact('page'));
    }
}

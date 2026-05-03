<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MigController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('mig.edit', [
            'user' => $user,
            'course' => $user->currentCourse(),
            'membership' => $user->currentMembership(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:200',
            'title' => 'nullable|string|max:120',
            'bio' => 'nullable|string|max:1000',
            'linkedin' => 'nullable|url|max:300',
            'image' => 'nullable|image|max:5120',
            'poster_name' => 'nullable|string|max:120',
            'poster_image' => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();
        if ($request->filled('name')) $user->name = $request->input('name');
        if ($request->filled('email')) $user->email = $request->input('email');
        $user->save();

        $m = $user->currentMembership();
        if (!$m) return back()->with('error', 'Intet aktivt kursus.');

        foreach (['title', 'bio', 'linkedin', 'poster_name'] as $f) {
            $m->{$f} = $request->input($f);
        }

        if ($request->hasFile('image')) {
            if ($m->image_path) Storage::disk('public')->delete($m->image_path);
            $m->image_path = $request->file('image')->store('mig', 'public');
        }
        if ($request->hasFile('poster_image')) {
            if ($m->poster_image_path) Storage::disk('public')->delete($m->poster_image_path);
            $m->poster_image_path = $request->file('poster_image')->store('mig', 'public');
        }
        $m->save();

        return redirect('/simulation/mig')->with('success', 'Profil gemt for dette kursus.');
    }
}

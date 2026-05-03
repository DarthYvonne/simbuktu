<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $course = Course::where('invite_token', $token)->firstOrFail();
        return view('auth.invite', ['course' => $course]);
    }

    public function accept(Request $request, string $token)
    {
        $course = Course::where('invite_token', $token)->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:200',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'password' => Hash::make($data['password'])]
        );

        // If user already existed and password didn't match, reject
        if ($user->wasRecentlyCreated === false && !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Denne email er allerede registreret. Log ind i stedet.'])->withInput();
        }

        CourseMembership::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['role' => 'student']
        );

        $user->active_course_id = $course->id;
        $user->save();

        Auth::login($user, true);
        return redirect('/slophub');
    }
}

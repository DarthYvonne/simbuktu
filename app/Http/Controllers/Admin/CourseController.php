<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Persona;
use App\Models\Population;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount(['memberships', 'posts'])->orderByDesc('created_at')->get();
        return view('admin.courses.index', ['courses' => $courses]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
        ]);
        $course = Course::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => Auth::id(),
        ]);
        $course->memberships()->create(['user_id' => Auth::id(), 'role' => 'instructor']);
        $user = Auth::user();
        $user->active_course_id = $course->id;
        $user->save();
        return redirect('/simulation/admin/courses/'.$course->id)->with('success', 'Kursus oprettet.');
    }

    public function show(Course $course)
    {
        $memberships = $course->memberships()->with('user')->orderBy('created_at')->get();
        $posts = Post::where('course_id', $course->id)
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->get();

        $totalComments = $posts->sum('comments_count');
        $totalReactions = $posts->sum(fn ($p) => $p->reactionTotal());
        $totalShares = $posts->sum('shares');
        $totalReach = $posts->sum('reach');
        $topPost = $posts->sortByDesc('virality_score')->first();
        $avgVirality = $posts->count() > 0 ? round($posts->avg('virality_score'), 1) : 0;

        return view('admin.courses.show', [
            'course'      => $course,
            'memberships' => $memberships,
            'posts'       => $posts,
            'stats' => [
                'posts' => $posts->count(),
                'participants' => $memberships->count(),
                'comments' => $totalComments,
                'reactions' => $totalReactions,
                'shares' => $totalShares,
                'reach' => $totalReach,
                'avg_virality' => $avgVirality,
                'top_post' => $topPost,
            ],
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'description' => 'nullable|string|max:500',
            'manager_name' => 'nullable|string|max:120',
            'manager_email' => 'nullable|email|max:200',
            'manager_phone' => 'nullable|string|max:60',
            'platform_name' => 'nullable|string|max:60',
            'accent_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'logo' => 'nullable|image|max:5120',
            'favicon' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
        ]);

        if (array_key_exists('accent_color', $data) && $data['accent_color'] !== null) {
            $data['accent_color'] = strtolower($data['accent_color']);
            if ($data['accent_color'] === '#1877f2') $data['accent_color'] = null;
        }

        if ($request->boolean('remove_logo') && $course->logo_path) {
            Storage::disk('public')->delete($course->logo_path);
            $data['logo_path'] = null;
        }
        if ($request->hasFile('logo')) {
            if ($course->logo_path) Storage::disk('public')->delete($course->logo_path);
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->boolean('remove_favicon') && $course->favicon_path) {
            Storage::disk('public')->delete($course->favicon_path);
            $data['favicon_path'] = null;
        }
        if ($request->hasFile('favicon')) {
            if ($course->favicon_path) Storage::disk('public')->delete($course->favicon_path);
            $data['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        unset($data['logo'], $data['favicon'], $data['remove_logo'], $data['remove_favicon']);

        $course->update($data);
        $tab = $request->input('_tab');
        $anchor = $tab ? '#'.$tab : '';
        return redirect('/simulation/admin/courses/'.$course->id.$anchor)->with('success', 'Kursus opdateret.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect('/simulation/admin/courses')->with('success', 'Kursus slettet.');
    }

    public function setPopulation(Request $request, Course $course)
    {
        $data    = $request->validate(['population_id' => 'nullable|exists:populations,id']);
        $newId   = $data['population_id'] ?? null;

        $postIds       = Post::where('course_id', $course->id)->pluck('id');
        $commentCount  = DB::table('comments')->whereIn('post_id', $postIds)->count();
        $exposureCount = DB::table('post_exposures')->whereIn('post_id', $postIds)->count();
        $activityCount = $commentCount + $exposureCount;

        if ($activityCount > 0 && !$request->boolean('force')) {
            return back()->withErrors(['population' => "Kurset har {$activityCount} reaktioner/kommentarer. Brug 'Skift og ryd' for at fortsætte."]);
        }

        if ($activityCount > 0) {
            DB::table('comments')->whereIn('post_id', $postIds)->delete();
            DB::table('post_exposures')->whereIn('post_id', $postIds)->delete();
            Post::whereIn('id', $postIds)->update([
                'reach'          => 0,
                'virality_score' => 0,
                'reactions'      => null,
                'shares'         => 0,
                'round'          => 0,
            ]);

            if ($course->population_id) {
                $oldIds = Persona::where('population_id', $course->population_id)->pluck('id');
                if ($oldIds->isNotEmpty()) {
                    DB::table('friendships')
                        ->whereIn('persona_id_1', $oldIds)
                        ->orWhereIn('persona_id_2', $oldIds)
                        ->delete();
                }
            }
        }

        $course->update(['population_id' => $newId]);
        $msg     = $activityCount > 0 ? 'Population skiftet. Al aktivitet er slettet.' : 'Population gemt.';
        $target  = $newId ? "/simulation/admin/populations/{$newId}/personas" : '/simulation/admin/populations';
        return redirect($target)->with('success', $msg);
    }

    public function switch(Request $request, Course $course)
    {
        $user = Auth::user();
        if (!$user->hasAccessTo($course)) abort(403);
        $user->active_course_id = $course->id;
        $user->save();
        return back()->with('success', "Skiftet til {$course->name}.");
    }
}

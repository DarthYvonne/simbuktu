<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlgorithmController extends Controller
{
    // Platform reach mix: tight = small audience per round; loose = wider random reach
    private array $presets = [
        'facebook' => ['exposure_growth_factor' => 1.2, 'base_exposure_per_round' => 12],
        'tiktok' => ['exposure_growth_factor' => 2.2, 'base_exposure_per_round' => 25],
        'x' => ['exposure_growth_factor' => 1.8, 'base_exposure_per_round' => 18],
        'reddit' => ['exposure_growth_factor' => 1.3, 'base_exposure_per_round' => 15],
    ];

    public function index()
    {
        $course = Auth::user()->currentCourse();
        return view('admin.algorithm.index', [
            'course' => $course,
            'config' => self::configFor($course),
            'defaults' => config('simulation'),
            'presets' => $this->presets,
            'models' => config('llm_models'),
        ]);
    }

    public function update(Request $request)
    {
        $course = Auth::user()->currentCourse();
        if (!$course) return back()->with('error', 'Intet aktivt kursus.');

        $preset = $request->input('preset');
        if ($preset && isset($this->presets[$preset])) {
            $config = array_merge(config('simulation'), $this->presets[$preset]);
        } else {
            $config = array_merge(config('simulation'), [
                'base_exposure_per_round' => (int) $request->input('base_exposure_per_round'),
                'exposure_growth_factor' => (float) $request->input('exposure_growth_factor'),
                'trending_threshold' => (int) $request->input('trending_threshold'),
                'max_llm_calls_per_round' => (int) $request->input('max_llm_calls_per_round'),
                'comments_visible_to_personas' => max(1, min(50, (int) $request->input('comments_visible_to_personas', 8))),
                'notification_return_probability' => (float) $request->input('notification_return_probability'),
                'reaction_spread_rate' => max(0, min(100, (int) $request->input('reaction_spread_rate'))),
                'share_spread_rate' => max(0, min(100, (int) $request->input('share_spread_rate'))),
                'max_rounds' => (int) $request->input('max_rounds'),
                'inactive_threshold_rounds' => (int) $request->input('inactive_threshold_rounds'),
            ]);
        }

        $config['round_duration_minutes'] = max(1, min(60, (int) $request->input('round_duration_minutes', 1)));
        $config['students_can_comment'] = (bool) $request->input('students_can_comment', false);

        $models = config('llm_models');
        $pick = $request->input('comment_model');
        $config['comment_model'] = isset($models[$pick]) ? $pick : 'gemini-2.5-flash';

        $course->algorithm_config = $config;
        $course->save();
        return redirect('/simulation/admin/algorithm')->with('success', 'Opsætning gemt for dette kursus.');
    }

    public function reset()
    {
        $course = Auth::user()->currentCourse();
        if ($course) {
            $course->algorithm_config = null;
            $course->save();
        }
        return redirect('/simulation/admin/algorithm')->with('success', 'Nulstillet til standard for dette kursus.');
    }

    /**
     * Get the current course's effective algorithm config.
     * Used by callers outside of an auth context (e.g. tick command).
     */
    public static function current(): array
    {
        return self::configFor(null);
    }

    public static function configFor(?Course $course): array
    {
        if ($course && $course->algorithm_config) {
            return array_merge(config('simulation'), $course->algorithm_config);
        }
        return config('simulation');
    }
}

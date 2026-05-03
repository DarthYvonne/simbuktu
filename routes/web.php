<?php

use App\Http\Controllers\Admin\AlgorithmController;
use App\Http\Controllers\Admin\BlueprintController;
use App\Http\Controllers\Admin\BlueprintLibraryController;
use App\Http\Controllers\Admin\ContextController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\PersonaController;
use App\Http\Controllers\Admin\PersonalityController;
use App\Http\Controllers\Admin\PersonaTesterController;
use App\Http\Controllers\Admin\PopulationController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\SocialGraphController;
use App\Http\Controllers\AnalyseController;
use App\Http\Controllers\Auth\InviteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MigController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfilerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', fn () => view('coming-soon'));
Route::get('/lovestormlab', fn () => view('lovestormlab'));

Route::post('/booking', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'  => 'required|string|max:255',
        'org'   => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'type'  => 'required|string|in:heldag,kort,spm',
        'msg'   => 'nullable|string|max:5000',
    ]);

    $typeLabel = [
        'heldag' => 'Booking — heldag med kommunikationsafdelingen',
        'kort'   => 'Kortere oplæg / keynote',
        'spm'    => 'Spørgsmål',
    ][$data['type']];

    $body = "Henvendelse fra shitstormlab.dk\n\n"
        ."Navn: {$data['name']}\n"
        ."Organisation: ".($data['org'] ?? '—')."\n"
        ."E-mail: {$data['email']}\n"
        ."Telefon: ".($data['phone'] ?? '—')."\n"
        ."Type: {$typeLabel}\n\n"
        ."Besked:\n".($data['msg'] ?? '—')."\n";

    Mail::raw($body, function ($m) use ($data) {
        $m->to('anders@klinikken.ai')
          ->replyTo($data['email'], $data['name'])
          ->subject('Shitstormlab — ny henvendelse fra '.$data['name']);
    });

    return redirect('/tak');
});

Route::get('/tak', fn () => view('coming-soon-tak'));

// Auth (public)
Route::prefix('simulation')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/invite/{token}', [InviteController::class, 'show']);
    Route::post('/invite/{token}', [InviteController::class, 'accept']);
});

// Authenticated
Route::prefix('simulation')->middleware(['auth', 'course'])->group(function () {
    Route::get('/', [PostController::class, 'publicFeed']);
    Route::get('/feed-data', [PostController::class, 'feedData']);
    Route::get('/unread-count', [PostController::class, 'unreadCount']);
    Route::post('/mark-feed-seen', [PostController::class, 'markFeedSeen']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/all', [PostController::class, 'allPosts']);
    Route::get('/posts/create', [PostController::class, 'create']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/posts/link-preview', [PostController::class, 'linkPreview']);
    Route::get('/posts/{post}/feedback', [\App\Http\Controllers\PostFeedbackController::class, 'show']);
    Route::get('/posts/{post}/feedback/data', [\App\Http\Controllers\PostFeedbackController::class, 'fetch']);
    Route::post('/posts/{post}/feedback', [\App\Http\Controllers\PostFeedbackController::class, 'send']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::get('/posts/{post}/feed', [PostController::class, 'feed']);
    Route::post('/posts/{post}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
    Route::post('/comments/{comment}/react', [\App\Http\Controllers\CommentController::class, 'react']);
    Route::get('/posts/{post}/reactions', [PostController::class, 'reactionsDetails']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    Route::get('/mig', [MigController::class, 'edit']);
    Route::post('/mig', [MigController::class, 'update']);

    Route::get('/analyse', [AnalyseController::class, 'index']);
    Route::get('/analyse/{post}/spread', [AnalyseController::class, 'spreadGraph']);

    Route::get('/beskeder', [MessageController::class, 'index']);
    Route::post('/beskeder/open/{personaId}', [MessageController::class, 'open']);
    Route::get('/beskeder/{conversation}', [MessageController::class, 'show']);
    Route::post('/beskeder/{conversation}/send', [MessageController::class, 'send']);

    Route::get('/profiler', [ProfilerController::class, 'index']);
    Route::get('/profiler/graph', [ProfilerController::class, 'graph']);
    Route::get('/profiler/graph/data', [ProfilerController::class, 'graphData']);
    Route::get('/profiler/{id}', [ProfilerController::class, 'show']);
    Route::get('/profiler/{id}/image', [ProfilerController::class, 'image']);
    Route::get('/profiler/{id}/thumb', [ProfilerController::class, 'thumb']);

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/courses', [CourseController::class, 'index']);
        Route::post('/courses', [CourseController::class, 'store']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        Route::patch('/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        Route::post('/courses/{course}/switch', [CourseController::class, 'switch']);
        Route::patch('/courses/{course}/population', [CourseController::class, 'setPopulation']);

        Route::get('/populations', [PopulationController::class, 'index']);
        Route::post('/populations', [PopulationController::class, 'store']);
        Route::get('/populations/{population}', [PopulationController::class, 'show']);
        Route::patch('/populations/{population}', [PopulationController::class, 'update']);
        Route::delete('/populations/{population}', [PopulationController::class, 'destroy']);

        Route::prefix('populations/{population}')->group(function () {
            Route::get('/personas',               [PersonaController::class, 'index']);
            Route::post('/personas/generate',     [PersonaController::class, 'generate']);
            Route::get('/personas/status',        [PersonaController::class, 'status']);
            Route::post('/personas/clear',        [PersonaController::class, 'clear']);

            Route::get('/personas/graph',         [SocialGraphController::class, 'generate']);
            Route::post('/personas/graph',        [SocialGraphController::class, 'build']);
            Route::get('/personas/graph/status',  [SocialGraphController::class, 'status']);
            Route::get('/personas/graph/view',    [SocialGraphController::class, 'view']);
            Route::get('/personas/graph/data',    [SocialGraphController::class, 'data']);

            Route::get('/personas/tester',        [PersonaTesterController::class, 'index']);
            Route::post('/personas/tester',       [PersonaTesterController::class, 'react']);
            Route::get('/personas/tester/status', [PersonaTesterController::class, 'status']);

            Route::get('/personas/{id}',          [PersonaController::class, 'show']);
            Route::delete('/personas/{id}',       [PersonaController::class, 'destroy']);
            Route::get('/personas/{id}/image',    [PersonaController::class, 'image']);
            Route::get('/personas/{id}/thumb',    [PersonaController::class, 'thumb']);

            Route::get('/demografi',                       [PopulationController::class, 'demografi']);
            Route::patch('/demografi',                     [PopulationController::class, 'saveDemografi']);
            Route::post('/demografi/{dim}/reset',          [PopulationController::class, 'resetDemografiDimension']);

            Route::get('/subkultur',                       [PopulationController::class, 'subkultur']);
            Route::patch('/subkultur',                     [PopulationController::class, 'saveSubkultur']);
            Route::post('/subkultur/reset',                [PopulationController::class, 'resetSubkultur']);

            Route::get('/prompts',                     [PopulationController::class, 'prompts']);
            Route::patch('/prompts',                   [PopulationController::class, 'savePrompt']);

            Route::get('/personlighed',                [PersonalityController::class, 'index']);
            Route::patch('/personlighed/{rule}',       [PersonalityController::class, 'update']);
            Route::post('/personlighed/{rule}/reset',  [PersonalityController::class, 'reset']);
            Route::post('/personlighed/reset-all',     [PersonalityController::class, 'resetAll']);
        });

        Route::get('/blueprint-library',                      [BlueprintLibraryController::class, 'index']);
        Route::post('/blueprint-library',                     [BlueprintLibraryController::class, 'store']);
        Route::get('/blueprint-library/{parameter}',          [BlueprintLibraryController::class, 'edit']);
        Route::patch('/blueprint-library/{parameter}',        [BlueprintLibraryController::class, 'update']);
        Route::delete('/blueprint-library/{parameter}',       [BlueprintLibraryController::class, 'destroy']);

        Route::get('/blueprints',                             [BlueprintController::class, 'index']);
        Route::post('/blueprints',                            [BlueprintController::class, 'store']);
        Route::get('/blueprints/{blueprint}',                 [BlueprintController::class, 'edit']);
        Route::patch('/blueprints/{blueprint}',               [BlueprintController::class, 'update']);
        Route::delete('/blueprints/{blueprint}',              [BlueprintController::class, 'destroy']);
        Route::post('/blueprints/{blueprint}/promote',        [BlueprintController::class, 'promote']);

        Route::get('/usage', [\App\Http\Controllers\Admin\UsageController::class, 'index']);

        Route::get('/algorithm', [AlgorithmController::class, 'index']);
        Route::post('/algorithm', [AlgorithmController::class, 'update']);
        Route::post('/algorithm/reset', [AlgorithmController::class, 'reset']);

        Route::get('/context', [ContextController::class, 'index']);
        Route::post('/context/mode', [ContextController::class, 'setMode']);
        Route::post('/context/manual', [ContextController::class, 'saveManual']);
        Route::post('/context/build', [ContextController::class, 'buildDigest']);

        Route::get('/prompts/{key?}', [PromptController::class, 'index']);
        Route::patch('/prompts/{prompt}', [PromptController::class, 'update']);
        Route::post('/prompts/{prompt}/reset', [PromptController::class, 'reset']);

    });
});

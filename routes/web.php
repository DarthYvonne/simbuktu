<?php

use App\Http\Controllers\Admin\AlgorithmController;
use App\Http\Controllers\Admin\BlueprintLibraryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\PersonaController;
use App\Http\Controllers\Admin\PopulationController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\SocialGraphController;
use App\Http\Controllers\AnalyseController;
use App\Http\Controllers\Auth\InviteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MigController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfilerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', [PageController::class, 'home']);
Route::get('/lovestormlab', fn () => view('lovestormlab'));

// Simple CMS (admin-only)
Route::prefix('cms')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/',                [CmsController::class, 'index']);
    Route::get('/settings',        [CmsController::class, 'settings']);
    Route::post('/settings',       [CmsController::class, 'saveSettings']);
    Route::get('/create',          [CmsController::class, 'create']);
    Route::post('/spellcheck',     [CmsController::class, 'spellcheck']);
    Route::post('/upload-image',   [CmsController::class, 'uploadImage']);
    Route::post('/',               [CmsController::class, 'store']);
    Route::get('/{page}/edit',     [CmsController::class, 'edit']);
    Route::patch('/{page}',        [CmsController::class, 'update']);
    Route::delete('/{page}',       [CmsController::class, 'destroy']);
});

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

Route::post('/kontakt', function (\Illuminate\Http\Request $request) {
    if (filled($request->input('website'))) {
        return back()->with('kontakt_sent', true);
    }
    $ts = (int) $request->input('ts');
    if ($ts === 0 || (time() - $ts) < 2) {
        return back()->with('kontakt_sent', true);
    }

    $data = $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'msg'   => 'required|string|max:5000',
        'kilde' => 'nullable|string|max:200',
    ]);

    $body = "Henvendelse fra simbuktu.dk\n\n"
        ."Navn: {$data['name']}\n"
        ."E-mail: {$data['email']}\n"
        ."Side: ".($data['kilde'] ?? '—')."\n\n"
        ."Besked:\n{$data['msg']}\n";

    $to = \App\Models\CmsSetting::get('contact_email', 'anders@klinikken.ai');

    Mail::raw($body, function ($m) use ($data, $to) {
        $m->to($to)
          ->replyTo($data['email'], $data['name'])
          ->subject('Simbuktu — ny henvendelse fra '.$data['name']);
    });

    return back()->with('kontakt_sent', true);
});

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
    Route::post('/posts/{post}/feedback', [\App\Http\Controllers\PostFeedbackController::class, 'send'])
        ->middleware('throttle:15,1'); // triggers LLM coaching reply — cap per user
    Route::get('/posts/{post}/edit', [PostController::class, 'edit']);
    Route::patch('/posts/{post}', [PostController::class, 'update']);
    Route::post('/posts/{post}/rerun', [PostController::class, 'rerun']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::get('/posts/{post}/feed', [PostController::class, 'feed']);
    Route::post('/posts/{post}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
    Route::post('/comments/{comment}/react', [\App\Http\Controllers\CommentController::class, 'react']);
    Route::get('/posts/{post}/reactions', [PostController::class, 'reactionsDetails']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    Route::get('/mig', [MigController::class, 'edit']);
    Route::post('/mig', [MigController::class, 'update']);

    Route::get('/konto', [\App\Http\Controllers\KontoController::class, 'edit']);
    Route::post('/konto', [\App\Http\Controllers\KontoController::class, 'update']);

    Route::get('/analyse', [AnalyseController::class, 'index']);
    Route::get('/analyse/{post}/spread', [AnalyseController::class, 'spreadGraph']);
    Route::post('/analyse/{post}/sentiment', [AnalyseController::class, 'sentiment'])
        ->middleware('throttle:5,1'); // burns an LLM call — cap at 5/min per user
    Route::get('/analyse/{post}/sentiment/status', [AnalyseController::class, 'sentimentStatus']);

    Route::get('/beskeder', [MessageController::class, 'index']);
    Route::post('/beskeder/open/{personaId}', [MessageController::class, 'open']);
    Route::get('/beskeder/{conversation}', [MessageController::class, 'show']);
    Route::post('/beskeder/{conversation}/send', [MessageController::class, 'send'])
        ->middleware('throttle:15,1'); // each send triggers an LLM reply — cap per user
    Route::get('/beskeder/{conversation}/messages/{message}', [MessageController::class, 'pollMessage']);

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
            Route::post('/personas/cancel',       [PersonaController::class, 'cancelGeneration']);
            Route::get('/personas/status',        [PersonaController::class, 'status']);
            Route::post('/personas/clear',        [PersonaController::class, 'clear']);

            Route::get('/personas/graph',         [SocialGraphController::class, 'generate']);
            Route::post('/personas/graph',        [SocialGraphController::class, 'build']);
            Route::get('/personas/graph/status',  [SocialGraphController::class, 'status']);
            Route::get('/personas/graph/view',    [SocialGraphController::class, 'view']);
            Route::get('/personas/graph/data',    [SocialGraphController::class, 'data']);

            Route::get('/personas/{id}',          [PersonaController::class, 'show']);
            Route::get('/personas/{id}/edit',     [PersonaController::class, 'edit']);
            Route::patch('/personas/{id}',        [PersonaController::class, 'update']);
            Route::delete('/personas/{id}',       [PersonaController::class, 'destroy']);
            Route::get('/personas/{id}/image',    [PersonaController::class, 'image']);
            Route::get('/personas/{id}/thumb',    [PersonaController::class, 'thumb']);
            Route::post('/personas/{id}/coherence/accept', [PersonaController::class, 'acceptCoherence']);

            Route::get('/demografi',                       [PopulationController::class, 'demografi']);
            Route::patch('/demografi',                     [PopulationController::class, 'saveDemografi']);
            Route::post('/demografi/{dim}/reset',          [PopulationController::class, 'resetDemografiDimension']);

            // Personlighed — 1:1 with the population. Was the old top-level /blueprints surface.
            Route::get('/personlighed',                 [PopulationController::class, 'personlighedDimensioner']);
            Route::patch('/personlighed',               [PopulationController::class, 'updatePersonlighedDimensioner']);
            Route::get('/personlighed/om',              [PopulationController::class, 'personlighedOm']);
            Route::patch('/personlighed/om',            [PopulationController::class, 'updatePersonlighedOm']);
            Route::get('/personlighed/prompts',         [PopulationController::class, 'personlighedPrompts']);
            Route::patch('/personlighed/prompts',       [PopulationController::class, 'updatePersonlighedPrompts']);
            Route::post('/personlighed/chat',           [PopulationController::class, 'personlighedChat']);
            Route::post('/personlighed/promote',        [PopulationController::class, 'personlighedPromote']);
            Route::get('/personlighed/test',            [PopulationController::class, 'personlighedTest']);
            Route::post('/personlighed/test',           [PopulationController::class, 'runPersonlighedTest']);
            Route::get('/personlighed/test/status',     [PopulationController::class, 'statusPersonlighedTest']);

            // Kontekst — per-population manual context blob.
            Route::get('/kontekst',                     [PopulationController::class, 'kontekst']);
            Route::patch('/kontekst',                   [PopulationController::class, 'updateKontekst']);
        });

        // Personlighedskomponenter (reusable dimension building-blocks, shared across populations).
        Route::get('/personlighedskomponenter',                [BlueprintLibraryController::class, 'index']);
        Route::post('/personlighedskomponenter',               [BlueprintLibraryController::class, 'store']);
        Route::get('/personlighedskomponenter/{parameter}',    [BlueprintLibraryController::class, 'edit']);
        Route::patch('/personlighedskomponenter/{parameter}',  [BlueprintLibraryController::class, 'update']);
        Route::delete('/personlighedskomponenter/{parameter}', [BlueprintLibraryController::class, 'destroy']);

        Route::get('/api-check', [\App\Http\Controllers\Admin\ApiCheckController::class, 'index']);

        Route::get('/usage', [\App\Http\Controllers\Admin\UsageController::class, 'index']);

        Route::get('/test-ai',        [PopulationController::class, 'testAiSystem']);
        Route::post('/test-ai',       [PopulationController::class, 'runTestAiSystem']);
        Route::get('/test-ai/status', [PopulationController::class, 'testAiSystemStatus']);

        Route::get('/algorithm', [AlgorithmController::class, 'index']);
        Route::post('/algorithm', [AlgorithmController::class, 'update']);
        Route::post('/algorithm/reset', [AlgorithmController::class, 'reset']);

        Route::get('/prompts/{key?}', [PromptController::class, 'index']);
        Route::patch('/prompts/{prompt}', [PromptController::class, 'update']);
        Route::post('/prompts/{prompt}/reset', [PromptController::class, 'reset']);

    });
});

// Public CMS-rendered pages — must be last (catch-all by slug)
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[A-Za-z0-9\-_]+');
Route::get('/{parent}/{child}', [PageController::class, 'showChild'])
    ->where(['parent' => '[A-Za-z0-9\-_]+', 'child' => '[A-Za-z0-9\-_]+']);

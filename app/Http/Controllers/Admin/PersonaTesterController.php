<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TestReactionJob;
use App\Models\Population;
use App\Services\ImageDescriptionService;
use App\Services\LinkPreview;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonaTesterController extends Controller
{
    public function __construct(
        private PersonaRepository $repo,
        private ImageDescriptionService $imageDescriber,
    ) {}

    public function index(Request $request, Population $population)
    {
        $batchId   = session('tester_batch_id');
        $reactions = $batchId ? Cache::get("tester:results:{$batchId}", []) : [];
        $queued    = $batchId ? (int) Cache::get("tester:queued:{$batchId}", 0) : 0;
        $done      = $batchId ? (int) Cache::get("tester:done:{$batchId}", 0) : 0;
        if ($batchId) Cache::forget("tester:unread:{$batchId}");

        return view('admin.personas.tester', [
            'population'       => $population,
            'personas'         => $this->repo->forPopulation($population)->all(),
            'selectedId'       => $request->query('persona'),
            'reactions'        => $reactions,
            'postText'         => session('tester_post_text'),
            'imagePath'        => session('tester_image_path'),
            'imageDescription' => session('tester_image_description'),
            'linkPreview'      => session('tester_link_preview'),
            'batchId'          => $batchId,
            'queued'           => $queued,
            'done'             => $done,
        ]);
    }

    public function react(Request $request, Population $population, LinkPreview $linkPreviewService)
    {
        $request->validate([
            'post'       => 'required|string|min:3',
            'persona_ids' => 'array',
            'image'      => 'nullable|image|max:5120',
        ]);

        $personas  = $this->repo->forPopulation($population)->all();
        $ids       = $request->input('persona_ids', []);
        $count     = (int) $request->input('count', 5);
        $postText  = $request->input('post');

        $picked = empty($ids)
            ? collect($personas)->shuffle()->take($count)->all()
            : collect($personas)->whereIn('id', $ids)->all();

        $imagePath        = null;
        $imageDescription = null;
        if ($request->hasFile('image')) {
            $imagePath        = $request->file('image')->store('tester', 'public');
            $imageDescription = $this->imageDescriber->describe(Storage::disk('public')->path($imagePath));
        }

        $linkPreview = null;
        if (!$imagePath) {
            $url = $linkPreviewService->extractFirstUrl($postText);
            if ($url) $linkPreview = $linkPreviewService->fetch($url);
        }

        $batchId = (string) Str::uuid();
        Cache::put("tester:queued:{$batchId}", count($picked), 3600);
        Cache::put("tester:done:{$batchId}",   0,              3600);
        Cache::put("tester:results:{$batchId}", [],             3600);

        foreach ($picked as $p) {
            TestReactionJob::dispatch($batchId, $p, $postText, $imageDescription, $linkPreview);
        }

        session([
            'tester_batch_id'          => $batchId,
            'tester_post_text'         => $postText,
            'tester_image_path'        => $imagePath,
            'tester_image_description' => $imageDescription,
            'tester_link_preview'      => $linkPreview,
        ]);

        return redirect("/simulation/admin/populations/{$population->id}/personas/tester");
    }

    public function status(Population $population)
    {
        $batchId = session('tester_batch_id');
        if (!$batchId) return response()->json(['queued' => 0, 'done' => 0, 'reactions' => []]);
        return response()->json([
            'queued'    => (int) Cache::get("tester:queued:{$batchId}", 0),
            'done'      => (int) Cache::get("tester:done:{$batchId}",   0),
            'reactions' => Cache::get("tester:results:{$batchId}", []),
        ]);
    }
}

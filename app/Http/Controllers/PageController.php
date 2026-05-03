<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\CmsSetting;

class PageController extends Controller
{
    public function home()
    {
        $heroImage      = CmsSetting::get('home_hero_image', 'img/hero-feed.png');
        $heroHeadline   = CmsSetting::get('home_hero_headline', 'Hvad hvis du kunne spørge dem?');
        $heroSubhead    = CmsSetting::get('home_hero_subhead', 'VI BYGGER SIMULEREDE POPULATIONER SOM KAN GIVE DIG INSPIRATION TIL AT FORSTÅ DIN MÅLGRUPPE BEDRE');
        $heroButtonText = CmsSetting::get('home_hero_button_text', 'Udforsk nu');
        $heroButtonUrl  = CmsSetting::get('home_hero_button_url', '#');
        $homeContent    = CmsSetting::get('home_content', $this->defaultHomeTemplate());
        $editUrl        = '/cms/settings';
        return view('landing', compact('heroImage', 'heroHeadline', 'heroSubhead', 'heroButtonText', 'heroButtonUrl', 'homeContent', 'editUrl'));
    }

    private function defaultHomeTemplate(): string
    {
        return <<<HTML
<h2>Troværdige simulationer</h2>
<p>Simbuktu hjælper organisationer med at simulere realistiske scenarier på data-drevne personaer — så I kan teste budskaber, forudsige reaktioner og træne beredskab inden virkeligheden rammer.</p>

<h3>Sådan virker det</h3>
<ul>
  <li><strong>Byg en population</strong> af personaer baseret på demografi og psykografi.</li>
  <li><strong>Kør et scenarie</strong> — opslag, kommentarer og spredning simuleres.</li>
  <li><strong>Analyser resultatet</strong> og juster kommunikationen før udrulning.</li>
</ul>

<h3>Anvendelser</h3>
<p>Krisekommunikation · politiske kampagner · produktlanceringer · medietræning · risikoanalyse.</p>
HTML;
    }

    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_visible', true)->firstOrFail();
        $editUrl = '/cms/'.$page->id.'/edit';
        return view('page', compact('page', 'editUrl'));
    }
}

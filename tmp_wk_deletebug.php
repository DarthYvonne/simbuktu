<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webkraft\Cms\Http\Controllers\Admin\PageController;
use Webkraft\Cms\Models\Page;

view()->share('errors', new Illuminate\Support\ViewErrorBag);

$page = Page::create(['title' => 'Delete-bug test', 'slug' => 'delete-bug-test', 'is_published' => true, 'body' => []]);
$html = (new PageController)->edit($page)->render();

// Split at the END of the main editor form (first </form>).
$mainEnd = strpos($html, '</form>');
$mainForm = substr($html, 0, $mainEnd);
$afterMain = substr($html, $mainEnd);

$methodInMain = substr_count($mainForm, 'name="_method"');
$mainHasPatch = str_contains($mainForm, 'name="_method" value="PATCH"');
$mainHasDelete = str_contains($mainForm, 'name="_method" value="DELETE"');
$deleteFormOutside = str_contains($afterMain, 'id="wk-page-delete"') && str_contains($afterMain, 'value="DELETE"');
$buttonRefsForm = str_contains($mainForm, 'form="wk-page-delete"');

echo "main form _method count : {$methodInMain} (expect 1)\n";
echo "main form has PATCH     : ".($mainHasPatch ? 'yes' : 'NO')."\n";
echo "main form has DELETE    : ".($mainHasDelete ? 'YES (BUG!)' : 'no (good)')."\n";
echo "delete form is separate : ".($deleteFormOutside ? 'yes' : 'NO')."\n";
echo "delete btn refs ext form: ".($buttonRefsForm ? 'yes' : 'NO')."\n";

echo "\nRESULT: ".(($methodInMain === 1 && $mainHasPatch && !$mainHasDelete && $deleteFormOutside && $buttonRefsForm) ? 'FIXED ✔' : 'STILL BROKEN ✘')."\n";

$page->delete();

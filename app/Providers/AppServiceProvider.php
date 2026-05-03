<?php

namespace App\Providers;

use App\Services\Llm\GeminiClient;
use App\Services\Personas\PersonaRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GeminiClient::class, fn () => new GeminiClient(config('gemini')));

        $this->app->bind(PersonaRepository::class, function () {
            $pop = Auth::user()?->currentCourse()?->population;
            return new PersonaRepository($pop?->id);
        });
    }

    public function boot(): void
    {
    }
}

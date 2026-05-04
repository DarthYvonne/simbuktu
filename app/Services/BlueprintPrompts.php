<?php

namespace App\Services;

use App\Models\Blueprint;

/**
 * Overlays a blueprint's prompt_overrides onto a PromptRepository so every
 * persona-facing prompt (narrative, comment compose/interpret, DM, batch
 * reaction) reads the blueprint-scoped body if the blueprint provides one.
 *
 * Empty/missing keys fall through to the global prompt body or PromptRepository defaults.
 */
class BlueprintPrompts
{
    /** @var array<int, ?Blueprint> request-local cache */
    private static array $cache = [];

    public static function overlay(PromptRepository $base, ?int $blueprintId): PromptRepository
    {
        if ($blueprintId === null) return $base;
        $bp = self::load($blueprintId);
        if (!$bp) return $base;
        $overrides = $bp->prompt_overrides ?? [];
        if (!is_array($overrides) || empty($overrides)) return $base;

        $repo = $base;
        foreach ($overrides as $key => $body) {
            if (!is_string($body)) continue;
            $body = trim($body);
            if ($body === '') continue;
            if (!in_array($key, Blueprint::PROMPT_KEYS, true)) continue;
            $repo = $repo->withOverride($key, $body);
        }
        return $repo;
    }

    private static function load(int $id): ?Blueprint
    {
        if (!array_key_exists($id, self::$cache)) {
            self::$cache[$id] = Blueprint::find($id);
        }
        return self::$cache[$id];
    }
}

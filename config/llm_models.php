<?php

/**
 * LLM models available for runtime comment generation.
 * Prices are USD per 1M tokens (input / output) from provider docs as of 2025.
 * Rough conversion: 1000 characters of Danish text ≈ 300 tokens.
 */
return [
    // --- Google Gemini ---
    'gemini-2.5-flash-lite' => [
        'label' => 'Gemini 2.5 Flash Lite (billigst)',
        'provider' => 'gemini',
        'price_in' => 0.019,
        'price_out' => 0.075,
    ],
    'gemini-2.5-flash' => [
        'label' => 'Gemini 2.5 Flash',
        'provider' => 'gemini',
        'price_in' => 0.075,
        'price_out' => 0.30,
    ],
    'gemini-2.5-pro' => [
        'label' => 'Gemini 2.5 Pro',
        'provider' => 'gemini',
        'price_in' => 1.25,
        'price_out' => 10.00,
    ],

    // --- xAI Grok ---
    'grok-3-mini' => [
        'label' => 'Grok 3 Mini',
        'provider' => 'grok',
        'price_in' => 0.30,
        'price_out' => 0.50,
    ],
    'grok-3' => [
        'label' => 'Grok 3',
        'provider' => 'grok',
        'price_in' => 3.00,
        'price_out' => 15.00,
    ],
    'grok-4' => [
        'label' => 'Grok 4 (nyeste)',
        'provider' => 'grok',
        'price_in' => 5.00,
        'price_out' => 25.00,
    ],
];

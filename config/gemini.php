<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
    'text_model' => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
    'narrative_model' => env('GEMINI_NARRATIVE_MODEL', 'gemini-2.5-pro'),
    'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    'timeout' => 120,
];

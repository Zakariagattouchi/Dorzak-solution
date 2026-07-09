<?php

namespace App\Services;

use App\Contracts\TranslationProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCloudTranslationProvider implements TranslationProvider
{
    public function translateToArabic(array $texts): array
    {
        $key = (string) config('services.google_translate.key');
        if ($key === '') {
            throw new RuntimeException('Google Cloud Translation is not configured.');
        }

        $response = Http::timeout(15)->post(
            'https://translation.googleapis.com/language/translate/v2?key='.urlencode($key),
            ['q' => $texts, 'source' => 'en', 'target' => 'ar', 'format' => 'text'],
        )->throw()->json('data.translations', []);

        return array_map(
            fn (array $translation) => html_entity_decode((string) ($translation['translatedText'] ?? ''), ENT_QUOTES | ENT_HTML5),
            $response,
        );
    }
}

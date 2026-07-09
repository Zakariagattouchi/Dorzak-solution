<?php

namespace App\Http\Controllers\Api;

use App\Contracts\TranslationProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\TranslateProductRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProductTranslationController extends Controller
{
    public function __invoke(TranslateProductRequest $request, TranslationProvider $provider): JsonResponse
    {
        $description = (string) ($request->validated('description') ?? '');

        try {
            $translated = $provider->translateToArabic([
                $request->validated('name'),
                $description,
            ]);
        } catch (Throwable $error) {
            report($error);

            return response()->json([
                'message' => 'Arabic translation is temporarily unavailable. Check the translation service configuration.',
            ], 503);
        }

        return response()->json(['data' => [
            'name_ar' => $translated[0] ?? '',
            'description_ar' => $description === '' ? '' : ($translated[1] ?? ''),
        ]]);
    }
}

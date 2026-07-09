<?php

namespace App\Contracts;

interface TranslationProvider
{
    /** @param list<string> $texts @return list<string> */
    public function translateToArabic(array $texts): array;
}

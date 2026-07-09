<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Store;

/**
 * Bulk customer import from parsed CSV rows. Rows missing name/phone are reported as
 * errors; rows whose phone already exists in the store are skipped (idempotent).
 */
class CustomerImportService
{
    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{imported:int, skipped:int, errors:list<array{row:int, message:string}>}
     */
    public function import(Store $store, array $rows): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $existing = $store->customers()->pluck('phone_normalized')->flip();

        foreach ($rows as $index => $row) {
            $line = $index + 2; // account for header row
            $name = trim((string) ($row['name'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));

            if ($name === '' || $phone === '') {
                $errors[] = ['row' => $line, 'message' => 'name and phone are required'];

                continue;
            }

            $normalized = preg_replace('/\D/', '', $phone);
            if ($normalized !== '' && $existing->has($normalized)) {
                $skipped++;

                continue;
            }

            $store->customers()->create([
                'name' => $name,
                'phone' => $phone,
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
                'city' => $row['city'] ?? null,
                'notes' => $row['notes'] ?? null,
            ]);

            $existing->put($normalized, true);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }
}

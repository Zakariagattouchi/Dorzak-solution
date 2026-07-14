<?php

namespace App\Support;

interface E2eSupervisor
{
    public function facts(): array;

    public function candidateExists(string $database, string $role): bool;

    public function noncandidateFingerprint(): string;

    public function createCandidate(string $database, string $role, string $password): void;

    public function candidateUrl(string $database, string $role, string $password): string;

    public function noncandidateDatabasesWithAccess(string $role, string $candidate): array;

    public function candidateFacts(string $url): array;

    public function activate(
        string $url,
        string $database,
        string $role,
        string $activationNonceSha256,
        string $fixtureContractSha256,
        string $serviceNonceSha256,
    ): void;
}

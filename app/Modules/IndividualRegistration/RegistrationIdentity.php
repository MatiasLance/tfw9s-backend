<?php

namespace App\Modules\IndividualRegistration;

final class RegistrationIdentity
{
    public static function make(int $seriesId, array $metadata): string
    {
        $parts = [
            $seriesId,
            $metadata['teamName'] ?? '',
            $metadata['ageGroup'] ?? '',
            self::normalize($metadata['contactEmail'] ?? ''),
            self::normalize($metadata['playerFirstName'] ?? ''),
            self::normalize($metadata['playerLastName'] ?? ''),
            $metadata['dob'] ?? '',
        ];

        return hash('sha256', implode('|', $parts));
    }

    private static function normalize(mixed $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}

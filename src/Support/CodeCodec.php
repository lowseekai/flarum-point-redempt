<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Support;

final class CodeCodec
{
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public static function generate(): string
    {
        $raw = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < 16; $i++) {
            $raw .= self::ALPHABET[random_int(0, $max)];
        }
        return 'LS-'.implode('-', str_split($raw, 4));
    }

    public static function normalize(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', trim($code)));
    }

    public static function hash(string $code, string $secret): string
    {
        return hash_hmac('sha256', self::normalize($code), $secret);
    }

    public static function suffix(string $code): string
    {
        return substr(self::normalize($code), -4);
    }
}

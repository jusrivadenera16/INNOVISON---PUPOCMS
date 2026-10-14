<?php

namespace App\Support;

use Throwable;

class SensitiveLogRedactor
{
    public static function identifier(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $identifierPositions = [];
        foreach ($characters as $index => $character) {
            if (preg_match('/[\p{L}\p{N}]/u', $character)) {
                $identifierPositions[] = $index;
            }
        }

        $length = count($identifierPositions);
        $visiblePositions = $length > 8
            ? array_merge(array_slice($identifierPositions, 0, 4), array_slice($identifierPositions, -4))
            : array_slice($identifierPositions, -min(2, $length));
        $visiblePositions = array_flip($visiblePositions);

        foreach ($characters as $index => $character) {
            if (preg_match('/[\p{L}\p{N}]/u', $character) && !isset($visiblePositions[$index])) {
                $characters[$index] = '*';
            }
        }

        return implode('', $characters);
    }

    public static function email(?string $value): ?string
    {
        $value = trim((string) $value);
        $at = strrpos($value, '@');
        if ($value === '' || $at === false || $at === 0 || $at === strlen($value) - 1) {
            return self::identifier($value);
        }

        $localPart = substr($value, 0, $at);
        $localLength = mb_strlen($localPart);
        $visibleLength = $localLength >= 4 ? 2 : max(0, $localLength - 1);
        $maskedLength = max(2, $localLength - $visibleLength);

        return mb_substr($localPart, 0, $visibleLength)
            . str_repeat('*', $maskedLength)
            . substr($value, $at);
    }

    public static function name(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $parts = preg_split('/[^\p{L}\p{N}]+/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($parts === []) {
            return null;
        }

        return implode('.', array_map(
            static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)),
            $parts
        )) . '.';
    }

    public static function exceptionContext(Throwable $exception): array
    {
        $context = [
            'exception_type' => get_class($exception),
            'exception_code' => $exception->getCode(),
            'exception_location' => basename($exception->getFile()) . ':' . $exception->getLine(),
        ];

        if ($previous = $exception->getPrevious()) {
            $context['previous_exception_type'] = get_class($previous);
            $context['previous_exception_code'] = $previous->getCode();
            $context['previous_exception_location'] = basename($previous->getFile()) . ':' . $previous->getLine();
        }

        return $context;
    }
}

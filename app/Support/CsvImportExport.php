<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvImportExport
{
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('Unable to open CSV output stream.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public static function read(UploadedFile $file): array
    {
        $input = fopen($file->getRealPath(), 'r');

        if ($input === false) {
            throw new RuntimeException('Unable to read uploaded CSV file.');
        }

        $headers = fgetcsv($input);

        if ($headers === false) {
            fclose($input);

            return [];
        }

        $headers = array_map(static fn ($header) => self::normalizeHeader($header), $headers);
        $rows = [];

        while (($values = fgetcsv($input)) !== false) {
            if (self::isEmptyRow($values)) {
                continue;
            }

            $values = array_pad($values, count($headers), null);
            $rows[] = array_combine($headers, array_slice($values, 0, count($headers))) ?: [];
        }

        fclose($input);

        return $rows;
    }

    /**
     * @param callable(array<string, string|null>, int): mixed $rowHandler
     */
    public static function import(UploadedFile $file, callable $rowHandler): int
    {
        $rows = self::read($file);

        if ($rows === []) {
            throw new RuntimeException('The uploaded CSV file is empty.');
        }

        $processed = 0;

        foreach ($rows as $index => $row) {
            $rowHandler($row, $index + 2);
            $processed++;
        }

        return $processed;
    }

    private static function normalizeHeader(mixed $header): string
    {
        return strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
    }

    /**
     * @param array<int, mixed> $values
     */
    private static function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
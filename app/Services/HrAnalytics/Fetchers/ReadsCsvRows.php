<?php

namespace App\Services\HrAnalytics\Fetchers;

use RuntimeException;
use SplFileObject;
use SplTempFileObject;

trait ReadsCsvRows
{
    protected function readCsvFile(string $path, int $limit): array
    {
        return iterator_to_array($this->iterateCsvFile($path, $limit), false);
    }

    protected function iterateCsvFile(string $path, int $limit): \Generator
    {
        if (! is_file($path)) {
            throw new RuntimeException("File CSV tidak ditemukan: {$path}");
        }

        $file = new SplFileObject($path);
        $file->setCsvControl($this->detectDelimiter(file_get_contents($path, false, null, 0, 4096) ?: ''));

        yield from $this->iterateCsvObject($file, $limit);
    }

    protected function readCsvString(string $contents, int $limit): array
    {
        $file = new SplTempFileObject();
        $file->fwrite($contents);
        $file->rewind();
        $file->setCsvControl($this->detectDelimiter(substr($contents, 0, 4096)));

        return $this->readCsvObject($file, $limit);
    }

    private function readCsvObject(SplFileObject $file, int $limit): array
    {
        return iterator_to_array($this->iterateCsvObject($file, $limit), false);
    }

    private function iterateCsvObject(SplFileObject $file, int $limit): \Generator
    {
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = [];
        $count = 0;

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($index === 0) {
                $headers = array_map(fn ($header) => trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"), $row);
                continue;
            }

            if ($headers === []) {
                continue;
            }

            if ($count >= $limit) {
                break;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), null);
            }

            if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            $count++;
            yield array_combine($headers, $row);
        }
    }

    private function detectDelimiter(string $sample): string
    {
        $firstLine = strtok($sample, "\r\n") ?: $sample;
        $delimiters = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($delimiters);

        return array_key_first($delimiters) ?: ',';
    }
}

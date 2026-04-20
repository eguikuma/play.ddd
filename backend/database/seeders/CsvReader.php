<?php

namespace Database\Seeders;

/**
 * シーダー用の CSV 読み込みユーティリティ
 *
 * ヘッダー行をキーとした連想配列の配列を返す
 */
class CsvReader
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function read(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $rows = [];

        while (($fields = fgetcsv($handle)) !== false) {
            if (count($fields) === count($headers)) {
                $rows[] = array_combine($headers, $fields);
            }
        }

        fclose($handle);

        return $rows;
    }
}

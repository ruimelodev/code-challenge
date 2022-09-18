<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use League\Csv\Reader;

class ComputersModel
{
    public function list(): array
    {
        if (Cache::has('computers_list')) {
            return Cache::get('computers_list');
        }

        $csv = Reader::createFromPath(
            storage_path('fixtures/computers.csv'), 
            'r'
        );
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $list = $this->sanitize($csv->getRecords());

        Cache::add('computers_list', $list);
        
        return $list;
    }

    public function sanitize($list)
    {
        $clean_list = [];

        foreach ($list as $item) {
            preg_match_all('/^(\d+\D{2})(.*)$/i', $item['RAM'], $memory_matches);
            preg_match_all('/^(\d+x)(\d+\D{2})(.*)$/i', $item['HDD'], $storage_matches);

            $clean_list[] = [
                "model" => $item['Model'],
                "memory_size" => $memory_matches[1][0],
                "memory_type" => $memory_matches[2][0],
                "storage_count" => intval($storage_matches[1][0]),
                "storage_size" => $storage_matches[2][0],
                "storage_type" => $storage_matches[3][0],
                "location" => $item['Location'],
                "price" => (double) filter_var(
                    $item['Price'], 
                    FILTER_SANITIZE_NUMBER_FLOAT, 
                    FILTER_FLAG_ALLOW_FRACTION
                )
            ];
        }

        return $clean_list;
    }
}

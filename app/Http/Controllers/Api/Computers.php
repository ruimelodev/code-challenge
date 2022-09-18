<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComputersModel;
use Illuminate\Http\Request;
use Exception;

class Computers extends Controller
{
    /**
     * @var $request Request 
     */
    private $request;

    /**
     * @var $computersModel ComputersModel 
     */
    private $computersModel;

    private $result = [];

    private $storage_sizes = [
        '0', '250GB', '500GB', '1TB', '2TB', '3TB', '4TB', '8TB', '12TB', '24TB', '48TB', '72TB'
    ];

    private $memory_sizes = [
        '2GB', '4GB', '8GB', '12GB', '16GB', '24GB', '32GB', '48GB', '64GB', '96GB'
    ];

    private $storage_types = ['SAS', 'SATA2', 'SSD'];

    public function index(Request $request): array
    {
        $this->request = $request;
        $this->result = $this->fetch_result();

        try {
            if ($this->request->has('filter')) {
                $this->parse_filter_parameters();
            }
        } catch (Exception $e) {
            return [
                "error" => ["message" => $e->getMessage()]
            ];
        }

        return array_values($this->result);
    }

    private function fetch_result(): array
    {
        $this->computersModel = resolve(ComputersModel::class);
        return $this->computersModel->list();
    }

    private function parse_filter_parameters(): void
    {
        if ($this->request->has(['filter.storage_min_size', 'filter.storage_max_size'])) {
            $this->filter_result_by_storage_size();
        }

        if ($this->request->has('filter.memory_size')) {
            $this->filter_result_by_memory_size();
        }

        if ($this->request->has('filter.storage_type')) {
            $this->filter_result_by_storage_type();
        }
    }

    private function filter_result_by_storage_size(): void
    {
        $min = strtoupper($this->request->input('filter.storage_min_size'));
        $max = strtoupper($this->request->input('filter.storage_max_size'));

        if (in_array($min, $this->storage_sizes) == false 
            || in_array($max, $this->storage_sizes) == false
        ) {
            throw new Exception("Invalid storage size filter");
        }

        $min_key = array_search($min, $this->storage_sizes);
        $max_key = array_search($max, $this->storage_sizes);

        $this->result = array_filter(
            $this->result, function ($item) use ($min_key, $max_key) {
                $key = array_search(strtoupper($item['storage_size']), $this->storage_sizes);
                return $key >= $min_key && $key <= $max_key;
            }
        );
    }

    private function filter_result_by_memory_size(): void
    {
        $sizes = strtoupper($this->request->input('filter.memory_size'));
        $sizes = array_map('trim', explode(',', $sizes));

        if (array_diff($sizes, $this->memory_sizes) == true) {
            throw new Exception("Invalid memory size filter");
        }

        $this->result = array_filter(
            $this->result, function ($item) use ($sizes) {
                return in_array($item['memory_size'], $sizes);
            }
        );
    }

    private function filter_result_by_storage_type(): void
    {
        $type = strtoupper($this->request->input('filter.storage_type'));

        if (in_array($type, $this->storage_types) == false) {
            throw new Exception("Invalid storage type filter");
        }

        $this->result = array_filter(
            $this->result, function ($item) use ($type) {
                return $type == strtoupper($item['storage_type']);
            }
        );
    }
}

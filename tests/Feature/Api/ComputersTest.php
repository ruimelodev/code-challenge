<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\ComputersModel;

class ComputersTest extends TestCase
{
    private $fixtures = [
        0 => [
            "model" => "Dell R210Intel Xeon X3440",
            "memory_size" => "16GB",
            "memory_type" => "DDR3",
            "storage_count" => "2x",
            "storage_size" => "4TB",
            "storage_type" => "SATA2",
            "location" => "AmsterdamAMS-01",
            "price" => 49.99
        ],
        1 => [
            "model" => "HP DL180G62x Intel Xeon E5620",
            "memory_size" => "64GB",
            "memory_type" => "DDR4",
            "storage_count" => "8x",
            "storage_size" => "4tb",
            "storage_type" => "SATA2",
            "location" => "AmsterdamAMS-01",
            "price" => 119.99
        ],
        2 => [
            "model" => "Dell R210-IIIntel Xeon E3-1270v2",
            "memory_size" => "16GB",
            "memory_type" => "DDR3",
            "storage_count" => "2x",
            "storage_size" => "2TB",
            "storage_type" => "SATA2",
            "location" => "SingaporeSIN-11",
            "price" => 565.99
        ],
        3 => [
            "model" => "HP DL120G7Intel Xeon E3-1230",
            "memory_size" => "8GB",
            "memory_type" => "DDR3",
            "storage_count" => "4x",
            "storage_size" => "500GB",
            "storage_type" => "SSD",
            "location" => "Washington D.C.WDC-01",
            "price" => 1807.99
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var $computersMock MockObject */
        $computersMock = $this->createMock(ComputersModel::class);
        $computersMock->method('list')->willReturn($this->fixtures);
        $this->app->instance(ComputersModel::class, $computersMock);
    }

    public function test_get_list(): void
    {
        $response = $this->get('/api/computers');

        $response->assertStatus(200);
        $this->assertSame(json_encode($this->fixtures), $response->getContent());
    }

    public function storage_size_provider(): array
    {
        return [
            ['3TB', '12TB', [$this->fixtures[0], $this->fixtures[1]]],
            ['3tb', '12tb', [$this->fixtures[0], $this->fixtures[1]]],
            ['250GB', '500GB', [$this->fixtures[3]]],
            ['500GB', '1TB', [$this->fixtures[3]]],
            ['2TB', '2TB', [$this->fixtures[2]]],
            [' 2TB ', ' 2TB ', [$this->fixtures[2]]],
            ['0', '72TB', $this->fixtures],
            ['72TB', '0', []],
            ['0', '250GB', []],
            [' ', ' ', ["error" => ["message" => "Invalid storage size filter"]]],
            ['2TB', '', ["error" => ["message" => "Invalid storage size filter"]]],
            ['', '2TB', ["error" => ["message" => "Invalid storage size filter"]]],
            ['invalid', 'invalid', ["error" => ["message" => "Invalid storage size filter"]]],
        ];
    }

    /**
     * @dataProvider storage_size_provider 
     */
    public function test_filter_by_storage_size($min_size, $max_size, $expected_result): void
    {
        $response = $this->get(
            '/api/computers?' .
                'filter[storage_min_size]=' . $min_size . '&' .
                'filter[storage_max_size]=' . $max_size
        );

        $response->assertStatus(200);
        $this->assertSame(
            json_encode($expected_result),
            $response->getContent()
        );
    }

    public function memory_size_provider(): array
    {
        return [
            ['16GB', [$this->fixtures[0], $this->fixtures[2]]],
            ['16gb', [$this->fixtures[0], $this->fixtures[2]]],
            ['12GB,16GB,24GB', [$this->fixtures[0], $this->fixtures[2]]],
            ['4GB,12GB,16GB', [$this->fixtures[0], $this->fixtures[2]]],
            ['16GB,24GB,32GB', [$this->fixtures[0], $this->fixtures[2]]],
            [' 16GB, 24GB ,32GB ', [$this->fixtures[0], $this->fixtures[2]]],
            ['12GB,24GB', []],
            ['2GB,4GB,8GB,12GB,16GB,24GB,32GB,48GB,64GB,96GB', $this->fixtures],
            [' ', ["error" => ["message" => "Invalid memory size filter"]]],
            ['invalid', ["error" => ["message" => "Invalid memory size filter"]]],
        ];
    }

    /**
     * @dataProvider memory_size_provider 
     */
    public function test_filter_by_memory_size($sizes, $expected_result): void
    {
        $response = $this->get('/api/computers?filter[memory_size]=' . $sizes);

        $response->assertStatus(200);
        $this->assertSame(
            json_encode($expected_result),
            $response->getContent()
        );
    }

    public function storage_type_provider(): array
    {
        return [
            ['SSD', [$this->fixtures[3]]],
            [' SSD ', [$this->fixtures[3]]],
            ['SATA2', [$this->fixtures[0], $this->fixtures[1], $this->fixtures[2]]],
            ['SAS', []],
            ['SATA3', ["error" => ["message" => "Invalid storage type filter"]]],
            [' ', ["error" => ["message" => "Invalid storage type filter"]]],
            ['invalid', ["error" => ["message" => "Invalid storage type filter"]]],
        ];
    }

    /**
     * @dataProvider storage_type_provider 
     */
    public function test_filter_by_storage_type($type, $expected_result): void
    {
        $response = $this->get('/api/computers?filter[storage_type]=' . $type);

        $response->assertStatus(200);
        $this->assertSame(
            json_encode($expected_result),
            $response->getContent()
        );
    }

    public function multiple_filters_provider(): array
    {
        return [
            [
                ['filter' => [
                    'storage_min_size' => '2TB',
                    'storage_max_size' => '4TB',
                    'storage_type' => 'SATA2',
                    'memory_size' => '16GB',
                ]],
                [$this->fixtures[0], $this->fixtures[2]]
            ],
            [
                ['filter' => [
                    'storage_min_size' => '2TB',
                    'storage_max_size' => '4TB',
                    'storage_type' => 'SAS',
                    'memory_size' => '16GB',
                ]],
                []
            ],
        ];
    }

    /**
     * @dataProvider multiple_filters_provider 
     */
    public function test_multiple_filters($filters, $expected_result): void
    {
        $response = $this->get('/api/computers?' . http_build_query($filters));

        $response->assertStatus(200);
        $this->assertSame(
            json_encode($expected_result),
            $response->getContent()
        );
    }
}

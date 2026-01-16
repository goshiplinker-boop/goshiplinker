<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SellerPincodeImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $companyId;
    protected int $courierId;

    public function __construct(int $companyId, int $courierId)
    {
        $this->companyId = $companyId;
        $this->courierId = $courierId;
    }

    /**
     * This method is called once per chunk
     */
    public function collection(Collection $rows)
    {
        $payload = [];

        foreach ($rows as $row) {
            $pincode = trim($row['pincodes'] ?? '');

            if (!$pincode || strlen($pincode) !== 6) {
                continue;
            }

            $payload[] = [
                'company_id' => $this->companyId,
                'courier_id' => $this->courierId,
                'pincode'    => $pincode,
                'status'     => isset($row['status']) ? (int)$row['status'] : 1,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        if (!empty($payload)) {
            DB::table('seller_pincodes')->upsert(
                $payload,
                ['company_id', 'courier_id', 'pincode'], // UNIQUE KEY
                ['status', 'updated_at']
            );
        }
    }

    /**
     * Number of rows read per chunk
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}

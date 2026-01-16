<?php

namespace App\Imports;
use Illuminate\Validation\Rule;
use App\Models\CourierRateCard;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
class CourierRateCardImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;
    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }
    public function rules(): array
    {
        return [
            'courier_id' => [
                'required',
                Rule::exists('couriers', 'id')
                    ->where(fn($q) => $q->where('company_id', $this->companyId)->where('status', 1)),
            ],
            'zone_name'      => [ 'required', Rule::in(['A','B','C','D','E','F']) ],

            'weight_slabkg' => [
                'required',
                'numeric',
                Rule::in([0.1, 0.25, 0.5, 1, 2, 3, 5, 10]),
            ],
            'base_freight_forwardrs' => ['required', 'numeric'],
            'additional_freightrs'   => ['required', 'numeric'],
            'rto_freightrs'          => ['required', 'numeric'],
            'cod_chargers'           => ['required', 'numeric'],
            'cod_percentage'       => ['required', 'numeric'],
            'delivery_sla' => [
                'required',
                Rule::in(['0', '0-1', '1-2', '2-3', '3-5', '5-7']),
            ],
            'cod_allowed'          => [],
            'sorting'              => ['nullable', 'numeric'],            
        ];
    }
    public function model(array $row)
    {
        if (empty($row['zone_name'])) {
            return null; // skip blank rows
        }

        // Normalize zone → A/B/C/D/E
        $zone = trim($row['zone_name']);
        $zone = strtoupper($zone);

        // Accept "Zone A", "ZONE A", "A"
        if (str_starts_with($zone, 'ZONE')) {
            $zone = trim(substr($zone, 4));
        }

        // Convert Y/N to boolean
        $codAllowed = strtoupper(trim($row['cod_allowed'] ?? 'N')) === 'Y';
        //\Log::info($row);
        //maaatwebsite/excel converts to lowercase and underscores automatically
        return CourierRateCard::updateOrCreate(
            [
                'company_id'     => $this->companyId,
                'courier_id'     => $row['courier_id'],
                'zone_name'      => $zone,
                'weight_slab_kg' => $row['weight_slabkg'],
            ],
            [
                'base_freight_forward' => $row['base_freight_forwardrs'],
                'additional_freight'   => $row['additional_freightrs'],
                'rto_freight'          => $row['rto_freightrs'],
                'cod_charge'           => $row['cod_chargers'],
                'cod_percentage'       => $row['cod_percentage'],
                'delivery_sla'         => $row['delivery_sla'],
                'cod_allowed'          => $codAllowed,
                'sort_order'           => $row['sorting'],
            ]
        );
    }
}

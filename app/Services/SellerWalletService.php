<?php

namespace App\Services;

use App\Models\SellerWallet;
use App\Models\SellerWalletLedger;
use Illuminate\Support\Facades\DB;
use Exception;

class SellerWalletService
{
    /**
     * Apply freight charge when tracking is assigned
     */
    public function applyFreight(array $data): SellerWalletLedger
    {
        return DB::transaction(function () use ($data) {

            $wallet = SellerWallet::where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $openingBalance = $wallet->balance;
            $closingBalance = $openingBalance - $data['amount'];

            if ($closingBalance < 0) {
                throw new Exception('Insufficient wallet balance');
            }

            $ledger = SellerWalletLedger::create([
                'company_id'       => $data['company_id'],
                'order_id'         => $data['order_id'] ?? null,
                'shipment_id'      => $data['shipment_id'],
                'courier_id'       => $data['courier_id'] ?? null,
                'courier_code'     => $data['courier_code'] ?? null,
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'freight_charge',
                'cod_charges' => $data['cod_charges'] ?? 0,
                'direction'        => 'debit',
                'amount'           => $data['amount'],
                'opening_balance'  => $openingBalance,
                'closing_balance'  => $closingBalance,
                'description'      => 'Freight charge applied on tracking assign',
                'source'           => 'system',
            ]);

            $wallet->update([
                'balance' => $closingBalance,
            ]);

            return $ledger;
        });
    }

    /**
     * Revert freight charge when tracking is unassigned
     */
    public function revertFreight(array $data): SellerWalletLedger
    {
        return DB::transaction(function () use ($data) {

            /**
             * STEP 1: Prevent double reversal
             */
            $alreadyReverted = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'freight_reversal',
            ])->exists();

            if ($alreadyReverted) {
                throw new Exception('Freight already reversed for this shipment');
            }

            /**
             * STEP 2: Lock wallet row
             */
            $wallet = SellerWallet::where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * STEP 3: Find original freight charge
             */
            $freightCharge = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'freight_charge',
            ])->latest()->first();

            if (!$freightCharge) {
                throw new Exception('Freight charge not found for this shipment');
            }

            /**
             * STEP 4: Credit wallet
             */
            $openingBalance = $wallet->balance;
            $closingBalance = $openingBalance + $freightCharge->amount;

            $ledger = SellerWalletLedger::create([
                'company_id'       => $data['company_id'],
                'order_id'         => $freightCharge->order_id,
                'shipment_id'      => $freightCharge->shipment_id,
                'courier_id'       => $freightCharge->courier_id,
                'courier_code'     => $freightCharge->courier_code,
                'tracking_number'  => $freightCharge->tracking_number,
                'transaction_type' => 'freight_reversal',
                'direction'        => 'credit',
                'amount'           => $freightCharge->amount,
                'opening_balance'  => $openingBalance,
                'closing_balance'  => $closingBalance,
                'description'      => 'Freight reversed due to tracking unassign',
                'source'           => 'system',
            ]);

            /**
             * STEP 5: Update wallet balance
             */
            $wallet->update([
                'balance' => $closingBalance,
            ]);

            return $ledger;
        });
    }
    /**
     * Revert COD charge when RTO is delivered
     */
    public function revertCodCharge(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Prevent double reversal
            $alreadyReverted = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'cod_reversal',
            ])->exists();

            if ($alreadyReverted) {
                throw new \Exception('COD already reverted');
            }

            // Find original COD charge
            $codCharge = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'freight_charge',
            ])->latest()->first();

            if (!$codCharge || !$codCharge->cod_charges || $codCharge->cod_charges <= 0) {
                throw new \Exception('COD charge not found');
            }

            $wallet = SellerWallet::where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $openingBalance = $wallet->balance;
            $closingBalance = $openingBalance - $codCharge->cod_charges;

            SellerWalletLedger::create([
                'company_id'       => $data['company_id'],
                'order_id'         => $codCharge->order_id,
                'shipment_id'      => $codCharge->shipment_id,
                'courier_id'       => $codCharge->courier_id,
                'courier_code'     => $codCharge->courier_code,
                'tracking_number'  => $codCharge->tracking_number,
                'transaction_type' => 'cod_reversal',
                'direction'        => 'credit',
                'amount'           => $codCharge->cod_charges,
                'opening_balance'  => $openingBalance,
                'closing_balance'  => $closingBalance,
                'description'      => 'COD charge reversed on RTO delivered',
                'source'           => 'system',
            ]);

            $wallet->update([
                'balance' => $closingBalance
            ]);
        });
    }

    public function applyWeightDiscrepancyCharge(array $data)
    {
        return DB::transaction(function () use ($data) {

            /**
             * 1️⃣ Prevent duplicate charge
             */
            $alreadyCharged = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'transaction_type' => 'additional_weight_charge',
            ])
            ->where('description', 'LIKE', '%Weight discrepancy%')
            ->exists();

            if ($alreadyCharged) {
                return;
            }

            /**
             * 2️⃣ Lock wallet
             */
            $wallet = SellerWallet::where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $opening = $wallet->balance;
            $closing = $opening - $data['amount'];

            /**
             * 3️⃣ Create ledger entry
             */
            SellerWalletLedger::create([
                'company_id'       => $data['company_id'],
                'order_id'         => $data['order_id'] ?? null,
                'shipment_id'      => $data['shipment_id'],
                'courier_id'       => $data['courier_id'] ?? null,
                'courier_code'     => $data['courier_code'] ?? null,
                'tracking_number'  => $data['tracking_number'],
                'transaction_type' => 'additional_weight_charge',
                'direction'        => 'debit',
                'amount'           => $data['amount'],
                'opening_balance'  => $opening,
                'closing_balance'  => $closing,
                'description'      => 'Weight discrepancy charge',
                'source'           => 'cron',
            ]);

            /**
             * 4️⃣ Update wallet
             */
            $wallet->update(['balance' => $closing]);
        });
    }
    public function revertWeightDiscrepancyCharge(array $data)
    {
        return DB::transaction(function () use ($data) {

            $original = SellerWalletLedger::where([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'transaction_type' => 'additional_weight_charge',
            ])
            ->where('description', 'LIKE', '%Weight discrepancy%')
            ->latest()
            ->first();

            if (!$original) return;

            $wallet = SellerWallet::where('company_id', $data['company_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $opening = $wallet->balance;
            $closing = $opening + $original->amount;

            SellerWalletLedger::create([
                'company_id'       => $data['company_id'],
                'shipment_id'      => $data['shipment_id'],
                'tracking_number'  => $original->tracking_number,
                'transaction_type' => 'additional_weight_reversal',
                'direction'        => 'credit',
                'amount'           => $original->amount,
                'opening_balance'  => $opening,
                'closing_balance'  => $closing,
                'description'      => 'Weight discrepancy reversal (dispute approved)',
                'source'           => 'manual',
            ]);

            $wallet->update(['balance' => $closing]);
        });
    }

    public function applyFreightBulk(array $shipments): void
    {
        foreach ($shipments as $shipment) {
            try {
                $this->applyFreight($shipment);
            } catch (\Throwable $e) {
                \Log::error('Freight apply failed', [
                    'shipment_id' => $shipment['shipment_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    public function hasSufficientBalance(int $companyId, float $amount): bool
    {
        $wallet = SellerWallet::where('company_id', $companyId)->first();

        return $wallet && $wallet->balance >= $amount;
    }   


}

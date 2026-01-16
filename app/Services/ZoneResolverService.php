<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ZoneResolverService
{
    // Metro Cities
    protected $metroCities = [
        'Delhi', 'New Delhi',
        'Mumbai', 'Bombay',
        'Kolkata','Calcutta',
        'Chennai',
        'Hyderabad',
        'Bengaluru', 'Bangalore'
    ];

    // Special Zone States (Zone E)
    protected $specialStates = [
        'Arunachal Pradesh',
        'Assam',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Tripura',
        'Jammu and Kashmir',
        'Kerala'
    ];

    // Remote Islands (Zone F)
    protected $remoteZones = [
        'Andaman and Nicobar Islands'
    ];

    /**
     * Resolve Zone Dynamically
     */
    public function getZone($company_id,$seller_company_id,$courier_id,$originPincode, $destinationPincode, $isCod)
    {
        $origin = $this->getPincodeData($company_id,$seller_company_id,$courier_id,$originPincode, $isCod, true, false);
        $dest   = $this->getPincodeData($company_id,$seller_company_id,$courier_id,$destinationPincode, $isCod, false, true);
        if (!$origin || !$dest) {
            \Log::warning("ZoneResolverService: Pincode data not found or not serviceable. Origin: {$originPincode}, Dest: {$destinationPincode}, Courier ID: {$courier_id}");
            return null; // Pincode not serviceable
        }

        // -------- Zone A → Same City --------
        if ($origin->city === $dest->city) {
            return 'A';
        }

        // -------- Zone B → Same State --------
        if ($origin->state === $dest->state) {
            return 'B';
        }

        // -------- Zone C → Metro ↔ Metro --------
        if (
            in_array($origin->city, $this->metroCities) &&
            in_array($dest->city, $this->metroCities)
        ) {
            return 'C';
        }

        // -------- Zone E → Special States --------
        if (
            in_array($origin->state, $this->specialStates) ||
            in_array($dest->state, $this->specialStates)
        ) {
            return 'E';
        }

        // -------- Zone F → Remote Islands --------
        if (
            in_array($origin->state, $this->remoteZones) ||
            in_array($dest->state, $this->remoteZones)
        ) {
            return 'F';
        }       

        // -------- Zone D → Rest of India --------
        return 'D';
    }

    /**
     * Fetch pincode details (origin/destination)
     */
    protected function getPincodeData($company_id,$seller_company_id,$courier_id,$pincode, $isCod, $pickup = false, $delivery = false)
    {
        
        if($seller_company_id>0){
            $query = DB::table('seller_pincodes')->select('status')->where('company_id', $seller_company_id)->where('courier_id', $courier_id)->where('pincode', $pincode)->first();
            if($query && $query->status==0){
                return null;
            }
        }
        
        $query = DB::table('pincode_master')
            ->select('pincode', 'city', 'state')
            ->where('pincode', $pincode)
            ->where('company_id', $company_id)
            ->where('status', 1);
        
        if ($courier_id) {
            $query->where('courier_id', $courier_id);
        }
        // Pickup / Delivery checks
        if ($pickup) {
            $query->where('forward_pickup', 1);
        }
        if ($delivery) {
            $query->where('forward_delivery', 1);
        }

        // COD or Prepaid check
        if ($isCod) {
            $query->where('cod', 1);
        } else {
            $query->where('prepaid', 1);
        }
        return $query->first();
    }
}

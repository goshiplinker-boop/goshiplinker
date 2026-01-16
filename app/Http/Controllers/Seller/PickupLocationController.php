<?php

namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupLocation;
use App\Models\Country;
use App\Models\State;
class PickupLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id; 

        $pickupLocations = PickupLocation::where("company_id", $companyId)
            ->paginate(default_pagination_limit());

        if ($request->wantsJson() || $request->is("api/*")) {
            return response()->json([
                "success" => true,
                "data" => $pickupLocations
            ]);
        }

        return view("seller.pickup_locations.index", compact("pickupLocations"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();

        return view("seller.pickup_locations.create", compact("countries"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //return $request->all();
        $companyId = session("company_id");

        $validatedData = $request->validate([
            "location_title" => "required|string|max:255",
            "brand_name" => "required|string|max:255",
            "contact_person_name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone" => "required|string|max:15",
            "alternate_phone" => "nullable|string|max:15",
            "address" => "required|string",
            "landmark" => "nullable|string|max:255",
            "city" => "required|string|max:255",
            "state_code" => "required|string|max:2",
            "country_code" => "required|string|max:2",
            "zipcode" => "required|string|max:6",
            "gstin" => "required|string|max:15",
            "courier_warehouse_id" => "nullable|integer",
            "location_type" => "required|in:home,office,other",
            "default" => "required|boolean",
            "status" => "required|boolean",
            "pickup_day"=>'required',
            // "pickup_time" => "required", // Validate pickup_time
        ]);
        $validatedData["company_id"] = $companyId;
        PickupLocation::create($validatedData); // Create a new pickup location

        return redirect()
            ->route("pickup_locations.index")
            ->with("success", "Pickup location created successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $companyId = session("company_id");
        try {
            // Retrieve the pickup location by its ID or throw a 404 error if not found
            $pickupLocation = PickupLocation::where([
                "id" => $id,
                "company_id" => $companyId,
            ])->firstOrFail();
            $countries = Country::all();
            // Pass the retrieved data to the edit view
            return view("seller.pickup_locations.edit", [
                "pickupLocation" => $pickupLocation,
                "countries" => $countries,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view("errors.404", ["error" => "location does not exist"]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session("company_id");
        $post_data = $request->all();
        $post_data["company_id"] = $companyId;
        $validatedData = $request->validate([
            "location_title" => "required|string|max:255",
            "brand_name" => "required|string|max:255",
            "contact_person_name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone" => "required|string|max:15",
            "alternate_phone" => "nullable|string|max:15",
            "address" => "required|string",
            "landmark" => "nullable|string|max:255",
            "city" => "required|string|max:255",
            "state_code" => "required|string|max:2",
            "country_code" => "required|string|max:2",
            "zipcode" => "required|string|max:6",
            "gstin" => "required|string|max:15",
            "courier_warehouse_id" => "nullable|integer",
            "location_type" => "required|in:home,office,other",
            "default" => "required|boolean",
            "status" => "required|boolean",
             "pickup_day"=>'required',
            //"pickup_time" => "required", // Validate pickup_time
        ]);
        try {
            $pickupLocation = PickupLocation::where([
                "id" => $id,
                "company_id" => $companyId,
            ])->firstOrFail();
            $pickupLocation->update($validatedData); // Update the existing pickup location

            return redirect()
                ->route("pickup_locations.index")
                ->with("success", "Pickup location updated successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where no record was found
            return view("errors.404", ["error" => "location does not exist"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getStates($countryCode)
    {
        // Fetch states based on country_code
        $states = State::where("country_code", $countryCode)->get();
        // echo "<pre>";print_r($states);
        return response()->json($states);
    }
    public function CreatePickupLocation(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            "id" => "nullable|integer|exists:pickup_locations,id",
            "location_title" => "required|string|max:255",
            "brand_name" => "required|string|max:255",
            "contact_person_name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone" => "required|string|max:15",
            "alternate_phone" => "nullable|string|max:15",
            "address" => "required|string",
            "landmark" => "nullable|string|max:255",
            "city" => "required|string|max:255",
            "state_code" => "required|string|max:2",
            "country_code" => "required|string|max:2",
            "zipcode" => "required|string|max:6",
            "gstin" => "required|string|max:15",
            "location_type" => "required|in:home,office,other",
            "default" => "required|boolean",
            "status" => "required|boolean",
            //  "pickup_time" => "required",
            "company_id" => "required|integer|exists:companies,id",
        ]);

        try {
            // If an 'id' is provided, update the existing location
            if (!empty($validatedData['id'])) {
                $pickupLocation = PickupLocation::find($validatedData['id']);
                
                // If the pickup location is not found, return error
                if (!$pickupLocation) {
                    return response()->json([
                        'error' => 'Pickup location not found.'
                    ], 404);
                }

                // Remove the 'id' field from the validated data before updating
                unset($validatedData['id']);
                
                // Update the pickup location with new data
                $pickupLocation->update($validatedData);

                return response()->json([
                    'success' => true,
                    'message' => 'Pickup location updated successfully.',
                    'data' => $pickupLocation
                ]);
            }

            // If no 'id' is provided, create a new pickup location
            $pickupLocation = PickupLocation::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Pickup location created successfully.',
                'data' => $pickupLocation
            ], 201);

        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Pickup Location Creation/Update Error: ' . $e->getMessage());

            // Catch any unexpected error and return a response
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}
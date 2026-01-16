<?php

namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Retrieve a customer based on provided query parameters (email, phone, or customer_id).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomer(Request $request)
    {   
        // Get the authenticated user and their associated company ID
        $user = Auth::user();
        $companyId = $user->company_id;  

        // Validate the request to ensure email_id, phone, or customer_id is provided
        $validator = Validator::make($request->all(), [
            'email_id'    => 'nullable|email|exists:customers,email_id',
            'phone'       => 'nullable|string|exists:customers,phone',
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        // Return validation errors, if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Retrieve the query parameters
        $emailId    = $request->query('email_id');
        $phone      = $request->query('phone');
        $customerId = $request->query('customer_id');

        // Build the query based on the provided parameters, company_id is mandatory
        $query = Customer::where('company_id', $companyId);

        if ($emailId) {
            $query->where('email_id', $emailId); 
        }
        if ($phone) {
            $query->where('phone', $phone);
        }
        if ($customerId) {
            $query->where('id', $customerId);
        }

        // Retrieve the matching customer(s)
        $customers = $query->get();

        // Check if any customer was found
        if ($customers->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => $customers,
                'message' => 'Customer(s) found successfully.',
            ], 200);
        }

        // If no customer was found, return an appropriate response
        return response()->json([
            'success' => false,
            'message' => 'Customer not found.',
        ], 404);
    }

    /**
     * Create a new customer record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCustomer(Request $request)
    {     
        // Get the authenticated user and their associated company ID
        $user = Auth::user();
        $companyId = $user->company_id;

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'fullname'  => 'required|string|max:255',
            'email_id'  => 'required|email|unique:customers,email_id',
            'phone'     => 'required|string|max:15',
        ]);

        // Return validation errors, if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Add company_id to the request data
        $postData = $request->all();
        $postData['company_id'] = $companyId;

        // Create the customer record in the database
        $customer = Customer::create($postData);

        // Return a success response with the created customer data
        return response()->json([
            'success' => true,
            'data'    => $customer,
            'message' => 'Customer created successfully.',
        ], 201);
    }
}

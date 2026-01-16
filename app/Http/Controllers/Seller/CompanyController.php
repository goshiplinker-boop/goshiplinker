<?php

namespace App\Http\Controllers\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Country;
use App\Models\CompanyType;
class CompanyController extends Controller
{
    public function edit()
    {
        $company_id = session('company_id');
        $company = Company::join('users', 'companies.id', '=', 'users.company_id')
        ->select('users.*', 'companies.*')
        ->where('users.role_id', '=', 2)
        ->where('companies.id', '=', $company_id)->first();
        $countries = Country::all();
        $company_types = CompanyType::where('status',1)->get()->groupBy('type');
        $company->bank_details = json_decode($company->bank_details,true);
        $company->doc_urls = json_decode($company->doc_urls,true);
        $company_type_data = CompanyType::where('id', $company->company_type_id)
        ->select('type', 'subtype')
        ->first();
        $selectedType = $company_type_data->type??'';
        $selectedSubtype = $company_type_data->subtype??'';
     
        return view('seller.companies.profile',['company'=>$company,'countries'=>$countries,'company_types'=>$company_types,'selectedType'=>$selectedType,'selectedSubtype'=>$selectedSubtype]);
    }
    public function update(Request $request, string $id)
    {
        $company_id = session('company_id');
        if ($company_id != $id) {
            return redirect()->route('profile')
                ->withErrors('Unauthorized access')
                ->withInput();
        }
        $request->company_type_id = $request->company_type_id?:3;
        $company_type = $request->company_type;
        $company = Company::findOrFail($id);

        // -------------------------
        // VALIDATION RULES
        // -------------------------        
        $validated = $request->validate([
            'company_email_id' => 'required|string',
            'phone_number' => 'required|string|max:15',
            'legal_registered_name' => 'required|string|max:50',
            'pan_number' => 'required|string|max:10',
            'pan_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'brand_name' => 'required|string|max:50',
            'website_url' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'pincode' => 'required|numeric',
            'city' => 'required|string',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'country_code' => 'required|string|min:2|max:2',
            'state_code' => 'required|string|min:2|max:2',

            'company_type' => 'required|string',
            'company_type_id' => 'nullable',

            'aadhaar_number' => 'nullable|string',
            'aadhaar_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhaar_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'udyam_no' => 'nullable|string',
            'udyam_cert' => 'nullable|mimes:jpeg,png,jpg,gif,pdf|max:2048',

            'gstin_no' => 'nullable|string',
            'gstin_cert' => 'nullable|mimes:jpeg,png,jpg,gif,pdf|max:2048',

            // BANK DETAILS
            'bank_details.cancelled_check' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank_details.bank_name' => 'required|string',
            'bank_details.account_number' => 'required|string',
            'bank_details.account_holder_name' => 'required|string',
            'bank_details.ifsc_code' => 'required|string',

            // OTHER DETAILS
            'shipment_weight' => 'required|string',
            'channel_name' => 'required|string',
            'courier_using' => 'required|string',
            'product_category' => 'required|string',
            'monthly_orders' => 'required|string',
        ]);
        $company_details=[];
        $bank_details = [];
        $doc_urls = [];
        $company_docs = $company->doc_urls?json_decode($company->doc_urls.true):[];
        $company->bank_details = $company->bank_details?json_decode($company->bank_details.true):[];
        if ($request->hasFile('brand_logo')) {
                // Get the new image file
            $newImage = $request->file('brand_logo');
            // Generate a new unique filename
            $imageName = 'brand_logo' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if ($company->brand_logo) {
                $oldImagePath = public_path('assets/images/companies/logo/' . $company->brand_logo);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/logo'), $imageName);               
            // Update the company record with the new image name
            $company_details['brand_logo'] = $imageName;
        } else {
            unset($company_details['brand_logo']);
        }
        if ($request->hasFile('bank_details.cancelled_check')) {
            // Get the new image file
            $newImage = $request->file('bank_details.cancelled_check');
            // Generate a new unique filename
            $imageName = 'cancelled_check' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if (isset($company->bank_details['cancelled_check'])) {
                $oldImagePath = public_path('assets/images/companies/cancelled_checks/' . $company->bank_details['cancelled_check']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/cancelled_checks'), $imageName);               
            // Update the company record with the new image name
            $bank_details['cancelled_check'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($bank_details['cancelled_check']);
        }
        if ($request->hasFile('pan_image')) {
            // Get the new image file
            $newImage = $request->file('pan_image');
            // Generate a new unique filename
            $imageName = 'pan_docs' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if ($company->pan_doc) {
                $oldImagePath = public_path('assets/images/companies/pan_docs/' . $company->pan_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/pan_docs'), $imageName);               
            // Update the company record with the new image name
            $company_details['pan_image'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($company_details['pan_image']);
        }
        if ($request->hasFile('gstin_cert')) {
            // Get the new image file
            $newImage = $request->file('gstin_cert');
            // Generate a new unique filename
            $imageName = 'gstin_cert' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if (isset($company_docs['gstin']['gstin_cert'])) {
                $oldImagePath = public_path('assets/images/companies/gstin_cert/' . $company_docs['gstin']['gstin_cert']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/gstin_cert'), $imageName);               
            // Update the company record with the new image name
            $doc_urls['gstin']['gstin_cert'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($doc_urls['gstin']['gstin_cert']);
        }
        if ($request->hasFile('udyam_cert')) {
            // Get the new image file
            $newImage = $request->file('udyam_cert');
            // Generate a new unique filename
            $imageName = 'udyam_cert' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if (isset($company_docs['udyam']['udyam_cert'])) {
                $oldImagePath = public_path('assets/images/companies/udyam_cert/' . $company_docs['udyam']['udyam_cert']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/udyam_cert'), $imageName);               
            // Update the company record with the new image name
            $doc_urls['udyam']['udyam_cert'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($doc_urls['udyam']['udyam_cert']);
        }
        if ($request->hasFile('aadhaar_front')) {
            // Get the new image file
            $newImage = $request->file('aadhaar_front');
            // Generate a new unique filename
            $imageName = 'aadhaar' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if (isset($company_docs['aadhaar']['aadhaar_front'])) {
                $oldImagePath = public_path('assets/images/companies/aadhaar/' . $company_docs['aadhaar']['aadhaar_front']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/aadhaar'), $imageName);               
            // Update the company record with the new image name
            $doc_urls['aadhaar']['aadhaar_front'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($doc_urls['aadhaar']['aadhaar_front']);
        }
        if ($request->hasFile('aadhaar_back')) {
            // Get the new image file
            $newImage = $request->file('aadhaar_back');
            // Generate a new unique filename
            $imageName = 'aadhaar' . $id . '.' . $newImage->getClientOriginalExtension();
             // If there was an old image, delete it
            if (isset($company_docs['aadhaar']['aadhaar_back'])) {
                $oldImagePath = public_path('assets/images/companies/aadhaar/' . $company_docs['aadhaar']['aadhaar_back']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }
            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/companies/aadhaar'), $imageName);               
            // Update the company record with the new image name
            $doc_urls['aadhaar']['aadhaar_back'] = $imageName;
        } else {
            // Keep the old image if a new one is not uploaded
            unset($doc_urls['aadhaar']['aadhaar_back']);
        }
        $company_details['company_type_id'] = $request->company_type_id;
        $company_details['shipment_weight'] = $request->shipment_weight;
        $company_details['channel_name'] = $request->channel_name;
        $company_details['courier_using'] = $request->courier_using;
        $company_details['product_category'] = $request->product_category;
        $company_details['monthly_orders'] = $request->monthly_orders;
        $company_details['company_email_id'] = $request->company_email_id??'';
        $company_details['phone_number'] = $request->phone_number??'';
        $company_details['legal_registered_name'] = $request->legal_registered_name??'';
        $company_details['pan_number'] = $request->pan_number??'';
        $company_details['brand_name'] = $request->brand_name??'';
        $company_details['website_url'] = $request->website_url??'';
        $company_details['address'] = $request->address??'';
        $company_details['pincode'] = $request->pincode??'';
        $company_details['city'] = $request->city??'';
        $company_details['country_code'] = $request->country_code??'';
        $company_details['state_code'] = $request->state_code??'';
        $company_details['country_code'] = $request->country_code??'';
       
        $aadhaar_number = $request->aadhaar_number??'';
        $company_details['doc_type']='';
        if($aadhaar_number){
            $company_details['doc_type'] = 'Aadhaar';
            $doc_urls['aadhaar']['aadhaar_number'] = $aadhaar_number;
        }
        $udyam_no = $request->udyam_no??'';
        if($udyam_no){
            $company_details['doc_type'] = 'Udyam';
            $doc_urls['udyam']['udyam_no'] = $udyam_no;
        }
        
        $gstin_no = $request->gstin_no??'';
        if($gstin_no){
            $company_details['doc_type'] = 'Gstin';
            $company_details['doc_number'] = $gstin_no;
            $doc_urls['gstin']['gstin_no'] = $gstin_no;
        }
        
        $company_details['doc_urls'] = $doc_urls;
        // -------------------------
        // BANK DETAILS ASSIGNMENT
        // -------------------------
    
        $bank_details['bank_name'] = $request->bank_details['bank_name'];
        $bank_details['account_number'] = $request->bank_details['account_number'];
        $bank_details['account_holder_name'] = $request->bank_details['account_holder_name'];
        $bank_details['ifsc_code'] = $request->bank_details['ifsc_code'];
        $company_details['bank_details'] = $bank_details;
        $errors=[];
        if(isset($company_details['brand_logo']) && empty($company_details['brand_logo'])){
            $errors[] = 'Signature is required';
           
        } 
        if(isset($company_details['pan_image']) && empty($company_details['pan_image'])){
            $errors[] = 'Pan image is required';
           
        } 
        if(isset($company_details['bank_details']['cancelled_check']) && empty($company_details['bank_details']['cancelled_check'])){
            $errors[] = 'cancelled check is required';           
        }  
        if($company_type=='Company'){
            if(!isset($doc_urls['gstin']['gstin_no']) || empty($doc_urls['gstin']['gstin_no'])){
                $errors[] = 'gstin_no is required';           
            } 
            if(!isset($doc_urls['gstin']['gstin_cert']) || empty($doc_urls['gstin']['gstin_cert'])){
                $errors[] = 'gstin certificate is required';           
            } 

        }elseif($company_type=='Individual'){
            if(!isset($doc_urls['aadhaar']['aadhaar_number']) || empty($doc_urls['aadhaar']['aadhaar_number'])){
                $errors[] = 'aadhaar is required';           
            } 
            if(!isset($doc_urls['aadhaar']['aadhaar_front']) || empty($doc_urls['aadhaar']['aadhaar_front'])){
                $errors[] = 'aadhaar_front doc is required';           
            }
            if(!isset($doc_urls['aadhaar']['aadhaar_back']) || empty($doc_urls['aadhaar']['aadhaar_back'])){
                $errors[] = 'aadhaar_back doc is required';           
            }
        }elseif($company_type=='Sole Proprietor'){
            $certificate=0;
            if (isset($doc_urls['udyam'])) {
                $hasNumber = isset($doc_urls['udyam']['udyam_no']) && !empty($doc_urls['udyam']['udyam_no']);
                $hasCert   = isset($doc_urls['udyam']['udyam_cert']) && !empty($doc_urls['udyam']['udyam_cert']);

                if ($hasNumber && $hasCert) {
                    $certificate = 1;
                }
            }
            if (isset($doc_urls['gstin'])) {
                $hasNumber = isset($doc_urls['gstin']['gstin_no']) && !empty($doc_urls['gstin']['gstin_no']);
                $hasCert   =isset($doc_urls['gstin']['udyam_cert']) && !empty($doc_urls['gstin']['gstin_cert']);

                if ($hasNumber && $hasCert) {
                    $certificate = 1;
                }
            }
           
            if($certificate==0){
                $errors[] = 'gstin no and gstin certificate or udyam no and udyam certificate at least one is required';           
            } 

        }
        if(!empty($errors)){
            return redirect()->route('profile')
                ->withErrors(implode('</br>',$errors))
                ->withInput();
        }
        $company->update($company_details);

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function getCompany(Request $request){
        //used in wordpress pluguin
        $company_id=$request->company_id??0;
        $website_url=$request->site_url??null;
        if(empty($website_url) && empty($website_url)){
            return response()->json([
                "success" => false,
                "errors" => ['company_id'=>'company id is required']
            ]);
        }
        $search=array();
        if($company_id){
            $search['id'] = $company_id;
        }
        if($website_url){
            $search['website_url'] = $website_url;
        }
        $company = Company::where($search)->first();
        
        return response()->json([
            "success" => true,
            "result" => [
                'company_id'=>$company->id
            ],
            "message" => "Company found successfully",
        ]);
        
    }

}
<?php

namespace App\Http\Controllers\Seller\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageTrackingPage;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display the create settings page.
     */
    public function create()
    {
        
        $companyId = session('company_id');
        if (!$companyId) {
            return redirect()->route('dashboard')->with('error', 'Company ID not found.');
        }

        $manageTrackingPage = ManageTrackingPage::where('company_id', $companyId)->first();
        $jsonData = $manageTrackingPage ? json_decode($manageTrackingPage->json_data, true) : [];

        return view('seller.tracking.settings.create', compact('manageTrackingPage', 'jsonData'));
    }

    /**
     * Store or update tracking settings.
     */
    public function store(Request $request)
    {
        $companyId = session('company_id');
        if (!$companyId) {
            return redirect()->route('tracking_create')->with('error', 'Company ID not found.');
        }

        // Fetch existing data for the company
        $manageTrackingPage = ManageTrackingPage::where('company_id', $companyId)->first();
        $jsonData = $manageTrackingPage ? json_decode($manageTrackingPage->json_data, true) : [];

        // Validate the request data
        $validated = $request->validate([
            'website_url' => 'nullable|url',
            'website_domain' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($companyId) {
                    $existingPage = ManageTrackingPage::where('website_domain', $value)
                        ->where('company_id', '!=', $companyId) // Check if the domain is used by another company
                        ->first();

                    if ($existingPage) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'heading_title' => 'nullable|string|max:255',
            'heading_sub_title' => 'nullable|string|max:255',
            'tracking_type' => 'required|array',
            'support_email_address' => 'nullable|email',
            'support_contact_number' => 'nullable|string|max:15',
            'youtube_video' => 'nullable|url',
            'custom_style_script' => 'nullable|string',
            'promotional_banner' => 'nullable|image|max:1024',
            'website_logo' => 'nullable|image|max:1024',
            'theme_color' => 'nullable|string',
            'announcement' => 'nullable|string',
            'announcement_url' => 'nullable|string',
            'promotional_url' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        // Handle file upload for website_logo
        if ($request->hasFile('website_logo')) {
            $newImage = $request->file('website_logo');
            $imageName = 'website_logo_' . $companyId . '.' . $newImage->getClientOriginalExtension();

            // Delete old image if exists
            if (!empty($jsonData['website_logo'])) {
                $oldImagePath = public_path('assets/images/tracking/logos/' . $jsonData['website_logo']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/tracking/logos'), $imageName);
            $validated['website_logo'] = $imageName;
        } else {
            // Retain old logo if no new logo is uploaded
            if (isset($jsonData['website_logo']) && !empty($jsonData['website_logo'])) {
                $validated['website_logo'] = $jsonData['website_logo'];
            } else {
                return redirect()->route('tracking_create')->with('error', 'Website logo is required.');
            }
        }

        // Handle file upload for promotional_banner
        if ($request->hasFile('promotional_banner')) {
            $newImage = $request->file('promotional_banner');
            $imageName = 'promotional_banner_' . $companyId . '.' . $newImage->getClientOriginalExtension();

            // Delete old image if exists
            if (!empty($jsonData['promotional_banner'])) {
                $oldImagePath = public_path('assets/images/tracking/banner/' . $jsonData['promotional_banner']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Move the new image to the desired location
            $newImage->move(public_path('assets/images/tracking/banner'), $imageName);
            $validated['promotional_banner'] = $imageName;
        } else {
            // Retain old promotional banner if no new one is uploaded
            if (isset($jsonData['promotional_banner'])) {
                $validated['promotional_banner'] = $jsonData['promotional_banner'];
            }
        }

        $validated['company_id'] = $companyId;

        // Merge validated data into JSON data
        $validated['json_data'] = json_encode(array_merge($jsonData, $validated));

        // Update or create tracking page data
        try {
            ManageTrackingPage::updateOrCreate(
                ['company_id' => $companyId],
                $validated
            );

            return redirect()->route('tracking_create')->with('success', 'Tracking page details saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
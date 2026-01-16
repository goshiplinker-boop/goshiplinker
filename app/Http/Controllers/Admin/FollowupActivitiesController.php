<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadActivity;
class FollowupActivitiesController extends Controller
{
    public function show(Request $request, $company_id)
    {
        // Eager load the 'leadStatus' relationship and filter by company_id
        $remarks = LeadActivity::with("leadStatus") // Eager load the leadStatus relationship
            ->where("company_id", $company_id) // Filter records by company_id
            ->orderBy("created_at", "desc") // Order by the latest created_at
            ->paginate(default_pagination_limit()); // Apply pagination

        return view(
            "admin.followup_activities.show",
            compact("remarks", "company_id")
        );
    }
}

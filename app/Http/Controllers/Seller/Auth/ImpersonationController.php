<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    public function impersonate(Request $request, string $token)
    {
        // pull => get and delete so it's one-time
        $data = Cache::pull('impersonate_vendor_' . $token);

        if (!$data) {
            abort(403, 'Invalid or expired impersonation link.');
        }

        $vendor = User::find($data['vendor_id']);
        if (!$vendor) {
            abort(404, 'Vendor not found.');
        }

        // At this point URL is /seller/impersonate/{token} -> SetGuardSession will set pm_seller_session
        Auth::guard('web')->login($vendor);
        $request->session()->regenerate();

        // put seller session values
        Session::put('role_id', $vendor->role_id);
        Session::put('company_id', $vendor->company_id);

        $company = $vendor->company;
        Session::put('subscription_plan', $company?->subscription_plan ? strtolower($company->subscription_plan) : null);
        Session::put('subscription_status', $company->subscription_status ?? null);
        Session::put("parent_company_id", $company->parent_id??null);
        // token for API calls if you need it
        $tokenApi = $vendor->createToken("parcelmind")->plainTextToken;
        Session::put("token", $tokenApi);

        // track who impersonated (optional)
        Session::put('impersonated_by_admin_id', $data['admin_id']);

        return redirect()->route('dashboard'); // seller dashboard
    }
}

<?php

namespace App\Http\Controllers\Admin\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {         
        if (Auth::guard('admin')->check()) {            
            return redirect()->route("vendors_list",['tab'=>'fresh_lead']);
        } else {
            return view("admin.auth.login");
        }
    }
    public function login(Request $request)
    {
        
        try {
            $request->validate([
                "email" => "required|email",
                "password" => "required",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
           
            return redirect()
                    ->route("adminForm")
                    ->withErrors($e->validator->errors());
           
        }

        $user = User::where([
            ["email", "=", $request->email],
            ["role_id", "=", 1],
        ])->first();
        
        if (!$user) {            
            return redirect()
                    ->route("adminForm")
                    ->withErrors(["Invalid username or password"]);
            
        }
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials)) {   
            $subscription_plan = auth()->guard('admin')->user()->company->subscription_plan? strtolower(auth()->guard('admin')->user()->company->subscription_status): null;
            $subscription_status = auth()->guard('admin')->user()->company->subscription_status ?? null;  
            $parent_company_id = auth()->guard('admin')->user()->company->parent_id ?? null;  
            session()->regenerate();    
            Session::put("role_id", $user->role_id);
            Session::put("company_id", $user->company_id);
            Session::put("parent_company_id", $parent_company_id);
            Session::put("subscription_plan", $subscription_plan);
            Session::put("subscription_status", $subscription_status);

            $token = $user->createToken("parcelmind")->plainTextToken;
            Session::put("token", $token);      
            return redirect()->route("vendors_list",['tab'=>'fresh_lead']);
        }
        return redirect()
                ->route("adminForm")
                ->withErrors(["Invalid username or password"]);
        
    }
    public function loginAsVendor(int $user_id)
    {
        $vendor = User::find($user_id);

        if (!$vendor) {
            return redirect()
                ->route("vendors_list", ['tab' => 'fresh_lead'])
                ->withErrors(["Invalid user id"]);
        }

        // create one-time token, valid short time
        $token = Str::random(64);

        Cache::put('impersonate_vendor_' . $token, [
            'vendor_id' => $vendor->id,
            'admin_id'  => auth('admin')->id(),
        ], now()->addMinutes(5)); // 5 minute TTL

        // redirect to seller-side URL that will actually perform the login
        return redirect()->route('seller.impersonate', ['token' => $token]);
    }


    public function signUp(Request $request)
    {
        $path = $request->path();
        // Validate incoming request
        try {
            $request->validate([
                "name" => "required",
                "email" => "required|email|unique:users,email",
                "password" => "required|min:8",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            
            return redirect()
                ->route("registerForm")
                ->withErrors($e->validator->errors())
                ->withInput();
        }

        $input = $request->all();        
        $input["password"] = bcrypt($input["password"]);
        // Create the user
        $user = User::create($input);
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials)) {
            $subscription_plan = auth()->guard('admin')->user()->company->subscription_plan? strtolower(auth()->guard('admin')->user()->company->subscription_status): null;
            $subscription_status = auth()->guard('admin')->user()->company->subscription_status ?? null; 
            $parent_company_id = auth()->guard('admin')->user()->company->parent_id ?? null;   
            session()->regenerate();    
            Session::put("role_id", $user->role_id);
            Session::put("company_id", $user->company_id);
            Session::put("parent_company_id", $parent_company_id);
            Session::put("subscription_plan", $subscription_plan);
            Session::put("subscription_status", $subscription_status);

            $token = $user->createToken("parcelmind")->plainTextToken;
            Session::put("token", $token);      
            return redirect()->route("vendors_list",['tab'=>'fresh_lead']);
        }
        
        return redirect()
        ->route("registerForm")
        ->withErrors(["Invalid username or password"])
        ->withInput();
    }

    public function logout()
    {
        // Check if the user is authenticated
        if (Auth::guard('admin')->check()) {
            auth()->guard('admin')->logout();
                 session()->invalidate();
                session()->regenerateToken();
                return redirect()
                    ->route("adminForm")
                    ->with("message", "Successfully logged out.");
           
        }
        return redirect()
            ->route("adminForm")
            ->withErrors(["No user is logged in."]);
    }
    
    public function getUsers(Request $request)
    {
        $company_id = session('company_id');
        $tab = $request->get('tab') ?? 'freshLeadsCount';
        $lead_status_id = $request->get('lead_status_ids') ?? '';
        $subscription_plan = $request->get('subscription_plan') ?? '';
        $subscription_status = $request->get('subscription_status') ?? '';
        $company_name = $request->get('company_name') ?? '';
        if(isset($request->startDate)){
            $filters['startDate'] = $request->startDate ?? null;
        }else{
            $fromDate = Carbon::now()->subDays(30)->format('Y-m-d H:i:s');
            $filters['startDate'] =$fromDate;
        }
        if(isset($request->endDate)){
            $filters['endDate'] = isset($request->endDate) ? $request->endDate . " 23:59:59" : null;
        }
        $lead_status_ids = [];
        $filters = $request->all();
        $leadStatuses = LeadStatus::where('status', 1)->get();
        $leadStatusesGrouped = $leadStatuses
            ->groupBy('status_mapping')        // group by mapping type
            ->map(fn($group) => $group->pluck('id')->values()); 
       
        $lead_status_ids = $leadStatusesGrouped[$tab]??[];

        $today = Carbon::today();
        $vendors = Company::with([
            'user',
            'leadStatus',
            'leadActivities' => function ($query) {
                $query->latest()->limit(1); 
            }
        ])
        ->whereHas('user', fn($query) => $query->where('role_id', 2));
        
        $vendors->whereNotNull('companies.parent_id');
        $vendors->where('companies.parent_id', $company_id);
        if (!empty($lead_status_id)) {
            $vendors->where('lead_status_id', $lead_status_id);
        }else{
            if(!empty($lead_status_ids)) {
                $vendors->whereIn('lead_status_id', $lead_status_ids);
            }
        }
        if (!empty($subscription_plan)) {
            $vendors->where('subscription_plan', $subscription_plan);
        }
        if ($subscription_status !='') {
            $vendors->where('subscription_status', $subscription_status);
        }
        if (!empty($company_name)) {
            $vendors->where('legal_registered_name', 'LIKE', "%{$company_name}%");
        }
        if (!empty($filters['startDate'])) {
            $vendors->where("created_at", ">=", $filters['startDate']);
        }
        if (!empty($filters['endDate'])) {
            $vendors->where("created_at", "<=", $filters['endDate'] . " 23:59:59");
        }   

        if ($tab === 'todaysFollowUpCount') {
            $vendors->whereHas('leadActivities', function ($query) use ($today) {
                $query->whereNotNull('followup_date')
                    ->where('is_followup_completed', 0)
                    ->whereDate('followup_date', $today)
                    ->whereIn('id', function ($subquery) {
                    $subquery->selectRaw('MAX(id)')
                            ->from('lead_activities')
                            ->groupBy('company_id');
                    });
            });
        }

        if ($tab === 'overdueFollowUpCount') {
            $vendors->whereHas('leadActivities', function ($query) use ($today) {
                $query->whereNotNull('followup_date')
                    ->where('is_followup_completed', 0)
                    ->whereDate('followup_date', '<', $today)
                    ->whereIn('id', function ($subquery) {
                    $subquery->selectRaw('MAX(id)')
                            ->from('lead_activities')
                            ->groupBy('company_id');
                    });
            });
        }
        $vendors = $vendors->orderByDesc('created_at')->paginate(default_pagination_limit());
       
        $statusCounts = DB::table('companies')
            ->join('lead_statuses', 'companies.lead_status_id', '=', 'lead_statuses.id')
            ->join('users', 'companies.id', '=', 'users.company_id')
            ->where('users.role_id', 2)
            ->whereNotNull('companies.parent_id')
            ->where('companies.parent_id', $company_id)
            ->select('lead_statuses.status_mapping', DB::raw('COUNT(companies.id) as total'))
            ->groupBy('lead_statuses.status_mapping')
            ->pluck('total', 'lead_statuses.status_mapping')
            ->toArray();

        // 🟢 Today follow-ups count
        $todayFollowupsCount = Company::whereHas('leadActivities', function ($query) {
            $query->where('is_followup_completed', 0)
                ->whereNotNull('followup_date')
                ->whereDate('followup_date', Carbon::today())
                ->whereIn('id', function ($subquery) {
                    $subquery->selectRaw('MAX(id)')
                            ->from('lead_activities')
                            ->groupBy('company_id');
                });
        })->count();

        // 🟢 Overdue follow-ups count
        $overdueFollowupsCount = Company::whereHas('leadActivities', function ($query) {
            $query->where('is_followup_completed', 0)
                ->whereNotNull('followup_date')
                ->whereDate('followup_date', '<', Carbon::today())
                ->whereIn('id', function ($subquery) {
                    $subquery->selectRaw('MAX(id)')
                            ->from('lead_activities')
                            ->groupBy('company_id');
                });
        })->count();
        // 🟢 Prepare final counts
        $leadCounts = (object)[
            'fresh_lead' => $statusCounts['fresh_lead'] ?? 0,
            'qualified' => $statusCounts['qualified'] ?? 0,
            'unqualified' => $statusCounts['unqualified'] ?? 0,
            'lost' => $statusCounts['lost'] ?? 0,
            'todaysFollowUpCount' => $todayFollowupsCount,
            'overdueFollowUpCount' => $overdueFollowupsCount,
            'allLeadsCount' => array_sum($statusCounts),
        ];
        $plans = Plan::where('status',1)->get();
        return view("admin.users_list", compact("vendors","leadStatuses","leadCounts","tab","plans","filters"));
    }    

    public function storeLeadActivity(Request $request)
    {
        $user_id = optional(auth()->guard('admin')->user())->id;        
        $company_id = $request->company_id;

        // Validate incoming data
        $validated = $request->validate([
            "status" => "nullable|exists:lead_statuses,id",
            "remarks" => "nullable|string",
            "is_followup_completed" => "required|boolean",
        ]);

        // Store the lead activity
        $leadActivity = new LeadActivity();
        $leadActivity->user_id = $user_id;
        $leadActivity->company_id = $company_id;
        $leadActivity->lead_status_id = $request->status;
        $leadActivity->remarks = $request->remarks;
        $leadActivity->followup_date = $request->followup_date;
        $leadActivity->is_followup_completed = $request->is_followup_completed;
        $leadActivity->save();

        // Update the company's lead_status_id based on the new lead activity
        Company::where("id", $company_id)->update([
            "lead_status_id" => $request->status,
        ]);

        // Redirect with a success message
        return redirect()
            ->route("vendors_list",['tab'=>'fresh_lead'])
            ->with(
                "success",
                "Lead Activity stored successfully and company lead status updated."
            );
    }

    public function showUsersList()
    {
        $sessionId = session()->getId(); // Retrieve the session ID
        return view("admin.users_list", compact("sessionId")); // Pass it to the view
    }
}
<?php

namespace App\Http\Controllers\Seller\Auth;
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
use App\Models\Order;
use App\Models\ChannelSetting;
use App\Models\Channel;
use App\Models\NotificationTemplate;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SellerAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {            
            return redirect()->route("dashboard");
        } else {
            $role_type = "vendor";
            return view("seller.auth.login", ["role_type" => $role_type]);
        }
    }
    public function login(Request $request)
    {
        $path = $request->path();
        
        try {
            $request->validate([
                "email" => "required|email",
                "password" => "required",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($path == "api/login") {
                return response()->json(
                    [
                        "success" => false,
                        "errors" => $e->validator->errors(),
                    ],
                    422
                ); // Return 422 Unprocessable Entity
            } else {
                return redirect()
                    ->route("loginForm")
                    ->withErrors($e->validator->errors());
            }
        }

        $user = User::where([
            ["email", "=", $request->email],
            ["role_id", "=", 2],
        ])->first();

        if (!$user) {
            if ($path == "api/login") {
                return response()->json(
                    [
                        "success" => false,
                        "result" => "Invalid username or password",
                    ],
                    401
                );
            }  else {
                return redirect()
                    ->route("loginForm")
                    ->withErrors(["Invalid username or password"]);
            }
        }
        $credentials = $request->only('email', 'password');
        if (Auth::guard('web')->attempt($credentials)) {
            $subscription_plan = auth()->guard('web')->user()->company->subscription_plan? strtolower(auth()->guard('web')->user()->company->subscription_status): null;
            $subscription_status = auth()->guard('web')->user()->company->subscription_status ?? null;
            $parent_company_id = auth()->guard('web')->user()->company->parent_id ?? null;
            session()->regenerate();
            Session::put("role_id", $user->role_id);
            Session::put("company_id", $user->company_id);
            Session::put("parent_company_id", $parent_company_id);
            Session::put("subscription_plan", $subscription_plan);
            Session::put("subscription_status", $subscription_status);

            $token = $user->createToken("parcelmind")->plainTextToken;
            Session::put("token", $token);
            $success = [
                "token" => $token,
                "name" => $user->name,
                "company_id" => $user->company_id,
            ];

            if ($path == "api/login") {
                return response()->json([
                    "success" => true,
                    "result" => $success,
                    "message" => "User logged in successfully",
                ]);
            }
            $company = Company::Where("id", $user->company_id)->first();
            if (is_null($company->state_code)) {
                return redirect()->route("profile", $user->company_id);
            }
            return redirect()->route("dashboard");
        }

        return redirect()
        ->route("loginForm")
        ->withErrors(["Invalid username or password"]);
        
    }

    public function signUp(Request $request)
    {
        $path = $request->path();
        // Validate incoming request
        $utm_source=$request->utm_source??'';
        $website_url=$request->utm_content??null;
        $utm_data = session('utm_data') ?? [];
        if(!empty($utm_data) && empty($utm_source)){
            $utm_source=$utm_data['utm_source']??'';
            $website_url=$utm_data['utm_content']??null;
        }
        try {
            $rules = [
                "name" => "required",
                "email" => "required|email|unique:users,email",
                "country_code" =>"required",
                "password" => "required|min:8",
                "phone_number" => "required",
                "monthly_orders" => "required",
                // Add other validation rules as necessary
            ];
            if ($request->country_code === 'IN') {
                $rules['phone_number'] .= '|min:10|max:10';
            }
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($path == "api/signup") {
                return response()->json(
                    [
                        "success" => false,
                        "errors" => $e->validator->errors(),
                    ],
                    422
                ); // Return 422 Unprocessable Entity
            }
            return redirect()
                ->route("registerForm",$request->query())
                ->withErrors($e->validator->errors())
                ->withInput();
        }

        
        try {
            $input = $companydata = $request->all();
            $companydata["legal_registered_name"] = "";
            $input["password"] = bcrypt($input["password"]);
            $input["role_id"] = 2;
            $companydata["parent_id"] = 1;
            $companydata["company_email_id"] = $input["email"];
            $companydata["website_url"] = !empty($utm_source) ? $website_url : null;
            $utm_data = !(empty($utm_data))?json_encode($utm_data):null;
            $companydata["utm_data"] = !empty($request->query())?json_encode($request->query()):$utm_data;
            // Create the company
            $company = Company::create($companydata);
            $input["company_id"] = $company->id;
            // Create the user
            $user = User::create($input);
            if($user){
                $channel = Channel::create([
                    "company_id" => $company->id,
                    "parent_id" => "1", // Assuming the parent ID is static
                    "name" => "Custom",
                    "status" => 1,
                    "image_url" => "images/channels/custom.png", // Set default image
                ]);
                // Prepare post data
                $post_data = [];
                $post_data["company_id"] = $company->id;
                $post_data["channel_id"] = $channel->id; // Use the channel ID that was just created
                $post_data["channel_code"] = "custom";
                $post_data["channel_title"] = "Custom";
                $post_data["brand_name"] = $user->name;
                $post_data["status"] = 1;
                // Create the ChannelSetting entry
                ChannelSetting::create($post_data);
                if ($path == "api/signup") {
                    $success["token"] = $user->createToken("parcelmind")->plainTextToken;
                    $success["name"] = $user->name;
                    $success["company_id"] = $company->id;
                    return response()->json([
                        "success" => true,
                        "result" => $success,
                        "message" => "User registered successfully",
                    ]);
                }
                $templates = NotificationTemplate::where('channel', 'email')
                ->where('status', '1')
                ->where('event_type', 'New Registration')
                ->where('user_type', 'admin')
                ->whereNull('company_id')
                ->get();
                session()->forget('utm_data');
                foreach ($templates as $notification) {
                    $subject = $notification->meta['subject'] ?? 'New User Registrations';
                    $body = $notification->body;
                    if($notification->user_type=='admin'){
                        $email_to = env('NOTIFICATION_EMAIL_ID');
                        $body = str_replace(
                            ['{user_name}', '{user_email}','{user_phone}'],
                            [$user->name, $company->company_email_id ?? '',$company->phone_number ?? ''],
                            $body
                        );
                    }else{
                        $email_to = $company->company_email_id;
                        $customerName = $user->name??'';
                        $body = str_replace('{customer_name}', $customerName, $body);
                    }                   
                    try {
                        Mail::to($email_to)->send(new NotificationMail($body, $subject));  
        
                    } catch (\Exception $e) {
                         Log::error("Failed to send email to user {$email_to} or admin: " . $e->getMessage());
                    }
                }
                return redirect()->route("welcome_user");
            }  
        } catch (\Exception $e) { 
            if ($path == "api/signup") {
                return response()->json(
                    [
                        "success" => false,
                        "errors" => $e->getMessage(),
                    ],
                    422
                ); 
            }
            return redirect()
            ->route("registerForm",$request->query())
            ->withErrors($e->getMessage())
            ->withInput();
        }

        
    }

    public function logout()
    {
        // Check if the user is authenticated
        if (Auth::guard('web')->check()) {
            $companyId = session("company_id");
            auth()->guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()
                ->route("loginForm")
                ->with("message", "Successfully logged out.");
           
        }
        return redirect()
            ->route("loginForm")
            ->withErrors(["No user is logged in."]);
        
    }

   
    public function welcome()
    {
        return view("website.thankyou");
    }
    public function refreshToken(Request $request)
    {
        $url = $request->site_url??'';
        $email = $request->email??'';

        if (!$url) {
            return response()->json([
                "success" => false,
                "message" => "Site URL is required"
            ], 400);
        }
        
        $company = Company::where("company_email_id", $email)
                        ->orWhere("website_url", $url)
                        ->first();

        if (!$company) {
            return response()->json([
                "success" => false,
                "message" => "Company not found"
            ], 404);
        }

        $user = User::where('company_id', $company->id)->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "User not found"
            ], 404);
        }

        // Manually logging in the user
        Auth::login($user);

        $token = $user->createToken("parcelmind")->plainTextToken;

        return response()->json([
            "success" => true,
            "result" => [
                "token" => $token,
                "name" => $user->name,
                "email" => $user->email,
                "company_id" => $user->company_id
            ],
            "message" => "User verify successfully"
        ]);
    }
}
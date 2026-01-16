<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddCourierController extends Controller
{
    public function create()
    {
        return view('seller.couriers.settings.aramex.create');
    }
    public function store(Request $request){
        return redirect()->route(panelPrefix().'.couriers_list')->with('success', 'No courier integration found. Please contact the admin at parcelmind@gmail.com for assistance.');
    }
}

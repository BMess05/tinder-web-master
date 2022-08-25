<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Advertisement;

class DashboardController extends Controller
{
    public function index() {
        $advertisements_count = Advertisement::where('company_id', auth('companies')->user()->id)->count();
        return view('companies.dashboard', compact('advertisements_count'));
    }
}

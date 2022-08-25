<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company;
use App\Events\CompanyRegistered;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function listCompanies() {
        $companies = Company::orderBy('id', 'DESC')->get();
        return view('admin.companies.list', compact(['companies']));
    }

    public function add() { 
        return view('admin.companies.add');
    }

    public function save(Request $request) {
        $validatedData = $request->validate([
            'company_name' => 'string|nullable',
            'contact_name' => 'string|nullable',
            'email' => 'required|email|unique:companies,email,NULL,id'
        ]);
        
        $data = $request->all();
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }
        DB::beginTransaction();
        $company = new Company();
        $company->company_name = $data['company_name'];
        $company->contact_name = $data['contact_name'];
        $company->address = $data['address'];
        $company->email = $data['email'];
        if($company->save()) {
            event(new CompanyRegistered($company));
            DB::commit();
            return redirect('companies')->with(['status' => 'success', 'message' => 'Company Saved Successfully.']);
        }
        DB::rollback();
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
    }

    public function edit($id) { 
        $company = Company::find($id);
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
        return view('admin.companies.edit', compact('company'));
    }

    public function update($id, Request $request) {
        $company = Company::find($id);
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
        $validatedData = $request->validate([
            'company_name' => 'string|nullable',
            'contact_name' => 'string|nullable',
            'email' => 'required|email|unique:companies,email,'.$id
        ]);

        $data = $request->all();
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }

        DB::beginTransaction();
        $company->company_name = $data['company_name'];
        $company->contact_name = $data['contact_name'];
        $company->address = $data['address'];
        $email_changes = 0;
        if($company->email != $data['email']) {
            $company->email = $data['email'];
            $email_changes = 1;
            $company->is_verified = 0;
        }
        
        if($company->save()) {
            if($email_changes == 1) {
                event(new CompanyRegistered($company));
            }
            DB::commit();
            return redirect('companies')->with(['status' => 'success', 'message' => 'Company Updated Successfully.']);
        }
        DB::rollback();
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();

    }

    public function delete($id) {
        $company = Company::find($id);
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
        if($company->delete()) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Company successfully deleted.']);
        }
        return view('admin.companies.view', compact('company'));
    }


}

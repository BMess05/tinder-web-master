<?php

namespace App\Exports;

use App\Model\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $filter;

    function __construct($filter) {
        $this->filter = $filter;
    }
    public function headings(): array {
        return [
            "Name",
            "Email",
            "Birthdate",
            "GENDER",
            "Interested In",
            "Verfied",
            "Premium",
            // "Blocked"
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
    */
    public function collection() {
        if($this->filter == "online") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1,'is_blocked' => 0, 'is_online' => 1])->orderBy('id', 'DESC')->get();
        }   elseif($this->filter == "blocked") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1, 'is_blocked' => 1])->orderBy('id', 'DESC')->get();
        }    elseif($this->filter == "premium") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1, 'is_premium' => 1])->orderBy('id', 'DESC')->get();
        }   elseif($this->filter == "males") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1,'is_blocked' => 0, 'gender' => 1])->orderBy('id', 'DESC')->get();   
        }   elseif($this->filter == "females") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1,'is_blocked' => 0, 'gender' => 2])->orderBy('id', 'DESC')->get();
        }   elseif($this->filter == "others") {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->
            where(['type' => 1, 'is_blocked' => 0])
            ->where(function($q) {
                $q->where('gender', 3)
                  ->orWhere('gender', null);
            })
            ->orderBy('id', 'DESC')->get();
        }  else {
            $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->where('type', 1)->orderBy('id', 'DESC')->get();
        } 

        // $users = User::select('name', 'email','dob','gender', 'interested_in', 'is_verified', 'is_premium')->where(['type' => 1])->orderBy('id', 'DESC')->get();
        foreach($users as $k => $user) {
            if($user->gender == 1) {
                $users[$k]->gender = "Male";
            }   elseif($user->gender == 2) {
                $users[$k]->gender = "Female";
            }   elseif($user->gender == 3) {
                $users[$k]->gender = "Other";
            }   else {
                $users[$k]->gender = "Not Mentioned";
            }

            if($user->interested_in == 1) {
                $users[$k]->interested_in = "Male";
            }   elseif($user->interested_in == 2) {
                $users[$k]->interested_in = "Female";
            }   elseif($user->interested_in == 3) {
                $users[$k]->interested_in = "Other";
            }   else {
                $users[$k]->interested_in = "Not Mentioned";
            }

            if($user->is_verified == 1) {
                $users[$k]->is_verified = "Yes";
            }   else {
                $users[$k]->is_verified = "No";
            }

            if($user->is_premium == 1) {
                $users[$k]->is_premium = "Yes";
            }   else {
                $users[$k]->is_premium = "No";
            }

            // if($user->is_blocked == 1) {
            //     $users[$k]->is_blocked = "Yes";
            // }   else {
            //     $users[$k]->is_blocked = "No";
            // }
            
        }
        return collect($users);
    }
}

<?php

namespace App\Listeners;

use App\Events\CompanyForgetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CompanySendPasswordResetLink
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CompanyForgetPassword  $event
     * @return void
     */
    public function handle(CompanyForgetPassword $event)
    {
        $details = [
            'email' => $event->company->email,
            'password_reset_token' => $event->company->password_reset_token
        ];
        \Mail::to($event->company->email)->send(new \App\Mail\CompanyResetPasswordLink($details));
    }
}

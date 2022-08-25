<?php

namespace App\Listeners;

use App\Events\CompanyRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
class CompanySendPassword
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
     * @param  CompanyRegistered  $event
     * @return void
     */
    public function handle(CompanyRegistered $event)
    {
        $details = [
            'email' => $event->company->email,
            'company_name' => $event->company->company_name,
            'contact_name' => $event->company->contact_name,
            'address' => $event->company->address
        ];
        \Mail::to($event->company->email)->send(new \App\Mail\CompanySendPasswordLink($details));
    }
}

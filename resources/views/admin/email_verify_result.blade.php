@extends('layouts.auth_layout')

@section('content')
<!-- Header -->
<div class="header bg-pink-grediant py-lg-5 pt-lg-6 email_verify_page">
    <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <h1 class="text-white">Welcome to TUNDUR!</h1>
              <p class="text-lead text-white">{{$data['message']}}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
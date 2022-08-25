@extends('layouts.auth_layout')

@section('content')

<style>
    
    img.logo_login{
        width: 40%;
    }
    .reset-btn{
        font-size: 12px;
    }

</style>

<!-- Header -->
<div class="header bg-pink-grediant py-lg-5 pt-lg-6">
    <div class="container">
        <div class="header-body text-center mb-7">
            <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
                <h1 class="text-white">Welcome to TUNDUR!</h1>
                <p class="text-lead text-white">Sign in and manage the app from admin panel</p>
            </div>
            </div>
        </div>
    </div>
    <!-- <div class="separator separator-bottom separator-skew zindex-100">
    <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
        <polygon class="fill-default" points="2560 0 2560 100 0 100"></polygon>
    </svg>
    </div> -->
</div>
    <!-- Page content -->
    <div class="container mt--8 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card bg-secondary border-0 mb-0">
                    {{-- <div class="card-header">{{ __('Reset Password') }}</div> --}}
                    <div class="card-body px-lg-5 py-lg-5">
                        <div class="text-center">
                            @if(session('status'))
                                <div class="alert alert-{{ Session::get('status') }}" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    {{ Session::get('message') }}
                                </div>
                            @endif
                            <a class="logo_wrap" href="javascript:void(0)">
                            <img src="{{asset('assets/img/logo.png')}}" class="logo_login" alt="...">
                            </a>
                        </div>
                        <div class="text-center text-muted mb-4">
                            <small>{{ __('Reset Password') }}</small>
                        </div>

                        <form method="POST" action="{{ route('companyPasswordResetMail') }}">
                            @csrf

                            <div class="form-group text-center row">
                                <label for="email" class="col-md-4 col-form-label">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-8">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="reset-btn btn btn-primary">
                                        {{ __('Send Password Reset Link') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 text-center">
                @if (Route::has('login'))
                    <a class="text-muted" id="login" href="{{ route('companyLogin') }}">
                    <small>{{ __('Login') }}</small>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

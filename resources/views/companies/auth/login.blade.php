@extends('layouts.auth_layout')

@section('content')

<!-- Header -->
<div class="header bg-pink-grediant py-lg-5 pt-lg-6">
    <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <h1 class="text-white">Welcome to Tündür!</h1>
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
        <div class="col-lg-5 col-md-7">
          <div class="card bg-secondary border-0 mb-0">
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
                <small>Sign in with credentials</small>
              </div>
              <form method="POST" action="{{ route('companySignIn') }}">
                @csrf
                <div class="form-group mb-3">
                  <div class="input-group input-group-merge input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-merge input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input id="password" type="password" class="password-field form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Password">
                    <div class="show-password">
                      <i class="fa fa-eye show"></i>
                      <i class="fa fa-eye-slash hide"></i>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>
                </div>
                <div class="custom-control custom-control-alternative custom-checkbox">
                    <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="custom-control-label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary my-4">Sign in</button>
                </div>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12 text-center">
                @if (Route::has('password.request'))
                    <a class="text-light" id="forgot-password" href="{{ route('companyForgetPassword') }}">
                    <small>{{ __('Forgot Your Password?') }}</small>
                    </a>
                @endif
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@extends('layouts.auth_layout')

@section('content')

<!-- Header -->
<div class="header bg-pink-grediant py-lg-5 pt-lg-6">
    <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <h1 class="text-white">Welcome to Tündür!</h1>
              <p class="text-lead text-white">Set password and manage the dasboard from panel</p>
            </div>
          </div>
        </div>
      </div>
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
              <form id="set-password-form" method="POST" action="{{ route('setCompanyPassword') }}">
                @csrf
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

                <div class="form-group">
                  <div class="input-group input-group-merge input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input id="password_confirmation" type="password" class="password-field-conf form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="current-password" placeholder="Confirm password">
                    <div class="show-password-conf">
                      <i class="fa fa-eye show"></i>
                      <i class="fa fa-eye-slash hide"></i>
                    </div>
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>
                </div>

                <input type="hidden" name="email" value="{{ $email }}">
                <div class="text-center">
                  <button type="submit" class="btn btn-primary my-4">Set Password</button>
                </div>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12 text-center">
                <a class="text-light" id="login" href="{{ route('companyLogin') }}">
                <small>{{ __('Log In') }}</small>
                </a>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

@endsection
@section('scripts')

<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\PasswordSetRequest', '#set-password-form'); !!}
@endsection
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
  <meta name="author" content="Creative Tim">
  <title>Contact Us - TUNDUR</title>
  <!-- Favicon -->
  <link rel="icon" href="{{asset('assets/img/brand/favicon.png')}}" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  
  
  <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">


  <link rel="stylesheet" href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" type="text/css">
  <link rel="stylesheet" href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" type="text/css">
  <!-- Page plugins -->
  <!-- Argon CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/argon.css?v=1.2.0')}}" type="text/css">
  <link rel="stylesheet" href="{{asset('assets/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" type="text/css">
  <style>
    a.navbar-logo-center {
        margin: 3px auto;
    }
    img.navbar-center-img {
        height: 36px;
    }
  </style>
</head>

<body>
<!-- Main content -->
<div class="main-content" id="panel">
    <nav class="navbar navbar-inverse bg-pink-grediant">
        <div class="container-fluid"> 
            <a class="navbar-logo-center" href="javascript:void(0)">
                <img src="{{asset('assets/img/logo_white.png')}}" class="navbar-center-img" alt="...">
            </a>
        </div>
    </nav> 
    <div class="container">
        <div class="row">
            <div class="col-md-3 d-sm-block d-xs-block">
            
            </div>
            <div class="col-md-6 col-xs-12">
                <h1 class="text-center mt-5 mb-5">@lang('lang.contact-form-name')</h1>

                <p>@lang('lang.contact-form-title')</p> 
                @if(session('status'))
                    <div class="alert alert-{{ Session::get('status') }}" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        {{ Session::get('message') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('send_mail') }}">
                @csrf
                    <div class="form-group">
                        <input type="text" class="form-control" name="title" placeholder="@lang('lang.title')" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="description" placeholder="@lang('lang.description')" minlength="30" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mt-3">@lang('lang.send')</button>
                    </div>
                    <input type="hidden" name="lang" value="{{$lang}}">
                    <input type="hidden" name="type" value="1">
                </form>

            </div>
            <div class="col-md-3 d-sm-block d-xs-block">
            
            </div>
        </div>
    </div>
</div>

  <!-- Argon Scripts -->
  <!-- Core -->
  <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('assets/vendor/js-cookie/js.cookie.js')}}"></script>
  <script src="{{asset('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js')}}"></script>
  <script src="{{asset('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js')}}"></script>
  <!-- Optional JS -->
  <script src="{{asset('assets/vendor/chart.js/dist/Chart.min.js')}}"></script>
  <script src="{{asset('assets/vendor/chart.js/dist/Chart.extension.js')}}"></script>
  <!-- Argon JS -->
  <script src="{{asset('assets/js/argon.js?v=1.2.0')}}"></script>

  <!-- Page level plugins -->
  <script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script>
    $(document).ready(function() {

    //   $(document).ready(function() {
    //       $('#dataTable').DataTable();
    //   });
      setTimeout(function() {
        $('.alert').remove();
      }, 3000);
    });
  </script>
  @yield('script')
</body>

</html>

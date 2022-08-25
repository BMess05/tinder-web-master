<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
  <meta name="author" content="Creative Tim">
  <title>Tündür</title>
  <!-- Favicon -->
  <link rel="icon" href="{{asset('assets/img/brand/favicon.png')}}" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" type="text/css">
  <link rel="stylesheet" href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" type="text/css">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/argon.css?v=1.2.0')}}" type="text/css">
  <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
</head>

<body class="">
  <!-- Main content -->
  <div class="main-content">
    @yield('content')
  <!-- Footer -->
    <footer class="py-5" id="footer-main">
      <div class="container">
        <div class="row align-items-center justify-content-xl-between">
          <div class="col-xl-12 text-center">
            <div class="copyright text-center text-xl-center text-muted">
              &copy; {{date('Y')}} <a href="{{url('/')}}" class="font-weight-bold ml-1 text-pink" target="_blank">Tündür</a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
  <!-- Argon Scripts -->
  <!-- Core -->
  <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('assets/vendor/js-cookie/js.cookie.js')}}"></script>
  <script src="{{asset('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js')}}"></script>
  <script src="{{asset('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js')}}"></script>
  <!-- Argon JS -->
  <script src="{{asset('assets/js/argon.js?v=1.2.0')}}"></script>
<script>
$('.show-password i').on('click', function(){
  var type = $('.password-field').attr('type');
  $(this).hide();
  if(type == "password"){
    $('.show-password i.hide').show();
    $('.password-field').attr('type', 'text');
  }else{
    $('.show-password i.show').show();
    $('.password-field').attr('type', 'password');
  }
});

$('.show-password-conf i').on('click', function(){
  var type = $('.password-field-conf').attr('type');
  $(this).hide();
  if(type == "password"){
    $('.show-password-conf i.hide').show();
    $('.password-field-conf').attr('type', 'text');
  }else{
    $('.show-password-conf i.show').show();
    $('.password-field-conf').attr('type', 'password');
  }
});

setTimeout(function() {
    $('.alert').remove();
  }, 3000);

</script>
@yield('scripts')
</body>

</html>
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
  
  
  <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">


  <link rel="stylesheet" href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" type="text/css">
  <link rel="stylesheet" href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" type="text/css">
  <!-- Page plugins -->
  <!-- Argon CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/argon.css?v=1.2.0')}}" type="text/css">
  <link rel="stylesheet" href="{{asset('assets/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" type="text/css">
  @yield('head')
</head>

<body>
  <!-- Sidenav -->
<nav class="bg-pink-grediant sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
        <!-- Brand -->
        <div class="sidenav-header  align-items-center">
            <a class="navbar-brand" href="javascript:void(0)">
            <img src="{{asset('assets/img/logo_white.png')}}" class="navbar-brand-img" alt="...">
            </a>
        </div>
        <div class="navbar-inner">
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Nav items -->
                <ul class="navbar-nav">
                  @if(auth()->guard('companies')->check())
                  <li class="nav-item">
                    <a class="nav-link {{ request()->segment(2) == '' ? 'active' : '' }}" href="{{route('companyDashboard')}}">
                      <i class="ni ni-tv-2 text-white"></i>
                      <span class="nav-link-text text-white">Dashboard</span>
                    </a>
                  </li>
                  @if(auth('companies')->user()->subscription == 1)
                  <li class="nav-item">
                    <a class="nav-link {{ request()->segment(2) == 'advertisements' ? 'active' : '' }}" href="{{route('listAdvertisements')}}">
                      <i class="fas fa-ad text-white"></i>
                      <span class="nav-link-text text-white">Advertisements</span>
                    </a>
                  </li>
                  @endif
                  @else

                    @if(Auth::user()->type == 0)
                      <li class="nav-item">
                      <a class="nav-link {{ request()->segment(1) == '' ? 'active' : '' }}" href="{{url('/')}}">
                          <i class="ni ni-tv-2 text-white"></i>
                          <span class="nav-link-text text-white">Dashboard</span>
                      </a>
                      </li>
                    @endif
                      <li class="nav-item">
                      <a class="nav-link {{ ((request()->segment(1) == 'users') || request()->segment(1) == 'user') ? 'active' : '' }}" href="{{url('users')}}">
                          <i class="ni ni-single-02 text-white"></i>
                          <span class="nav-link-text text-white">Users</span>
                      </a>
                      </li>
                      @if(Auth::user()->type == 0)
                      <li class="nav-item">
                      <a class="nav-link {{ request()->segment(1) == 'reason' ? 'active' : '' }}" href="{{url('reason/list')}}">
                          <i class="ni ni-active-40 text-white"></i>
                          <span class="nav-link-text text-white">Report Reasons</span>
                      </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link {{ ((request()->segment(1) == 'admins') || request()->segment(1) == 'admin') ? 'active' : '' }}" href="{{url('admins')}}">
                            <i class="ni ni-planet text-white"></i>
                            <span class="nav-link-text text-white">Admins</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link {{ request()->segment(1) == 'interests' ? 'active' : '' }}" href="{{route('list_interests')}}">
                            <i class="ni ni-bullet-list-67 text-white"></i>
                            <span class="nav-link-text text-white">Interests</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link {{ request()->segment(1) == 'feedback' ? 'active' : '' }}" href="{{route('userFeedback')}}">
                            <i class="ni ni-bullet-list-67 text-white"></i>
                            <span class="nav-link-text text-white">Feedback</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link {{ request()->segment(1) == 'companies' ? 'active' : '' }}" href="{{route('listCompanies')}}">
                            <i class="ni ni-building text-white"></i>
                            <span class="nav-link-text text-white">Companies</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link {{ request()->segment(1) == 'plans' ? 'active' : '' }}" href="{{route('listPlans')}}">
                            <i class="ni ni-ui-04 text-white"></i>
                            <span class="nav-link-text text-white">Plans (Companies)</span>
                        </a>
                      </li>
                      {{-- <li class="nav-item">
                        <a class="nav-link" href="{{route('listSubscriptionPlans')}}">
                            <i class="ni ni-bullet-list-67 text-white"></i>
                            <span class="nav-link-text text-white">Subscription Plans</span>
                        </a>
                      </li> --}}
                    @endif
                  @endif
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- Main content -->
<div class="main-content" id="panel">
  <!-- Topnav -->
  @include('layouts.top_nav')
  <!-- Header -->
  <!-- Header -->
  @yield('content')
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
      $('.show-password i').on('click', function(){
        var type = $('.password-field').attr('type');
        $(this).hide();
        if(type == "password"){
          $('i.hide').show();
          $('.password-field').attr('type', 'text');
        }else{
          $('i.show').show();
          $('.password-field').attr('type', 'password');
        }
      });
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

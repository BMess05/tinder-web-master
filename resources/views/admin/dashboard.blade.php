@extends('layouts.main')
@section('content')
<div class="header pb-6" id="main_content">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row align-items-center py-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                    <!-- Card body -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">All Users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$all_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                    <!-- Card body -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Average Age</h5>
                                    <span class="h2 font-weight-bold mb-0">{{number_format($average_age, 1)}}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-purple text-white rounded-circle shadow">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/online')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Online users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$online_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <!-- Card body -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/reported')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Reported Users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$reported_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                                        <i class="fas fa-flag-checkered"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/blocked')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Blocked Users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$blocked_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!-- <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/premium')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Premium users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$premium_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div> -->

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/males')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Male users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$male_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-blue text-white rounded-circle shadow">
                                        <i class="fa fa-male" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/females')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Female users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$female_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-pink text-white rounded-circle shadow">
                                        <i class="fa fa-female" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/others')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Other users</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$other_users}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-purple text-white rounded-circle shadow">
                                        <i class="fas fa-transgender"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
               <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{url('users/location')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Location</h5>
                                        <span class="h2 font-weight-bold mb-0">{{count($cities_count)}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                                        <i class="fa fa-map-marker"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{route('listCompanies')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Companies</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$companies_count}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-pink-grediant text-white rounded-circle shadow">
                                        <i class="ni ni-building"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <a href="{{route('listPlans')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Plans (Companies)</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$plans_count}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-pink-grediant text-white rounded-circle shadow">
                                        <i class="ni ni-ui-04"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Page content -->

@include('layouts.footer')
@endsection

@section('script')
<script>
    function confirmationDelete(anchor) {
        swal({
            title: "Are you sure want to delete this Category?",
            text: "Once deleted, you will not be able to recover this category!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                window.location = anchor.attr("href");
            }
        });
        //   var conf = confirm("Are you sure want to delete this User?");
        //   if (conf) window.location = anchor.attr("href");
    }

</script>
@endsection
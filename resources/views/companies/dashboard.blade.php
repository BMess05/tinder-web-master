@extends('layouts.main')
@section('content')
<div class="header pb-6" id="main_content">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row mt-3">
                <div class="col-xl-12">
                    @if(session('status'))
                        <div class="alert alert-{{ Session::get('status') }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ Session::get('message') }}   
                        </div>
                    @endif
                </div>
            </div>
            <div class="row align-items-center py-4">
                @if(auth('companies')->user()->subscription == 1)
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col">
                                    <a href="{{route('listAdvertisements')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Advertisement</h5>
                                        <span class="h2 font-weight-bold mb-0">{{$advertisements_count}}</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-pink-grediant text-white rounded-circle shadow">
                                        <i class="fas fa-ad"></i>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats"> 
                        <div class="card-body">
                            <div class="row">
                                @if(auth('companies')->user()->subscription == 1) 


                                <div class="col">
                                    <a href="{{route('chooseCompanySubscription')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Subscription</h5>
                                        <span class="h2 font-weight-bold mb-0">Active</span>
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-pink-grediant text-white rounded-circle shadow">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                </div>


                                @else


                                <div class="col">
                                    <a href="{{route('chooseCompanySubscription')}}">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Subctiption</h5>
                                        <span class="h2 font-weight-bold mb-0">Inactive </span>(Click to add subscription)
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-pink-grediant text-white rounded-circle shadow">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                </div>


                                @endif
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
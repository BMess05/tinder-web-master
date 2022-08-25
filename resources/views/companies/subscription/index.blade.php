@extends('layouts.main')
@section('content')
<div class="container-fluid mt-3">
    <div class="row" id="main_content">
        <div class="col-xl-12">
            <div class="card mt-5">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Choose Plan</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('/company')}}" class="btn btn-sm btn-primary">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-{{ Session::get('status') }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            {{ Session::get('message') }}
                        </div>
                    @endif
                    <ul class="list-group">
                        @foreach($plans as $plan)
                        <li class="list-group-item clearfix">
                            <div class="pull-left">
                                <h5>{{ $plan->name }}</h5>
                                <h5>${{ number_format($plan->cost, 2) }} monthly</h5>
                                <h5>{{ $plan->description }}</h5>
                                <a href="{{ route('showSubscription', $plan->slug) }}" class="btn btn-outline-dark pull-right">Choose</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
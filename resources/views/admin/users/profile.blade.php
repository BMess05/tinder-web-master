@extends('layouts.main')
@section('content')
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-xl-12">
            <div class="col text-right">
                <a href="{{url('users')}}" class="btn btn-sm btn-primary">Back</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mt-3">
            <div class="alert-custom alert-danger" role="alert">
            If you found mandatory fields empty it means user has been registered but the signup process is not completed by the user.
            </div>
        </div>
    </div>
</div>
<div class="profile">
    <h2>Profile</h2>
    <div class="name">
        <span>Full name</span>
        <h5>@if($user->name != '') {{ $user->name }} @else <b>N/A</b>@endif</h5>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <span>Email:</span>
            <strong>{{$user->email}}</strong>
        </div>
        <div class="col-lg-6">
            <span>Birthday:</span>
            @if($user->dob == NULL)
            <strong>N/A</strong>
            @else
            <strong>{{date('d M, Y', strtotime($user->dob))}}</strong>
            @endif
        </div>
        <div class="col-lg-6">
            <span>Gender:</span>
            <strong>@if($user->gender == 1) 
                    Male
                    @elseif($user->gender == 2)
                    Female
                    @elseif($user->gender == 3)
                    Other
                    @else
                    N/A
                    @endif</strong>
        </div>
        <div class="col-lg-6">
            <span>Interested In:</span>
            <strong>@if($user->interested_in == 1)
                    Male
                    @elseif($user->interested_in == 2)
                    Female
                    @elseif($user->interested_in == 3)
                    Both
                    @else
                    N/A
                    @endif</strong>
        </div>
        <div class="col-lg-6">
            <span>University:</span>
            <strong>@if($user->university != '') {{ $user->university }} @else <b>N/A</b>@endif</strong>
        </div>
        <div class="col-lg-6">
            <span>Business:</span>
            <strong>@if($user->business != '') {{ $user->business }} @else <b>N/A</b>@endif</strong>
        </div>
        <div class="col-lg-6">
            <span>Interests:</span>
            <strong>@if($user->user_interest != '') {{ $user->user_interest }} @else <b>N/A</b>@endif</strong>
        </div>
    </div>

    <div class="row">
        @forelse($user->user_images as $img)
        <div class="col-md-2">
            <div class="text-center">
                <img class="sm-img" src="{{url('uploads/users')}}/{{$img->image_name}}" alt="" data-id="{{$img->id}}">
            </div>
        </div>
        @empty
        <div class="col-md-12">
            No Images Found
        </div>
        @endforelse
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-striped table-hover dataTable no-footer" id="dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse($logs as $k => $log)
                    <tr>
                        <td>{{++$k}}</td>
                        <td>{{$log->type}}</td>
                        <td>{{date('d M, Y', strtotime($log->created_at))}}</td>
                        <td>{{$log->status}}</td>
                        <td>{{$log->description}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">No Logs Available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container">
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner text-center">
                        @foreach($user->user_images as $k => $img)
                        <div class="carousel-item text-center img_active_{{$img->id}}">
                            <img class="d-block w-100" src="{{url('uploads/users')}}/{{$img->image_name}}" alt="slide">
                        </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('.sm-img').on('click', function() {
        var id = $(this).data('id');
        $('.carousel-item').removeClass('active');
        $(`.img_active_${id}`).addClass('active');
        $('.bs-example-modal-lg').modal('show');
    });
    $('.carousel').carousel({
        interval: 8000
    })
</script>
@endsection
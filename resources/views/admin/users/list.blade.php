@extends('layouts.main')
@section('content')
<div class="">
    <div class="container-fluid mt-3">
        <div class="row" id="main_content">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">{{ucwords($filter)}} Users</h3>
                            </div>
                            <div class="col text-right">
                                @if(Auth::user()->type == 0)
                                <a href="{{url('user/export_user')}}/{{$filter}}" class="btn btn-sm btn-primary">Export Users</a>
                                @endif
                                <a href="{{url('user/add')}}" class="btn btn-sm btn-primary">Add User</a>
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
                    <div class="card-body table-responsive">
                        @if(session('status'))
                            <div class="alert alert-{{ Session::get('status') }}" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                {{ Session::get('message') }}
                            </div>
                        @endif
                        <!-- Projects table -->
                        <table class="table table-sm table-striped table-hover dataTable no-footer" id="dataTable">
                            <thead>
                                <tr>
                                    <th scope="col">Action</th>
                                    <th scope="col" class="sort" data-sort="name">Name</th>
                                    <th scope="col" class="sort" data-sort="email">Email</th>
                                    <th scope="col" class="sort" data-sort="gender">Gender</th>
                                    <th scope="col" class="sort" data-sort="university">University</th>
                                    <th scope="col" class="sort" data-sort="business">Business</th>
                                    <th scope="col" class="sort" data-sort="interested_in">Interested In</th>
                                    <th scope="col" class="sort" data-sort="interested_in">Interests</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($users as $user)
                                <tr>
                                    <th>
                                    @if(Auth::user()->type == 0)
                                    <a href="{{url('user/edit')}}/{{$user->id}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{url('user/delete')}}/{{$user->id}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                    @endif
                                    <a href="{{url('user/profile')}}/{{$user->id}}" class="btn btn-success btn-sm"><i class="far fa-eye"></i></a>
                                    </th>
                                    <th>@if($user->name != '') {{ $user->name }} @else <b>N/A</b>@endif</th>
                                    <th>{{ $user->email }}</th>
                                    <td>@if($user->gender == 1) 
                                        Male
                                        @elseif($user->gender == 2)
                                        Female
                                        @elseif($user->gender == 3)
                                        Other
                                        @else
                                        <b>N/A</b>
                                        @endif
                                    </td>
                                    <td>@if($user->university != '') {{ $user->university }} @else <b>N/A</b>@endif</td>
                                    <td>@if($user->business != '') {{ $user->business }} @else <b>N/A</b>@endif</td>
                                    @if($user->interested_in == 1)
                                    <td>Male</td>
                                    @elseif($user->interested_in == 2)
                                    <td>Female</td>
                                    @elseif($user->interested_in == 3)
                                    <td>Other</td>
                                    @else
                                    <td><b>N/A</b></td>
                                    @endif
                                    @if($user->interested_in != "")
                                        <td>{{ $user->user_interest }}</td>
                                    @else
                                        <td><b>N/A</b></td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No users found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footer')
    </div>
</div>
@endsection

@section('script')
<script>
    function confirmationDelete(anchor) {
        swal({
            title: "Are you sure you want to delete this User?",
            text: "Once deleted, you will not be able to recover this data!",
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
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "paginate": {
            "previous": "<",
            "next": ">"
            }
        }
    });
} );
</script>
@endsection
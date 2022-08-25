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
                            <h3 class="mb-0">Reported Users</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('users')}}" class="btn btn-sm btn-primary">Back</a>
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
                        <table class="table align-items-center table-flush" id="dataTable">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Action</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Gender</th>
                                    <th scope="col">University</th>
                                    <th scope="col">Business</th>
                                    <th scope="col">Interested In</th>
                                    <th scope="col">Reports Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <th>
                                    @if($user->is_blocked == 0)
                                    <a onclick="javascript:confirmationBlock($(this));return false;" href="{{url('user/block')}}/{{$user->id}}" class="btn btn-warning btn-sm" title="Block"><i class="fa fa-ban" aria-hidden="true"></i></a>
                                    @else
                                    <a onclick="javascript:confirmationUnBlock($(this));return false;" href="{{url('user/unblock')}}/{{$user->id}}" class="btn btn-info btn-sm" title="Unblock"><i class="fa fa-unlock" aria-hidden="true"></i></a>
                                    @endif
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{url('user/delete')}}/{{$user->id}}" class="btn btn-danger btn-sm" title="Delete"><i class="far fa-trash-alt"></i></a>
                                    </th>
                                    <th>{{ $user->name }}</th>
                                    <th>{{ $user->email }}</th>
                                    <td>@if($user->gender == 1) 
                                        Male
                                        @elseif($user->gender == 2)
                                        Female
                                        @elseif($user->gender == 3)
                                        Other
                                        @else
                                        Not Mentioned
                                        @endif
                                    </td>
                                    <td>{{ $user->university }}</td>
                                    <td>{{ $user->business }}</td>
                                    @if($user->interested_in == 1)
                                    <td>Male</td>
                                    @elseif($user->interested_in == 2)
                                    <td>Female</td>
                                    @elseif($user->interested_in == 3)
                                    <td>Both</td>
                                    @else
                                    <td>Not Mentioned</td>
                                    @endif
                                    <td>{{ $user->reported_ids_count }}</td>
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
    }

    function confirmationBlock(anchor) {
        swal({
            title: "Are you sure want to block this User?",
            text: "Once blocked, you unblock this later!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                window.location = anchor.attr("href");
            }
        });
    }

    function confirmationUnBlock(anchor) {
        swal({
            title: "Are you sure want to unblock this User?",
            text: "Once unblocked, you block this later!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((willDelete) => {
            if (willDelete) {
                window.location = anchor.attr("href");
            }
        });
    }

</script>
@endsection
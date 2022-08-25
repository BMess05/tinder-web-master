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
                            <h3 class="mb-0">Admins</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('admin/add')}}" class="btn btn-sm btn-primary">Add Admin</a>
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
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($users as $user)
                                <tr>
                                    <th>
                                    <a href="{{url('admin/edit')}}/{{$user->id}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{url('user/delete')}}/{{$user->id}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                    </th>
                                    <th>{{ $user->name }}</th>
                                    <th>{{ $user->email }}</th>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No admins found</td>
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
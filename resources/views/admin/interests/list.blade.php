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
                            <h3 class="mb-0">Interests</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('add_interest') }}" class="btn btn-sm btn-primary">Add Interest</a>
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
                                    <th scope="col" class="sort" data-sort="name">Title</th>
                                    <th scope="col" class="sort" data-sort="name">Title Germen</th>
                                    <th scope="col" class="sort" data-sort="name">Title Turkish</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($interests as $interest)
                                <tr>
                                    <th>
                                    <a href="{{route('edit_interest', $interest->id)}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{route('delete_interest', $interest['id'])}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                    </th>
                                    <th>{{ $interest->title }}</th>
                                    <th>{{ $interest->title_de }}</th>
                                    <th>{{ $interest->title_tr }}</th>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No interests found</td>
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
            title: "Are you sure you want to delete this Interest?",
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
});
</script>
@endsection
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
                            <h3 class="mb-0">Report Reasons</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('reason/add')}}" class="btn btn-sm btn-primary">Add Reason</a>
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
                                @if( count($reasons) > 0)
                                    @foreach($reasons as $reason)
                                        <tr>
                                            <th>
                                            <a href="{{url('reason/edit')}}/{{$reason['id']}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                            <a onclick="javascript:confirmationDelete($(this));return false;" href="{{url('reason/delete')}}/{{$reason['id']}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                            </th>
                                            <th>{{ $reason['reason_text_en'] }}</th>
                                            <th>{{ $reason['reason_text_de'] }}</th>
                                            <th>{{ $reason['reason_text_tr'] }}</th>
                                        </tr>
                                    @endforeach                              
                                @else
                                    <tr>
                                        <td colspan="5">No reasons found</td>
                                    </tr>
                                @endif

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
            title: "Are you sure you want to delete this Reason?",
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
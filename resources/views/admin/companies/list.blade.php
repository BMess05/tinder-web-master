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
                                <h3 class="mb-0">Companies</h3>
                            </div>
                            <div class="col text-right">
                                <a href="{{route('addCompany')}}" class="btn btn-sm btn-primary">Add Company</a>
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
                                    <th scope="col" class="sort" data-sort="name">Company Name</th>
                                    <th scope="col" class="sort" data-sort="email">Contact Name</th>
                                    <th scope="col" class="sort" data-sort="gender">Email</th>
                                    <th scope="col" class="sort" data-sort="university">Address</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($companies as $company)
                                <tr>
                                    <th>
                                    <a href="{{route('editCompany', $company->id)}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{route('deleteCompany', $company->id)}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                    </th>
                                    <td>{{ $company->company_name ?? 'NA' }}</td>
                                    <td>{{ $company->contact_name ?? 'NA' }}</td>
                                    <td>{{ $company->email }}</td>
                                    <td>{{ strlen($company->address) > 30 ? substr($company->address,0,30)."..." : $company->address ?? 'NA' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No companies found</td>
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
            title: "Are you sure you want to delete this Company?",
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
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
                                <h3 class="mb-0">Advertisements</h3>
                            </div>
                            <div class="col text-right">
                                <a href="{{route('addAdvertisement')}}" class="btn btn-sm btn-primary">Add Advertisement</a>
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
                                    <th scope="col" class="sort" data-sort="email">Description</th>
                                    <th scope="col" class="sort" data-sort="gender">url</th>
                                    <th scope="col" class="sort" data-sort="university">Image</th>
                                    <th scope="col" class="sort" data-sort="university">Status</th>
                                    <th scope="col" class="sort" data-sort="university">Active/Inactive</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($advertisements as $adv)
                                <tr>
                                    <th style="vertical-align:middle;">
                                    <a href="{{route('editAdvertisement', $adv->id)}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                    <a onclick="javascript:confirmationDelete($(this));return false;" href="{{route('deleteAdvertisement', $adv->id)}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                    </th>
                                    <td style="vertical-align:middle;">{{ $adv->title ?? '-' }}</td>
                                    <td style="vertical-align:middle;">{{ strlen($adv->description) > 30 ? substr($adv->description,0,30)."..." : $adv->description }}</td>
                                    <td style="vertical-align:middle;">{{ strlen($adv->url) > 30 ? substr($adv->url,0,30)."..." : $adv->url }}</td>
                                    <td style="vertical-align:middle;"><img src="{{ $adv->image_url }}" class="img img-thumbnail img-listing"></td>
                                    <td class="status_{{$adv->id}}" style="vertical-align:middle;">{{ ($adv->is_active == 0) ? 'Inactive' : 'Active' }}</td>
                                    <td style="vertical-align:middle;">
                                        
                                        <button onclick="javascript:confirmationChangeStatus($(this));return false;" class="btn btn-info btn-sm change_status btn-active{{$adv->id}} {{ ($adv->is_active == 1) ? 'hide' : '' }}" data-id="{{$adv->id}}" data-status="{{$adv->is_active}}">Activate</button>
                                        
                                        <button onclick="javascript:confirmationChangeStatus($(this));return false;" class="btn btn-warning btn-sm change_status btn-inactive{{$adv->id}} {{ ($adv->is_active == 0) ? 'hide' : '' }}" data-id="{{$adv->id}}" data-status="{{$adv->is_active}}">Deactivate</button>
                                        
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td style="text-align: center;" colspan="7">No advertisements found</td>
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
            title: "Are you sure you want to delete this Advertisement?",
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

    function confirmationChangeStatus(anchor) {
        let id = anchor.data('id');
        // let status = anchor.data('status');
        let status = $(`.status_${id}`).text();
        let title = '';
        if(status == 'Inactive') {
            title = 'Are you sure you want to activate this advertisement?';
        }   else {
            title = 'Are you sure you want to deactivate this advertisement?';
        }
        swal({
            title: title,
            text: "",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((res) => {
            if (res) {
                
                
                
                let data = {
                    "_token" : "{{csrf_token()}}",
                    "id" : id
                };

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('activeInactiveAdd') }}",
                    data: data,
                    success: function(res) {
                        if(res.success == 1) {
                            status = $(`.status_${id}`).text();
                            if(status == 'Inactive') {
                                $(`.status_${id}`).html('Active');
                                $(`.btn-inactive${id}`).removeClass('hide');
                                $(`.btn-active${id}`).addClass('hide');
                            }   else {
                                $(`.status_${id}`).html('Inactive');
                                $(`.btn-active${id}`).removeClass('hide');
                                $(`.btn-inactive${id}`).addClass('hide');
                            }
                        }
                    }
                });
            }
        });
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
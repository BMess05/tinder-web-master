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
                            <h3 class="mb-0">Subscription Plans</h3>
                        </div>
                        <div class="col text-right">                            
                            <a href="{{route('createSubscriptionPlan')}}" class="btn btn-sm btn-primary">Add Subscription Plan</a>
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
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($plans as $plan)
                                <tr>
                                    <th>
                                    @if(Auth::user()->type == 0)
                                        <a title="Edit" href="{{route('editSubscriptionPlan',$plan->id)}}" class="btn btn-info btn-sm"><i class="fas fa-user-edit"></i></a>
                                        <a title="Delete" onclick="javascript:confirmationDelete($(this));return false;" href="{{route('deleteSubscriptionPlan',$plan->id)}}" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a>
                                        @if( ! $plan->is_active)
                                            <a title="Publish" onclick="javascript:confirmationPublish($(this));return false;" href="{{route('publishSubscriptionPlan',$plan->id)}}" class="btn btn-danger btn-sm"><i class="far fa-check-circle"></i></a>
                                        @else
                                            <a  title="Unpublish" onclick="javascript:confirmationUnpublish($(this));return false;" href="{{route('unpublishSubscriptionPlan',$plan->id)}}" class="btn btn-danger btn-sm"><i class="far fa-times-circle"></i></a>
                                        @endif
                                    @endif
                                    <a title="Details" href="{{route('showSubscriptionPlan',$plan->id)}}" class="btn btn-success btn-sm"><i class="far fa-eye"></i></a>
                                    </th>
                                    <th>{{ $plan->plan_name }}</th>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">No plans found</td>
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
            title: "Are you sure you want to delete this Subscription Plan?",
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

    function confirmationPublish(anchor) {
        swal({
            title: "Are you sure you want to publish this Subscription Plan?",
            text: "Subscription plan will be published now.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((willPublish) => {
            if (willPublish) {
                window.location = anchor.attr("href");
            }
        });
    }

    function confirmationUnpublish(anchor) {
        swal({
            title: "Are you sure you want to unpublish this Subscription Plan?",
            text: "Subscription plan will be unpublished now.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((willUnpublish) => {
            if (willUnpublish) {
                window.location = anchor.attr("href");
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
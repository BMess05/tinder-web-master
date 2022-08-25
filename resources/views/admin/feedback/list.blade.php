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
                            <h3 class="mb-0">Feedback List</h3>
                        </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
               <div class="table-responsive">
                 <table class="table align-items-center text-center" id="userTable">
                   <thead class="thead-light">
                     <tr>
                        <th scope="col" style="width: 10px;">Sr.No</th>
                        <th scope="col" style="width: 10px;">Sender Name</th>
                        <th scope="col" class="text-center">Title</th>
                        <th scope="col" class="text-center">Description</th>
                        <th scope="col" class="text-center">Created At</th>
                     </tr>
                   </thead>
                   <tbody>
                      @if(count($feedbacks)>0)
                        @php
                            $i = ($feedbacks->currentpage()-1)* $feedbacks->perpage() + 1;
                        @endphp
                       @foreach($feedbacks as $feedback)
                       <tr>
                            <td class="text-center" style="max-width: 10px;">
                                {{$i++}}
                            </td>
                            <td class="text-center">
                                <div class="media-body">
                                    <span class="mb-0 text-sm">@if($feedback->feedbackUser) {{ $feedback->feedbackUser->name }} @else <b>N/A</b> @endif</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="media-body">
                                    <span class="mb-0 text-sm">{{$feedback->title}}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="media-body">
                                    <span class="mb-0 text-sm">{{$feedback->description}}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="media-body">
                                    <span class="mb-0 text-sm">{{$feedback->created_at}}</span>
                                </div>
                            </td>
                       </tr>
                       @endforeach
                     @else
                       <tr>
                         <th colspan="12">
                           <div class="media-body text-center">
                               <span class="mb-0 text-sm">No data found.</span>
                           </div>
                         </th>
                       </tr>
                     @endif
                   </tbody>
                 </table>
               </div>
               <div class="ads_pagination mt-3 mb-0">
                   {{$feedbacks->appends(request()->except('page'))->links()}}
               </div>
            </div>
                </div>
            </div>
        </div>
        @include('layouts.footer')
    </div>
</div>
@endsection
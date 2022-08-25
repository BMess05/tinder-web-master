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
                            <h3 class="mb-0">Edit Subscription Plan</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{route('listSubscriptionPlans')}}" class="btn btn-sm btn-primary">Back</a>
                        </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ Session::get('status') }}" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                {{ Session::get('message') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('updateSubscriptionPlan',$plan->id) }}">
                            @csrf

                             <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i></span>
                                    </div>
                                    <input id="plan_name" type="text" class="form-control @error('plan_name') is-invalid @enderror" name="plan_name" value="{{ $plan->plan_name }}" required autocomplete="plan_name" placeholder="Plan Name" autofocus>

                                    @error('plan_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <div class="input-group  input-group-alternative mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-align-left-2"></i>Boost</span>
                                        </div>

                                       <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="boost_yes" value="1" name="boost" class="custom-control-input" {{ $plan->boost ? 'checked' : ''}} >
                                            <label class="custom-control-label" for="boost_yes">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="boost_no" value="0"  name="boost" class="custom-control-input" {{ $plan->boost ? '' : 'checked'}} >
                                            <label class="custom-control-label" for="boost_no">No</label>
                                        </div>
                                      
                                        @error('boost')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-sm-4">
                                    <div class="input-group  input-group-alternative mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-align-left-2"></i>Boost Count</span>
                                        </div>
                                       <input id="boost_count" type="number" min="0" class="form-control @error('boost_count') is-invalid @enderror" name="boost_count" value="{{ $plan->boost_count}}"  autocomplete="boost_count" placeholder="" autofocus>
                                        @error('boost_count')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group  input-group-alternative mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-align-left-2"></i>Boost Duartion</span>
                                        </div>
                                        <input id="boost_duration" type="number" class="form-control @error('boost_duration') is-invalid @enderror" name="boost_duration" value="{{ $plan->boost_duration}}" autocomplete="boost_duration" placeholder="" autofocus>
                                      
                                        @error('boost_duration')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            

                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <div class="input-group  input-group-alternative mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-align-left-2"></i> Last likes</span>
                                        </div>
                                        {{-- <input id="last_likes" type="checkbox" class="form-control @error('last_likes') is-invalid @enderror" name="last_likes" value="{{ old('last_likes') }}" required autocomplete="last_likes" placeholder="Last likes" autofocus>
                                         --}}
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="last_likes_yes" value="1" name="last_likes" class="custom-control-input" {{ $plan->last_likes ? 'checked' : ''}} >
                                            <label class="custom-control-label" for="last_likes_yes">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="last_likes_no" value="0"  name="last_likes" class="custom-control-input" {{ $plan->last_likes ? '' : 'checked'}}>
                                            <label class="custom-control-label" for="last_likes_no">No</label>
                                        </div>
                                      
                                        @error('last_likes')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                   
                                </div>
                                <div class="form-group col-sm-6">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Lask likes count</span>
                                    </div>
                                    <input id="last_likes_duration" type="number" min="0" class="form-control @error('last_likes_duration') is-invalid @enderror" name="last_likes_duration" value="{{ $plan->last_likes_duration}}" autocomplete="last_likes_duration" placeholder="" autofocus>
                                    @error('last_likes_duration')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Super Likes</span>
                                    </div>
                                    {{-- <input id="super_likes" type="text" class="form-control @error('super_likes') is-invalid @enderror" name="super_likes" value="{{ old('super_likes') }}" required autocomplete="super_likes" placeholder="Super Likes" autofocus> --}}
                                    
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="super_likes_yes" value="1" name="super_likes" class="custom-control-input" {{ $plan->super_likes ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="super_likes_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="super_likes_no" value="0" name="super_likes" class="custom-control-input" {{ $plan->super_likes ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="super_likes_no">No</label>
                                    </div>

                                    @error('super_likes')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Super likes count</span>
                                    </div>
                                    <input id="super_likes_count" type="number" min="0" class="form-control @error('super_likes_count') is-invalid @enderror" name="super_likes_count" value="{{ $plan->super_likes_count}}" autocomplete="super_likes_count" placeholder="" autofocus>

                                    @error('super_likes_count')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Super likes duration</span>
                                    </div>
                                    <input id="super_likes_duration" type="number" min="0" class="form-control @error('super_likes_duration') is-invalid @enderror" name="super_likes_duration" value="{{ $plan->super_likes_duration }}" autocomplete="super_likes_duration" placeholder="" autofocus>

                                    @error('super_likes_duration')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Top picks</span>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="top_picks_yes" value="1" name="top_picks" class="custom-control-input" {{ $plan->top_picks ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="top_picks_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="top_picks_no" value="0" name="top_picks" class="custom-control-input" {{ $plan->top_picks ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="top_picks_no">No</label>
                                    </div>

                                    @error('top_picks')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Top picks visible</span>
                                    </div>
                                    <input id="top_picks_visible" type="number" min="0" class="form-control @error('top_picks_visible') is-invalid @enderror" name="top_picks_visible" value="{{ $plan->top_picks_visible}}" autocomplete="top_picks_visible" placeholder="" autofocus>

                                    @error('top_picks_visible')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Top picks count</span>
                                    </div>
                                    <input id="top_picks_count" type="number" min="0" class="form-control @error('top_picks_count') is-invalid @enderror" name="top_picks_count" value="{{ $plan->top_picks_count}}" autocomplete="top_picks_count" placeholder="" autofocus>

                                    @error('top_picks_count')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Top picks duration</span>
                                    </div>
                                    <input id="top_picks_duration" type="number" min="0" class="form-control @error('top_picks_duration') is-invalid @enderror" name="top_picks_duration" value="{{ $plan->top_picks_duration }}" autocomplete="top_picks_duration" placeholder="" autofocus>

                                    @error('top_picks_duration')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Unlimited Likes</span>
                                    </div>
                                    {{-- <input id="unlimited_likes" type="text" class="form-control @error('unlimited_likes') is-invalid @enderror" name="unlimited_likes" value="{{ old('superunlimited_likes_likes') }}" required autocomplete="unlimited_likes" placeholder="Unlimited Likes" autofocus> --}}

                                     <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="unmlimited_likes_yes" value="1" name="unlimited_likes" class="custom-control-input"  {{ $plan->unlimited_likes ? 'checked' : ''}} >
                                        <label class="custom-control-label" for="unmlimited_likes_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="unmlimited_likes_no" value="0" name="unlimited_likes" class="custom-control-input" {{ $plan->unlimited_likes ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="unmlimited_likes_no">No</label>
                                    </div>

                                    @error('unlimited_likes')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>

                                 <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Likes count</span>
                                    </div>
                                    <input id="likes_count" type="number" min="0" class="form-control @error('likes_count') is-invalid @enderror" name="likes_count" value="{{ $plan->likes_count}}" autocomplete="likes_count" placeholder="" autofocus>

                                    @error('likes_count')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Likes duration</span>
                                    </div>
                                    <input id="likes_duration" type="number" min="0" class="form-control @error('likes_duration') is-invalid @enderror" name="likes_duration" value="{{ $plan->likes_duration }}" autocomplete="likes_duration" placeholder="" autofocus>

                                    @error('likes_duration')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Passport</span>
                                    </div>
                                    {{-- <input id="passport" type="text" class="form-control @error('passport') is-invalid @enderror" name="passport" value="{{ old('passport') }}" required autocomplete="passport" placeholder="Passport" autofocus> --}}

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="passport_yes" value="1" name="passport" class="custom-control-input" {{ $plan->passport ? 'checked' : ''}} >
                                        <label class="custom-control-label" for="passport_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="passport_no" value="0" name="passport" class="custom-control-input" {{ $plan->passport ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="passport_no">No</label>
                                    </div>


                                    @error('passport')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Unmlimted Rewinds</span>
                                    </div>
                                    {{-- <input id="unlimited_rewinds" type="text" class="form-control @error('unlimited_rewinds') is-invalid @enderror" name="unlimited_rewinds" value="{{ old('unlimited_rewinds') }}" required autocomplete="unlimited_rewinds" placeholder="Rewinds" autofocus> --}}


                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="unlimited_rewinds_yes" value="1" name="unlimited_rewinds" class="custom-control-input" {{ $plan->unlimited_rewinds ? 'checked' : ''}}  >
                                        <label class="custom-control-label" for="unlimited_rewinds_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="unlimited_rewinds_no" value="0" name="unlimited_rewinds" class="custom-control-input" {{ $plan->unlimited_rewinds ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="unlimited_rewinds_no">No</label>
                                    </div>

                                    @error('unlimited_rewinds')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Ads</span>
                                    </div>
                                    {{-- <input id="ads" type="text" class="form-control @error('ads') is-invalid @enderror" name="ads" value="{{ old('ads') }}" required autocomplete="ads" placeholder="Ads" autofocus> --}}

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="ads_yes" value="1" name="ads" class="custom-control-input" {{ $plan->ads ? 'checked' : ''}} >
                                        <label class="custom-control-label" for="ads_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="ads_no" value="0" name="ads" class="custom-control-input" {{ $plan->ads ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="ads_no">No</label>
                                    </div>
                                    
                                    @error('ads')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>                               
                            </div>
                            
                            <div class="row">
                                
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>See who likes me</span>
                                    </div>
                                    {{-- <input id="see_who_likes_me" type="see_who_likes_me" class="form-control @error('see_who_likes_me') is-invalid @enderror" name="see_who_likes_me" value="{{ old('see_who_likes_me') }}" required autocomplete="see_who_likes_me" placeholder="See who likes me" autofocus> --}}

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="see_who_likes_me_yes" value="1" name="see_who_likes_me" class="custom-control-input" {{ $plan->see_who_likes_me ? 'checked' : ''}} >
                                        <label class="custom-control-label" for="see_who_likes_me_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="see_who_likes_me_no" value="0" name="see_who_likes_me" class="custom-control-input" {{ $plan->see_who_likes_me ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="see_who_likes_me_no">No</label>
                                    </div>

                                    @error('see_who_likes_me')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Priority likes</span>
                                    </div>
                                    {{-- <input id="priority_likes" type="priority_likes" class="form-control @error('priority_likes') is-invalid @enderror" name="priority_likes" value="{{ old('priority_likes') }}" required autocomplete="priority_likes" placeholder="Priority likes" autofocus> --}}

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="priority_likes_yes" value="1" name="priority_likes" class="custom-control-input" {{ $plan->priority_likes ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="priority_likes_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="priority_likes_no" value="0" name="priority_likes" class="custom-control-input" {{ $plan->priority_likes ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="priority_likes_no">No</label>
                                    </div>

                                    @error('priority_likes')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Attach message</span>
                                    </div>
                                    {{-- <input id="attach_message" type="attach_message" class="form-control @error('attach_message') is-invalid @enderror" name="attach_message" value="{{ old('attach_message') }}" required autocomplete="attach_message" placeholder="Attach message" autofocus> --}}

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="attach_message_yes" value="1" name="attach_message" class="custom-control-input" {{ $plan->attach_message ? 'checked' : ''}}>
                                        <label class="custom-control-label" for="attach_message_yes">Yes</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="attach_message_no" value="0" name="attach_message" class="custom-control-input" {{ $plan->attach_message ? '' : 'checked'}}>
                                        <label class="custom-control-label" for="attach_message_no">No</label>
                                    </div>

                                    @error('attach_message')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                            </div>

                             <div class="row">



                                 <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Apple id</span>
                                    </div>
                                     <input id="apple_id" type="text" class="form-control @error('apple_id') is-invalid @enderror" name="apple_id" value="{{ $plan->apple_id }}" required autocomplete="apple_id" placeholder="" autofocus>
                                    @error('apple_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>

                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Android id</span>
                                    </div>
                                     <input id="android_id" type="text" class="form-control @error('android_id') is-invalid @enderror" name="android_id" value="{{ $plan->android_id }}" required autocomplete="android_id" placeholder="" autofocus>
                                    @error('android_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>
                               
                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Price</span>
                                    </div>
                                     <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ $plan->price }}" required autocomplete="price" placeholder="" autofocus>
                                    @error('price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>

                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Offer Price</span>
                                    </div>
                                     <input id="offer_price" type="text" class="form-control @error('offer_price') is-invalid @enderror" name="offer_price" value="{{ $plan->offer_price }}" autocomplete="offer_price" placeholder="" autofocus>
                                    @error('offer_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>

                                <div class="form-group col-sm-3">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Grace Period</span>
                                    </div>
                                     <input id="grace_period" type="number"  min="0" class="form-control @error('grace_period') is-invalid @enderror" name="grace_period" value="{{ $plan->grace_period }}" required autocomplete="grace_period" placeholder="" autofocus>
                                    @error('grace_period')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>


                                <div class="form-group col-sm-6">
                                    <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-align-left-2"></i>Product id</span>
                                    </div>
                                     <input id="product_id" type="text" class="form-control @error('product_id') is-invalid @enderror" name="product_id" value="{{ $plan->product_id }}" required autocomplete="product_id" placeholder="" autofocus>
                                    @error('product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>



                               
                            </div>
                            

                           
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary mt-3">Save</button>
                            </div>
                        </form>
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
    
</script>
@endsection
@extends('layouts.main')
@section('content')
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-xl-12">
            <div class="col text-right">
                <a href="{{route('listSubscriptionPlans')}}" class="btn btn-sm btn-primary">Back</a>
            </div>
        </div>
    </div>
</div>
<div class="profile">
    <h2>Subscription plan</h2>
    <div class="name">
        <span>Plan name</span>
        <h5>{{$plan->plan_name}}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <span>Last Likes:</span>
            <strong>{{$plan->last_likes ? 'Yes' : 'No'}}</strong>
        </div>
        @if( $plan->last_likes )
            <div class="col-lg-12">
                <span>Last Likes Duration:</span>
                <strong>{{$plan->last_likes_duration}}</strong>
            </div>
        @endif

        <div class="col-lg-12">
            <span>Priority Likes:</span>
            <strong>{{$plan->priority_likes ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>Super Likes:</span>
            <strong>{{$plan->super_likes ? 'Yes' : 'No'}}</strong>
        </div>

        @if( $plan->super_likes )
            <div class="col-lg-12">
                <span>Super Likes Duration:</span>
                <strong>{{$plan->super_likes_duration}}</strong>
            </div>
            <div class="col-lg-12">
                <span>Super Likes Count:</span>
                <strong>{{$plan->super_likes_count}}</strong>
            </div>
        @endif
        
        <div class="col-lg-12">
            <span>Top Picks:</span>
            <strong>{{$plan->top_picks ? 'Yes' : 'No'}}</strong>
        </div>

        @if( $plan->top_picks )
            <div class="col-lg-12">
                <span>Top Picks Duration:</span>
                <strong>{{$plan->top_picks_duration}}</strong>
            </div>
            <div class="col-lg-12">
                <span>Top Picks Count:</span>
                <strong>{{$plan->top_picks_count}}</strong>
            </div>
            <div class="col-lg-12">
                <span>Top Picks Visible:</span>
                <strong>{{$plan->top_picks_visible}}</strong>
            </div>
        @endif
        
        
        <div class="col-lg-12">
            <span>Unlimted Likes:</span>
            <strong>{{$plan->unlimited_likes ? 'Yes' : 'No'}}</strong>
        </div>
        @if( ! $plan->unlimited_likes )
            <div class="col-lg-12">
                <span>Likes Duration:</span>
                <strong>{{$plan->likes_duration}}</strong>
            </div>
            <div class="col-lg-12">
                <span>Likes Count:</span>
                <strong>{{$plan->likes_count}}</strong>
            </div>
        @endif

        <div class="col-lg-12">
            <span>Boost:</span>
            <strong>{{$plan->boost ? 'Yes' : 'No'}}</strong>
        </div>

        @if( $plan->boost )
            <div class="col-lg-12">
                <span>Boost Count:</span>
                <strong>{{$plan->boost_count}}</strong>
            </div>
            <div class="col-lg-12">
                <span>Boost Duration:</span>
                <strong>{{$plan->boost_duration ? $plan->boost_duration : 0 }}</strong>
            </div>
        @endif


        <div class="col-lg-12">
            <span>Passport:</span>
            <strong>{{$plan->passport ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>Unlimited rewinds:</span>
            <strong>{{$plan->unlimited_rewinds ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>Ads:</span>
            <strong>{{$plan->ads ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>See who likes me:</span>
            <strong>{{$plan->see_who_likes_me ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>Attach message:</span>
            <strong>{{$plan->attach_message ? 'Yes' : 'No'}}</strong>
        </div>

        <div class="col-lg-12">
            <span>Product ID:</span>
            <strong>{{$plan->product_id }}</strong>
        </div>

        <div class="col-lg-12">
            <span>Apple ID:</span>
            <strong>{{$plan->apple_id }}</strong>
        </div>

        <div class="col-lg-12">
            <span>Android ID:</span>
            <strong>{{$plan->android_id }}</strong>
        </div>

        <div class="col-lg-12">
            <span>Price:</span>
            <strong>{{$plan->price }}</strong>
        </div>

        <div class="col-lg-12">
            <span>Offer Price:</span>
            <strong>{{$plan->offer_price }}</strong>
        </div>

        <div class="col-lg-12">
            <span>Grace Period:</span>
            <strong>{{$plan->grace_period }}</strong>
        </div>

    </div>
   
</div>

@endsection

@section('script')
<script>
    
</script>
@endsection
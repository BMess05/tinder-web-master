@extends('layouts.main')
@section('content')
<div class="container-fluid mt-3">
    <div class="row" id="main_content">
        <div class="col-xl-12">
            <div class="card mt-5">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Add Plan</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{route('listPlans')}}" class="btn btn-sm btn-primary">Back</a>
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
                    <div class="payment-errors"></div>
                    <form action="{{route('store.plan')}}" method="post" id="addPlanForm">
                        @csrf
                        <div class="form-group">
                            <label for="plan name">Plan Name:</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Plan Name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="interval">Interval</label>
                            <select name="interval" class="form-control" required>
                                <option value=""></option>
                                <option value="day" {{ (old('interval') == 'day') ? 'selected' : '' }}>Day</option>
                                <option value="week" {{ (old('interval') == 'week') ? 'selected' : '' }}>Week</option>
                                <option value="month" {{ (old('interval') == 'month') ? 'selected' : '' }}>Month</option>
                                <option value="year" {{ (old('interval') == 'year') ? 'selected' : '' }}>Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cost">Cost:</label>
                            <input type="text" class="form-control" name="cost" placeholder="Enter Cost" value="{{ old('cost') }}">
                        </div>
                        <div class="form-group">
                            <label for="cost">Plan Description:</label>
                            <input type="text" class="form-control" name="description" placeholder="Enter Description" value="{{ old('description') }}">
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\StoreCompanyPlanRequest', '#addPlanForm'); !!}

<!-- 
   
<script>
    window.ParsleyConfig = {
        errorsWrapper: '<div></div>',
        errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
        errorClass: 'has-error',
        successClass: 'has-success'
    };
</script>

<script src="http://parsleyjs.org/dist/parsley.js"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    Stripe.setPublishableKey("<?php // echo env('STRIPE_KEY', 'pk_test_51L0k3fSCbcoZxLIW3zr9Z7kW3ehGFgCwkcEgoRQEhSQIX8c6lFWo599od1bpqoULzxudAh7BVqgm36AWMLcBYX3T00ZHwTiy84') ?>");
    jQuery(function($) {
        $('#addPlanForm').submit(function(event) {
            var $form = $(this);
            $form.parsley().subscribe('parsley:form:validate', function(formInstance) {
                formInstance.submitEvent.preventDefault();
                return false;
            });
            $form.find('#submitBtn').prop('disabled', true);
            Stripe.card.createToken($form, stripeResponseHandler);
            return false;
        });
    });
    
    function stripeResponseHandler(status, response) {
        var $form = $('#addPlanForm');
        if (response.error) {
            console.log("Errors: " + response.error.message);
            $form.find('.payment-errors').text(response.error.message);
            $form.find('.payment-errors').addClass('alert alert-danger');
            $form.find('#submitBtn').prop('disabled', false);
            $('#submitBtn').button('reset');
        } else {
            var token = response.id;
            alert("Here: "+token);
            $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            $form.get(0).submit();
        }
    };
</script> -->

@endsection
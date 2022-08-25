@extends('layouts.main')
@section('content')
<div class="container-fluid mt-3">
    <div class="row" id="main_content">
        <div class="col-xl-12">
            <div class="card mt-5">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Payment</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{route('chooseCompanySubscription')}}" class="btn btn-sm btn-primary">Back</a>
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
                    <form method="POST" action="{{ route('saveCompanySubscription') }}" accept-charset="UTF-8" data-parsley-validate="" id="payment-form" novalidate="">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-block">
                                <button type="button" class="close" data-dismiss="alert">×</button> 
                                <strong>{{ $message }}</strong>
                            </div>
                        @endif
                        @csrf
                        <div class="form-group" id="product-group">
                            <input type="hidden" name="plan" value="{{ $plan->slug }}" />
                            <label for=""><strong>You have selected {{ $plan->name }} plan and you will have to pay ${{$plan->cost}}.</strong></label>
                        </div>
                        <div class="form-group" id="cc-group">
                            <label for="">Credit card number:</label>
                            <input class="form-control" required="required" data-stripe="number" data-parsley-type="number" maxlength="16" data-parsley-trigger="change focusout" data-parsley-class-handler="#cc-group" type="text" data-parsley-id="7">
                        </div>
                        <div class="form-group" id="ccv-group">
                            <label for="">CVC (3 or 4 digit number):</label>
                            <input class="form-control" required="required" data-stripe="cvc" data-parsley-type="number" data-parsley-trigger="change focusout" maxlength="4" data-parsley-class-handler="#ccv-group" type="text">
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="exp-m-group">
                                <label for="">Ex. Month:</label>
                                <select class="form-control" required="required" data-stripe="exp-month">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="exp-y-group">
                                <label for="">Ex. Year:</label>
                                <select class="form-control" required="required" data-stripe="exp-year">
                                    @for($y = date('Y'); $y <= date('Y') + 35; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-lg btn-block btn-primary btn-order" id="submitBtn" style="margin-bottom: 10px;">Subscribe!</button>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="payment-errors" style="color: rgba(194, 17, 17, 0.818);margin-top:10px;"></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\AddSubscriptionRequest', '#payment-form'); !!}

 
<script>
    window.ParsleyConfig = {
        errorsWrapper: '<div></div>',
        errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
        errorClass: 'has-error',
        successClass: 'has-success'
    };
</script>

<script src="https://parsleyjs.org/dist/parsley.js"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    let key = "{{ env('STRIPE_KEY') }}";
    Stripe.setPublishableKey(key);
    jQuery(function($) {
        $('#payment-form').submit(function(event) {
            var $form = $(this);
            $form.parsley().subscribe('parsley:form:validate', function(formInstance) {
                formInstance.submitEvent.preventDefault();
                alert();
                return false;
            });
            $form.find('#submitBtn').prop('disabled', true);
            Stripe.card.createToken($form, stripeResponseHandler);
            return false;
        });
    });
    
    function stripeResponseHandler(status, response) {
        var $form = $('#payment-form');
        if (response.error) {
            $form.find('.payment-errors').text(response.error.message);
            $form.find('.payment-errors').addClass('alert alert-danger');
            $form.find('#submitBtn').prop('disabled', false);
            $('#submitBtn').button('reset');
        } else {
            var token = response.id;
            $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            $form.get(0).submit();
        }
    };
</script>
@endsection
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
                            <h3 class="mb-0">Add Reason</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('reason/list')}}" class="btn btn-sm btn-primary">Back</a>
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
                        <form method="POST" action="{{ url('reason/save') }}">
                            @csrf
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-active-40"></i></span>
                                </div>
                                <input maxlength="150" id="reason_text" type="text" class="form-control @error('reason_text') is-invalid @enderror" name="reason_text" value="{{ old('reason_text') }}" required autocomplete="reason_text" placeholder="English Reason Text" autofocus>

                                @error('reason_text')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>

                             <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-active-40"></i></span>
                                </div>
                                <input maxlength="150" id="ge_reason_text" type="text" class="form-control @error('ge_reason_text') is-invalid @enderror" name="ge_reason_text" value="{{ old('ge_reason_text') }}" required autocomplete="ge_reason_text" placeholder="Germen Grund Text" autofocus>

                                @error('ge_reason_text')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>


                           <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-active-40"></i></span>
                                </div>
                                <input maxlength="150" id="tr_reason_text" type="text" class="form-control @error('tr_reason_text') is-invalid @enderror" name="tr_reason_text" value="{{ old('tr_reason_text') }}" required autocomplete="tr_reason_text" placeholder="Türkçe Sebep Metni" autofocus>

                                @error('tr_reason_text')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
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
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
                            <h3 class="mb-0">Edit Interest</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{route('list_interests')}}" class="btn btn-sm btn-primary">Back</a>
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
                        <form method="POST" action="{{ route('update_interest', $interest['id']) }}">
                            @csrf
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-favourite-28"></i></span>
                                    </div>
                                    <input maxlength="30" id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') ?? $interest['title'] }}" required autocomplete="Title" placeholder="title" autofocus>

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                             <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-favourite-28"></i></span>
                                    </div>
                                    <input maxlength="30" id="titleg" type="text" class="form-control @error('titleg') is-invalid @enderror" name="titleg" value="{{ old('titleg')?? $interest['title_de'] }}" required autocomplete="titleg" placeholder="Title in Germen" autofocus>

                                    @error('titleg')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-favourite-28"></i></span>
                                    </div>
                                    <input maxlength="30" id="titleTur" type="text" class="form-control @error('titleTur') is-invalid @enderror" name="titleTur" value="{{ old('titleTur')?? $interest['title_tr'] }}" required autocomplete="titleTur" placeholder="Title in Turkish" autofocus>

                                    @error('titleTur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary mt-3">Update</button>
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
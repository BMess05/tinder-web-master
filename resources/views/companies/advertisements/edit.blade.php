@extends('layouts.main')
@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css"/>
<style>
img {
    display: block;
    max-width: 100%;
}
.preview {
    overflow: hidden;
    width: 160px;
    height: 160px;
    margin: 10px;
    border: 1px solid red;
}
.modal-lg{
    max-width: 1000px !important;
}
img.cropped_image {
    height: 150px;
}
.hide {
    display: none;
}


/* Preview modal css */
.modal {
  font-family: system-ui;
}
.bg_mobile{
  max-width: 400px;
  margin: auto;
  position:relative;
}
.bg_mobile .modal-body{
  position: absolute;
  top: 100px;
}
.bg_mobile img{
  width:100%;
}
.bodyContent{
  position: absolute;
  top: 150px;
  width: 90%;
  left: 50%;
  transform: translateX(-50%);
  padding: 15px;
  height: 635px;
  overflow-y: auto;
}
.inner_content{
  border-radius: 10px;
  background-color: #fff;
  padding: 10px;
  
}
.inner_content h3 {
  font-size: 23px;
  padding: 5px 0px;
}
.inner_content button {
  position: absolute;
    right: 0px;
    color: #fff;
    background: #000000 !important;
    border-radius: 22px;
    z-index: 999;
    top: 0px;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0px !important;
    width: 23px;
    height: 24px;
    cursor: pointer;
}
.close_btn{
  background: #17151d !important;
  padding: 3px 6px !important;
  border-radius: 50%;
  font-size: 16px !important;
  color: #fff !important;
  position: absolute;
  right: -12px;
  top: -13px;
  margin-top: 5px;
  font-weight: 300 !important;
  opacity: 1 !important;
}
.close_btn span {
    color: #fff !important;
}
.close:hover {
  color: #fff !important;
}
@media screen and (max-width: 500px) {
  .header_modal button {
    font-size: 16px;
  }
}


</style>
@endsection
@section('content')
<div class="">
    <div class="container-fluid mt-3">
        <div class="row" id="main_content">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Edit Advertisement</h3>
                            </div>
                            <div class="col text-right">
                                <a href="{{route('listAdvertisements')}}" class="btn btn-sm btn-primary">Back</a>
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
                        <form id="advertisementForm" method="POST" action="{{ route('updateAdvertisement', $adv->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <input maxlength="50" id="title" type="text" class="form-control @error('title') is-invalid @enderror ad_title" name="title" value="{{ old('title') ?? $adv->title }}" autocomplete="title" placeholder="Title" autofocus>
                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror ad_descripton" placeholder="Description">{{ old('description') ?? $adv->description }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <input maxlength="50" id="url" type="text" class="form-control @error('url') is-invalid @enderror ad_url" name="url" value="{{ old('url') ?? $adv->url }}" autocomplete="url" placeholder="URL: http://example.com/" autofocus>
                                    @error('url')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                            <label for="image">Image:</label>
                                <div class="input-group-merge input-group-alternative mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="file" class="form-control image @error('image') is-invalid @enderror" name="image">
                                            @error('image')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <img class="cropped_image img img-sm" id="cropped_image" src="{{$adv->image_url}}">
                                            <input type="hidden" id="cropped_image_name" name="cropped_image_name" value="">
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="Preferences"><strong>Preferences:</strong></label>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-merge input-group-alternative mb-3">
                                            <input id="autocomplete" type="text" class="form-control @error('preferred_location') is-invalid @enderror" name="preferred_location" value="{{ old('preferred_location') ?? $adv->ad_preference->address ?? '' }}" placeholder="Preferred location" autofocus>
                                            @error('preferred_location')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="no_lat_long error-help-block"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-merge input-group-alternative mb-3">
                                            <input min="1" type="number" class="form-control @error('diameter') is-invalid @enderror" name="diameter" value="{{ old('diameter')?? $adv->ad_preference->diameter ?? '' }}" autocomplete="diameter" placeholder="Diameter in Kilometers" step="0.1" autofocus>
                                            @error('diameter')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="age_group">Age group: </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="age_from">From: </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="age_from">To: </label>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-merge input-group-alternative mb-3">
                                            <input type="number" class="form-control @error('age_from') is-invalid @enderror" name="age_from" value="{{ old('age_from') ?? $adv->ad_preference->age_from ?? 18 }}" min=18 max=100>
                                            @error('age_from')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-merge input-group-alternative mb-3">
                                            <input type="number" class="form-control @error('age_to') is-invalid @enderror" name="age_to" value="{{ old('age_to') ?? $adv->ad_preference->age_to ?? 18 }}" min=18 max=100>
                                            @error('age_to')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="age_group">Gender group: </label>
                                    </div>
                                    @php
                                    if(old('gender_group')) {
                                        $gender_grp = old('gender_group');
                                    }   elseif($adv->ad_preference && $adv->ad_preference->gender_group) {
                                        $gender_grp = explode(',', $adv->ad_preference->gender_group);
                                    }   else {
                                        $gender_grp = [];
                                    }
                                    
                                    @endphp
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="checkbox" class="gender_group @error('gender_group') is-invalid @enderror" name="gender_group[]" value="1" {{ in_array('1', $gender_grp) ? 'checked' : ''}}> &nbsp;&nbsp; Male
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="checkbox" class="gender_group @error('gender_group') is-invalid @enderror" name="gender_group[]" value="2" {{ in_array('2', $gender_grp) ? 'checked' : ''}}> &nbsp;&nbsp; Female
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="checkbox" class="gender_group @error('gender_group') is-invalid @enderror" name="gender_group[]" value="3" {{ in_array('3', $gender_grp) ? 'checked' : ''}}> &nbsp;&nbsp; Others
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        @error('gender_group')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-primary mt-3 preview-btn">Preview</button>
                                <input type="hidden" name="lat" id="lat" value="{{ old('lat') ?? $adv->ad_preference->latitude ?? '' }}">
                                <input type="hidden" name="long" id="long" value="{{ old('long') ?? $adv->ad_preference->longitude ?? '' }}">
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


<!-- Cropping modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Crop Image Before Upload</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <div class="row">
                        <div class="col-md-8">
                        <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                        </div>
                        <div class="col-md-4">
                            <div class="preview"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary crop-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="crop">Crop</button>
            </div>
        </div>
    </div>
</div>


<!-- Preview Modal Starts -->

<div
      class="modal fade"
      id="previewModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
    <div class="modal-dialog" role="document" style="max-width:400px !important">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="bg_mobile">
                    <button
                        type="button"
                        class="close close_btn"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <img src="{{ url('assets/img/home.png') }}" />
                    <div class="bodyContent">
                        <div class="inner_content">
                            <button class="btn-close">
                                <span aria-hidden="true" style="padding-bottom: 3px"
                                >&times;</span
                                >
                            </button>
                            <a class="preview-link" target="_blank" href="#">
                            <img class="preview-img"
                            src="{{ url('assets/img/salelogo.png') }}"
                            width="100%"
                            style="border-radius: 10px; margin-top: 16px"
                            />
                            <h3 class="preview-title">
                            Lorem Ipsum is simply dummy text of the printing and typesetting
                            industry.
                            </h3>
                            <p class="mt-3 preview-description" style="color: #00000066; font-size: 18px">
                            It is a long established fact that a reader will be distracted by
                            the readable content of a page when looking at its layout. The
                            point of using Lorem Ipsum is that it has a more-or-less normal
                            distribution of letters, as opposed.
                            </p>
                        </a>                     
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>
    <!-- Preview modal ends -->

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\AdvertisementRequest', '#advertisementForm'); !!}
<script>
$('#url').keyup(function(e) {
    let url = $(this).val();
    let lw = url.toLowerCase();
    $(this).val(lw);
});

$('.preview-btn').on('click', function() {
    let ad_img = $('.cropped_image').attr('src');
    // alert(ad_img);
    if((ad_img == "") || ($('.ad_title').val() == "") || ($('.ad_descripton').val() == "") || ($('.ad_url').val() == "")) {
        $('.submit-btn').trigger('click');
        return false;
    }
    $('.preview-img').attr('src', ad_img);
    $('.preview-title').text($('.ad_title').val());
    $('.preview-description').text($('.ad_descripton').val());
    $('.preview-link').attr('href', $('.ad_url').val());

    $('#previewModal').modal({
        backdrop: 'static',
        keyboard: false
    });
});


var loadCategoryPic = function(event) {
    var output = document.getElementById('cp_preview');
    output.src = URL.createObjectURL(event.target.files[0]);
    output.onload = function() {
        URL.revokeObjectURL(output.src); // free memory
    }
};

let $modal = $('#modal');
let image = document.getElementById('image');
let cropper;
$(document).on("change", ".image", function(e){

    let files = e.target.files;
    let done = function (url) {
        image.src = url;
        $modal.modal({
            backdrop: 'static',
            keyboard: false
        });
    };
    let reader;
    let file;
    let url;
    if (files && files.length > 0) {
        file = files[0];
        if (URL) {
            done(URL.createObjectURL(file));
        } else if (FileReader) {
            reader = new FileReader();
                reader.onload = function (e) {
                done(reader.result);
            };
            reader.readAsDataURL(file);
        }
    }
});
$modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 3,
        preview: '.preview'
    });
}).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    });

    $("#crop").click(function(){
            canvas = cropper.getCroppedCanvas({
            width: 160,
            height: 160,
        });
    canvas.toBlob(function(blob) {
        url = URL.createObjectURL(blob);
        let reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function() {
            let base64data = reader.result;
            $('#cropped_image_name').val(base64data);
            var output = document.getElementById('cropped_image');
            output.src = url;
            $('.cropped_image').removeClass('hide');
            $modal.modal('hide');
        }
    });
});
$(document).on('click', '.crop-cancel', function() {
    $('.image').val('');
});

</script>

<script>
function fillInAddress() {
    var place = autocomplete.getPlace();
    $('#lat').val(place.geometry.location.lat());
    $('#long').val(place.geometry.location.lng());
    
}
function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'));
    autocomplete.addListener('place_changed', fillInAddress);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9_aECcOQYBXTqJdeff9N4P87jrkL5tY8&callback=initAutocomplete&libraries=geometry,places"></script>
@endsection
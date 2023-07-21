@extends('layouts.load')

@section('content')
            <div class="content-area">

              <div class="add-product-content">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">

                      <form id="geniusformdata" action="{{route('admin-category-store')}}" method="POST" enctype="multipart/form-data">
                        @include('includes.admin.form-error') 
                        {{csrf_field()}}
                     
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{__('Title')}} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-7">
                          <input type="text" class="input-field" name="title"  value="" required="" placeholder="{{__('Enter Title')}}">
                          </div>
                        </div>
                    
                        <div class="row">
                         <div class="col-lg-4">
                           <div class="left-area">
                              <h4 class="heading">{{__('Slug')}} *</h4>
                            </div>
                         </div>
                         <div class="col-lg-7">
                            <input type="text" class="input-field" name="slug" value="" required="" placeholder="{{__('Enter Slug')}}">
                         </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{__('Set Image')}} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-7">
                            <div class="img-upload">
                                <div id="image-preview" class="img-preview" style="background: url({{ asset('assets/images/noimage.png') }});">
                                    <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{__('Upload Image')}}</label>
                                    <input type="file" name="image" class="img-upload" id="image-upload">
                                  </div>
                                  <p class="text">{{__('Prefered Size')}}: {{__('(600x600) or Square Sized Image')}}</p>
                            </div>

                          </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                              
                            </div>
                          </div>
                          <div class="col-lg-7">
                            <button class="addProductSubmit-btn" type="submit">{{__('Create Category')}}</button>
                          </div>
                        </div>
                      </form>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

@endsection
@extends('layouts.admin')

@section('content')

<div class="content-area">
              <div class="mr-breadcrumb">
                <div class="row">
                  <div class="col-lg-12">
                      <h4 class="heading">{{__('Email Configuration')}}</h4>
                    <ul class="links">
                      <li>
                        <a href="{{ route('admin.dashboard') }}">{{__('Dashboard')}} </a>
                      </li>
                      <li>
                        <a href="javascript:;">{{__('Email Settings')}}</a>
                      </li>
                      <li>
                        <a href="{{ route('admin-mail-config') }}">{{__('Email Configuration')}}</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="add-product-content">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                        <form action="{{ route('admin-gs-update') }}" id="geniusform" method="POST" enctype="multipart/form-data">
                          {{ csrf_field() }}

                        @include('includes.admin.form-both')  

                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    {{__('SMTP')}}
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select email_is_type droplinks {{ $gs->is_smtp == 1 ? 'drop-success' : 'drop-danger' }}">
                                      <option data-val="1" value="{{route('admin-gs-issmtp',1)}}" {{ $gs->is_smtp == 1 ? 'selected' : '' }}>Activated</option>
                                      <option data-val="0" value="{{route('admin-gs-issmtp',0)}}" {{ $gs->is_smtp == 0 ? 'selected' : '' }}>Deactivated</option>
                                    </select>
                                  </div>
                            </div>
                          </div>
                       
                          <div id="email__show__box" class="{{$gs->is_smtp == 0 ? 'd-none' : ''}}">

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('SMTP Host')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="SMTP Host" name="smtp_host" value="{{ $gs->smtp_host }}" required="">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('SMTP Port')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{__('SMTP Port')}} " name="smtp_port" value="{{ $gs->smtp_port }}" required="">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('SMTP Username')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{__('SMTP Username')}} " name="smtp_user" value="{{ $gs->smtp_user }}" required="">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('SMTP Password')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{__('SMTP Password')}} " name="smtp_pass" value="{{ $gs->smtp_pass }}" required="">
                          </div>
                        </div>
                      </div>
                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('From Email')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{__('From Email')}} " name="from_email" value="{{ $gs->from_email }}" required="">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{__('From Name')}} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{__('From Name')}} " name="from_name" value="{{ $gs->from_name }}" required="">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                              
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <button class="addProductSubmit-btn" type="submit">{{__('Submit')}}</button>
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

@section('scripts')
<script>
  $(document).on('change','.email_is_type',function(){
    let Type = $('option:selected', this).attr('data-val');
    if(Type ==1){
      $('#email__show__box').removeClass('d-none');
    }else{
      $('#email__show__box').addClass('d-none');
    }
  })
</script>
@endsection
{{-- Change Password Modal --}}
<div class="modal fade" id="modal_change-password" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-width">
        <div class="modal-content modal-wrapper">
            <!-- Modal body -->
            <div class="modal-body p-0">
                <div class="modal-wrapper-inner">
                    <div class="row">
                        <div class="col-xl-6 col-md-5 d-none d-lg-block bg-grey px-0">
                            <div class="modal-left-wrapper">
                            <div class="popup-logo">
                                <img class="img-fluid" src="{{ asset('images/channel-fight.png') }}" alt="logo">
                            </div>
                            <div class="popup-banner"></div>
                            <div class="popup-txt-wrapper">
                                <h4 class="popup-title">WHAT'S THE FIRST RULE OF CHANNEL FIGHT?</h4>
                                <p>There are none.<br><br>
                                Watch 1000's of titles anywhere, at any time on your TV, computer, phone or tablet. From martial arts classics to sports and big blockbusters fresh from overseas with more being added all the time.<br><br>
                                Register now to watch instantly.</p>
                            </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-7 col-md-12 col-sm-12">
                            <div class="modal-form-wrapper bg-dark">
                                <div class="">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Change Password</h4>
                                    
                                    {!!Form::open(['url' => route('changePassword'), 'method' => 'post','novalidate'=>'novalidate','id'=>"frm_change_password"]) !!}     
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                {!! Form::password('old_password',['class' => 'form-control', 'data-required'=>1, 'id'=>'old_password' ]) !!}

                                                <label for="old_password" class="label-float">Old Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                
                                                {!! Form::password('change_password',['class' => 'form-control', 'data-required'=>1, 'id'=>'change_password' ]) !!}    

                                                <label for="change_password" class="label-float">New Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                               
                                                {!! Form::password('change_confirm_password',['class' => 'form-control', 'data-required'=>1, 'id'=>'change_confirm_password' ]) !!} 

                                                <label for="change_confirm_password" class="label-float">Confirm Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                            <div class="input-group">
                                               
                                                {!! Form::submit('Proceed', 
                                                     [
                                                     'class' => 'btn btn-solid btn-lg btn-block', 
                                                     'id'=>'btn_change_password'
                                                 ])  !!}

                                            </div>
                                        </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Change Password Modal --}}
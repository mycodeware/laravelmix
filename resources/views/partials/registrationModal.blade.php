{{-- Registraion Modal --}}
<div class="modal fade" id="modal_registration" tabindex="-1">
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
                                <div class="reg-profile-step-1" > 
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Register</h4>
                                   
                                     {!!Form::open(['url' => route('memberRegistration'), 'method' => 'post','novalidate'=>'novalidate','id'=>"frm_register_user"]) !!}   
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                
                                                 {!! Form::text('reg_full_name',null, ['class' => 'form-control','data-required'=>1,'id'=>'reg_full_name'])  !!} 
                                                <label for="reg_full_name" class="label-float">Full Name</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                
                                                {!! Form::email('reg_email',null,['class' => 'form-control', 'data-required'=>1, 'id'=>'reg_email']) !!}
                                                <label for="reg_email" class="label-float">Email</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                
                                                {!! Form::email('reg_confirm_email',null,['class' => 'form-control', 'data-required'=>1, 'id'=>'reg_confirm_email' ]) !!}
                                                <label for="reg_confirm_email" class="label-float">Confirm Email</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                {!! Form::password('reg_password',['class' => 'form-control', 'data-required'=>1, 'id'=>'reg_password' ]) !!}
                                                <label for="reg_password" class="label-float">Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                {!! Form::password('reg_confirm_password',['class' => 'form-control', 'data-required'=>1, 'id'=>'reg_confirm_password' ]) !!}
                                                <label for="reg_confirm_password" class="label-float">Confirm Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                             <span class="login-failed-error"></span>
                                            <div class="input-group">
                                                 {!! Form::submit('Register', 
                                                     [
                                                     'class' => 'btn btn-solid btn-lg btn-block', 
                                                     'id'=>'btn_register_member'
                                                 ])  !!}
                                            </div>
                                        </div>
                                     {!! Form::close() !!}
                                    <div class="form-bottom-wrapper">
                                        <div class="divider">or</div>
                                        <button type="submit" class="btn btn-facebook btn-lg btn-block"><i class="fa fab fa-facebook-f"></i>Register with Facebook</button>
                                        <p class="txt-terms">By signing in you agree to our <a href="javascript:void(0);">Terms of use</a></p>
                                        <a href="javascript:void(0);" class="txt-link d-block" data-toggle="modal" data-target="#modal_login" data-dismiss="modal">Already have an Account?</a>
                                    </div>
                                </div>

                                <div class="reg-profile-step-2" style="display: none">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Email Verification</h4>
                                    <p class="modal-txt-msg">We have sent a Code on <span id="show_reg_email"></span></p>
                                        
                                        {!!Form::open(['url' => route('emailVerification'), 'method' => 'post', 'novalidate'=>'novalidate','id'=>"frm_reg_verify_email"]) !!}   
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                
                                                {!! Form::number('verify_reg_email_otp',null,['class' => 'form-control', 'data-required'=>1, 'id'=>'verify_reg_email_otp'    ]) !!}

                                                {!! Form::hidden('verify_reg_email',null,['class' => 'form-control verify_email', 'data-required'=>1, 'id'=>'verify_reg_email'    ]) !!}

                                                <label for="verify_email_otp" class="label-float">Code</label>
                                                <a href="javascript:void(0);" class="txt-link btn-resend-otp" id="btn_resend_otp">Resend Code</a>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                             <span class="login-failed-error"></span>
                                            <div class="input-group">
                                               

                                                {!! Form::submit('Proceed', 
                                                     [
                                                     'class' => 'btn btn-solid btn-lg btn-block', 
                                                     'id'=>'btn_reg_verify_email'
                                                 ])  !!}

                                            </div>
                                        </div>
                                    {!! Form::close() !!}
                                    <div class="form-bottom-wrapper">
                                        <div class="lnk-group">
                                            <a href="javascript:void(0);" class="txt-link mt-4" id="btn_profile_change_email">Change Email</a>
                                            <a href="javascript:void(0);" class="txt-link mt-4" data-toggle="modal" data-target="#modal_login" data-dismiss="modal">Go back to Login</a>
                                        </div>
                                    </div>
                                </div>
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Registraion Modal --}}
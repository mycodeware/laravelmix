{{-- Forget Password Modal --}}
<div class="modal fade" id="modal_forget-password" tabindex="-1">
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
                                <div class="reset-step-1">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">FORGOT PASSWORD?</h4>
                                    <form method="POST" name="frm_forget_password" id="frm_forget_password" novalidate="novalidate">
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="email" class="form-control" name="forget_email" id="forget_email">
                                                <label for="forget_email" class="label-float">Email</label>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                            <div class="input-group">
                                                <button type="submit" class="btn btn-solid btn-lg btn-block" name="btn_reset_password" id="btn_reset_password">Reset</button>
                                            </div>
                                            </div>
                                        </form>
                                        <div class="form-bottom-wrapper">
                                            <div class="lnk-group">
                                                <a href="javascript:void(0);" class="txt-link mt-4" data-toggle="modal" data-target="#modal_login" data-dismiss="modal">Login</a>
                                                <a href="javascript:void(0);" class="txt-link mt-4" data-toggle="modal" data-target="#modal_registration" data-dismiss="modal">Register an Account</a>
                                            </div>
                                    </div>
                                </div>
                                <div class="reset-step-2">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Set New Password</h4>
                                    <p class="modal-txt-msg">We have sent a Code on steve.robinson@mail.com</p>
                                    <form method="POST" name="frm_set_password" id="frm_set_password" novalidate="novalidate">
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="reset_otp" id="reset_otp">
                                                <label for="reset_otp" class="label-float">Code</label>
                                                <a href="javascript:void(0);" class="txt-link btn-resend-otp" id="btn_resend_otp">Resend Code</a>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="reset_password" id="reset_password">
                                                <label for="reset_password" class="label-float">Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="reset_confirm_password" id="reset_confirm_password">
                                                <label for="reset_confirm_password" class="label-float">Confiem Password</label>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                            <div class="input-group">
                                                <button type="submit" class="btn btn-solid btn-lg btn-block" name="btn_set_password" id="btn_set_password">Proceed</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="form-bottom-wrapper">
                                        <div class="lnk-group">
                                            <a href="javascript:void(0);" class="txt-link mt-4" id="btn_change_email">Change Email</a>
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
{{-- Forget Password Modal --}}
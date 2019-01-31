
 

{{-- Profile Details Modal --}}
<div class="modal fade" id="modal_profile_details" tabindex="-1">
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
                                <div class="profile-step-1">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Profile Details</h4>
                                    <form method="POST" name="frm_profile_details" id="frm_profile_details" novalidate="novalidate">
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="profile_details_name" id="profile_details_name">
                                                <label for="profile_details_name" class="label-float">Full Name</label>
                                            </div>
                                        </div>
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="email" class="form-control" name="profile_details_email" id="profile_details_email">
                                                <label for="profile_details_email" class="label-float">Email</label>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                            <div class="input-group">
                                                @if (false)
                                                    <button type="submit" class="btn btn-solid btn-lg btn-block" name="btn_save_profile" id="btn_save_profile">Save</button>
                                                @else
                                                    <button type="submit" class="btn btn-solid btn-lg btn-block" name="btn_verify_profile_email" id="btn_verify_profile_email">Proceed</button>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="profile-step-2">
                                    <button type="button" class="close" data-dismiss="modal">
                                        <svg viewBox="0 0 24 24" role="img" aria-label="Close" focusable="false" style="height: 100%; width: 100%;"><path d="m23.25 24c-.19 0-.38-.07-.53-.22l-10.72-10.72-10.72 10.72c-.29.29-.77.29-1.06 0s-.29-.77 0-1.06l10.72-10.72-10.72-10.72c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l10.72 10.72 10.72-10.72c.29-.29.77-.29 1.06 0s .29.77 0 1.06l-10.72 10.72 10.72 10.72c.29.29.29.77 0 1.06-.15.15-.34.22-.53.22" fill-rule="evenodd"></path></svg>
                                    </button>
                                    <h4 class="modal-title">Email Verification</h4>
                                    <p class="modal-txt-msg">We have sent a Code on steve.robinson@mail.com</p>
                                    <form method="POST" name="frm_verify_email" id="frm_verify_email" novalidate="novalidate">
                                        <div class="form-group float-label-control">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="verify_email_otp" id="verify_email_otp">
                                                <label for="verify_email_otp" class="label-float">Code</label>
                                                <a href="javascript:void(0);" class="txt-link btn-resend-otp" id="btn_resend_otp">Resend Code</a>
                                            </div>
                                        </div>
                                        <div class="form-group m-0">
                                            <div class="input-group">
                                                <button type="submit" class="btn btn-solid btn-lg btn-block" name="btn_verify_email" id="btn_verify_email">Proceed</button>
                                            </div>
                                        </div>
                                    </form>
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
{{-- Profile Details Modal --}}
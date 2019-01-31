import {hideShow} from './app';

 /* @method : register
    * @param : full name,email,confirm email,password,confirm password
    * Response : json
    * Return : sucess or error message and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID)  
    */
//code for new user registration form validation and submission    
$("#frm_register_user").validate({
    rules: {
        reg_full_name: {
            required: true,
            minlength: 3,
            maxlength: 20
        },
        reg_email: {
            required:true,
            email: true
        },
        reg_confirm_email: {
            required:true,
            email: true,
            equalTo: validSameConfirmEmail
        },
        reg_password: { 
            required: true,
            minlength: 8
        },
        reg_confirm_password: {
            required: true,
            equalTo: "#reg_password"
        }
    },

    /*messages: {
        reg_full_name: 'Minimum three character required!',

        reg_email: 'Please put valid email address',
        reg_confirm_email: 'confirm email must be same as email',

        reg_password: 'Minimum 8 digit required',

        reg_confirm_password: 'confirm password must be same as password'
    }, */


    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_register_user').attr('action');
        event.preventDefault();  
         var email = $('#reg_email').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.login-failed-error').html("");
        axios({
                method: 'post',
                url: action,
                data:  $('#frm_register_user').serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                validateStatus: function (status) {
                    return status = 200; // default
                },  
            })
          .then(function (response) { 
              //console.log(response.data.code);
               if(response.data.code===200){
                    hideShow("reg-profile-step-1","reg-profile-step-2");
                   $("#show_reg_email").html(email);
                   $(".verify_email").val(email);
               }
               else{
                   $('.login-failed-error').html(response.data.message).css('color','#fff');
                }
          })
          .catch(function (error) {
            console.log(error);
        });


        // $.ajax({
        //     type: "POST", 
        //     data:  $('#frm_register_user').serialize(),
        //     url: action,
        //     beforeSend: function() {
        //        $('#btn_register_member').html('Processing');
        //     },
        //     success: function(response) { 
        //     if(response.code==200)
        //         {
        //            hideShow("reg-profile-step-1","reg-profile-step-2");
        //            $("#show_reg_email").html(email);
        //            $(".verify_email").val(email);
                    
        //         }else
        //         {
        //            $('.login-failed-error').html(response.message).css('color','#fff');
        //         }
        //     }
        // }); 
        // return false;
    }
});

/* @method : validSameConfirmEmail 
    * @param : email,confirm email
    * Response : flag
    * Return : true or false
    * Author : Kumood Bongale
    * Calling Method : form validation function calls for comparing email and confirm email inside rules  
    */
//function to chekc email and confirm email are same or not.

function validSameConfirmEmail()
{
    
    if($("#reg_email").val() != $("#reg_confirm_email").val())
        {
               return false
        }
}

/* @method : validSameConfirmEmail 
    * @param : email,confirm email ID
    * Response : json
    * Return : true or false
    * Author : Kumood Bongale
    * Calling Method : form validation function calls for comparing email and confirm email inside rules  
    */
//code for user login form validation and submission
$("#frm_login_user").validate({
    rules: {
        email: {
            required: true,
            email: true
        },
        password: { 
            required: true
        }
    },

    messages: {
        email: {},

        password: {}
    },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_login_user').attr('action');

        event.preventDefault();  

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            data:  $('#frm_login_user').serialize(),
            url: action,
            beforeSend: function() {
               $('#btn_login_user').html('Processing');
            },
            success: function(response) { 
                console.log(response);
              //  return false;
            if(response.code==200)
                {
                   //location.reload();
                   window.location.href = "/";
                }else
                {
                    $('.login-failed-error').html(response.message).css('color','#fff');
                }
            }
        }); 
        return false;
    }
});

/* @method : forget password 
    * @param : email
    * Response : json
    * Return : messages and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID) 
    */

//code for Forget password form validation and submission
$("#frm_forget_password").validate({
    rules: {
        forget_email: {
            required: true,
            email: true
        }
    },

    messages: {
        forget_email: {}
    },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_forget_password').attr('action');
        event.preventDefault();  
         var email = $('#forget_email').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

         $.ajax({
                 type: "POST",
                 url: action,
                 dataType:'json',
                 data: {email:email},
                beforeSend: function() {
                   $('#btn_reset_password').html('Processing');
                },
                 success: function( data ) {
                    console.log(data);
                     if(data.code == 200){


                            $(".reset-step-2").show();
                            $(".reset-step-1").hide();
                            $(".verify_email").val(email);
                            $("#show_forgot_email").html(email);
                        }
                         else
                        {
                            $('.login-failed-error').html(data.message).css('color','#fff');
                        } 
                    }
             });
        return false;
    }

});

/* @method : to set new password 
    * @param : OTP, password, Confirm password
    * Response : json
    * Return : messages and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID) 
    */

//code for set new password form validation and submission
$("#frm_set_password").validate({ 
    rules: {
        reset_otp: {
            required: true
        },
        reset_password: {
            passwordmethod: true,
            required: true,
            minlength: 8
        },
        reset_confirm_password: {
            equalTo: "#reset_password",
            required: true
        }
    },

    messages: {
        forget_email: {}
    },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_set_password').attr('action');
        event.preventDefault();  
         var email = $('#forget_email').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

         $.ajax({
                type: "POST",
                url: action,
                dataType:'json',
                data: $('#frm_set_password').serialize(),
                beforeSend: function() {
                   $('#btn_set_password').html('Processing');
                },
                 success: function( data ) {
                    console.log(data);
                     if(data.code == 200){
                           //console.log(data);
                            $(".reset-step-2").show();
                            $(".reset-step-1").hide();
                            $("#modal_forget-password").modal('hide');
                        }
                         else
                        {
                            $('.login-failed-error').html(data.message).css('color','#fff');
                        } 
                    }
             });
        return false;
    }

});



/* @method : to change password after loggin 
    * @param : password, Confirm password
    * Response : json
    * Return : messages and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID) 
    */

//code for set new password form validation and submission
$("#frm_change_password").validate({ 
    rules: {
        old_password: {
            required: true
        },
        change_password: { 
            required: true,
            minlength: 8
        },
        change_confirm_password: {
            equalTo: "#change_password",
            required: true
        }
    },

    messages: {
        change_password: {}
    },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_change_password').attr('action');
        event.preventDefault();  
         //var email = $('#forget_email').val();

        axios({
              method: 'post',
              url: action,
              data:  $('#frm_change_password').serialize(),
              headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
          .then(function (response) {

              $('#modal_change-password').modal('hide');
          })
          .catch(function (error) {
            console.log(error);
        });
    }

});


$("#frm_profile_details").validate({
    rules: {
        profile_details_name: {
            required: true
        },
        profile_details_email: {
            required: true,
            email: true
        }
    },

    messages: {
        profile_details_name: {},
        profile_details_email: {}
    },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
    submitHandler: function(form, event) {
        // Prevent form submission
        event.preventDefault();
        $("#btn_verify_profile_email").attr("disabled", "disabled");
        hideShow("profile-step-1", "profile-step-2");
        return false;
    }
});

/* @method : to user email verification 
    * @param : OTP
    * Response : json
    * Return : messages and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID) 
    */
//code for verify user email address
$("#frm_reg_verify_email").validate({
    rules: {
        verify_reg_email_otp: {
            required: true
        }
    },

    // messages: {
    //     profile_details_name: {},
    //     profile_details_email: {}
    // },
    errorPlacement: function(label, element) {
        if (label.text() !== "") {
            label.insertAfter($(element).closest(".input-group"));
        }
    },
        submitHandler: function(form, event) {
        // Prevent form submission
        var action = $('#frm_reg_verify_email').attr('action');
        event.preventDefault();  
        var otp = $('#verify_reg_email_otp').val();
        var email = $('#reg_email').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

         $.ajax({
                 type: "POST",
                 url: action,
                 dataType:'json',
                 data: {otp:otp, email:email},
                beforeSend: function() {
                   $('#btn_reg_verify_email').html('Processing');
                },
                 success: function( data ) {
                    console.log(data);
                     if(data.code == 200){
                            //console.log(data);
                            //$(".reg-profile-step-3").show();
                            $("#modal_registration").modal('hide');
                            $("#modal_login").modal('show');

                         }
                         else
                        {
                            $('.login-failed-error').html(data.message).css('color','#fff');
                        } 
                    }
             });
        return false;
    }
});

// Validate forms

/* @method : to resend OTP on user email ID 
    * @param : email ID
    * Response : json
    * Return : messages and status code
    * Author : Kumood Bongale
    * Calling Method : On submit calls(Ajax function by form ID) 
    */
// code start for resend  OTP code on user email

$(document).ready(function() {
    $('a#btn_resend_otp').on('click', function(e) {
        
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var base_url = window.location.origin;
                    
         e.preventDefault(); 
         
         var email = $('.verify_email').val();
        // alert(email);
             $.ajax({
             type: "POST",
             url: base_url+'/member/generateOtp',
             data: {email:email},
             success: function( data ) {
                console.log(data);
                 if(data.code == 200){
                        //console.log(data);
                       //$('.login-failed-error').html(data.message).css('color','#fff');
                        
                       }
                    else
                    {
                        $('.login-failed-error').html(data.message).css('color','#fff');
                    }   
                }
         });


    });
});


   

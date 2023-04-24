(function($){
 $(document).ready(function(){
// alert("custom_user_login_js");


  // console.log($(".form-gp .form-item input").html());


  /*================================
    login form
    ==================================*/

  $('.form-gp .form-item input').on('focus', function() {
      $(this).parent('.form-gp .form-item').addClass('custom_focused');
  });
  $('.form-gp .form-item input').on('focusout', function() {
      if ($(this).val().length === 0) {
          $(this).parent('.form-gp .form-item').removeClass('custom_focused');
      }
  });

  $("#edit-mail").val('');

  $('#send-verification-code').on('click',function(){
    // alert("clicked");

    // var mail=$("#edit-mail").val();
    var mail=$("input#edit-mail").val();
    var mail_pattern =/\S+@\S+\.\S+/;
    var email_sequence=mail_pattern.test(mail);

    if(mail =='' || mail == null){
      $(".email-validation").html('<div class="invalid-feedback d-block" style="margin-top: -18px;">Please Enter Email Address.</div>');
      return false;
    }

    if(mail !='' && email_sequence == false){
      $(".email-validation").html('<div class="invalid-feedback d-block style="margin-top: -18px;">Please enter a valid email address.</div>');
      return false;
    }

    var jqxhr = $.post("/send_verification_code?_format=json", {mail:mail}, function() {
      alert( "success" );
    })
      .done(function(result) {
        console.log(result);
        alert( "second success" );

        $(".email-validation").html("");

        $("input#edit-mail").attr("readonly",true);

        $("#send-verification-code").addClass("d-none");
        $("#edit-mail-btn").removeClass("d-none");

        $("#verify_code_field").removeClass("d-none");
        $("#verify-code-btn").removeClass("d-none");


      })
      .fail(function() {
        alert( "error" );
      })
      .always(function() {
        alert( "finished" );
      });

    return false;


  });

  $("#edit-mail-btn").on("click",function(){

    alert("clicked");

    $("input#edit-mail").removeAttr("readonly");
    $( "#input#edit-mail" ).focus();

    $("#verify_code_field").addClass("d-none");
    $("#verify-code-btn").addClass("d-none");
    $("#edit-mail-btn").addClass("d-none");

    $("#send-verification-code").removeClass("d-none");

    return false;

  });


  $("#verify-code-btn").on("click",function(){

    var mail=$("input#edit-mail").val();
    var verification_code=$("input#verify-code-field").val();

    // Assign handlers immediately after making the request,
// and remember the jqxhr object for this request
var jqxhr = $.post( "/verify_code?_format=json",{mail:mail,verification_code:verification_code}, function() {
  alert( "success" );
})
  .done(function(result) {
    alert( "second success" );
    console.log(result);
    if(result.status=="1"){
      alert("if");

      $("#name-field").removeClass("d-none");
      $("#pass_field").removeClass("d-none");

      // $("#edit-pass").removeClass("d-none");



    }
  })
  .fail(function() {
    alert( "error" );
  })
  .always(function() {
    alert( "finished" );
  });

    return false;

  });




    /*================================
    login form
    ==================================*/
  //   $('.form-gp input').on('focus', function() {
  //     $(this).parent('.form-gp').addClass('focused');
  // });
  // $('.form-gp input').on('focusout', function() {
  //     if ($(this).val().length === 0) {
  //         $(this).parent('.form-gp').removeClass('focused');
  //     }
  // });

 });
})(jQuery);

/* (function ($, Drupal) { */

  // 'use strict';
  /* CODE GOES HERE */

/* })(jQuery, Drupal); */
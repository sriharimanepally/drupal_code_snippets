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

  // $('#verify-code-button').click(function(){
  //   console.log('button clicked');

  // });


  // var input = document.querySelector("#edit-phone");

  // var iti = window.intlTelInput(input, {
  //   nationalMode: true,
  //   separateDialCode: true,
  //   utilsScript: "../themes/srt_theme/intl-tel-input-master/build/js/utils.js" // just for formatting/placeholders etc
  // });

  // var handleChange = function() {
    // var text = (iti.isValidNumber()) ? "International: " + iti.getNumber() : "Please enter a number below";
    // var textNode = document.createTextNode(text);
    // console.log(text);
    // output.innerHTML = "";
    // output.appendChild(textNode);
  // };

  // listen to "keyup", but also "change" to update when the user selects a country
  // input.addEventListener('change', handleChange);
  // input.addEventListener('keyup', handleChange);

  // intlTelInput(input, {
  //   initialCountry: "auto",
  //   geoIpLookup: function(success, failure) {
  //     $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
  //       var countryCode = (resp && resp.country) ? resp.country : "";
  //       success(countryCode);
  //     });
  //   },
  // });

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

  'use strict';
  /* CODE GOES HERE */

/* })(jQuery, Drupal); */
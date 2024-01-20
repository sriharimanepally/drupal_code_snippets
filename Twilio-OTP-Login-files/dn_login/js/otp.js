/**
 * @file
 * Contains the Js code.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches timer div.
   */
  Drupal.behaviors.dn_loginJs = {
    attach: function (context, settings) {
      //--first hide resend markup which only will display when timer hit 
      $("#resend-span").hide();
      var timer2 = drupalSettings.initial_time;
      console.log("timer - initiate-"+timer2);
     var interval = setInterval(function() { // time count down-executes this function every second.
     // console.log("in intervel");

    var timer = timer2.split(':');
    //by parsing integer, I avoid all extra string processing
    var minutes = parseInt(timer[0], 10);
    var seconds = parseInt(timer[1], 10);
    --seconds;
    minutes = (seconds < 0) ? --minutes : minutes;
    if (minutes < 0) clearInterval(interval);
    seconds = (seconds < 0) ? 59 : seconds;
    seconds = (seconds < 10) ? '0' + seconds : seconds;
    if (minutes == 0 || minutes == '00' || minutes == '0') {   //====if minutes hits zero show the resend to link to the user.
      $("#resend-span").show();
      //======inject resend link to resend  span element
      var link_txt = $("#resend-span").html();
      $("#resend-span").html("<a href='javascript:void(0)' id='resend'>"+link_txt+"</a>");
      $("#resend-span").click(function(){
        window.location = window.location.href+'/resend';
      });


    }
    //console.log("timer---"+minutes + ':' + seconds);
    $('#time').html(minutes + ':' + seconds); //====update time in time span element.
    timer2 = minutes + ':' + seconds;
  }, 1000);


   //========handling resend element click
  $("#resend-span").click(function(){
    window.location = window.location.href+'/resend';
  });


    }
  };
})(jQuery, Drupal, drupalSettings);

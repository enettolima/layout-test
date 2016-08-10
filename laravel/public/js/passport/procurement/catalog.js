/*
 *
 *   INSPINIA - Responsive Admin Theme
 *   version 2.4
 *
 */


$(document).ready(function () {
  var pending_total = 0;
  var pending_count = 0;

  function updatependingstatus(){
    var percent = Math.round((pending_count * 100) / pending_total);
    $('#pendingstatus .stat-percent').html(percent+'%');
    $('#pendingstatus .progress-bar').css("width", percent+'%');
    if(percent == 100){
      $('#pendingstatus .label').html('Complete').removeClass('label-warning').addClass('label-default');
    }else{
      $('#pendingstatus .label').html('Pending').removeClass('label-default').addClass('label-pending');
    }
  }
  // create click event
  $('div[data-box]').each(function( index ){
    var target = $(this).attr('data-box');
    $(this).find('span.prod-form').click(function () {
      if ($(this).hasClass("label-primary")) {
        pending_count -= 1;
        $('div[data-box="'+target+'"] form').find("input").prop( "disabled", false );
        $('div[data-box="'+target+'"] form').find("textarea").prop( "disabled", false );
        $('div[data-box="'+target+'"] form').find("select").prop( "disabled", false );
        $(this).removeClass('label-primary').addClass('label-default');
        //$(this).find('i').removeClass('fa-check').addClass('fa-uncheck');
      }else{
        pending_count += 1;
        $('div[data-box="'+target+'"] form').find("input").prop( "disabled", true );
        $('div[data-box="'+target+'"] form').find("textarea").prop( "disabled", true );
        $('div[data-box="'+target+'"] form').find("select").prop( "disabled", true );
        $(this).removeClass('label-default').addClass('label-primary');
        //$(this).find('i').removeClass('fa-check').addClass('fa-check');
      }
      updatependingstatus();
    });
  });

  //check on load form
  $('div[data-box]').each(function( index ){
    pending_total += 1;
    var target = $(this).attr('data-box');
    if ($(this).find(".label-primary").length) {
        pending_count += 1;
        $('div[data-box="'+target+'"] form').find("input").prop( "disabled", true );
        $('div[data-box="'+target+'"] form').find("textarea").prop( "disabled", true );
        $('div[data-box="'+target+'"] form').find("select").prop( "disabled", true );
    }else{
        $('div[data-box="'+target+'"] form').find("input").prop( "disabled", false );
        $('div[data-box="'+target+'"] form').find("textarea").prop( "disabled", false );
        $('div[data-box="'+target+'"] form').find("select").prop( "disabled", false );
    }
  });

  updatependingstatus();

  $('.product-images').slick({
      dots: true
  });
  $('.footable').footable();
});

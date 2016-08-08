/*
 *
 *   INSPINIA - Responsive Admin Theme
 *   version 2.4
 *
 */


$(document).ready(function () {

  // Collapse ibox function
  $('.fa-check').click(function () {
    alert('click');
    var input = $(this).closest('.ibox');

    $('input', $(each)).each(function () {
      console.log($(this)); //log every element found to console output
    });
  });
  $('.product-images').slick({
      dots: true
  });
  $('.footable').footable();
});

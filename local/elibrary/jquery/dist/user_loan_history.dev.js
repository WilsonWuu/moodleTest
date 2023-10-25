"use strict";

$(function () {
  function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');

    for (var i = 0; i < sURLVariables.length; i++) {
      var sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] == sParam) {
        if (sParameterName[1] == undefined) {
          return true;
        }

        return sParameterName[1];
      }
    }
  }

  $('select#status_selector').change(function () {
    var hash = window.location.hash;
    var href = window.location.href.replace(hash, "");
    href = updateURLParameter(href, 'status', $(this).val());
    href += window.location.hash;
    window.location = href;
  });
  var status = getUrlParameter('status');

  if (status != undefined) {
    $('select#status_selector').val(status);
  }
});
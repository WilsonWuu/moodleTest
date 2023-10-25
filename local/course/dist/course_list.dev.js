"use strict";

$(function () {
  $('#viewtype_active').click(function () {
    window.location = M.cfg.wwwroot + '/local/course?viewtype=active';
  });
  $('#viewtype_archive').click(function () {
    window.location = M.cfg.wwwroot + '/local/course?viewtype=archive';
  });
});
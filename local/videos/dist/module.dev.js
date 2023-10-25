"use strict";

YUI().use("node-base", function (Y) {
  var cat_changed = function cat_changed(e) {
    document.forms['category_list'].submit();
  };

  Y.on('change', cat_changed, '#id_category');
});
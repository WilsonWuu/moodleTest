var fontsizestr = '';
var current_timestamp = Math.round(new Date().getTime() / 1000);

function getDevice() {
    var ua = navigator.userAgent;

    return ua.match(/iPad/i) || ua.match(/tablet/i) && !ua.match(/RX-34/i) || ua.match(/FOLIO/i) ? 'tablet' :
        ua.match(/Linux/i) && ua.match(/Android/i) && !ua.match(/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i) ? 'tablet' :
            ua.match(/Kindle/i) || ua.match(/Mac.OS/i) && ua.match(/Silk/i) ? 'tablet' :
                ua.match(/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i) || ua.match(/MB511/i) && ua.match(/RUTEM/i) ? 'tablet' :
                    ua.match(/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i) ? 'mobile' :
                        ua.match(/Opera/i) && ua.match(/Windows.NT.5/i) && ua.match(/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i) ? 'mobile' :
                            ua.match(/Windows.(NT|XP|ME|9)/) && !ua.match(/Phone/i) || ua.match(/Win(9|.9|NT)/i) ? 'desktop' :
                                ua.match(/Macintosh|PowerPC/i) && !ua.match(/Silk/i) ? 'desktop' :
                                    ua.match(/Linux/i) && ua.match(/X11/i) ? 'desktop' :
                                        ua.match(/Solaris|SunOS|BSD/i) ? 'desktop' :
                                            ua.match(/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i) && !ua.match(/Mobile/i) ? 'desktop' :
                                                'mobile';
}

function setCookie(c_name, value, expiredays) {
    var urls = M.cfg.wwwroot.split('/');
    var domain = urls[2].split(':')[0];
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) +
        ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString() + ";domain=" + domain + ";path=/");
}

function common_set_footer_position() {
    //alert("html height="+$('html').height()+ "window height="+$(window).height());
    if ($('html').height() < $(window).height()) {
        $('#page-footer').addClass("fixbottom");
    }
}

function updateURLParameter(url, param, paramVal) {
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var additionalURL = tempArray[1];
    var temp = "";
    if (additionalURL) {
        tempArray = additionalURL.split("&");
        for (i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
                newAdditionalURL += temp + tempArray[i];
                temp = "&";
            }
        }
    }

    var rows_txt = temp + "" + param;
    if (paramVal != undefined) {
        rows_txt += "=" + paramVal;
    }
    return baseURL + "?" + newAdditionalURL + rows_txt;
}

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

function AddURLParameter(url, param, value) {
    var hash = {};
    var parser = document.createElement('a');

    parser.href = url;

    var parameters = parser.search.split(/\?|&/);

    for (var i = 0; i < parameters.length; i++) {
        if (!parameters[i])
            continue;

        var ary = parameters[i].split('=');
        hash[ary[0]] = ary[1];
    }

    hash[param] = value;

    var list = [];
    Object.keys(hash).forEach(function (key) {
        list.push(key + '=' + hash[key]);
    });

    parser.search = '?' + list.join('&');
    return parser.href;
}
require(['jquery'], function ($) {

    $('form, input').prop('autocomplete', 'off');
    $(':input').on('focus', function () {
        $(this).prop('autocomplete', 'off');
    });

    //Added by Tai, update the page view duration time to DB
    $(window).on("visibilitychange", function () {
        var duration = (Math.round(new Date().getTime() / 1000)) - current_timestamp;
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/innoverz/lib/ajax/update_pageview_duration.php',
            async: false,
            data: "id=" + pvid + "&duration=" + duration, //pvid come from /lib/outputrenderers.php : 796
            success: function (result) {
                return true;
            }
        });
    });

    $("a[href*='admin/tool/uploaduser/index.php']").parent('li').hide();

    setInterval(function () {
        jQuery('.fp-linktype-2 input').prop("disabled", true);
        jQuery('.fp-linktype-4 input').prop("checked", true);
    }, 100);

    $('input#viewtype_archive, input#viewtype_active').click(function () {
        window.location.href = AddURLParameter(window.location.href, 'viewtype', $(this).val())
    });

    /**
     * start: adjust local course menu link styling and reactions
     **/
    $('[href^="' + M.cfg.wwwroot + '/course/index.php' + '"]').each(function () {
        var href = $(this).prop('href').replace('/course/index.php', '/local/course/index.php')
        $(this).prop('href', href);
    })
    $('[href^="' + M.cfg.wwwroot + '/course/edit.php' + '"]').each(function () {
        var href = $(this).prop('href').replace('/course/edit.php', '/local/course/edit.php')
        $(this).prop('href', href);
    })
    /**
     * end
     **/

    /** InterRAI START*/
    var IR_selectors = $('body.role-ir_trainer, body.role-ir_trainee, body.role-ir_assessor');

    if ($("body").hasClass("lang-zh_tw")){
        IR_selectors.find('.user-card .card-footer')
            .html('<a href="' + M.cfg.wwwroot + '/login/change_password.php?id=1">更改密碼</a>');
    } else if ($("body").hasClass("lang-zh_cn")){
        IR_selectors.find('.user-card .card-footer')
            .html('<a href="' + M.cfg.wwwroot + '/login/change_password.php?id=1">更改密码</a>');
    } else {
        IR_selectors.find('.user-card .card-footer')
            .html('<a href="' + M.cfg.wwwroot + '/login/change_password.php?id=1">Change password</a>');
    }

    IR_selectors.find('.navbar-brand').html('InterRAI').show();

    if ($("body").hasClass("role-ir_trainer") || $("body").hasClass("role-ir_trainee") || $("body").hasClass("role-ir_assessor")) {
        if (window.location.href.indexOf("user/profile.php") > -1) {
            window.location.hash = '#details';
        }
    }

    $(".video-js").on("contextmenu",function(){
       return false;
    }); 
    /** InterRAI END*/
});
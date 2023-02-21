(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.transit_items = {
        attach: function (context, settings) {
            //Adds toggle icon to submenu items
            $('.transit-submenu--link').prepend('<div class="open-submenu"><i class="fa fa-caret-right" aria-hidden="true"><span class="sr-only">Open menu</span></i></div>');
            //Handles the opening and closing of said submenu
            $('.transit-submenu--link').click(function () {
                $(this).toggleClass('open');
                $(this).next('.transit-submenu--desc').toggleClass('hidden-xs');
            });
            
            //adds bootstrap table classes to tables. Assists in bootstrap formatting inside wysiwyg.
            $(function () {
                $("table").addClass("scrollable table table-bordered table-sm table-striped");
            });

            //used with the YAMM Mega Menu.    
            $(function () {
                $(document).on('click', '.yamm .dropdown-menu', function (e) {
                    e.stopPropagation()
                })
            });
            //adds the "ride, learn, connect" class to pages, based on URL path.
            $(function () {
                var loc = window.location.href; // returns the full URL     
                if ((/business/.test(loc)) || (/employment/.test(loc)) || (/agency-resources/.test(loc))) {
                    $('.transit-banner').addClass('connect');
                }
                if ((/bus/.test(loc)) || (/vanpool/.test(loc)) || (/village-vans/.test(loc)) || (/carpool/.test(loc)) || (/community-vans/.test(loc)) || (/bike/.test(loc)) || (/dial-a-lift/.test(loc)) || (/fare/.test(loc))) {
                    $('.transit-banner').addClass('ride');
                }
                if (((/how-to-ride/.test(loc)) || (/news/.test(loc)) || (/alerts/.test(loc)) || (/youth/.test(loc)) || (/agency/.test(loc)) || (/transit-centers/.test(loc)) || (/park-and-ride-lots/.test(loc))) != (/agency-resources/.test(loc))) {
                    $('.transit-banner').addClass('learn');
                }
            });
        }
    }
})(jQuery, Drupal, drupalSettings);
//Creates a new Google Translate element
function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
(function ($) {
    $(window).bind("load", function () {
        //Adds title attributes to Google translate elements. Alsoprovides a trigger to send these events. 
        var trigger = '';
        $('iframe.goog-te-menu-frame.skiptranslate').attr({'title': 'Google Translate languages', 'tabindex': '-1'});
        $('a.goog-te-menu-value').attr('title', 'Select an alternate language using Google Translate');
        return trigger;
    });
})(jQuery);
// Declaring route variables
var startExample;
var endExample;
var zip;
// Get and parse the user's current date/time
var isPM = false;
var currentDate = "";
var amOption = '<option value="am">AM';
var pmOption = '<option value="pm">PM';
function buildURL() {
    var sample = document.getElementsByTagName("input");
    var sampletest = document.getElementsByTagName("Select");
    var ttype = document.getElementById("ttype");
    document.getElementById("dep").value = ttype.options[ttype.selectedIndex].value;
    var ttime = document.getElementById("ttime");
    var timeval = ttime.options[ttime.selectedIndex].value;
    var tminute = document.getElementById("tminute");
    var tminuteval = tminute.options[tminute.selectedIndex].value;
    var loc = '/bus/trip-planner?ie=UTF8&f=d&';
    for (var i = 0; (i <= sample.length - 1); i++) {
        if (sample[i].name == 'ampm')
            continue;
        if (sample[i].name == 'ttime')
            loc += sample[i].name + '=' + timeval + sampletest.ampm.value + '&';
        else {
            if (sample[i].name == 'saddr') {
                if (sample[i].value == '')
                    loc += sample[i].name + '=' + sample[i].value + +zip + '&';
                else
                    loc += sample[i].name + '=' + sample[i].value + '&';

            }
            if (sample[i].name == 'daddr')
                loc += sample[i].name + '=' + sample[i].value + '&';
            if (sample[i].name == 'dep') {
                loc += 'ttype' + '=' + sample[i].value + '&';
            }
            if (sample[i].name == 'fromdatepicker') {
                loc += 'date' + '=' + sample[i].value + '&';
            }
        }
    }
    loc += 'dirflg=r' + '&time=' + timeval + ":" + tminuteval + sampletest.ampm.value;
    location.href = loc;
}
function number() {
    var ttype = document.getElementById("ttype");
    document.getElementById("dep").value = ttype.options[ttype.selectedIndex].value;
}
function mailaddr() {
    var mailloc = "http://visitor.r20.constantcontact.com/d.jsp?m=1103014241540&p=oi&ea=";
    var ea = document.getElementById("ea");
    mailloc += ea.value;
    location.href = mailloc;
}
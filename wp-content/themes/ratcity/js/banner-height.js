(function($) {


    if( $('body').hasClass('banner-full-height') ) {

        var setHeight = function() {

            var main_nav = $(".header"),
                page_header = $(".page-header"),
                nav_height = main_nav.height(),
                window_height = $(window).height();

            if ($('body').hasClass('logged-in')) {
                if ($(window).width() < 992) {
                    nav_height += 46;
                } else {
                    nav_height += 32;
                }
            }

            page_header.css('height', window_height - nav_height);

        }

        setHeight();

        $(window).resize(function() {
            if ($(window).width() >= 768) {
                setHeight();
            }
        });

    }


})(jQuery);
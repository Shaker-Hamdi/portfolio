$(document).ready(function() {

	(function() {
        var backToTop = $('a.backToTop');

        backToTop.on('click', function(event) {
            $('html, body').animate({
                scrollTop: 0
            }, 300);

            event.preventDefault();
        });

        $(window).on('scroll', function() {
            var self = $(this),
                height = self.height() / 8,
                top = self.scrollTop();

            if (top > height) {
                if (!backToTop.is(':visible')) {
                    backToTop.show();
                }
            } else {
                backToTop.hide();
            }
        });
    })(); //Back to top button

    (function() {
        $("nav.mainNav").singlePageNav({
            filter: ':not(.external)',
            updateHash: false
        });
    })(); //Navigation


    (function() {
        if ($("div.project a.more").length) {
            $("div.project a.more").magnificPopup({
                type: 'inline',
                preloader: false,
                focus: '#name',

                removalDelay: 500, //delay removal by X to allow out-animation

                // When elemened is focused, some mobile browsers in some cases zoom in
                // It looks not nice, so we disable it:
                callbacks: {
                    beforeOpen: function() {

                        if($(window).width() < 700) {
                            this.st.focus = false;
                        } else {
                            this.st.focus = '#name';
                        }

                        this.st.mainClass = this.st.el.attr('data-effect');
                    },
                    open: function() {
                        if($("ul.owlCarousel").length) {
                            var owl = $("ul.owlCarousel");

                            owl.owlCarousel({
                                loop: false,
                                items:1,
                                margin: 15,
                                responsiveClass: true,
                                dots: true,
                                nav: false,
                                autoHeight:false
                            });
                        }
                    },
                    afterClose: function() {
                        $("ul.owlCarousel").trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded');
                        $("ul.owlCarousel").find('.owl-stage-outer').children().unwrap();
                    }
                },

                midClick: true // allow opening popup on middle mouse click. Always set
            });
        }
    })(); //productLink

});

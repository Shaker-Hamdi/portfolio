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

});

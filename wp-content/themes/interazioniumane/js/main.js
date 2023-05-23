jQuery(document).ready(function () {

    AOS.init({
        easing: 'ease-in-out-sine',
        disable: function () {
            var maxWidth = 800;
            return window.innerWidth < maxWidth;
        }
    });


    // openpopup();
    // sliderCards();
    //isotopeExplore();
    // isotopeMagazine();
    // isotopeArchive();
    // hideMessage();
    // sliderReviews();
    // scrollTo();


    ///**** INTERAZIONI UMANE ******///
    stickyHeader();
    sliderSticky();
    openMenu();
    filterNav();
    showDownloads();
    downloadTab();
    footerMenu();
    faqs();
    sliderBooks();
    loginForms();
    openSubMenu();
    inputFile();
    //sliderRelatedBooks();
    makeFiles();
    changeAvatar();
    hideFromAccount();
    sliderPartner();
    closeInfo();
    openSearch();
    sliderRelated();
    infoIU();
    hideCheckout();


    // jQuery(window).resize(function() {
    // });

});


///**** INTERAZIONI UMANE ******///

function infoIU() {

    jQuery('.info_iu--button').on('click', function (e) {
        e.preventDefault();
        jQuery(this).toggleClass('active');
        jQuery('.info_iu').toggleClass('hide');
    });
}

function openSearch() {
    jQuery('.openSearch').on('click', function (e) {
        e.preventDefault();
        jQuery('.header--search').slideToggle();
    });
}

function closeInfo() {
    jQuery('.close-info').on('click', function () {
        jQuery('.old-users-info').fadeOut();
    });
}

function hideFromAccount() {
    var hideMe = jQuery('.woocommerce-flex .hide-from-account');
    if (hideMe.length) {
        hideMe.remove();
    }
}

function hideCheckout() {
    var checkItem = jQuery('.free-course-item');
    if (checkItem.length) {
        console.log('esiste');
        jQuery('.woocommerce-billing-fields').remove();
    }
}

function changeAvatar() {
    var form = jQuery('#general-user-avatar-form');
    var input = form.find('input[type=file]');
    var buttons = form.find('.current-avatar-buttons');

    input.change(function () {

        var fileName = input.val().split('\\').pop();

        if (fileName) {
            buttons.addClass('show-buttons');
            jQuery('<p class="current-avatar-file-name">' + fileName + '</p>').prependTo(buttons);
        }
    });

}

function makeFiles() {
    var ifFile = jQuery('.file-upload-type');
    var input = ifFile.find('input[type=file]');

    if (ifFile.length) {
        ifFile.addClass('file-area');
        input.wrap('<div class="wpcf7-form-control-wrap thats-file"></div>');
        jQuery('<div class="file-dummy"><div class="file-success">Fantastico, hai selezionato un file. Andiamo avanti!</div><div class="file-default">Seleziona un file</div></div>').appendTo(ifFile);

        input.change(function () {


            var messages = ifFile.find('.file-dummy');
            var fileName = input.val();

            if (fileName) { // returns true if the string is not empty
                messages.addClass('perfect');
            } else { // no file was selected
                messages.removeClass('perfect');
            }
        });
    }
}

function inputFile() {
    var formRow = jQuery('.file-area');

    formRow.each(function (index) {
        var input = jQuery(this).find('input[type=file]');
        var messages = jQuery(this).find('.file-dummy');

        jQuery(this).find('br').remove();
        jQuery(this).find('p').remove();

        input.change(function () {
            var fileName = input.val();

            if (fileName) { // returns true if the string is not empty
                messages.addClass('perfect');
            } else { // no file was selected
                messages.removeClass('perfect');
            }
        });

    });

    var checkboxGroup = jQuery('.checkbox-options input[type=checkbox]');

    checkboxGroup.each(function (index) {
        if (jQuery(this).is(':checked'))
            jQuery(this).closest('.control-label').addClass('selected');
        else {
            jQuery(this).closest('.control-label').removeClass('selected');
        }

        jQuery(this).on('change', function () {
            if (jQuery(this).is(':checked'))
                jQuery(this).closest('.control-label').addClass('selected');
            else {
                jQuery(this).closest('.control-label').removeClass('selected');
            }
        });

    });

    jQuery(".checkbox-form input[type=checkbox]").on('change', function () {
        if (jQuery(".checkbox-form input[type=checkbox]").is(':checked'))
            jQuery(this).closest('.checkbox-form').addClass('perfect');
        else {
            jQuery(this).closest('.checkbox-form').removeClass('perfect');
        }
    });

}

function openSubMenu() {
    var link = jQuery('.top-user__link');
    var menu = jQuery('.top-user__menu');

    if (window.screen.width > 640) {
        link.on('click', function (e) {
            e.preventDefault();
            jQuery(this).toggleClass('active');
            menu.toggleClass('active');
        });
    }
}

function loginForms() {
    var showLogin = jQuery('.show-login-form');
    var showRegister = jQuery('.show-register-form');
    var loginForm = jQuery('.check-login-form');
    var registerForm = jQuery('.check-register-form');

    showLogin.on('click', function (e) {
        e.preventDefault();
        loginForm.slideToggle();
        registerForm.slideToggle();
    });
    showRegister.on('click', function (e) {
        e.preventDefault();
        loginForm.slideToggle();
        registerForm.slideToggle();
    });

}

function faqs() {

    var faqTitle = jQuery('.faq__title');

    faqTitle.on('click', function (e) {
        e.preventDefault();
        var description = jQuery(this).next('.faq__description');
        var others = jQuery(this).closest('.faq__item').siblings();

        jQuery(this).find('.faq__icon').toggleClass('show-faq');
        others.find('.faq__icon').removeClass('show-faq');
        others.find('.faq__description').slideUp();
        description.slideToggle();
    });
    if (window.screen.width > 640) {
        jQuery('.faq-1 .faq__title').trigger("click");
    }
}

function downloadTab() {

    jQuery('.get-tab').on('click', function (e) {
        e.preventDefault();
        var menu = jQuery(this).attr("data-download");
        var popup = jQuery('.downloads__list[data-download="' + menu + '"]');

        jQuery(this).addClass('active');
        jQuery(this).siblings().removeClass('active');

        popup.siblings().slideUp();
        popup.slideDown();
    });

}


function showDownloads() {

    jQuery('.show-downloads-items').on('click', function () {
        var menu = jQuery(this).attr("data-download-item");
        var popup = jQuery('.user-profile__courses--downloads[data-download-item="' + menu + '"]');
        var container = jQuery(this).closest('.user-profile__courses--item');

        jQuery(this).toggleClass('active');
        jQuery(this).siblings('.show-downloads-items').removeClass('active');


        popup.siblings('.user-profile__courses--downloads').slideUp();
        popup.slideToggle();
        container.siblings('.user-profile__courses--item').removeClass('opened');
        container.addClass('opened');
    });

}

function stickyHeader() {
    var scrollTop = jQuery(this).scrollTop();
    var header = jQuery('.header');
    var headerH = header.outerHeight();
    var lastScrollTop = 0;
    var heroH = jQuery('.course__hero').outerHeight();

    jQuery(window).on('scroll', function () {
        var st = jQuery(this).scrollTop();

        if (jQuery(document).scrollTop() >= headerH) {
            if (st > lastScrollTop) {
                //scroll down
                jQuery('.header').addClass("sticky");
                jQuery('.header').addClass("sticky-hide");

                jQuery('body').addClass("im-here");
            } else {
                //scroll up
                jQuery('.header').removeClass("sticky-hide");

                jQuery('body').removeClass("im-here");
                jQuery('body').addClass('push-me');
            }
            lastScrollTop = st;
        } else {
            jQuery('.header').removeClass("sticky");
            jQuery('.header').removeClass("sticky-hide");
            jQuery('body').removeClass("push-me");
            jQuery('body').removeClass("im-here");
        }

    });
}

function filterNav() {
    var apply = jQuery('#apply-filters');
    var reset = jQuery("#refresh-filters");
    var select = jQuery('.filter--select');

    //var ajaxUrl = jQuery('#custom-ajax-url').html();
    select.on('change', function () {
        apply.trigger('click');
    });
    jQuery('#filter').submit(function () {
        var filter = jQuery('#filter');
        jQuery.ajax({
            url: filter.attr('action'),
            data: filter.serialize(), // form data
            type: filter.attr('method'), // POST
            beforeSend: function (xhr) {
                apply.text('Processing...'); // changing the button label
            },
            success: function (data) {
                apply.text('Apply filter'); // changing the button label back
                jQuery('#response').html(data); // insert data
            }
        });
        return false;
    });

    reset.click(function () {
        select.reset();
        return false;
    });

}

///**** INTERAZIONI UMANE ******///


function isotopeExplore() {
    //console.log('isotope');
    jQuery('.explore-isotope').masonry({
        // set itemSelector so .grid-sizer is not used in layout
        itemSelector: '.grid-item',
        // use element for option
        columnWidth: '.grid-sizer',
        percentPosition: true
    });
}

function isotopeMagazine() {
    //console.log('isotope');
    jQuery('.magazine--list').masonry({
        // set itemSelector so .grid-sizer is not used in layout
        itemSelector: '.grid-item',
        // use element for option
        columnWidth: '.grid-sizer',
        percentPosition: true
    });
}

function isotopeArchive() {
    //console.log('isotope');
    jQuery('.flat-cards .cards-list__container').masonry({
        // set itemSelector so .grid-sizer is not used in layout
        itemSelector: '.grid-item',
        // use element for option
        columnWidth: '.grid-sizer',
        percentPosition: true
    });
}


function hideMessage() {
    jQuery('.bp-messages').on('click', function () {
        jQuery(this).addClass('hide-message');
    });
}


function footerMenu() {
    if (window.screen.width < 641) {
        jQuery('.footer-menu--title').on('click', function () {
            jQuery(this).next('.footer-menu--list').slideToggle();
            jQuery(this).find('.footer-menu--title__icon').toggleClass('rotate');
            jQuery(this).closest('.footer-menu').siblings().find('.footer-menu--list').slideUp();
            jQuery(this).closest('.footer-menu').siblings().find('.footer-menu--title__icon').removeClass('rotate');
        });
    }
}


function openMenu() {
    jQuery('.hamburger-menu, .close-menu').on('click', function (e) {
        e.preventDefault();

        var menu = jQuery('.header__menu');
        var body = jQuery('body');

        menu.toggleClass('show-menu');
        body.toggleClass('fixed');
    });
}


function openpopup() {
    jQuery('.popup-toggle').on('click', function (e) {
        e.preventDefault();
        var link = jQuery(this).attr("data-popup");

        var popup = jQuery('.popup[data-popup="' + link + '"]');
        popup.toggleClass('is-visible');
    });
}


function sliderRelatedBooks() {

    var _carousel = jQuery(".books-related--slider");

    _carousel.slick({
        dots: false,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        centerMode: false,
        autoplay: true,
        autoplaySpeed: 2000,
        prevArrow: '<div class="slick-prev"><i class="icon-arrow-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="slick-next"><i class="icon-arrow-right" aria-hidden="true"></i></div>',
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
                dots: true,
                arrows: false,
            }
        },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }
        ]
    });

}

function sliderRelated() {

    var _carousel = jQuery(".teacher-courses--flex");

    _carousel.slick({
        dots: false,
        arrows: true,
        prevArrow: '<div class="slick-prev"><i class="icon-arrow-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="slick-next"><i class="icon-arrow-right" aria-hidden="true"></i></div>',
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        centerMode: false,
        adaptiveHeight: true,
        autoplay: false,
        autoplaySpeed: 3000,
        slidesToScroll: 1,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                dots: false,
                arrows: true
            }
        },
            {
                breakpoint: 600,
                settings: {
                    dots: true,
                    arrows: false,
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    dots: true,
                    arrows: false,
                    slidesToShow: 1
                }
            }
        ]
    });
}

function sliderBooks() {

    var _carousel = jQuery(".books-slider");

    _carousel.slick({
        dots: false,
        arrows: true,
        prevArrow: '<div class="slick-prev"><i class="icon-arrow-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="slick-next"><i class="icon-arrow-right" aria-hidden="true"></i></div>',
        infinite: true,
        speed: 300,
        slidesToShow: 4,
        centerMode: false,
        adaptiveHeight: true,
        autoplay: false,
        autoplaySpeed: 3000,
        slidesToScroll: 1,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                dots: false,
                arrows: true
            }
        },
            {
                breakpoint: 600,
                settings: {
                    dots: true,
                    arrows: false,
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    dots: true,
                    arrows: false,
                    slidesToShow: 1
                }
            }
        ]
    });
}

function sliderSticky() {

    var _owl = jQuery(".sticky-courses");

    _owl.slick({
        dots: true,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        autoplay: true,
        autoplaySpeed: 2500,
        centerMode: false,
        adaptiveHeight: true,
        prevArrow: '<div class="slick-prev"><i class="icon-arrow-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="slick-next"><i class="icon-arrow-right" aria-hidden="true"></i></div>',
    });
}

function sliderPartner() {

    var _owl = jQuery(".course-partner--slider");

    _owl.slick({
        dots: false,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 3,
        centerMode: false,
        adaptiveHeight: true,
        prevArrow: '<div class="slick-prev"><i class="icon-arrow-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="slick-next"><i class="icon-arrow-right" aria-hidden="true"></i></div>',
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2,
                dots: true,
                arrows: false,
            }
        },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true
                }
            }
        ]
    });
}

function scrollTo() {
    jQuery('.scroll-to').on('click', function (event) {
        var target = jQuery(this.getAttribute('href'));
        var scrollto = target.offset().top - 90

        if (target.length) {
            event.preventDefault();
            jQuery('html, body').stop().animate({
                scrollTop: scrollto
            }, 2000);
        }
    });
}

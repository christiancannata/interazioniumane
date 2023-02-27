jQuery(function($) {
  $('.misha_loadmore').click(function() {

    var button = $(this),
      data = {
        'action': 'loadmore',
        'query': misha_loadmore_params.posts,
        'page': misha_loadmore_params.current_page
      };

    $.ajax({ // you can also use $.post here
      url: misha_loadmore_params.ajaxurl, // AJAX handler
      data: data,
      type: 'POST',
      beforeSend: function(xhr) {
        button.html('<span class="button button-primary loading">Aspetta...</span>');
      },
      success: function(data) {
        if (data) {
          button.html('<span class="button button-primary">Ne voglio ancora</span>').before(data);
          misha_loadmore_params.current_page++;

          var $win = jQuery(window),
            $con = jQuery('.isotope-grid'),
            $imgs = jQuery('img.lazy');

          $imgs.show().lazyload({
            effect: "fadeIn",
            failure_limit: Math.max($imgs.length - 1, 0),
            load : function() {
              jQuery('.isotope-grid').isotope('layout');
            }
          });

          $con.imagesLoaded(function() {
            $con.isotope( 'reloadItems').isotope({
              itemSelector: '.the-post',
              percentPosition: true,
              layoutMode : 'masonry'
            });
          });

          if (misha_loadmore_params.current_page == misha_loadmore_params.max_page)
            button.remove(); // if last page, remove the button

          // you can also fire the "post-load" event here if you use a plugin that requires it
          // $( document.body ).trigger( 'post-load' );
        } else {
          button.remove(); // if no data, remove the button as well
        }
      }
    });
  });
});

const api_name_space = 'wpowp-api/action';

(function ($) {

  $('#wpowp-settings-form').on('submit', function () {

    $.ajax({
      url: wpApiSettings.root + api_name_space + '/save-settings',
      method: 'POST',
      data: { 'settings': JSON.stringify($('#wpowp-settings-form').serializeArray()) },
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }, 
      success: function (data) {        
        toastr.success(data.message,'',{"positionClass": "toast-bottom-right",});
      }
    }, function (data, status) {
      console.log(data);
    });

    return false;

  })


})(jQuery)


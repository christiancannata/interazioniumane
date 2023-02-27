'use strict';

(function () {
  var lb_toolbarActive = false;

  // CallBack function to add content to Classic MCE editor //
  window.wpcp_add_content_to_mce = function (content) {
    tinymce.activeEditor.execCommand('mceInsertContent', false, content);
    tinymce.activeEditor.windowManager.close();
    tinymce.activeEditor.focus();
  };

  tinymce.PluginManager.add('letsbox', function (ed, url) {


    var t = this;
    t.url = url;

    ed.addCommand('mceLetsBox', function (query) {
      ed.windowManager.open({
        file: ajaxurl + '?action=letsbox-getpopup&type=shortcodebuilder&' + query + '&callback=wpcp_add_content_to_mce',
        width: 1000,
        height: 600,
        inline: 1
      }, {
        plugin_url: url
      });
    });
    ed.addCommand('mceLetsBox_links', function () {
      ed.windowManager.open({
        file: ajaxurl + '?action=letsbox-getpopup&type=links&callback=wpcp_add_content_to_mce',
        width: 1000,
        height: 600,
        inline: 1
      }, {
        plugin_url: url
      });
    });
    ed.addCommand('mceLetsBox_embed', function () {
      ed.windowManager.open({
        file: ajaxurl + '?action=letsbox-getpopup&type=embedded&callback=wpcp_add_content_to_mce',
        width: 1000,
        height: 600,
        inline: 1
      }, {
        plugin_url: url
      });
    });
    ed.addButton('letsbox', {
      title: 'Lets-Box shortcode',
      image: url + '/../../css/images/box_logo.png',
      cmd: 'mceLetsBox'
    });
    ed.addButton('letsbox_links', {
      title: 'Lets-Box links',
      image: url + '/../../css/images/box_link.png',
      cmd: 'mceLetsBox_links'
    });
    ed.addButton('letsbox_embed', {
      title: 'Embed Files from your Box',
      image: url + '/../../css/images/box_embed.png',
      cmd: 'mceLetsBox_embed'
    });

    ed.on('mousedown', function (event) {
      if (ed.dom.getParent(event.target, '#wp-lb-toolbar')) {
        if (tinymce.Env.ie) {
          // Stop IE > 8 from making the wrapper resizable on mousedown
          event.preventDefault();
        }
      } else {
        removeLbToolbar(ed);
      }
    });

    ed.on('mouseup', function (event) {
      var image,
        node = event.target,
        dom = ed.dom;

      // Don't trigger on right-click
      if (event.button && event.button > 1) {
        return;
      }

      if (node.nodeName === 'DIV' && dom.getParent(node, '#wp-lb-toolbar')) {
        image = dom.select('img[data-wp-lb-select]')[0];

        if (image) {
          ed.selection.select(image);

          if (dom.hasClass(node, 'remove')) {
            removeLbToolbar(ed);
            removeLbImage(image, ed);
          } else if (dom.hasClass(node, 'edit')) {
            var shortcode = ed.selection.getContent();
            shortcode = shortcode.replace('</p>', '').replace('<p>', '').replace('[letsbox ', '').replace('"]', '');
            var query = encodeURIComponent(shortcode).split('%3D%22').join('=').split('%22%20').join('&');
            removeLbToolbar(ed);
            ed.execCommand('mceLetsBox', query);
          }
        }
      } else if (node.nodeName === 'IMG' && !ed.dom.getAttrib(node, 'data-wp-lb-select') && isLbPlaceholder(node, ed)) {
        addLbToolbar(node, ed);
      } else if (node.nodeName !== 'IMG') {
        removeLbToolbar(ed);
      }
    });

    ed.on('keydown', function (event) {
      var keyCode = event.keyCode
      // Key presses will replace the image so we need to remove the toolbar
      if (lb_toolbarActive) {
        if (event.ctrlKey || event.metaKey || event.altKey ||
          (keyCode < 48 && keyCode > 90) || keyCode > 186) {
          return;
        }

        removeLbToolbar(ed);
      }
    });

    ed.on('cut', function () {
      removeLbToolbar(ed);
    });

    ed.on('BeforeSetcontent', function (ed) {
      ed.content = do_lb_shortcode(ed.content, t.url);
    });
    ed.on('PostProcess', function (ed) {
      if (ed.get)
        ed.content = get_lb_shortcode(ed.content);
    });

  });

  function do_lb_shortcode(co, url) {
    return co.replace(/\[letsbox([^\]]*)\]/g, function (a, b) {
      return '<img src="' + url + '/../../css/images/transparant.png" class="wp_lb_shortcode mceItem" title="Lets-Box" data-mce-placeholder="1" data-code="' + toBinary(b) + '"/>';
    });
  }

  function get_lb_shortcode(co) {

    function getAttr(s, n) {
      n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
      return n ? n[1] : '';
    };

    return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function (a, im) {
      var cls = getAttr(im, 'class');

      if (cls.indexOf('wp_lb_shortcode') != -1)
        return '<p>[letsbox ' + tinymce.trim(fromBinary(getAttr(im, 'data-code'))) + ']</p>';

      return a;
    });
  }

  function removeLbImage(node, editor) {
    editor.dom.remove(node);
    removeLbToolbar(editor);
  }

  function addLbToolbar(node, editor) {
    var toolbarHtml, toolbar,
      dom = editor.dom;

    removeLbToolbar(editor);

    // Don't add to placeholders
    if (!node || node.nodeName !== 'IMG' || !isLbPlaceholder(node, editor)) {
      return;
    }

    dom.setAttrib(node, 'data-wp-lb-select', 1);

    toolbarHtml = '<div class="dashicons dashicons-edit edit" data-mce-bogus="1"></div>' +
      '<div class="dashicons dashicons-no-alt remove" data-mce-bogus="1"></div>';

    toolbar = dom.create('div', {
      'id': 'wp-lb-toolbar',
      'data-mce-bogus': '1',
      'contenteditable': false
    }, toolbarHtml);

    var parentDiv = node.parentNode;
    parentDiv.insertBefore(toolbar, node);

    lb_toolbarActive = true;
  }

  function removeLbToolbar(editor) {
    var toolbar = editor.dom.get('wp-lb-toolbar');

    if (toolbar) {
      editor.dom.remove(toolbar);
    }

    editor.dom.setAttrib(editor.dom.select('img[data-wp-lb-select]'), 'data-wp-lb-select', null);

    lb_toolbarActive = false;
  }

  function isLbPlaceholder(node, editor) {
    var dom = editor.dom;

    if (dom.hasClass(node, 'wp_lb_shortcode')) {

      return true;
    }

    return false;
  }
  function toBinary(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
      function toSolidBytes(match, p1) {
        return String.fromCharCode('0x' + p1);
      }));
  }

  function fromBinary(str) {
    return decodeURIComponent(atob(str).split('').map(function (c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
  }

})();
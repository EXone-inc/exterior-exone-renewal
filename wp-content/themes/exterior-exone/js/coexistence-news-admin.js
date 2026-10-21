(function ($) {
  'use strict';
  $(function () {
    $('.exone-news-select-image').on('click', function () {
      var box = $(this).closest('.exone-news-image');
      var frame = wp.media({title: 'お知らせの画像を選ぶ', button: {text: 'この画像を使う'}, library: {type: 'image'}, multiple: false});
      frame.on('select', function () {
        var item = frame.state().get('selection').first().toJSON();
        var url = item.sizes && item.sizes.medium ? item.sizes.medium.url : item.url;
        box.find('input[name="image"]').val(item.id);
        box.find('.exone-news-image-preview').empty().append($('<img>', {src: url, alt: ''}));
      });
      frame.open();
    });
    $('.exone-news-remove-image').on('click', function () {
      var box = $(this).closest('.exone-news-image');
      box.find('input[name="image"]').val('0');
      box.find('.exone-news-image-preview').empty();
    });
    $('[data-news-trash]').on('click', function (event) {
      if (!window.confirm('このお知らせをゴミ箱へ移動しますか？ あとで元に戻せます。')) { event.preventDefault(); }
    });
  });
}(jQuery));

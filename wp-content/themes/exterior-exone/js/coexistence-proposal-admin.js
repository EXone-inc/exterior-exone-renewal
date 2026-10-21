(function ($) {
  'use strict';
  $(function () {
    $('.exone-proposal-select-image').on('click', function () {
      var box = $(this).closest('.exone-proposal-image');
      var frame = wp.media({title: '提案事例の画像を選ぶ', button: {text: 'この画像を使う'}, library: {type: 'image'}, multiple: false});
      frame.on('select', function () {
        var item = frame.state().get('selection').first().toJSON();
        var url = item.sizes && item.sizes.medium ? item.sizes.medium.url : item.url;
        box.find('input[type="hidden"]').val(item.id);
        box.find('.exone-proposal-image-preview').empty().append($('<img>', {src: url, alt: ''}));
      });
      frame.open();
    });
    $('.exone-proposal-remove-image').on('click', function () {
      var box = $(this).closest('.exone-proposal-image');
      box.find('input[type="hidden"]').val('0');
      box.find('.exone-proposal-image-preview').empty();
    });
    $('[data-proposal-delete-taste]').on('click', function (event) {
      var count = $(this).attr('data-proposal-delete-taste');
      if (!window.confirm('このデザインテイストを削除しますか？ 利用中の' + count + '件の事例と画像は残り、テイストは未設定になります。')) { event.preventDefault(); }
    });
    $('[data-proposal-trash]').on('click', function (event) {
      if (!window.confirm('この提案事例をゴミ箱へ移動しますか？ あとで元に戻せます。')) { event.preventDefault(); }
    });
  });
}(jQuery));

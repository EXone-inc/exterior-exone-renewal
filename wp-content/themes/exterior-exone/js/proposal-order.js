(function ($) {
  'use strict';
  $(function () {
    var controls = $('.exone-proposal-order-controls');
    var body = $('.exone-proposal-order-table tbody');
    if (!controls.length || !body.length) { return; }
    var save = controls.find('.exone-proposal-order-save');
    var status = controls.find('.exone-proposal-order-status');
    var busy = false;
    var uncertain = false;
    function ids() { return body.children('[data-proposal-id]').map(function () { return $(this).attr('data-proposal-id'); }).get().join(','); }
    var saved = ids();
    function dirty() { return ids() !== saved; }
    function refresh() {
      var rows = body.children('[data-proposal-id]');
      rows.find('.exone-proposal-order-up,.exone-proposal-order-down').prop('disabled', busy || uncertain);
      rows.first().find('.exone-proposal-order-up').prop('disabled', true);
      rows.last().find('.exone-proposal-order-down').prop('disabled', true);
      save.prop('disabled', busy || uncertain || !dirty());
      if (!busy && !uncertain) { status.text(dirty() ? '順番はまだ保存されていません。' : '保存済みの順番です。'); }
    }
    body.sortable({items: '> tr[data-proposal-id]', handle: '.exone-proposal-order-handle', axis: 'y',
      helper: function (event, row) { row.children().each(function () { $(this).width($(this).width()); }); return row; },
      update: refresh,
      stop: function () { body.find('td,th').css('width', ''); }
    });
    body.on('click', '.exone-proposal-order-up,.exone-proposal-order-down', function () {
      if (busy || uncertain) { return; }
      var row = $(this).closest('tr');
      if ($(this).hasClass('exone-proposal-order-up')) { row.prev('[data-proposal-id]').before(row); }
      else { row.next('[data-proposal-id]').after(row); }
      refresh(); this.focus();
    });
    save.on('click', function () {
      if (busy || uncertain || !dirty()) { return; }
      busy = true; body.sortable('disable'); refresh(); status.text('順番を保存しています…');
      var submitted = ids();
      $.ajax({url: controls.attr('data-endpoint'), method: 'POST', dataType: 'json', timeout: 30000,
        data: {action: 'exone_proposal_order', ids: submitted, version: controls.attr('data-version'), nonce: controls.attr('data-nonce')}
      }).done(function (response) {
        if (!response || !response.success || !response.data || !response.data.version) { failed(response); return; }
        saved = submitted; controls.attr('data-version', response.data.version); busy = false; body.sortable('enable'); refresh();
        status.text('表示順を保存しました。公開一覧と詳細ページ内の一覧に反映されます。');
        // The full-record editor tokens include menu_order. Refresh to renew existing row-action tokens.
        window.location.reload();
      }).fail(function (xhr) { failed(xhr.responseJSON); });
    });
    function failed(response) {
      busy = false; uncertain = true; refresh();
      var message = response && response.data && response.data.message;
      status.text((message || '保存結果を確認できませんでした。') + ' この画面を再読み込みして現在の順番を確認してください。');
    }
    $(window).on('beforeunload', function (event) {
      if (dirty() || busy) { event.preventDefault(); event.originalEvent.returnValue = ''; }
    });
    refresh();
  });
}(jQuery));

<?php
/**
 * サンクスページ（送信完了。固定ページ thanks・親 contact → /contact/thanks/・親 recruit → /recruit/thanks/）。
 *
 * カンプ: PC 964:47 / SP 964:78（ボード 853:582 の中。guide・注釈は無い）
 * 設計メモ: docs/figma-contact-thanks-page.md
 * 仕様書: docs/spec-20261015-thanks-page.md（決定事項 docs/spec-20261015-thanks-page-decisions.md）
 *
 * スラッグ thanks の固定ページに WordPress のテンプレート階層で当たる。親が版の無いページの
 * thanks は functions.php の exterior_exone_store_template() が index.php に戻す。
 * FV（CONTACT の FV 部品を流用）→ 完了メッセージ → Next Step → Explore EXone。
 * 文言は inc/thanks-data.php（版は親ページのスラッグで決まる）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_tk_texts = exterior_exone_thanks_texts();

get_header();
?>
<div class="p-thanks">
	<?php
	get_template_part( 'template-parts/contact/section', 'fv', array( 'data' => exterior_exone_thanks_fv() ) );

	if ( $exterior_exone_tk_texts ) {
		$exterior_exone_tk_headings = exterior_exone_thanks_headings();

		get_template_part(
			'template-parts/thanks/section',
			'message',
			array(
				'message' => $exterior_exone_tk_texts['message'],
				'body'    => $exterior_exone_tk_texts['body'],
			)
		);

		get_template_part(
			'template-parts/thanks/section',
			'steps',
			array(
				'heading' => $exterior_exone_tk_headings['steps'],
				'steps'   => $exterior_exone_tk_texts['steps'],
				'icons'   => exterior_exone_thanks_step_icons(),
			)
		);

		get_template_part(
			'template-parts/thanks/section',
			'explore',
			array(
				'heading' => $exterior_exone_tk_headings['explore'],
				'cards'   => exterior_exone_thanks_explore(),
			)
		);
	}
	?>
</div>
<?php
get_footer();

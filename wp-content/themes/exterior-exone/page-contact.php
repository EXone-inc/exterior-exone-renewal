<?php
/**
 * お問い合わせページ（固定ページ contact → /contact/）。
 *
 * カンプ: PC 親 853:581（デフォルト 860:142 / 個人 853:426 / 法人 853:198 / 支店 853:146 / 電話 853:467 / メール 853:94）
 *         SP 869:436（全部展開）・869:618（初回表示）/ guide 853:579
 * 設計メモ: docs/figma-contact-page.md
 * 仕様書: docs/spec-20261010-contact-page.md（決定事項 docs/spec-20261010-contact-page-decisions.md）
 *
 * スラッグ contact の固定ページに WordPress のテンプレート階層で自動的に当たる。
 * FV（写真帯・CONTACT・お問い合わせ）→ 選択フロー（リード・個人 / 法人 → 店舗 → 方法 → 電話 / フォーム）。
 * 文言は inc/contact-data.php。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="p-contact">
	<?php
	get_template_part( 'template-parts/contact/section', 'fv', array( 'data' => exterior_exone_contact_fv() ) );

	get_template_part(
		'template-parts/contact/section',
		'select',
		array(
			'lead'     => exterior_exone_contact_fv()['lead'],
			'types'    => exterior_exone_contact_types(),
			'stores'   => exterior_exone_contact_stores(),
			'methods'  => exterior_exone_contact_methods(),
			'headings' => exterior_exone_contact_headings(),
			'tel'      => exterior_exone_contact_tel(),
			'initial'  => exterior_exone_contact_initial_store(),
		)
	);
	?>
</div>
<?php
get_footer();

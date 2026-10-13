<?php
/**
 * 採用ページ（固定ページ recruit → /recruit/）。
 *
 * カンプは無い。正は現サイト https://exterior-exone.com/recruit/ と参照物 docs/reference-20261013-recruit/
 * 仕様書: docs/spec-20261013-recruit-page.md（決定事項 docs/spec-20261013-recruit-page-decisions.md）
 *
 * スラッグ recruit の固定ページに WordPress のテンプレート階層で自動的に当たる。
 * FV → Training → Work → 流れる大文字 → Requirements → Benefits → Flow → FAQ。
 * 並びは inc/recruit-data.php。部品（template-parts/recruit/section-<名前>.php）がまだ無いセクションは空の枠だけ置く。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="p-rc">
	<?php
	foreach ( exterior_exone_recruit_sections() as $exterior_exone_rc_section => $exterior_exone_rc_part ) {
		if ( locate_template( 'template-parts/recruit/section-' . $exterior_exone_rc_part . '.php' ) ) {
			get_template_part( 'template-parts/recruit/section', $exterior_exone_rc_part );
			continue;
		}
		?>
		<section class="p-rc__pending" data-section="<?php echo esc_attr( $exterior_exone_rc_section ); ?>"></section>
		<?php
	}
	?>
</div>
<?php
get_footer();

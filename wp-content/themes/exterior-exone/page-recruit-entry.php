<?php
/**
 * 応募フォーム（固定ページ entry・親 recruit → /recruit/entry/）。
 *
 * カンプは無い。正は現サイト https://exterior-exone.com/recruit/entry/ と参照物
 * docs/reference-20261013-recruit/prod-entry-body.html・entry-form.css
 * 仕様書: docs/spec-20261013-recruit-page.md F-10・F-11（決定事項 docs/spec-20261013-recruit-page-decisions.md）
 *
 * 階層では page-entry.php に当たるので、functions.php の exterior_exone_store_template() が
 * 「親が recruit の entry」だけをこのファイルに振り分ける。
 * 見出し → CF7「採用フォーム」（inc/recruit-form.php）。送信・完了・エラーの見せ方は js/recruit-entry.js。
 * 送信成功後は data-recruit-entry-thanks-url の /recruit/thanks/ へ遷移する（無ければ完了表示）。
 * CF7 が無効・未登録のときは案内文だけを出す（仕様書 Q14）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rce_texts = exterior_exone_recruit_entry_texts();
$exterior_exone_rce_html  = exterior_exone_recruit_form_html();
// 送信成功後の遷移先（?store= は付けない。サンクスページが無ければ空 = 同ページ内の完了表示）。
$exterior_exone_rce_thanks = function_exists( 'exterior_exone_thanks_url' ) ? exterior_exone_thanks_url( 'recruit' ) : '';

get_header();
?>
<!-- template: page-recruit-entry.php -->
<div class="p-rce" data-section="recruit-entry">
	<section class="p-rce__inner">
		<h1 class="p-rce__title">
			<span class="p-rce__title-en"><?php echo esc_html( $exterior_exone_rce_texts['title_en'] ); ?></span>
			<span class="p-rce__title-ja"><?php echo esc_html( $exterior_exone_rce_texts['title_ja'] ); ?></span>
		</h1>

		<div class="p-rce__form" data-recruit-entry<?php echo '' !== $exterior_exone_rce_thanks ? ' data-recruit-entry-thanks-url="' . esc_url( $exterior_exone_rce_thanks ) . '"' : ''; ?>>
			<?php if ( '' === $exterior_exone_rce_html ) : ?>
				<p class="p-rce__fallback"><?php echo esc_html( $exterior_exone_rce_texts['fallback'] ); ?></p>
			<?php else : ?>
				<?php echo $exterior_exone_rce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 が組み立てたフォーム。 ?>

				<?php // 完了表示（文面は CF7 の「送信完了」メッセージ。決定 §5 Q13）。 ?>
				<p class="p-rce__done" data-recruit-entry-done tabindex="-1" role="status" hidden></p>
			<?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();

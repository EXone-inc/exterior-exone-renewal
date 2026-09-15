<?php
/**
 * STORE の親ページ（固定ページ store）。
 *
 * 支店一覧ページのデザインは未定のため、当面は 7 拠点へのリンクだけを置く暫定表示。
 * 割り当ては functions.php の exterior_exone_store_template()。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="l-section">
	<div class="l-inner">
		<h1 class="c-display c-display--lg">STORE</h1>

		<ul class="p-store-index">
			<?php foreach ( exterior_exone_stores() as $exterior_exone_slug => $exterior_exone_store ) : ?>
				<li class="p-store-index__item">
					<a class="p-store-index__link" href="<?php echo esc_url( exterior_exone_store_url( $exterior_exone_slug ) ); ?>">
						<?php echo esc_html( $exterior_exone_store['name'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php
get_footer();

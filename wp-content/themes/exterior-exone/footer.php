<?php
/**
 * フッター（サイトフッター 〜 ドキュメント末尾）。
 *
 * カンプ: PC 917:2595（1920x850・MENU / STORE 横並び）
 *         SP  917:754〜917:787（375x648・MENU / STORE 縦積み）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="p-footer" role="contentinfo">
	<p class="p-footer__logo">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
				width="164"
				height="25"
				alt="<?php bloginfo( 'name' ); ?>"
			>
		</a>
	</p>

	<ul class="p-footer__sns">
		<?php foreach ( exterior_exone_sns_items() as $exterior_exone_sns ) : ?>
			<li class="p-footer__sns-item">
				<a class="p-footer__sns-link" href="<?php echo esc_url( $exterior_exone_sns['url'] ); ?>">
					<img
						class="p-footer__sns-icon"
						src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_sns['icon'] ) ); ?>"
						width="41"
						height="41"
						alt=""
						loading="lazy"
					>
					<span class="p-footer__sns-label"><?php echo esc_html( $exterior_exone_sns['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="p-footer__menus">
		<nav class="p-footer__block" aria-label="フッターメニュー">
			<h2 class="p-footer__heading">MENU</h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'p-footer__list',
					'fallback_cb'    => 'exterior_exone_footer_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<nav class="p-footer__block" aria-label="店舗一覧">
			<h2 class="p-footer__heading">STORE</h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'store',
					'container'      => false,
					'menu_class'     => 'p-footer__list',
					'fallback_cb'    => 'exterior_exone_store_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>

	<p class="p-footer__copyright">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> EXONE. All Rights Reserved.</p>
</footer>

<?php wp_footer(); ?>
</body>
</html>

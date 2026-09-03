<?php
/**
 * ヘッダー（ドキュメント冒頭 〜 サイトヘッダー）。
 *
 * カンプ: PC 917:2733（1920x154・FV に重なる半透明グラデーション）
 *         SP  917:796（375x45・#1a1a1a のバー + ハンバーガー）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#main">本文へスキップ</a>

<header class="p-header" role="banner">
	<div class="p-header__inner">
		<p class="p-header__logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img
					src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
					width="164"
					height="25"
					alt="<?php bloginfo( 'name' ); ?>"
				>
			</a>
		</p>

		<nav class="p-gnav" aria-label="グローバルナビ">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'global',
					'container'      => false,
					'menu_class'     => 'p-gnav__list',
					'fallback_cb'    => 'exterior_exone_global_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<?php // SP のみ表示。押下で下のドロワー（カンプ 1036:395）を開く。 ?>
		<button
			type="button"
			class="p-header__toggle"
			aria-label="メニューを開く"
			aria-expanded="false"
			aria-controls="global-drawer"
		>
			<span class="p-header__toggle-bar"></span>
			<span class="p-header__toggle-bar"></span>
			<span class="p-header__toggle-bar"></span>
		</button>
	</div>
</header>

<?php // ハンバーガーメニュー（カンプ 1036:395・SP のみ）。 ?>
<div class="p-drawer" id="global-drawer" data-drawer tabindex="-1">
	<div class="p-drawer__bar">
		<p class="p-drawer__logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img
					src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
					width="164"
					height="25"
					alt="<?php bloginfo( 'name' ); ?>"
				>
			</a>
		</p>

		<button type="button" class="p-drawer__close" aria-label="メニューを閉じる" data-drawer-close>
			<span class="p-drawer__close-icon" aria-hidden="true"></span>
		</button>
	</div>

	<div class="p-drawer__body">
		<nav class="p-drawer__block" aria-label="メニュー">
			<h2 class="p-drawer__heading">MENU</h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'p-drawer__list',
					'fallback_cb'    => 'exterior_exone_drawer_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<nav class="p-drawer__block" aria-label="店舗一覧">
			<h2 class="p-drawer__heading">STORE</h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'store',
					'container'      => false,
					'menu_class'     => 'p-drawer__list',
					'fallback_cb'    => 'exterior_exone_drawer_store_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<?php $exterior_exone_contact = exterior_exone_contact_item(); ?>
		<a class="c-btn c-btn--white p-drawer__contact" href="<?php echo esc_url( $exterior_exone_contact['url'] ); ?>">
			<?php echo esc_html( $exterior_exone_contact['label'] ); ?>
		</a>

		<ul class="p-drawer__sns">
			<?php foreach ( exterior_exone_sns_items() as $exterior_exone_sns ) : ?>
				<li class="p-drawer__sns-item">
					<a class="p-drawer__sns-link" href="<?php echo esc_url( $exterior_exone_sns['url'] ); ?>">
						<img
							class="p-drawer__sns-icon"
							src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_sns['icon'] ) ); ?>"
							width="41"
							height="41"
							alt=""
							loading="lazy"
						>
						<span class="p-drawer__sns-label"><?php echo esc_html( $exterior_exone_sns['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<main id="main" class="l-main">

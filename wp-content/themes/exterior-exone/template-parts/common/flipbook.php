<?php
/**
 * 共通部品: パンフレットのめくり（本）。ハイエンド Gallery と WORKS 詳細で使う。
 *
 * 左ページ（大）+ 右ページ（画面右端までの見切れ）+ 綴じ目の影 + 落ち影 + めくれる 1 枚 + 当たり判定。
 * 左右のページには全写真を重ねて置き、見せる 1 枚に is-current を付ける
 * （右ページは「次の写真」を左ページと同じ幅で左揃えにして左端だけ見せる）。
 * サーバー出力は 1 枚目の状態。JS 無効ではこのまま静止。めくり・自動送り・進捗は js/flipbook.js。
 * 写真が 1 枚だけなら右ページを出さず静止（c-flipbook--single）。
 * 寸法（--flipbook-page-w 等）は各ページの CSS が本の祖先要素で与える（css/flipbook.css）。
 *
 * $args:
 * - id       … 必須。本の要素の id（当たり判定・タブ・サムネの aria-controls の参照先）
 * - photos   … 必須。各要素は attachment_id か、テーマ内画像の src + width + height。どちらも alt
 * - size     … 添付のサイズ（既定 full）
 * - sizes    … 添付の sizes 属性（任意）
 * - loading  … 写真の loading。既定は先頭の左ページ・右ページだけ eager、ほかは lazy
 * - right    … 右ページを押したとき。prev = 左へめくって前へ（既定）/ next = 右へめくって次へ
 * - progress … 進捗下線の要素の id（本の外。JS が伸ばす）
 * - num      … 任意。本の中に重ねる番号（ハイエンドの「01」）
 * - labels   … 任意。当たり判定の aria-label（next = 左ページ / prev = 右ページ）
 * - class    … 任意。本の要素に足すクラス（配置用）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['id'] ) || ! isset( $args['photos'] ) || ! is_array( $args['photos'] ) ) {
	return;
}

$exterior_exone_fb_id     = (string) $args['id'];
$exterior_exone_fb_photos = array_values( $args['photos'] );
$exterior_exone_fb_count  = count( $exterior_exone_fb_photos );
$exterior_exone_fb_size   = isset( $args['size'] ) ? $args['size'] : 'full';
$exterior_exone_fb_right  = ( isset( $args['right'] ) && 'next' === $args['right'] ) ? 'next' : 'prev';
$exterior_exone_fb_labels = array_merge(
	array(
		'next' => '次の写真へ',
		'prev' => 'next' === $exterior_exone_fb_right ? '次の写真へ' : '前の写真へ',
	),
	isset( $args['labels'] ) ? (array) $args['labels'] : array()
);
$exterior_exone_fb_class  = 'c-flipbook' . ( empty( $args['class'] ) ? '' : ' ' . $args['class'] ) . ( $exterior_exone_fb_count ? '' : ' c-flipbook--empty' ) . ( 1 === $exterior_exone_fb_count ? ' c-flipbook--single' : '' );

/**
 * 写真 1 枚の img タグ。
 *
 * @param array $photo   写真。
 * @param bool  $current 表示中か。
 * @param bool  $left    左ページか（右ページは alt を空にする）。
 * @param bool  $first   先頭の 1 枚か（既定の loading の振り分けに使う）。
 */
$exterior_exone_fb_img = function ( $photo, $current, $left, $first ) use ( $args, $exterior_exone_fb_size ) {
	$alt     = $left && isset( $photo['alt'] ) ? (string) $photo['alt'] : '';
	$loading = isset( $args['loading'] ) ? (string) $args['loading'] : ( $first ? 'eager' : 'lazy' );
	$class   = 'c-flipbook__photo' . ( $current ? ' is-current' : '' );
	$hidden  = $left && ! $current;

	if ( ! empty( $photo['attachment_id'] ) ) {
		$attr = array(
			'class'    => $class,
			'alt'      => $alt,
			'loading'  => $loading,
			'decoding' => 'async',
		);

		if ( ! empty( $args['sizes'] ) ) {
			$attr['sizes'] = (string) $args['sizes'];
		}
		if ( $hidden ) {
			$attr['aria-hidden'] = 'true';
		}

		return wp_get_attachment_image( (int) $photo['attachment_id'], $exterior_exone_fb_size, false, $attr );
	}

	if ( empty( $photo['src'] ) ) {
		return '';
	}

	return sprintf(
		'<img class="%1$s" src="%2$s" width="%3$s" height="%4$s" alt="%5$s" loading="%6$s" decoding="async"%7$s>',
		esc_attr( $class ),
		esc_url( $photo['src'] ),
		esc_attr( isset( $photo['width'] ) ? (string) $photo['width'] : '' ),
		esc_attr( isset( $photo['height'] ) ? (string) $photo['height'] : '' ),
		esc_attr( $alt ),
		esc_attr( $loading ),
		$hidden ? ' aria-hidden="true"' : ''
	);
};
?>
<div
	class="<?php echo esc_attr( $exterior_exone_fb_class ); ?>"
	id="<?php echo esc_attr( $exterior_exone_fb_id ); ?>"
	data-flipbook
	data-flipbook-current="0"
	data-flipbook-right="<?php echo esc_attr( $exterior_exone_fb_right ); ?>"
	<?php echo empty( $args['progress'] ) ? '' : 'data-flipbook-progress="' . esc_attr( $args['progress'] ) . '"'; ?>
>
	<div class="c-flipbook__page c-flipbook__page--left">
		<?php
		foreach ( $exterior_exone_fb_photos as $exterior_exone_fb_index => $exterior_exone_fb_photo ) {
			echo $exterior_exone_fb_img( $exterior_exone_fb_photo, 0 === $exterior_exone_fb_index, true, 0 === $exterior_exone_fb_index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP 生成 / エスケープ済みの img タグ。
		}
		?>
		<span class="c-flipbook__fold c-flipbook__fold--left" aria-hidden="true"></span>
	</div>

	<?php // 写真が 1 枚だけのときは右ページ（次の写真の見切れ）を出さず、左ページだけで静止する。 ?>
	<?php if ( 1 !== $exterior_exone_fb_count ) : ?>
	<div class="c-flipbook__page c-flipbook__page--right" aria-hidden="true">
		<?php
		foreach ( $exterior_exone_fb_photos as $exterior_exone_fb_index => $exterior_exone_fb_photo ) {
			$exterior_exone_fb_on = 1 % $exterior_exone_fb_count === $exterior_exone_fb_index;
			echo $exterior_exone_fb_img( $exterior_exone_fb_photo, $exterior_exone_fb_on, false, $exterior_exone_fb_on ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP 生成 / エスケープ済みの img タグ。
		}
		?>
		<span class="c-flipbook__fold c-flipbook__fold--right"></span>
	</div>
	<?php endif; ?>

	<?php // めくりの落ち影と、めくれる 1 枚（短冊は JS が作る）。 ?>
	<span class="c-flipbook__cast" aria-hidden="true"></span>
	<div class="c-flipbook__leaf" aria-hidden="true"></div>

	<?php if ( isset( $args['num'] ) && '' !== (string) $args['num'] ) : ?>
		<p class="c-flipbook__num" data-flipbook-num><?php echo esc_html( $args['num'] ); ?></p>
	<?php endif; ?>

	<?php if ( $exterior_exone_fb_count > 1 ) : ?>
		<?php // 左ページを押すと右へめくれて次へ。右の見切れページは right の指定どおり。 ?>
		<button class="c-flipbook__hit c-flipbook__hit--next" type="button" aria-label="<?php echo esc_attr( $exterior_exone_fb_labels['next'] ); ?>" aria-controls="<?php echo esc_attr( $exterior_exone_fb_id ); ?>"></button>
		<button class="c-flipbook__hit c-flipbook__hit--prev" type="button" aria-label="<?php echo esc_attr( $exterior_exone_fb_labels['prev'] ); ?>" aria-controls="<?php echo esc_attr( $exterior_exone_fb_id ); ?>"></button>
	<?php endif; ?>
</div>

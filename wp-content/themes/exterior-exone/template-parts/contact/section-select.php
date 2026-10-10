<?php
/**
 * CONTACT: 選択フロー（リード・個人 / 法人 → 店舗 → 方法 → 電話 / フォーム）。
 *
 * カンプ: PC 860:142（リード 853:372 / btn list 1 860:140）・選択中の見本 853:426（860:143）・853:198（860:157）
 *         個人 853:426（見出し 853:454 / btn list 2 860:153）/ 支店 853:146（見出し 853:185 / btn list 3 864:183）
 *         電話 853:467（853:502・853:494・853:480・853:505・853:476）/ メール 853:94 / 法人 853:198
 *         SP 869:618（リード 869:666 / ボタン 869:640・869:641）/ guide 853:568・853:572・853:575・853:578・869:614
 *
 * 読み込み直後は個人 / 法人の 2 択だけを出し、下の段は hidden（guide 853:568）。
 * ?store=<slug> で来たときは 個人 + その支店 を選択済みにし、方法の段まで出しておく（決定 §5-3）。
 * 選択中は aria-pressed="true"。ホバーと選択中は色反転（CSS）。段の出し分けは js/contact-select.js。
 *
 * $args: lead（行の配列。exterior_exone_highend_lines() の書式）/ types / stores / methods / headings / tel
 *        （exterior_exone_contact_*()）/ initial（最初から選ぶ支店のスラッグ。無ければ ''）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_ct_types    = isset( $args['types'] ) && is_array( $args['types'] ) ? $args['types'] : array();
$exterior_exone_ct_lead     = isset( $args['lead'] ) ? $args['lead'] : array();
$exterior_exone_ct_stores   = isset( $args['stores'] ) && is_array( $args['stores'] ) ? $args['stores'] : array();
$exterior_exone_ct_methods  = isset( $args['methods'] ) && is_array( $args['methods'] ) ? $args['methods'] : array();
$exterior_exone_ct_headings = isset( $args['headings'] ) && is_array( $args['headings'] ) ? $args['headings'] : array();
$exterior_exone_ct_tel      = isset( $args['tel'] ) && is_array( $args['tel'] ) ? $args['tel'] : array();
$exterior_exone_ct_initial  = isset( $args['initial'], $exterior_exone_ct_stores[ $args['initial'] ] ) ? (string) $args['initial'] : '';

// 初期状態: 支店経由なら 個人 + 支店（方法はまだ選ばない）。
$exterior_exone_ct_type = '' !== $exterior_exone_ct_initial ? 'personal' : '';
$exterior_exone_ct_view = exterior_exone_contact_tel_view( '' !== $exterior_exone_ct_initial ? $exterior_exone_ct_stores[ $exterior_exone_ct_initial ] : null );
?>
<section class="p-contactsel" data-section="contact-select">
	<p class="p-contactsel__lead"><?php echo exterior_exone_highend_lines( $exterior_exone_ct_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>

	<div class="p-contactsel__step" data-contact-step="type">
		<ul class="p-contactsel__types">
			<?php foreach ( $exterior_exone_ct_types as $exterior_exone_ct_key => $exterior_exone_ct_item ) : ?>
				<li class="p-contactsel__types-item">
					<button
						type="button"
						class="p-contactsel__type"
						data-contact-type="<?php echo esc_attr( $exterior_exone_ct_key ); ?>"
						aria-pressed="<?php echo esc_attr( $exterior_exone_ct_key === $exterior_exone_ct_type ? 'true' : 'false' ); ?>"
					>
						<span class="p-contactsel__type-title"><?php echo esc_html( $exterior_exone_ct_item['title'] ); ?></span>
						<span class="p-contactsel__type-note"><?php echo esc_html( $exterior_exone_ct_item['note'] ); ?></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php // お問い合わせ先店舗（853:454 / 860:153）。電話ブロック・LINE の差し替え元を data-* に持たせる。 ?>
	<div class="p-contactsel__step p-contactsel__step--store" data-contact-step="store"<?php echo 'personal' === $exterior_exone_ct_type ? '' : ' hidden'; ?>>
		<h2 class="p-contactsel__heading"><?php echo esc_html( isset( $exterior_exone_ct_headings['store'] ) ? $exterior_exone_ct_headings['store'] : '' ); ?></h2>
		<ul class="p-contactsel__stores">
			<?php
			foreach ( $exterior_exone_ct_stores as $exterior_exone_ct_slug => $exterior_exone_ct_store ) :
				$exterior_exone_ct_store_view = exterior_exone_contact_tel_view( $exterior_exone_ct_store );
				?>
				<li class="p-contactsel__stores-item">
					<button
						type="button"
						class="p-contactsel__store"
						data-contact-store="<?php echo esc_attr( $exterior_exone_ct_slug ); ?>"
						data-contact-tel-title="<?php echo esc_attr( $exterior_exone_ct_store_view['title'] ); ?>"
						data-contact-tel="<?php echo esc_attr( $exterior_exone_ct_store_view['number'] ); ?>"
						data-contact-tel-url="<?php echo esc_url( $exterior_exone_ct_store_view['url'], array( 'tel' ) ); ?>"
						data-contact-tel-hours="<?php echo esc_attr( $exterior_exone_ct_store_view['hours'] ); ?>"
						data-contact-line-url="<?php echo esc_url( $exterior_exone_ct_store_view['line'] ); ?>"
						aria-pressed="<?php echo esc_attr( $exterior_exone_ct_slug === $exterior_exone_ct_initial ? 'true' : 'false' ); ?>"
					><?php echo esc_html( $exterior_exone_ct_store['name'] ); ?></button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php // お問い合わせ方法（853:185 / 864:183）。LINE は選択中の支店の line_url への直リンク（状態を持たない）。 ?>
	<div class="p-contactsel__step p-contactsel__step--method" data-contact-step="method"<?php echo '' !== $exterior_exone_ct_initial ? '' : ' hidden'; ?>>
		<h2 class="p-contactsel__heading"><?php echo esc_html( isset( $exterior_exone_ct_headings['method'] ) ? $exterior_exone_ct_headings['method'] : '' ); ?></h2>
		<ul class="p-contactsel__methods">
			<?php
			foreach ( $exterior_exone_ct_methods as $exterior_exone_ct_key => $exterior_exone_ct_method ) :
				$exterior_exone_ct_is_line = 'line' === $exterior_exone_ct_key;
				// PC の文言の「LINE」だけ字間を広げる（853:100）。
				$exterior_exone_ct_label = preg_replace(
					'/LINE/',
					'<span class="p-contactsel__method-line">LINE</span>',
					esc_html( $exterior_exone_ct_method['pc'] ),
					1
				);
				?>
				<li class="p-contactsel__methods-item">
					<?php if ( $exterior_exone_ct_is_line ) : ?>
						<a
							class="p-contactsel__method p-contactsel__method--line"
							data-contact-method="line"
							<?php if ( '' !== $exterior_exone_ct_view['line'] ) : ?>
								href="<?php echo esc_url( $exterior_exone_ct_view['line'] ); ?>"
							<?php endif; ?>
							target="_blank"
							rel="noopener"
						>
					<?php else : ?>
						<button
							type="button"
							class="p-contactsel__method p-contactsel__method--<?php echo esc_attr( $exterior_exone_ct_key ); ?>"
							data-contact-method="<?php echo esc_attr( $exterior_exone_ct_key ); ?>"
							aria-pressed="false"
						>
					<?php endif; ?>
						<img
							class="p-contactsel__method-icon"
							src="<?php echo esc_url( exterior_exone_contact_image( $exterior_exone_ct_method['icon'] ) ); ?>"
							width="<?php echo esc_attr( (string) $exterior_exone_ct_method['size'][0] ); ?>"
							height="<?php echo esc_attr( (string) $exterior_exone_ct_method['size'][1] ); ?>"
							alt=""
							loading="lazy"
						>
						<span class="p-contactsel__method-label p-contactsel__method-label--pc"><?php echo wp_kses( $exterior_exone_ct_label, array( 'span' => array( 'class' => array() ) ) ); ?></span>
						<span class="p-contactsel__method-label p-contactsel__method-label--sp"><?php echo esc_html( $exterior_exone_ct_method['sp'] ); ?></span>
					<?php if ( $exterior_exone_ct_is_line ) : ?>
						</a>
					<?php else : ?>
						</button>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php
	// 電話ブロック（853:502・853:494・853:480・853:505・853:476）。中身は選択中の支店で JS が差し替える。
	// data-contact-has-store … 最初から支店がある（JS 無効時に出してよい。仕様書 Q9）。
	?>
	<div class="p-contactsel__step p-contactsel__step--tel" data-contact-step="tel"<?php echo '' !== $exterior_exone_ct_initial ? ' data-contact-has-store' : ''; ?> hidden>
		<h3 class="p-contactsel__heading" data-contact-tel-slot="title"><?php echo esc_html( $exterior_exone_ct_view['title'] ); ?></h3>
		<div class="p-contacttel">
			<div class="p-contacttel__main">
				<a
					class="p-contacttel__number"
					data-contact-tel-slot="number"
					<?php if ( '' !== $exterior_exone_ct_view['url'] ) : ?>
						href="<?php echo esc_url( $exterior_exone_ct_view['url'], array( 'tel' ) ); ?>"
					<?php endif; ?>
				><?php echo esc_html( $exterior_exone_ct_view['number'] ); ?></a>
				<p class="p-contacttel__hours" data-contact-tel-slot="hours"><?php echo esc_html( $exterior_exone_ct_view['hours'] ); ?></p>
			</div>
			<span class="p-contacttel__rule" aria-hidden="true"></span>
			<p class="p-contacttel__note"><?php echo exterior_exone_highend_lines( isset( $exterior_exone_ct_tel['note'] ) ? $exterior_exone_ct_tel['note'] : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		</div>
	</div>

	<?php
	// フォームの段（個人 864:294 / 法人 860:166）。中身（CF7 のフォーム・確認・完了）は template-parts/contact/form.php。
	// 個人フォームの支店（隠しフィールド store-slug）は js/contact-select.js が選択中の支店に合わせる。
	foreach ( array( 'personal', 'corporate' ) as $exterior_exone_ct_form ) :
		?>
		<div class="p-contactsel__step p-contactsel__step--form-<?php echo esc_attr( $exterior_exone_ct_form ); ?>" data-contact-step="form-<?php echo esc_attr( $exterior_exone_ct_form ); ?>" hidden>
			<?php get_template_part( 'template-parts/contact/form', null, array( 'type' => $exterior_exone_ct_form ) ); ?>
		</div>
	<?php endforeach; ?>
</section>

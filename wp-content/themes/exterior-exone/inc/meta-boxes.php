<?php
/**
 * お知らせ・施工事例の編集フォーム。
 *
 * ブロックエディタは使わず（inc/post-types.php で無効化）、項目を縦 1 列に
 * 並べて上から順に埋められる形にする。タクソノミーもサイドバーではなく
 * 本文列に置き、左右を行き来せずに登録できるようにしている。
 *
 * 説明は post_content に入れる（テーマ側でそのまま出せるようにするため）。
 * WordPress 標準のエディタ枠は出していないので、保存もここで行う。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 施工事例で使うメタキー。
 */
const EXTERIOR_EXONE_WORKS_GALLERY = '_exterior_exone_works_gallery'; // 旧: 複数枚のギャラリー（読み出しの後方互換にだけ使う）
const EXTERIOR_EXONE_WORKS_AREA    = '_exterior_exone_works_area'; // 旧: 所在地（入力欄は廃止。値は残す）
const EXTERIOR_EXONE_WORKS_ON_TOP  = '_exterior_exone_works_on_top';

/**
 * 提案事例の画像 4 枚（ユーザー指示 2026-10-08: メイン画像 / 外観写真 / 3Dパース / 図面）。
 * 並び順がそのまま詳細のパンフレットの順になる（1 枚目 = メイン、以降は位置でキャプション固定）。
 *
 * @return array<string, array{meta: string, label: string, help: string}>
 */
function exterior_exone_works_image_fields() {
	return array(
		'main'        => array(
			'meta'  => '_exterior_exone_works_image_main',
			'label' => '画像（メイン画像）',
			'help'  => 'トップページ・一覧のカードと、詳細ページの左ページに使います。',
		),
		'exterior'    => array(
			'meta'  => '_exterior_exone_works_image_exterior',
			'label' => '画像（外観写真）',
			'help'  => '',
		),
		'perspective' => array(
			'meta'  => '_exterior_exone_works_image_perspective',
			'label' => '画像（3Dパース）',
			'help'  => '',
		),
		'plan'        => array(
			'meta'  => '_exterior_exone_works_image_plan',
			'label' => '画像（図面）',
			'help'  => '',
		),
	);
}

/**
 * 説明欄。書式は持たせず、素のテキストエリアにする。
 *
 * 表示側の見た目はテーマの CSS で作るため、入力は本文だけを受け取る。
 * 改行はそのまま保存され、出力時にテーマ側で扱う。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_description_field( $post ) {
	?>
	<textarea
		id="exterior_exone_description"
		name="exterior_exone_description"
		class="widefat"
		rows="10"
	><?php echo esc_textarea( $post->post_content ); ?></textarea>
	<?php
}

/* -------------------------------------------------------------------------
 * お知らせ
 * ---------------------------------------------------------------------- */

/**
 * お知らせの入力フォームを 1 枚にまとめる。
 */
function exterior_exone_add_news_meta_boxes() {
	// 既定のアイキャッチ枠は出さない（同じフォームの中に「画像」として置く）。
	remove_meta_box( 'postimagediv', 'news', 'side' );

	add_meta_box(
		'exterior-exone-news-form',
		'お知らせの内容',
		'exterior_exone_render_news_form',
		'news',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_news', 'exterior_exone_add_news_meta_boxes', 20 );

/**
 * お知らせの入力フォーム。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_news_form( $post ) {
	wp_nonce_field( 'exterior_exone_save_news', 'exterior_exone_news_nonce' );

	$image_id = (int) get_post_thumbnail_id( $post->ID );
	?>
	<div class="exone-form">
		<div class="exone-form__row">
			<label class="exone-form__label" for="exterior_exone_description">説明</label>
			<div class="exone-form__field">
				<?php exterior_exone_render_description_field( $post ); ?>
			</div>
		</div>

		<div class="exone-form__row">
			<span class="exone-form__label">画像</span>
			<div class="exone-form__field">
				<?php exterior_exone_render_single_image_field( $image_id, 'exterior_exone_news_image' ); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * お知らせを保存する。
 *
 * @param int $post_id 投稿 ID。
 */
function exterior_exone_save_news_meta( $post_id ) {
	if ( ! exterior_exone_can_save_meta( $post_id, 'exterior_exone_news_nonce', 'exterior_exone_save_news' ) ) {
		return;
	}

	exterior_exone_save_description( $post_id, 'save_post_news', 'exterior_exone_save_news_meta' );

	// 画像はアイキャッチとして持つ（表示側の呼び出しを標準のままにするため）。
	$image_id = isset( $_POST['exterior_exone_news_image'] )
		? absint( wp_unslash( $_POST['exterior_exone_news_image'] ) )
		: 0;

	if ( $image_id && 'attachment' === get_post_type( $image_id ) ) {
		set_post_thumbnail( $post_id, $image_id );
	} else {
		delete_post_thumbnail( $post_id );
	}
}
add_action( 'save_post_news', 'exterior_exone_save_news_meta' );

/* -------------------------------------------------------------------------
 * 施工事例
 * ---------------------------------------------------------------------- */

/**
 * 施工事例の入力フォームを縦 1 列に並べる。
 *
 * 項目はユーザー指示（2026-10-08）の 7 つ: タイトル（標準）/ 説明 / 画像（メイン画像）/
 * 画像（外観写真）/ 画像（3Dパース）/ 画像（図面）/ デザインテイスト。
 * 上から 説明 → 画像 4 枚 → デザインテイスト（1 つ選ぶ）→ トップページ掲載 の順。
 * カテゴリータイプ・ハッシュタグ・所在地の入力欄は出さない（タクソノミーの登録と保存済みの値は残す）。
 */
function exterior_exone_add_works_meta_boxes() {
	add_meta_box(
		'exterior-exone-works-description',
		'説明',
		'exterior_exone_render_works_description',
		'works',
		'normal',
		'high'
	);

	// 標準のタクソノミー枠はすべて外し、デザインテイストだけ独自の枠（単一選択）で出す。
	foreach ( array( 'works_category', 'works_package', 'works_tag' ) as $exterior_exone_tax ) {
		remove_meta_box( $exterior_exone_tax . 'div', 'works', 'side' );
	}

	add_meta_box(
		'exterior-exone-works-images',
		'画像',
		'exterior_exone_render_works_images_box',
		'works',
		'normal',
		'default'
	);

	add_meta_box(
		'exterior-exone-works-taste',
		'デザインテイスト',
		'exterior_exone_render_works_taste_box',
		'works',
		'normal',
		'default'
	);

	add_meta_box(
		'exterior-exone-works-detail',
		'掲載設定',
		'exterior_exone_render_works_detail_box',
		'works',
		'normal',
		'low'
	);
}
add_action( 'add_meta_boxes_works', 'exterior_exone_add_works_meta_boxes', 20 );

/**
 * 施工事例の説明文。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_works_description( $post ) {
	wp_nonce_field( 'exterior_exone_save_works', 'exterior_exone_works_nonce' );

	exterior_exone_render_description_field( $post );
}

/**
 * 画像 4 枚（メイン画像 / 外観写真 / 3Dパース / 図面）。1 枚ずつのフィールド。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_works_images_box( $post ) {
	?>
	<div class="exone-form">
		<?php foreach ( exterior_exone_works_image_fields() as $exterior_exone_key => $exterior_exone_field ) : ?>
			<div class="exone-form__row">
				<p class="exone-form__label"><?php echo esc_html( $exterior_exone_field['label'] ); ?></p>
				<div class="exone-form__field">
					<?php
					exterior_exone_render_single_image_field(
						(int) get_post_meta( $post->ID, $exterior_exone_field['meta'], true ),
						'exterior_exone_works_image_' . $exterior_exone_key
					);
					?>
					<?php if ( '' !== $exterior_exone_field['help'] ) : ?>
						<p class="description"><?php echo esc_html( $exterior_exone_field['help'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * デザインテイスト（タクソノミー works_package）。1 つだけ選ぶラジオボタン。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_works_taste_box( $post ) {
	$terms   = get_terms(
		array(
			'taxonomy'   => 'works_package',
			'hide_empty' => false,
		)
	);
	$current = wp_get_object_terms( $post->ID, 'works_package', array( 'fields' => 'ids' ) );
	$current = is_array( $current ) && $current ? (int) $current[0] : 0;
	?>
	<div class="exone-form">
		<?php if ( is_wp_error( $terms ) || ! $terms ) : ?>
			<p class="description">デザインテイストが登録されていません。「提案事例 → デザインテイスト」で追加してください。</p>
		<?php else : ?>
			<fieldset>
				<legend class="screen-reader-text">デザインテイスト</legend>
				<p>
					<label>
						<input type="radio" name="exterior_exone_works_taste" value="0" <?php checked( 0, $current ); ?>>
						未設定
					</label>
				</p>
				<?php foreach ( $terms as $exterior_exone_term ) : ?>
					<p>
						<label>
							<input type="radio" name="exterior_exone_works_taste" value="<?php echo esc_attr( (string) $exterior_exone_term->term_id ); ?>" <?php checked( $exterior_exone_term->term_id, $current ); ?>>
							<?php echo esc_html( $exterior_exone_term->name ); ?>
						</label>
					</p>
				<?php endforeach; ?>
			</fieldset>
			<p class="description">一覧の絞り込みと、詳細ページの英字の見出しに使います。</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * トップページ掲載（所在地の入力欄は 2026-10-08 に廃止）。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function exterior_exone_render_works_detail_box( $post ) {
	$on_top = (bool) get_post_meta( $post->ID, EXTERIOR_EXONE_WORKS_ON_TOP, true );
	?>
	<div class="exone-form">
		<div class="exone-form__row">
			<span class="exone-form__label">トップページ</span>
			<div class="exone-form__field">
				<label>
					<input
						type="checkbox"
						name="exterior_exone_works_on_top"
						value="1"
						<?php checked( $on_top ); ?>
					>
					トップページに記載する
				</label>
				<p class="description">
					チェックが入っているもののうち、新しい順に最大 5 件をトップページに出します。
				</p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 施工事例を保存する。
 *
 * @param int $post_id 投稿 ID。
 */
function exterior_exone_save_works_meta( $post_id ) {
	if ( ! exterior_exone_can_save_meta( $post_id, 'exterior_exone_works_nonce', 'exterior_exone_save_works' ) ) {
		return;
	}

	exterior_exone_save_description( $post_id, 'save_post_works', 'exterior_exone_save_works_meta' );

	// 画像 4 枚。実在する添付ファイルの ID だけ保存し、空なら値ごと消す。
	foreach ( exterior_exone_works_image_fields() as $key => $field ) {
		$id = isset( $_POST[ 'exterior_exone_works_image_' . $key ] )
			? absint( wp_unslash( $_POST[ 'exterior_exone_works_image_' . $key ] ) )
			: 0;

		if ( $id > 0 && 'attachment' === get_post_type( $id ) ) {
			update_post_meta( $post_id, $field['meta'], $id );
		} else {
			delete_post_meta( $post_id, $field['meta'] );
		}
	}

	// 新しい 4 枚の形で保存したら、旧ギャラリーは読まれないよう消す（二重管理を避ける）。
	delete_post_meta( $post_id, EXTERIOR_EXONE_WORKS_GALLERY );

	// デザインテイスト（1 つだけ。0 = 未設定）。
	if ( isset( $_POST['exterior_exone_works_taste'] ) ) {
		$taste = absint( wp_unslash( $_POST['exterior_exone_works_taste'] ) );
		wp_set_object_terms( $post_id, $taste > 0 ? array( $taste ) : array(), 'works_package', false );
	}

	// トップ掲載。チェックが外れたときは値ごと消す。
	if ( isset( $_POST['exterior_exone_works_on_top'] ) ) {
		update_post_meta( $post_id, EXTERIOR_EXONE_WORKS_ON_TOP, '1' );
	} else {
		delete_post_meta( $post_id, EXTERIOR_EXONE_WORKS_ON_TOP );
	}
}
add_action( 'save_post_works', 'exterior_exone_save_works_meta' );

/* -------------------------------------------------------------------------
 * 共通
 * ---------------------------------------------------------------------- */

/**
 * 1 枚だけの画像フィールド。
 *
 * @param int    $image_id 現在の添付ファイル ID（0 なら未設定）。
 * @param string $name     フォームの name 属性。
 */
function exterior_exone_render_single_image_field( $image_id, $name ) {
	$thumb = $image_id ? wp_get_attachment_image( $image_id, 'medium' ) : '';
	?>
	<div class="exone-image" data-exone-image>
		<div class="exone-image__preview" data-exone-image-preview>
			<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP 生成の img タグ。 ?>
		</div>

		<p>
			<button type="button" class="button" data-exone-image-add>画像を選ぶ</button>
			<button type="button" class="button-link exone-image__remove" data-exone-image-remove <?php echo $image_id ? '' : 'hidden'; ?>>画像を外す</button>
		</p>

		<input
			type="hidden"
			name="<?php echo esc_attr( $name ); ?>"
			value="<?php echo esc_attr( (string) $image_id ); ?>"
			data-exone-image-input
		>
	</div>
	<?php
}

/**
 * 保存してよい状況かを判定する（自動保存・権限・nonce）。
 *
 * @param int    $post_id 投稿 ID。
 * @param string $field   nonce のフィールド名。
 * @param string $action  nonce のアクション名。
 * @return bool
 */
function exterior_exone_can_save_meta( $post_id, $field, $action ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	$nonce = isset( $_POST[ $field ] )
		? sanitize_text_field( wp_unslash( $_POST[ $field ] ) )
		: '';

	return (bool) ( $nonce && wp_verify_nonce( $nonce, $action ) );
}

/**
 * 説明欄の内容を post_content に保存する。
 *
 * 標準のエディタ枠を出していないぶん、WordPress 側では post_content が
 * 更新されない。ここで書き戻すが、wp_update_post が同じ save_post を
 * 呼び戻すため、その間だけフックを外す。
 *
 * @param int    $post_id  投稿 ID。
 * @param string $hook     一時的に外すフック名。
 * @param string $callback 一時的に外すコールバック名。
 */
function exterior_exone_save_description( $post_id, $hook, $callback ) {
	if ( ! isset( $_POST['exterior_exone_description'] ) ) {
		return;
	}

	// 素のテキストとして受け取る。タグは落とし、改行はそのまま残す。
	$content = sanitize_textarea_field( wp_unslash( $_POST['exterior_exone_description'] ) );

	if ( $content === get_post_field( 'post_content', $post_id ) ) {
		return;
	}

	remove_action( $hook, $callback );

	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $content,
		)
	);

	add_action( $hook, $callback );
}

/**
 * 提案事例の画像 ID を表示順（メイン → 外観写真 → 3Dパース → 図面）で返す。
 *
 * 未設定の枠は飛ばす（詰める）。4 枚の形で 1 枚も無いときは、旧ギャラリー
 * （カンマ区切り）の値を読む（2026-10-08 より前に登録した投稿の後方互換）。
 *
 * @param int $post_id 投稿 ID。
 * @return array<int, int>
 */
function exterior_exone_works_gallery_ids( $post_id ) {
	$ids = array();

	foreach ( exterior_exone_works_image_fields() as $field ) {
		$id = absint( get_post_meta( $post_id, $field['meta'], true ) );

		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	if ( $ids ) {
		return $ids;
	}

	$raw = (string) get_post_meta( $post_id, EXTERIOR_EXONE_WORKS_GALLERY, true );

	if ( '' === $raw ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * お知らせ・施工事例の編集画面でだけ、入力フォーム用の JS / CSS を読む。
 *
 * @param string $hook 現在の管理画面。
 */
function exterior_exone_enqueue_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	// $typenow は新規追加でも編集でも WP が入れてくれる（$post より確実）。
	global $typenow;

	if ( ! in_array( $typenow, array( 'news', 'works' ), true ) ) {
		return;
	}

	// メディアモーダル（wp.media）を使えるようにする。
	wp_enqueue_media();

	wp_enqueue_script(
		'exterior-exone-editor-fields',
		get_theme_file_uri( 'js/admin/editor-fields.js' ),
		array( 'jquery', 'jquery-ui-sortable' ),
		exterior_exone_asset_version( 'js/admin/editor-fields.js' ),
		true
	);

	wp_enqueue_style(
		'exterior-exone-editor-fields',
		get_theme_file_uri( 'css/admin/editor-fields.css' ),
		array(),
		exterior_exone_asset_version( 'css/admin/editor-fields.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'exterior_exone_enqueue_editor_assets' );

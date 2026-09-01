<?php
/**
 * TOP ページの表示データ。
 *
 * CPT 接続前の暫定データをここに集約する（差し替え時はこのファイルだけを見る）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FV のスライド一覧。カンプ 1:280（PC 1920x1080）/ 16:64・16:71・16:93（SP）。
 *
 * 背景は 1920x1080 / 9.03 秒の動画 5 本。動画・サムネイル・テキストは
 * すべて同じ添字で対応させ、js/fv-slider.js が index 単位で切り替える。
 *
 * - video: videos/top/ 配下の動画ファイル名。
 * - thumb: 円形サムネイル画像（85x85 で書き出し済み・等倍で使う）。
 * - label: サムネイルの読み上げ用ラベル。
 * - texts: そのスライドで出す英字コピー。1 スライドに複数置ける
 *          （4 本目は PACKAGE PLAN / HIGH-END PLAN の 2 枚組）。
 *   - text:     1 文字ずつ span に割られて字送りアニメーションが掛かる。
 *   - byline:   コピー直下の小さい行（空なら出さない）。字送りはしない。
 *   - position: '' は画面中央、'left' は 25%、'right' は 75%。
 *   - small:    2 枚組のとき文字を一段小さくする。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_fv_slides() {
	return array(
		array(
			'video' => 'FV01.mp4',
			'thumb' => 'FV01_sub.png',
			'label' => '夜景のライトアップ外構',
			'texts' => array(
				array(
					'text'     => 'EXTERIOR',
					'byline'   => 'by EXone',
					'position' => '',
					'small'    => false,
				),
			),
		),
		array(
			'video' => 'FV02.mp4',
			'thumb' => 'FV02_sub.png',
			'label' => 'カーポートのある外構',
			'texts' => array(
				array(
					'text'     => 'CARPORT',
					'byline'   => '',
					'position' => '',
					'small'    => false,
				),
			),
		),
		array(
			'video' => 'FV03.mp4',
			'thumb' => 'FV03_sub.png',
			'label' => '植栽とアプローチ',
			'texts' => array(
				array(
					'text'     => 'GARDEN',
					'byline'   => '',
					'position' => '',
					'small'    => false,
				),
			),
		),
		array(
			'video' => 'FV04.mp4',
			'thumb' => 'FV04_sub.png',
			'label' => 'プランのご紹介',
			'texts' => array(
				array(
					'text'     => 'PACKAGE PLAN',
					'byline'   => '',
					'position' => 'left',
					'small'    => true,
				),
				// 「HIGH-END」と「PLAN」の間が 2 マスなのは参照実装どおり（見た目の詰まりを避けるため）。
				array(
					'text'     => 'HIGH-END  PLAN',
					'byline'   => '',
					'position' => 'right',
					'small'    => true,
				),
			),
		),
		array(
			'video' => 'FV05.mp4',
			'thumb' => 'FV05_sub.png',
			'label' => 'VR ショールーム',
			'texts' => array(
				array(
					'text'     => 'VR SHOWROOM',
					'byline'   => '',
					'position' => '',
					'small'    => false,
				),
			),
		),
	);
}

/**
 * 理念（MISSION / VISION / VALUE）。PC のみ表示。カンプ 917:279。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_philosophy_items() {
	return array(
		array(
			'eng'  => 'MISSION',
			'jp'   => '私たちの使命',
			'lead' => "エクステリア業界の不透明さをなくし、\n すべての人に見える顧客体験を提供する。",
		),
		array(
			'eng'  => 'VISION',
			'jp'   => '私たちの目指す未来',
			'lead' => "次世代の業界トレンドを創出し、\n 社会に長期的なインパクトを与える\n リーディングカンパニーへ。",
		),
		array(
			'eng'  => 'VALUE',
			'jp'   => '私たちの約束・価値観',
			'lead' => '見える顧客体験で、豊かな暮らしを。',
		),
	);
}

/**
 * 理念セクションの円環図の 6 項目。カンプ 16:535（PC）/ 16:171（SP）。
 *
 * 並び順が配置と対応する（1 左上 / 2 右上 / 3 左 / 4 右 / 5 左下 / 6 右下）。
 * アイコンは Figma から書き出した支給素材を切り出したもの。
 *
 * @return array<int, array{icon:string, lines:array<int, string>}>
 */
function exterior_exone_philosophy_diagram_items() {
	return array(
		array(
			'icon'  => 'vision-diagram-icon1.png', // ロケット
			'lines' => array( '変化を恐れず', '挑戦を続ける' ),
		),
		array(
			'icon'  => 'vision-diagram-icon5.png', // 共有
			'lines' => array( 'DXで体験を', 'アップデートする' ),
		),
		array(
			'icon'  => 'vision-diagram-icon3.png', // タブレット
			'lines' => array( '見える安心を', 'すべての基準に' ),
		),
		array(
			'icon'  => 'vision-diagram-icon4.png', // 握手
			'lines' => array( '誠実であることを', 'すべての判断基準に' ),
		),
		array(
			'icon'  => 'vision-diagram-icon2.png', // 電球
			'lines' => array( '常識に疑問を持ち', 'より良い方法を追求する' ),
		),
		array(
			'icon'  => 'vision-diagram-icon6.png', // 歯車
			'lines' => array( '新しいスタンダードを', '創り続ける' ),
		),
	);
}

/**
 * 実績数値。カンプ 16:566-592（PC 1:11）/ 16:194-222（SP 16:16）。
 *
 * 4 項目・アイコンとも PC / SP 共通。並び順も共通で、PC は横 1 列、
 * SP は 2x2 の円バッジになる。
 *
 * note は PC カンプにだけある補足（16:576）。SP カンプ（16:206）は項目名のみ。
 * unit は SP カンプの 2 項目目が「件/年」になっているが、指標名（従業員増加率）と
 * 合わないため PC カンプ（16:579）の「%」を採る。
 *
 * @return array<int, array<string, string|int>>
 */
function exterior_exone_stats_items() {
	return array(
		array(
			'label'  => '年間施工数',
			'note'   => '',
			'number' => '500',
			'unit'   => '件/年',
			'icon'   => 'vision-logo1.png',
			'icon_w' => 211,
			'icon_h' => 186,
		),
		array(
			'label'  => '従業員増加率',
			'note'   => '(昨対比率)',
			'number' => '160',
			'unit'   => '%',
			'icon'   => 'vision-logo2.png',
			'icon_w' => 233,
			'icon_h' => 187,
		),
		array(
			'label'  => '店舗数',
			'note'   => '',
			'number' => '6',
			'unit'   => '店舗',
			'icon'   => 'vision-logo3.png',
			'icon_w' => 254,
			'icon_h' => 185,
		),
		array(
			'label'  => '累計施工実績',
			'note'   => '',
			'number' => '3,500',
			'unit'   => '件以上',
			'icon'   => 'vision-logo4.png',
			'icon_w' => 228,
			'icon_h' => 187,
		),
	);
}

/**
 * DX EXPERIENCE の 3 ステップ。カンプ 917:174（PC）/ 917:841（SP・1 件のみ表示）。
 *
 * media はステップごとの右側ビジュアル。PC はスクロール、SP はスライダーの
 * 現在位置に同期して切り替わる（js/dx-slider.js）。
 *
 * - type: 'video' なら videos/top/、'image' / 'mockup' なら images/top/ を見る。
 * - 'mockup' は画像の上にスマホの図を CSS で重ねる（03 SHARE。参照実装 test.html の 3 枚目）。
 *   mockup.labels の 4 件が、外周の円とスマホ画面の中のリストの両方に使われる。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_dx_steps() {
	return array(
		array(
			'number' => '01 / EXPERIENCE',
			'title'  => '暮らしをその場で体感',
			'desc'   => '試す・探す・比べることで、暮らしのイメージをより直感的に。',
			'tags'   => array( 'バーチャル展示場', 'AIラフ再現' ),
			'media'  => array(
				'type' => 'video',
				'file' => 'dx_video.mp4',
			),
		),
		array(
			'number' => '02 / VISUALIZE',
			'title'  => '完成を見える化',
			'desc'   => '高精度3Dパースで、図面だけでは分かりにくい高さや奥行きまで確認。',
			'tags'   => array( '高精度3Dパース' ),
			'media'  => array(
				'type' => 'video',
				'file' => 'dx_video2.mp4',
			),
		),
		array(
			'number' => '03 / SHARE',
			'title'  => '確認事項をひとまとめ',
			'desc'   => '変更点・確認事項・共有内容を一箇所に集約し、認識のズレを防止。',
			'tags'   => array( '施主共有システム' ),
			'media'  => array(
				'type'   => 'mockup',
				'file'   => 'dx_share.jpg',
				'mockup' => array(
					'app'    => 'EXONE CHECK',
					'title'  => array( 'WEBに', 'すべてを集約' ),
					'labels' => array( '日程確認', '仕様確認', '資料共有', '色・素材' ),
					'alt'    => '確認事項をスマホ画面の中に集約していく図',
				),
			),
		),
	);
}

/**
 * PLANS のデザインテイスト 5 種。カンプ 917:143（PC）/ 917:722-724・917:857-858（SP）。
 *
 * image はカード内と PACKAGE PLAN の両方で使う商品画像（5 プランとも 1800x909）。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_plan_cards() {
	// 商品画像は 5 プランとも 1800x909 の透過 PNG で書き出し済み。
	// カード内も PACKAGE PLAN の大きい画像も同じファイルを使い、
	// 表示サイズ・位置は CSS 側で 5 プラン共通に扱う（個別調整は持たない）。
	return array(
		array(
			'name'  => 'シンプルモダン',
			'eng'   => 'SIMPLE',
			'image' => 'plans-card-1.png',
		),
		array(
			'name'  => 'ナチュラルモダン',
			'eng'   => 'NATURAL',
			'image' => 'plans-card-2.png',
		),
		array(
			'name'  => 'アーバンモダン',
			'eng'   => 'URBAN',
			'image' => 'plans-card-3.png',
		),
		array(
			'name'  => 'クールモダン',
			'eng'   => 'COOL',
			'image' => 'plans-card-4.png',
		),
		array(
			'name'  => 'スタイリッシュモダン',
			'eng'   => 'STYLISH',
			'image' => 'plans-card-5.png',
		),
	);
}


/**
 * WORKS のカード。カンプ 917:118（PC は扇状に配置）/ 917:881（SP）。
 *
 * 管理画面の施工事例（works）のうち「トップページに記載する」に
 * チェックが入っているものを、新しい順に最大 5 件返す。
 * カードに出すのは画像の 1 枚目とタイトルだけ。
 *
 * @return array<int, array{title:string, url:string, image_id:int}>
 */
function exterior_exone_works_cards() {
	$posts = get_posts(
		array(
			'post_type'        => 'works',
			'post_status'      => 'publish',
			'posts_per_page'   => 5,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'meta_key'         => EXTERIOR_EXONE_WORKS_ON_TOP, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- 件数が少なく、掲載フラグでの絞り込みが目的。
			'meta_value'       => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);

	return array_map(
		function ( $post ) {
			$images = exterior_exone_works_gallery_ids( $post->ID );

			return array(
				'title'    => get_the_title( $post ),
				'url'      => (string) get_permalink( $post ),
				// 1 枚目だけ使う。0 なら画像未設定。
				'image_id' => $images ? (int) $images[0] : 0,
			);
		},
		$posts
	);
}

/**
 * NEWS のカード。カンプ 917:96（PC 5 枚）/ 917:883（SP）。
 *
 * 管理画面のお知らせ（news）から公開済みの新しい 5 件を取る。
 * 1 件も無いときは空配列を返し、セクションごと出さない。
 *
 * @return array<int, array{title:string, url:string, image_id:int}>
 */
function exterior_exone_news_cards() {
	$posts = get_posts(
		array(
			'post_type'        => 'news',
			'post_status'      => 'publish',
			'posts_per_page'   => 5,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	return array_map(
		function ( $post ) {
			return array(
				'title'    => get_the_title( $post ),
				'url'      => (string) get_permalink( $post ),
				// 0 なら画像未設定。カンプ 917:111 と同じグレーの塗りを出す。
				'image_id' => (int) get_post_thumbnail_id( $post ),
			);
		},
		$posts
	);
}

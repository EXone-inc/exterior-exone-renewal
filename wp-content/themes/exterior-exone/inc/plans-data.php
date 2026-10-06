<?php
/**
 * PLANS ページ（固定ページ plans）の表示データ。
 *
 * カンプ: PC 586:390（1920x10871）/ SP 586:850（375x5949）/ guide 586:620
 * 設計メモ: docs/figma-plans-page.md
 * 仕様書: docs/spec-20260930-plans-page.md（決定事項 docs/spec-20260930-plans-page-decisions.md）
 *
 * 文言・画像の単一の出典。inc/dx-data.php / inc/store-data.php と同じ流儀で、
 * セクションごとの表示データはスプリントを進めるたびにここへ足していく。
 * テイスト 5 件の和名と画像は inc/top-data.php の exterior_exone_plan_cards() が
 * 出典（ここでは二重に持たない。英名・説明文だけをこちらで足す）。
 *
 * 画像はすべて差し替え前提の仮画像（AI 生成を含む）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FV（718:244・586:552 / 586:1018・586:1025・586:1023）。
 *
 * 静止画。暗幕は足さない（決定 B「FV」）。SP も同じ画像を切り抜いて使う。
 *
 * @return array<string, string>
 */
function exterior_exone_plans_fv() {
	return array(
		// 718:242（PC。2026-10-02 にカンプの写真が差し替わった）。夕暮れの住宅と前庭。
		// SP カンプ（586:1018）は旧写真のままだが、指示により SP も同じ写真を切り抜いて使う。
		'image'   => 'pc-fv-bg-718-242.jpg',
		// 586:423 / 586:1025。ページの主見出し。
		'title'   => 'PLANS',
		// 586:416 / 586:1023。
		'heading' => '暮らしに合わせて、外構を選ぶ',
	);
}

/**
 * PLAN select（PC 586:555 / SP 586:1029・586:1036）。左 PACKAGE・右 HIGH-END。
 *
 * 本文は段落ごとに行の配列で持つ。PC は行ごとに改行（3 行・中央揃え）、
 * SP は段落の中の改行を外して 2 段落・左揃えで流す（決定 B「PC / SP で文言が違う箇所」）。
 * コピーの copy_sp_break は SP だけ改行する位置（その前までが 1 行目）。
 * 本文中の不要な半角空白は除いてある（決定 C）。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_plans_select() {
	return array(
		array(
			'key'      => 'package',
			'href'     => '#package',
			'name'     => 'パッケージプラン', // 586:421 / 586:1066
			'eng'      => 'PACKAGE PLAN', // 586:419 / 586:1065
			// 586:406 / 586:1030。PC・SP とも同じ画像を同じ比率で切り抜く。
			'image'    => 'pc-package-simple-modern-586-406.png',
			'image_w'  => 1536,
			'image_h'  => 1024,
			'image_sp' => '',
			'copy'     => array( 'デザインを、', 'もっと選びやすく' ), // 586:418 / 586:1032
			// 586:420（PC 3 行）/ 586:1031（SP 2 段落）
			'body'     => array(
				array( '人気のデザインをもとに、建材や価格を最適化したパッケージプランです。' ),
				array( 'デザインテイストやご予算に合わせて、', '多彩なプランから理想の外構を選ぶことができます。' ),
			),
		),
		array(
			'key'      => 'high-end',
			'href'     => '#high-end',
			'name'     => 'ハイエンドプラン', // 586:441 / 586:1042
			'eng'      => 'HIGH-END PLAN', // 586:440 / 586:1037（色は #fefefe に統一。決定 C）
			// 586:579（PC）/ 586:1038（SP 専用）。どちらも影のぶん枠より大きく書き出してある。
			'image'    => 'pc-select-highend-img-586-579.png',
			'image_w'  => 1358,
			'image_h'  => 764,
			'image_sp' => 'sp-select-highend-img-586-1038.png',
			'copy'     => array( '世界に一つだけの', '外構を' ), // 586:439 / 586:1040
			// 586:442（PC 3 行）/ 586:1041（SP 2 段落）
			'body'     => array(
				array( '敷地条件やライフスタイル、ご要望に合わせて、', '一からデザインするフルオーダープラン。' ),
				array( '細部までこだわった設計で、唯一無二の住まいを実現します。' ),
			),
		),
	);
}

/**
 * PACKAGE PLAN の詳細（PC 586:561 / SP 586:1054 ほか）。
 *
 * img list の crop は Figma の画像塗りの切り抜き（幅 % / 左 % / 上 %）。
 * 高さは画像の縦横比で決まるので持たない。w / h はカンプ 1920 での枠の寸法で、
 * SP は同じ帯を縮小して使う（SP カンプの 3 枚は PC の 0.4498 倍）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_plans_package() {
	return array(
		'name'    => 'パッケージプラン', // 586:437 / 586:1034
		'eng'     => 'PACKAGE PLAN', // 586:434 / 586:1033
		// img list 586:409（SP 586:1055）。住宅パース 5 枚。
		'imglist' => array(
			array(
				'file' => 'pc-imglist-01-586-412.png',
				'img'  => array( 976, 651 ),
				'w'    => 413.93,
				'h'    => 209,
				'crop' => array( '117.75%', '-5.25%', '-35.32%' ),
			),
			array(
				'file' => 'pc-imglist-02-586-414.png',
				'img'  => array( 680, 453 ),
				'w'    => 329.84,
				'h'    => 206.01,
				'crop' => array( '100%', '0%', '0%' ),
			),
			array(
				'file' => 'pc-imglist-03-586-411.png',
				'img'  => array( 712, 475 ),
				'w'    => 355.23,
				'h'    => 209,
				'crop' => array( '99.83%', '-0.77%', '-6.23%' ),
			),
			array(
				// PLAN select 左と同じ画像。この 1 枚だけ下角が丸い。
				'file' => 'pc-package-simple-modern-586-406.png',
				'img'  => array( 1536, 1024 ),
				'w'    => 366.29,
				'h'    => 179.97,
				'crop' => array( '121.75%', '-11.1%', '-34.33%' ),
			),
			array(
				'file' => 'pc-imglist-05-586-413.png',
				'img'  => array( 736, 721 ),
				'w'    => 364.61,
				'h'    => 209,
				'crop' => array( '100.81%', '-1.43%', '-70.66%' ),
			),
		),
		'heading' => 'デザインから選ぶ、新しい外構', // 586:433 / 586:1063
		// 586:435。PC の本文（3 行・中央揃え）。
		'body_pc' => array(
			'EXoneのパッケージプランは、人気の外構デザインをもとに、',
			'建材・価格・デザインを最適化した新しい選択肢です。',
			'住宅の雰囲気やライフスタイルに合わせて、豊富なラインアップから理想のプランをお選びいただけます。',
		),
		// 586:1064。SP は PLAN select 左と同じ文（2 段落・左揃え）。
		'body_sp' => array(
			'人気のデザインをもとに、建材や価格を最適化したパッケージプランです。',
			'デザインテイストやご予算に合わせて、多彩なプランから理想の外構を選ぶことができます。',
		),
		// point 586:558（SP 586:1084）。円の中の行。
		'points'  => array(
			array( '分かりやすい', '価格' ),
			array( '選べる', 'デザイン' ),
			array( '豊富な', 'プラン数' ),
			array( '短期間で', '提案可能' ),
			array( '高品質' ),
		),
		// btn black 586:481 / 586:1219。パッケージプランの専用サイト（外部）へ（2026-09-30 決定）。
		'more'    => 'https://www.exone-package.com/',
		'more_new_tab' => true, // 外部サイトなので別タブで開く
	);
}

/**
 * PACKAGE PLAN のデザインテイスト 5 件（style view 586:560 / style list 586:559）。
 *
 * 和名と画像は TOP の exterior_exone_plan_cards() が出典（決定 A）。
 * ここでは英名（「SIMPLE MODERN」の形）と説明文だけを足して合わせる。
 * 画像は TOP の images/top/ から exterior_exone_top_image() で取る。
 *
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_plans_styles() {
	$extras = array(
		array(
			'eng'  => 'SIMPLE MODERN',
			// 586:436（カンプの文）
			'desc' => array(
				'シンプルなブラックの建物に和風のカラーと素材を合わせた和のモダンプランです。',
				'先に駐輪スペースを設けることで、一体感のある空間となりました。',
			),
		),
		array(
			'eng'  => 'NATURAL MODERN',
			// 仮（公開前に差し替え）
			'desc' => array(
				'木目調の素材と植栽を組み合わせ、やわらかな印象に仕上げたナチュラルモダンプランです。',
				'自然素材の温かみが、建物と庭をやさしくつなぎます。',
			),
		),
		array(
			'eng'  => 'URBAN MODERN',
			// 仮（公開前に差し替え）
			'desc' => array(
				'コンクリートとダークトーンの素材で、都会的な印象にまとめたアーバンモダンプランです。',
				'直線を生かした構成で、すっきりとした外観をつくります。',
			),
		),
		array(
			'eng'  => 'COOL MODERN',
			// 仮（公開前に差し替え）
			'desc' => array(
				'モノトーンの色使いと金属素材で、シャープな印象に仕上げたクールモダンプランです。',
				'照明を組み合わせることで、夜の表情も引き立ちます。',
			),
		),
		array(
			'eng'  => 'STYLISH MODERN',
			// 仮（公開前に差し替え）
			'desc' => array(
				'異素材の組み合わせとアクセントカラーで、個性を演出したスタイリッシュモダンプランです。',
				'細部まで整えたデザインで、洗練された住まいを彩ります。',
			),
		),
	);

	$styles = array();

	foreach ( exterior_exone_plan_cards() as $index => $card ) {
		$extra    = isset( $extras[ $index ] ) ? $extras[ $index ] : array(
			'eng'  => '',
			'desc' => array(),
		);
		$styles[] = array(
			'name'  => $card['name'],
			'eng'   => $extra['eng'],
			'desc'  => $extra['desc'],
			'image' => $card['image'],
		);
	}

	return $styles;
}

/**
 * HIGH-END PLAN の詳細（PC 586:569 / SP 586:1227 ほか）。
 *
 * point の各行は円の中の改行。'br' => 'sp' の項目は SP だけ改行する
 * （「高級住宅対応」は SP の円幅で 2 行に折り返る。586:1258）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_plans_highend() {
	// デザイナープラン 2 件の本文はカンプどおり同文（仮。公開前に差し替え）。
	$plan_body = array(
		'細かなディテールまで設計するオーダーメイドプラン。建物との一体感、素材、ライティング、植栽まで。',
		'一人ひとりの理想に合わせてデザインします。',
	);

	return array(
		'name'    => 'ハイエンドプラン', // 586:456（SP 586:1229 の「パッケージプラン」は誤り。決定 C）
		'eng'     => 'HIGH-END PLAN', // 586:444
		// box 586:563。main img は PC のみ（SP は sub img だけ）。
		'main'    => array(
			'file' => 'pc-highend-main-img-586-505.png',
			'img'  => array( 1498, 1050 ),
		),
		'sub'     => array(
			'file' => 'pc-highend-sub-img-586-542.png',
			'img'  => array( 535, 347 ),
		),
		'heading' => 'デザインに、妥協しない', // 586:443 / 586:1239
		// 586:458 / 586:1240（PC・SP とも 3 行）
		'body'    => array(
			'細かなディテールまで設計するオーダーメイドプラン。',
			'建物との一体感、素材、ライティング、植栽まで。',
			'一人ひとりの理想に合わせてデザインします。',
		),
		// point 586:564 / 586:1258
		'points'  => array(
			array( 'lines' => array( 'フルオーダー' ) ),
			array(
				'lines' => array( '高級住宅', '対応' ),
				'br'    => 'sp',
			),
			array( 'lines' => array( 'デザイナー', '提案' ) ),
			array( 'lines' => array( 'オーダーメイド' ) ),
			array( 'lines' => array( '唯一無二の', '世界観を演出' ) ),
		),
		// designer plan 586:566 / top designer plan 586:568。価格はカンプどおり（仮）。
		'plans'   => array(
			array(
				'key'   => 'designer',
				'name'  => 'デザイナープラン', // 586:448 / 586:1281
				'eng'   => 'DESIGNER PLAN', // 586:445 / 586:1280
				'file'  => 'pc-designer-plan-img-586-540.png', // 586:540 / 586:1278
				'img'   => array( 1580, 889 ),
				'body'  => $plan_body, // 586:454 / 586:1282
				'price' => '30,000', // 586:446 / 586:1292
			),
			array(
				'key'   => 'top-designer',
				'name'  => 'トップデザイナープラン', // 586:455 / 586:1285
				'eng'   => 'TOP DESIGNER PLAN', // 586:453 / 586:1284
				'file'  => 'pc-top-designer-plan-img-586-400.png', // 586:400 / 586:1289
				'img'   => array( 1580, 889 ),
				'body'  => $plan_body, // 586:457 / 586:1286
				'price' => '100,000', // 586:447
			),
		),
		'more'    => home_url( '/plans/high-end/' ), // btn white 586:402 / 586:1300。ハイエンドページ（出力時に exterior_exone_store_link()）
	);
}

/**
 * Design Process（PC 586:573 / SP 586:1304 ほか）。共通セクション。
 *
 * 工程 4 つ。写真カードは PC・SP とも同じ画像（SP は縦に切り抜く）。
 * crop_sp は SP の画像塗りの切り抜き（幅 % / 高さ % / 左 % / 上 %）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_plans_process() {
	return array(
		'title'   => 'Design Process', // 586:506 / 586:1307
		'heading' => '理想が形になるまで', // 586:507 / 586:1306
		// 586:397 / 586:1309。白と黒の箱形住宅の 3D パース。
		'image'   => array(
			'file' => 'pc-process-img-586-397.png',
			'img'  => array( 597, 418 ),
		),
		'steps'   => array(
			array(
				'num'     => '01',
				'label'   => 'ご相談',
				'file'    => 'pc-process-card-01-586-512.png', // 586:512 / 586:1331
				'img'     => array( 770, 962 ),
				'crop_sp' => array( '100.29%', '197.3%', '-0.15%', '-29.05%' ),
			),
			array(
				'num'     => '02',
				'label'   => 'パース提案',
				'file'    => 'pc-process-card-02-586-396.png', // 586:396 / 586:1347
				'img'     => array( 770, 962 ),
				'crop_sp' => array( '100%', '197.64%', '0%', '-33.48%' ),
			),
			array(
				'num'     => '03',
				'label'   => 'デザイン',
				'file'    => 'pc-process-card-03-586-399.png', // 586:399 / 586:1349
				'img'     => array( 772, 965 ),
				'crop_sp' => array( '100%', '197.39%', '0%', '-26.96%' ),
			),
			array(
				'num'     => '04',
				'label'   => '施工',
				'file'    => 'pc-process-card-04-586-398.png', // 586:398 / 586:1351
				'img'     => array( 770, 962 ),
				'crop_sp' => array( '100%', '197.39%', '0%', '-82.75%' ),
			),
		),
	);
}

/**
 * Beyond the Plan（PC 586:576 / SP 586:1423 ほか）。共通セクション。
 *
 * 本文は段落ごとに行の配列で持つ。PC は全行を改行して 4 行・中央揃え、
 * SP は段落の中の改行を外し、段落の間を 1 行空けて左揃えで流す。
 * 本文・英字の不要な半角空白は除いてある（決定 C）。
 * 重複見出し 586:1383 は実装しない（決定 C）。
 * 線画の枠・切り抜き（幅 % / 左 % / 上 %）・不透明度はカンプどおり（不透明度の不揃いも触らない）。
 * 切り抜きは PC・SP で同じ（04 だけ SP が別の値）。null は枠と同比率（object-cover）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_plans_beyond() {
	return array(
		// 586:393（PC 横構図）/ 586:1423（SP 縦構図）。別アセット。
		'bg_pc'   => array(
			'file' => 'pc-beyond-bg-586-393.png',
			'img'  => array( 1280, 1229 ),
		),
		'bg_sp'   => 'sp-beyond-bg-586-1423.png',
		'title'   => 'Beyond the Plan', // 586:523 / 586:1386
		'heading' => 'プランの先にある、EXoneの価値', // 586:524 / 586:1385
		// 586:525 / 586:1393
		'body'    => array(
			array(
				'どのプランを選んでも、変わらないものがあります。',
				'「デザインへのこだわり」「DXによる分かりやすい提案」「品質を支える施工体制」',
			),
			array(
				'私たちは、一つひとつのプランに、EXoneが培ってきた技術と経験、',
				'そして「見える顧客体験」という思想を込めています。',
			),
		),
		// point 586:575 / 586:1399〜586:1421
		'points'  => array(
			array(
				'eng'     => 'AI / DX EXPERIENCE', // 586:527（末尾の半角空白は除く）
				'label'   => '完成イメージを、よりリアルに',
				'file'    => 'pc-beyond-01-dx-586-535.png', // 586:535 / 586:1411
				'img'     => array( 564, 266 ),
				'size_pc' => array( 220, 133 ),
				'size_sp' => array( 79, 48 ),
				'crop'    => array( '127.9%', '-14.1%', '0%' ),
				'opacity' => '0.5',
			),
			array(
				'eng'     => 'DESIGN', // 586:528
				'label'   => '住まいと調和するデザイン提案',
				'file'    => 'pc-beyond-02-design-586-536.png', // 586:536 / 586:1407
				'img'     => array( 636, 424 ),
				'size_pc' => array( 290, 137 ),
				'size_sp' => array( 82, 39 ),
				'crop'    => array( '109.39%', '0%', '-15.66%' ),
				'opacity' => '0.8',
			),
			array(
				'eng'     => 'QUALITY', // 586:529
				'label'   => '品質管理と5年間の工事保証',
				'file'    => 'pc-beyond-03-quality-586-394.png', // 586:394 / 586:1403
				'img'     => array( 468, 312 ),
				'size_pc' => array( 234, 156 ),
				'size_sp' => array( 68, 45 ),
				'crop'    => null,
				'opacity' => '0.74',
			),
			array(
				'eng'     => 'SUPPORT', // 586:530
				'label'   => '相談から完成後まで、一貫したサポート',
				'file'    => 'pc-beyond-04-support-586-541.png', // 586:541 / 586:1399
				'img'     => array( 624, 295 ),
				'size_pc' => array( 298, 147 ),
				'size_sp' => array( 82, 42 ),
				'crop'    => array( '104.51%', '0%', '0%' ),
				'crop_sp' => array( '109.49%', '-4.76%', '0%' ),
				'opacity' => '0.9',
			),
		),
	);
}

/**
 * PLANS ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * images/plans/webp/ に同名の .webp があればそちらを返す。元画像は残してあるので、
 * webp ディレクトリを削除すれば元の配信に戻る（images/dx/ と同じ）。
 *
 * @param string $file images/plans/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_plans_image( $file ) {
	$webp = 'images/plans/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/plans/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/plans/' . $file );
}

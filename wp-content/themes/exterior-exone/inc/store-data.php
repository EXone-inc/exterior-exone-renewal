<?php
/**
 * 支店ページ（STORE）の表示データ。
 *
 * カンプ: PC 534:628（青森支店ベース）/ SP カンプなし
 * 設計メモ: docs/figma-store-page.md
 *
 * 7 拠点の単一の出典。フッター・ハンバーガーメニューの STORE もここから導出する
 *（inc/menus.php の exterior_exone_store_menu_items()）。
 * セクションごとの表示データはスプリントを進めるたびにここへ足していく。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 7 拠点のデータ。キーは固定ページのスラッグ。
 *
 * 並び順はフッター STORE の並び（カンプ 669:836）に合わせる。
 *
 * TODO: 青森支店はカンプ（628:307 の店舗情報テーブル）の実値。
 *       他 6 拠点は住所・電話・営業時間・対応エリアが未支給のため、
 *       当面は青森と同じ値 + 支店名 / 地域名だけ差し替えた仮データ。
 *       実データが届いたらこの配列を差し替える。
 *
 * @return array<string, array<string, string>>
 */
function exterior_exone_stores() {
	// 仮データの共通部分（青森支店の値）。実データが届いた拠点から個別に上書きする。
	$provisional = array(
		'zip'      => '〒030-0846',
		'address'  => '青森県青森市青葉３丁目１−８',
		'tel'      => '017-711-8031',
		'hours'    => 'AM10:00～PM18:00',
		'closed'   => '日・祝',
		'parking'  => '10台',
		'areas'    => '青森市、五所川原市、平内町、野辺地町、外ヶ浜町',
		// CTA（§16）の 2 ボタンの遷移先。LINE の友だち追加 URL と発信先が
		// 未定のため仮置き（決定事項 Q9）。決まったら支店ごとに入れる。
		'line_url' => '#',
		'tel_url'  => '#',
	);

	return array(
		'sendai'    => array_merge(
			$provisional,
			array(
				'name'   => '仙台支店',
				'region' => '仙台',
			)
		),
		'totsuka'   => array_merge(
			$provisional,
			array(
				'name'   => '戸塚支店',
				'region' => '戸塚',
			)
		),
		'morioka'   => array_merge(
			$provisional,
			array(
				'name'   => '盛岡支店',
				'region' => '盛岡',
			)
		),
		// 青森支店のみカンプの実値。
		'aomori'    => array(
			'name'     => '青森支店',
			'region'   => '青森',
			'zip'      => '〒030-0846',
			'address'  => '青森県青森市青葉３丁目１−８',
			'tel'      => '017-711-8031',
			'hours'    => 'AM10:00～PM18:00',
			'closed'   => '日・祝',
			'parking'  => '10台',
			'areas'    => '青森市、五所川原市、平内町、野辺地町、外ヶ浜町',
			// 上と同じく仮置き。
			'line_url' => '#',
			'tel_url'  => '#',
		),
		'hachinohe' => array_merge(
			$provisional,
			array(
				'name'   => '八戸支店',
				'region' => '八戸',
			)
		),
		'hirosaki'  => array_merge(
			$provisional,
			array(
				'name'   => '弘前支店',
				'region' => '弘前',
			)
		),
		'tokyo'     => array_merge(
			$provisional,
			array(
				'name'   => '東京オフィス',
				'region' => '東京',
			)
		),
	);
}

/**
 * 支店ページの URL。
 *
 * @param string $slug 支店のスラッグ。
 * @return string
 */
function exterior_exone_store_url( $slug ) {
	return home_url( '/store/' . $slug . '/' );
}

/**
 * STORE の親ページ（支店一覧）の URL。
 *
 * @return string
 */
function exterior_exone_store_index_url() {
	return home_url( '/store/' );
}

/**
 * 表示中の固定ページに対応する支店データ。
 *
 * どの支店かは固定ページのスラッグで決まる（カスタムフィールドは使わない）。
 *
 * @return array<string, string>|null 支店ページでなければ null。
 */
function exterior_exone_current_store() {
	if ( ! is_page() ) {
		return null;
	}

	$slug   = (string) get_post_field( 'post_name', get_queried_object_id() );
	$stores = exterior_exone_stores();

	return isset( $stores[ $slug ] ) ? array_merge( array( 'slug' => $slug ), $stores[ $slug ] ) : null;
}

/**
 * 支店ページ（7 拠点のいずれか）を表示中かどうか。
 *
 * @return bool
 */
function exterior_exone_is_store_page() {
	return null !== exterior_exone_current_store();
}

/**
 * FV（ヒーロー）の共通コピーと画像。535:838 / 561:3 / 561:46 / 535:844-850。
 *
 * カンプ上で支店ごとに変わるのはバッジの支店名だけ。写真は全支店共通の仮画像で、
 * 後日同名の高解像度版に差し替える前提。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_fv() {
	return array(
		'eyebrow' => '外構エクステリア施工専門店',
		// 534:650。2 行組み。
		'titles'  => array( 'DESIGN', 'YOUR LIFE' ),
		'lead'    => '理想の暮らしを、カタチに。',
		'logo'    => 'hero-logo-535-951.png',
		// 背景写真 5 枚 + 561:46 の円形サムネイル 5 個。サムネイルを押すと背景が
		// クロスフェードで切り替わり、9 秒で自動送りされる（js/store-fv.js）。
		// TOP の FV（動画 5 本）と同じ操作感だが、こちらは写真。
		//
		// TODO: カンプの背景写真は 535:838 の 1 枚だけなので、2〜5 枚目は TOP の FV の
		//       静止画（images/top/）を仮で借りている。本番写真 5 枚は後日支給。
		//       dir は画像の置き場所（store = images/store/ / top = images/top/）。
		//
		// 4 個目のサムネイルはカンプが 3 枚のコラージュなので、カンプと同じ配置
		//（左半分 561:56 + その下に 561:59 / 右半分 561:58）で 1 枚に合成したものを使う。
		'slides'  => array(
			array(
				'image' => 'hero-bg-535-838.jpg',
				'dir'   => 'store',
				'thumb' => 'hero-thumb-561-62.jpg',
			),
			array(
				'image' => 'FV02-poster.jpg',
				'dir'   => 'top',
				'thumb' => 'hero-thumb-561-65.jpg',
			),
			array(
				'image' => 'FV03-poster.jpg',
				'dir'   => 'top',
				'thumb' => 'hero-thumb-561-68.jpg',
			),
			array(
				'image' => 'FV04-poster.jpg',
				'dir'   => 'top',
				'thumb' => 'hero-thumb-561-56-58-59.jpg',
			),
			array(
				'image' => 'FV05-poster.jpg',
				'dir'   => 'top',
				'thumb' => 'hero-thumb-561-52.jpg',
			),
		),
		// FV 下端をまたぐ写真 4 枚（535:844 / 535:845 / 268:348 / 535:850）。
		'photos'  => array(
			'hero-band-img1-535-844.jpg',
			'hero-band-img2-535-845.jpg',
			'hero-band-img3-268-348.jpg',
			'hero-band-img4-535-850.jpg',
		),
	);
}

/**
 * 導入コピー。535:853 / 535:855 / 535:966。
 *
 * 本文は支店ごとに ［支店名］［地域名］ が入れ替わる。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_intro( array $store ) {
	return array(
		'icon'    => 'intro-icon-house-535-966.png',
		'heading' => '新築外構からカーポート、フェンス、庭づくりまで',
		'lead'    => array(
			'EXone' . $store['name'] . 'は、デザインと分かりやすい提案で、',
			'住まいと暮らしに調和する外構・エクステリアをご提案します。',
			$store['region'] . '周辺の外構工事は、私たちにご相談ください。',
		),
	);
}

/**
 * 施工イメージ帯の線画パース 5 枚。535:860 / 669:916 / 535:861 / 669:920 / 535:871。
 *
 * width / height はカンプ 1920 での表示サイズ（大きさが不揃いなので個別に持つ）。
 * CSS 側で --strip-w / --strip-h として受け取り、帯ごと比例縮小する。
 *
 * @return array<int, array{file: string, width: float, height: float}>
 */
function exterior_exone_store_strip_images() {
	return array(
		array(
			'file'   => 'strip-house1-535-860.png',
			'width'  => 413.9,
			'height' => 209,
		),
		array(
			'file'   => 'strip-house2-669-916.png',
			'width'  => 329.8,
			'height' => 206,
		),
		array(
			'file'   => 'strip-house3-535-861.png',
			'width'  => 355.2,
			'height' => 209,
		),
		array(
			'file'   => 'strip-house4-669-920.png',
			'width'  => 366.3,
			'height' => 180,
		),
		array(
			'file'   => 'strip-house5-535-871.png',
			'width'  => 364.6,
			'height' => 209,
		),
	);
}

/**
 * 実績数字の 4 項目。Group 316（535:911）。全支店共通。
 *
 * number は js/scroll-reveal.js の data-countup がそのまま読む書式。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_store_stats() {
	return array(
		array(
			'number' => '500',
			'unit'   => '件/年',
			'label'  => '年間施工数',
			'image'  => 'stats-photo1-535-877.jpg',
		),
		array(
			'number' => '98.5',
			'unit'   => '%',
			'label'  => '顧客満足度',
			'image'  => 'stats-photo2-535-887.jpg',
		),
		array(
			'number' => '5',
			'unit'   => '年',
			'label'  => '長期施工保証',
			'image'  => 'stats-photo3-535-880.jpg',
		),
		array(
			'number' => '3,500',
			'unit'   => '件以上',
			'label'  => '累計実績',
			'image'  => 'stats-photo4-535-884.jpg',
		),
	);
}

/**
 * 不安提示。535:917 / 535:919 / 535:922 / 535:933。全支店共通。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_concern() {
	return array(
		'heading' => '外構づくりで、こんな不安を感じていませんか',
		// カンプは 1 行ごとに「 」で囲っている（535:919）。
		'worries' => array(
			'何から決めればいいのか分からない',
			'見積りの金額が妥当なのか判断できない',
			'新築住宅に合う外構を提案してほしい',
		),
		'notes'   => array(
			'外構工事は、価格や工事内容が分かりにくく、完成するまで実際のイメージをつかみにくいものです。',
			'そんな外構づくりの不安を、EXoneが一つずつ解消します。',
		),
		'sketch'  => 'concern-sketch-535-933.png',
	);
}

/**
 * 選ばれる理由。535:1009 / 535:1000 / Group 354（669:925）。全支店共通。
 *
 * 6 項目の円周上の配置（座標）は css/store.css 側が持つ（--item-x / --icon-y）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_reasons() {
	return array(
		'copy_heading' => array(
			'外構づくりを、',
			'もっと分かりやすく、もっと心地よく。',
		),
		'copy_lead'    => array(
			'EXoneは、デザイン・価格・施工品質のどれか一つだけではなく、相談から完成後までの体験全体を大切にしています。',
			'専門スタッフによる提案力と、テクノロジーを活用した分かりやすい仕組みを組み合わせ、',
			'お客様が安心して選べる外構づくりを実現します。',
		),
		'title'        => '選ばれる理由',
		'title_lead'   => array(
			'デザイン・品質・対応力。すべてに理由があります。',
			'完成までのプロセスも、安心も、分かりやすくご提供します。',
		),
		'eng'          => 'EXTERIOR',
		'eng_sub'      => 'DESIGN FOR A BETTER LIFE',
		'items'        => array(
			array(
				'lead'   => '暮らしと建物に調和する',
				'label'  => 'デザイン',
				'icon'   => 'reasons-icon-house-535-1014.png',
				'icon_w' => 171.2,
				'icon_h' => 89.9,
			),
			array(
				'lead'   => '完成イメージを確認できる',
				'label'  => 'ビジュアル提案',
				'icon'   => 'reasons-icon-tablet-599-73.png',
				'icon_w' => 144.4,
				'icon_h' => 90.6,
			),
			array(
				'lead'   => '地域の気候や住環境に合わせた',
				'label'  => '提案',
				'icon'   => 'reasons-icon-weather-602-89.png',
				'icon_w' => 134.3,
				'icon_h' => 100.5,
			),
			array(
				'lead'   => '分かりやすく整理された',
				'label'  => '見積',
				'icon'   => 'reasons-icon-doc-599-69.png',
				'icon_w' => 114.7,
				'icon_h' => 100.6,
			),
			array(
				'lead'   => '工事後も安心できる',
				'label'  => '5年間の工事保証',
				'icon'   => 'reasons-icon-5years-602-93.png',
				'icon_w' => 89.1,
				'icon_h' => 104.4,
			),
			array(
				'lead'   => '専門スタッフによる',
				'label'  => '施工・品質管理',
				'icon'   => 'reasons-icon-worker-602-82.png',
				'icon_w' => 96.2,
				'icon_h' => 102.3,
			),
		),
	);
}

/**
 * SERVICE。572:456 / 691:1036 / 689:1001 / Group 377（691:1009）。全支店共通。
 *
 * 1 行目の英字はカンプでは「GAR SPACE」だが、誤記のため「CAR SPACE」で実装する
 *（docs/spec-20260911-store-page-decisions.md Q1）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_service() {
	return array(
		'title'      => 'SERVICE',
		'cards'      => array(
			array(
				'eng'   => 'NEW BUILD',
				'label' => '新築外構工事',
				// 元 PNG は外周 20px ほどが半透明でカード下端に灰色の帯が出たため、
				// フェザーを落として不透明化した JPG に差し替えてある。
				'image' => 'service-newbuild-691-1036.jpg',
			),
			array(
				'eng'   => 'RENOVATION',
				'label' => 'リフォーム外構工事',
				'image' => 'service-renovation-689-1001.jpg',
			),
		),
		'banner'     => '外構・エクステリアを、まとめてご相談いただけます',
		'lead'       => array(
			'新築住宅のトータル外構から、カーポートやフェンスなどの部分工事、庭のリフォームまで幅広く対応しています。',
			'複数の商品や工事をまとめて計画することで、デザインの統一感だけでなく、使いやすさや予算のバランスまで考えたご提案が可能です。',
		),
		'categories' => array(
			array(
				'eng'    => 'CAR SPACE',
				'label'  => '駐車スペース',
				'photos' => array(
					array(
						'label' => 'カーポート',
						'image' => 'service-carport-572-487.jpg',
					),
					array(
						'label' => 'ガレージ',
						'image' => 'service-garage-572-488.jpg',
					),
					array(
						'label' => '土間コンクリート',
						'image' => 'service-concrete-572-489.jpg',
					),
				),
			),
			array(
				'eng'    => 'GARDEN',
				'label'  => '庭・ガーデン',
				'photos' => array(
					array(
						'label' => '人工芝',
						'image' => 'service-turf-572-490.jpg',
					),
					array(
						'label' => 'ウッドデッキ・タイルデッキ',
						'image' => 'service-deck-572-484.jpg',
					),
					array(
						'label' => '植栽',
						'image' => 'service-planting-572-491.jpg',
					),
				),
			),
			array(
				'eng'    => 'GATE AREA',
				'label'  => '門まわり',
				'photos' => array(
					array(
						'label' => '門柱',
						'image' => 'service-gatepost-572-477.jpg',
					),
					array(
						'label' => '門まわり',
						'image' => 'service-gate-572-486.jpg',
					),
					array(
						'label' => 'アプローチ',
						'image' => 'service-approach-572-478.jpg',
					),
				),
			),
			array(
				'eng'    => 'OTHERS',
				'label'  => 'その他',
				'photos' => array(
					array(
						'label' => 'フェンス',
						'image' => 'service-fence-572-475.jpg',
					),
					array(
						'label' => '照明・ライティング',
						'image' => 'service-lighting-685-977.jpg',
					),
					array(
						'label' => '物置',
						'image' => 'service-shed-685-985.jpg',
					),
				),
			),
		),
	);
}

/**
 * 見える安心。Group 360（669:931）/ Group 376（685:994）/ 572:617。全支店共通。
 *
 * 写真 4 枚は §1 の帯と同じグリッド（429.3x224.7・ピッチ 454.5）に並ぶ。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_visible() {
	return array(
		// 572:282 / 572:280。
		'eyebrow' => 'Clearer Exterior Experience',
		'heading' => '見えない不安を、見える安心へ。',
		// 572:567。線画スケッチ（opacity は CSS 側で .3 にする）。
		'sketch'  => 'visible-sketch-572-567.png',
		'cards'   => array(
			array(
				'eng'   => 'Visible Pricing',
				'label' => '見える価格',
				'image' => 'visible-pricing-572-292.jpg',
			),
			array(
				'eng'   => 'Visible Design',
				'label' => '見えるデザイン',
				'image' => 'visible-design-685-988.jpg',
			),
			array(
				'eng'   => 'Visible Process',
				'label' => '見える行程',
				'image' => 'visible-process-685-991.jpg',
			),
			array(
				'eng'   => 'Visible Quality',
				'label' => '見える品質',
				'image' => 'visible-quality-685-993.jpg',
			),
		),
		'lead'    => array(
			'EXoneが大切にしているのは、完成した外構だけではありません。',
			'価格、デザイン、工事内容、完成までの流れをできる限り分かりやすくし、',
			'お客様が納得しながら外構づくりを進められる体験を提供します。',
			'考えていることをうまく言葉にできなくても大丈夫です。',
			'対話とビジュアルを通じて、理想の暮らしを一緒に形にしていきます。',
		),
	);
}

/**
 * DX EXPERIENCE。Group 331（572:619）。全支店共通。
 *
 * TOP の exterior_exone_dx_steps() と同じ 3 ステップだが、支店はビジュアルが
 * 動画ではなく静止画で、03 のタグが「高精度3Dパース」になる
 *（docs/spec-20260911-store-page-decisions.md Q3）。
 *
 * ビジュアルは 01 がカンプの画像（572:593・images/store/）、02 / 03 は TOP の
 * DX と同じ画像（images/top/）を参照する。dir がその置き場所。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_dx() {
	return array(
		'title' => 'DX EXPERIENCE',
		// 572:599。
		'copy'  => array(
			'デジタル技術で外構を完成前に可視化し、',
			'理想の暮らしをカタチに。',
		),
		'steps' => array(
			array(
				'number' => '01 / EXPERIENCE',
				'title'  => '暮らしをその場で体感',
				'desc'   => '試す・探す・比べることで、暮らしのイメージをより直感的に。',
				'tags'   => array( 'バーチャル展示場', 'AIラフ再現' ),
				'image'  => 'dx-main-image-572-593.jpg',
				'dir'    => 'store',
			),
			array(
				'number' => '02 / VISUALIZE',
				'title'  => '完成を見える化',
				'desc'   => '高精度3Dパースで、図面だけでは分かりにくい高さや奥行きまで確認。',
				'tags'   => array( '高精度3Dパース' ),
				// TOP の DX 02（dx_video2.mp4）と同じ絵。
				'image'  => 'dx_video2-poster.jpg',
				'dir'    => 'top',
			),
			array(
				'number' => '03 / SHARE',
				'title'  => '確認事項をひとまとめ',
				'desc'   => '変更点・確認事項・共有内容を一箇所に集約し、認識のズレを防止。',
				'tags'   => array( '高精度3Dパース' ),
				// TOP の DX 03 の背景写真。
				'image'  => 'dx_share.jpg',
				'dir'    => 'top',
			),
		),
		// 572:602。リンク先は未定のため仮置き（決定事項 Q9）。
		'more'  => '#',
	);
}

/**
 * PLANS。599:50 / Group 362（669:933）/ Group 363（669:934）/
 * Group 338（610:283）/ Group 374（669:965）。全支店共通。
 *
 * 特長 4 列のアイコンは「選ばれる理由」（§6）と同じ線画ファイルを使う。
 * icon_w / icon_h はカンプ 1920 での表示サイズ（CSS 側で単位を掛ける）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_plans() {
	return array(
		'title'   => 'PLANS',
		'jp'      => '理想に近い外構を、分かりやすいプランから探す',
		'lead'    => array(
			'「どんな外構にしたいか、まだ具体的に決まっていない」という方にも、デザインや暮らし方、予算の目安から選べる外構プランをご用意しています。',
			'気になるプランを起点に、敷地条件や建物、ご希望に合わせて調整することも可能です。',
		),
		'package' => array(
			'label'    => 'パッケージプラン',
			'eng'      => 'PACKAGE PLAN',
			'lead'     => array(
				'人気の外構デザインをベースに、必要な要素をわかりやすく整理。',
				'予算感をつかみながら、スムーズにお選びいただけます。',
			),
			'image'    => 'plans-package-house-599-13.png',
			// 599:59。リンク先は未定のため仮置き（決定事項 Q9）。
			'more'     => '#',
			'features' => array(
				array(
					'icon'   => 'reasons-icon-doc-599-69.png',
					'icon_w' => 114.7,
					'icon_h' => 100.6,
					'lines'  => array( 'ご予算に合わせた', '分かりやすい価格設計' ),
				),
				array(
					'icon'   => 'reasons-icon-tablet-599-73.png',
					'icon_w' => 144.4,
					'icon_h' => 90.6,
					'lines'  => array( '豊富なデザインから', '簡単に選べる' ),
				),
				array(
					'icon'   => 'reasons-icon-worker-602-82.png',
					'icon_w' => 96.2,
					'icon_h' => 102.3,
					'lines'  => array( '打ち合わせから着工まで', 'スピーディー' ),
				),
				array(
					'icon'   => 'reasons-icon-house-535-1014.png',
					'icon_w' => 171.2,
					'icon_h' => 89.9,
					'lines'  => array( 'コストとデザインの', 'バランスに優れたプラン' ),
				),
			),
		),
		'highend' => array(
			'label'    => 'ハイエンドプラン',
			'eng'      => 'HIGH-END PLAN',
			'lead'     => array(
				'敷地条件や暮らしに合わせ、細部までこだわりたい方向けの自由設計プラン。',
				'理想の外構を一からかたちにします。',
			),
			'image'    => 'highend-photo-610-273.jpg',
			// 610:280。リンク先は未定のため仮置き（決定事項 Q9）。
			'more'     => '#',
			'features' => array(
				array(
					'icon'   => 'reasons-icon-tablet-599-73.png',
					'icon_w' => 144.4,
					'icon_h' => 90.6,
					'lines'  => array( '暮らしに合わせた', 'デザイン提案' ),
				),
				array(
					'icon'   => 'highend-icon-diamond-606-133.png',
					'icon_w' => 130.6,
					'icon_h' => 88.2,
					'lines'  => array( 'ハイグレードな', '素材・商品を採用' ),
				),
				array(
					'icon'   => 'highend-icon-blueprint-622-298.png',
					'icon_w' => 128.4,
					'icon_h' => 90.5,
					'lines'  => array( '唯一無二の', 'デザインを実現' ),
				),
				array(
					'icon'   => 'highend-icon-house-606-138.png',
					'icon_w' => 204.1,
					'icon_h' => 89.9,
					'lines'  => array( '細部までこだわる', '自由設計プラン' ),
				),
			),
		),
	);
}

/**
 * WORKS の事例。698:1136 / Group 380 / Group 382 / Group 379。全支店共通。
 *
 * 写真グリッドは既存 CPT works の最新を使い（template-parts/store/section-works.php）、
 * ここが持つのは枠を埋めるカンプ写真と、要望・提案・見積の文言。
 *
 * 見出し「EXoneからのご提案」はカンプ上で見切れているが全文を出す
 *（docs/spec-20260911-store-page-decisions.md Q4）。左右ボックスの本文も 18px に統一。
 *
 * TODO: 要望・提案・見積はカンプの 1 事例ぶんの実値。支店ごと・事例ごとの
 *       出し分けが必要になったらこの配列を投稿側へ移す。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_works() {
	return array(
		'title'     => 'WORKS',
		'jp'        => '暮らしの希望から生まれた、外構のご提案',
		'lead'      => array(
			'EXoneが紹介するのは、完成写真だけではありません。',
			'お客様がどのような悩みや希望を持ち、どんな考え方でデザインをご提案したのか。',
			'完成までの背景とともに、外構づくりの事例をご紹介します。',
		),
		// 698:1181 / 698:1178 / 698:1180。CPT の画像が足りないときに使う。
		'photos'    => array(
			'works-main-698-1181.jpg',
			'works-sub2-698-1178.jpg',
			'works-sub3-698-1180.jpg',
		),
		// 698:1197 + Group 381（698:1329）。
		'render'    => array(
			'image' => 'works-3d-698-1197.png',
			'label' => 'ご提案時3Dパース',
		),
		'request'   => array(
			'title' => 'お客様のご要望',
			'lines' => array(
				'・和風の自宅に調和する落ち着いた雰囲気の外構にしたい',
				'・シンプルながらも門まわりにデザイン性を取り入れたい',
				'・予算を350万に抑えたい',
			),
		),
		'proposal'  => array(
			'title' => 'EXoneからのご提案',
			'body'  => '建物の雰囲気に合わせ、ツートンカラーの門柱を採用し、和モダンな印象に仕上げました。玄関前には植栽と高さの異なる塗り壁を配置し、立体感とアクセントのあるアプローチをご提案しました。',
		),
		'estimate'  => array(
			'columns' => array( '施工内容', '使用建材', '概算見積' ),
			'rows'    => array(
				array(
					'work'     => 'フロア',
					'material' => 'コンクリート、タイル:セラレバンテ、砕石 + 防草シート:グランドシールド、ウッドチップ、化粧真砂土敷きならし',
					'price'    => '¥1,048,220',
				),
				array(
					'work'     => 'アクセントライン',
					'material' => '砕石ライン 幅:80mm + 型枠、ピンコロライン 幅:90mm',
					'price'    => '¥81,060',
				),
				array(
					'work'     => '壁',
					'material' => '12cmコンクリートブロック3段 + 塗り、12cmコンクリートブロック5段 + 塗り',
					'price'    => '¥302,400',
				),
				array(
					'work'     => '門まわり(その他)',
					'material' => 'エバースクリーンフレーム W1800・H2400',
					'price'    => '¥76,600',
				),
				array(
					'work'     => 'ライティング',
					'material' => 'ガーデンポールライト 5型 (12V)、ガーデンアップライト ミオ 4.5W フード 12V、ウォールアップライト 900 12V、LEDIUS ローボルトトランス75W、ガーデンスケープ用コード 15m(12V用)、ドライコーン(12V用)',
					'price'    => '¥180,000',
				),
				array(
					'work'     => 'ウッドデッキ',
					'material' => '人工木 エバーエコウッドII(床板幅195mm)',
					'price'    => '¥425,600',
				),
				array(
					'work'     => '植栽',
					'material' => '落葉樹(高さ2.0m/2.5m/4.0m/3.0m) + 土壌改良、下草・草花類 + 土壌改良',
					'price'    => '¥307,500',
				),
				array(
					'work'     => 'その他',
					'material' => '門まわりパッケージ AB46、アートボード門柱、エクスレッズ ウォールライト5型、letter cube',
					'price'    => '¥479,700',
				),
			),
		),
		// 698:1217。全角スペースを含めてカンプどおり。
		'total'     => '合計　¥2,901,080',
	);
}

/**
 * REVIEWS。620:290 / 658:10 / 658:9 / Group 369（669:960）。全支店共通。
 *
 * カンプの英字見出しは「REVIERS」だが誤記として REVIEWS で実装する
 *（docs/spec-20260911-store-page-decisions.md）。
 *
 * カンプのカードは画像・タイトル・本文ともダミー。決定事項 Q10 のとおり
 * ダミーのまま置き、本文が長いときは 3 行で省略する（CSS の line-clamp）。
 * 画像はカンプでも灰色のプレースホルダなので写真は持たせていない。
 *
 * TODO: 実際の口コミが用意できたらこの配列を差し替える。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_reviews() {
	// 658:9 のダミー本文（「テキスト」×22）。
	$dummy = str_repeat( 'テキスト', 22 );

	return array(
		'title' => 'REVIEWS',
		'jp'    => 'EXoneで外構づくりをされたお客様の声',
		'lead'  => array(
			'デザインのこと、費用のこと、担当者の対応、完成後の暮らし。',
			'実際にEXoneへご相談いただいたお客様の声を通じて、外構づくりの過程や完成後の変化をご紹介します。',
		),
		'stars' => 'reviews-stars-669-223.svg',
		'cards' => array(
			array(
				'score' => '5.0',
				'title' => 'タイトル',
				'body'  => $dummy,
			),
			array(
				'score' => '5.0',
				'title' => 'タイトル',
				'body'  => $dummy,
			),
			array(
				'score' => '5.0',
				'title' => 'タイトル',
				'body'  => $dummy,
			),
		),
	);
}

/**
 * FLOW。661:13-15 / Group 371（669:962 矢羽根）/ Group 370（669:961 行）。全支店共通。
 *
 * label は矢羽根と行の見出しで共用する（カンプは 06 が「完成・お引き渡し」と
 * 「完成・お引渡し」で揺れているが、決定事項のとおり前者に統一する）。
 * 03 の説明文が 01 と同じなのもカンプどおり（決定事項 Q2）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_flow() {
	return array(
		'title' => 'FLOW',
		'jp'    => 'ご相談から完成まで、分かりやすくサポート',
		'lead'  => array(
			'外構づくりが初めての方にも安心して進めていただけるよう、',
			'ご相談から設計、見積り、施工、完成後のアフターサポートまで、一つひとつ丁寧にご案内します。',
		),
		'steps' => array(
			array(
				'number' => '01',
				'label'  => 'お問い合わせ・来店予約',
				'lines'  => array(
					'フォーム、LINE、お電話のいずれかでご相談ください。',
					'現状のお悩みやご要望など、お気軽にお問い合わせください。',
				),
			),
			array(
				'number' => '02',
				'label'  => 'ヒアリング・現地調査',
				'lines'  => array(
					'現地調査、もしくは図面を用いたヒアリングを行い、',
					'実際にかかる費用など具体的にご提示させていただきます。',
				),
			),
			array(
				'number' => '03',
				'label'  => 'ご提案・お見積り',
				'lines'  => array(
					'フォーム、LINE、お電話のいずれかでご相談ください。',
					'現状のお悩みやご要望など、お気軽にお問い合わせください。',
				),
			),
			array(
				'number' => '04',
				'label'  => '内容の調整・ご契約',
				'lines'  => array(
					'内容・プラン・お見積りにご納得いただけましたら、ご契約となります。',
					'ご契約から施工開始まで最短1週間で行います。',
				),
			),
			array(
				'number' => '05',
				'label'  => '着工・施工管理',
				'lines'  => array(
					'カーポートの施工期間は2日、コンクリートの施工期間は1週間〜10日程度',
					'を目安としております。',
				),
			),
			array(
				'number' => '06',
				'label'  => '完成・お引き渡し',
				'lines'  => array(
					'完成後、現地にてお客さまと施工箇所を確認し、問題がなければお引き渡し',
					'となります。',
				),
			),
			array(
				'number' => '07',
				'label'  => 'アフターサポート',
				'lines'  => array(
					'工事が終わってからも、安心して暮らしていただくためにEXoneでは、',
					'対象工事を5年間保証しています。',
				),
			),
		),
	);
}

/**
 * Quality（施工保証）。669:258 / 669:261 / 669:255 / 669:263 / 669:969 / 669:265。
 * 全支店共通。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_quality() {
	return array(
		'image'   => 'quality-bg-669-258.jpg',
		'eng'     => 'Quality You Can Trust',
		'heading' => '完成したあとも続く、安心の品質。',
		'lead'    => array(
			'外構工事は、完成時の見た目だけでなく、長く安心して使えることが大切です。',
			'EXoneでは、施工基準や工程管理を整備し、担当者だけに依存しない品質管理に取り組んでいます。',
			'さらに、対象工事には5年間の工事保証を設け、完成後の暮らしも支えます。',
		),
		// 669:967。「5」だけ 2px 大きい（--st-quality-badge-num）。
		'badge'   => array(
			'label'  => '施工保証',
			'number' => '5',
			'unit'   => '年',
		),
		'note'    => '※工事保証は製品メーカーが定める製品保証とは異なります。保証対象・条件についてはスタッフへご確認ください。',
	);
}

/**
 * FAQ。669:872-874 / Group 372（669:963）/ 669:906（山形アイコン）。全支店共通。
 *
 * TODO: 回答文はカンプに無いため仮文（決定事項 Q11）。公開前に正文へ差し替える。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_faq() {
	return array(
		'title' => 'FAQ',
		'jp'    => '外構のご相談で、よくいただくご質問',
		'lead'  => array(
			'費用や相談方法、工事期間、対応エリアなど、ご相談前に多く寄せられる質問をまとめています。',
			'掲載されていない内容についても、LINE・電話・お問い合わせフォームからお気軽にお問い合わせください。',
		),
		'items' => array(
			array(
				'q' => '相談や見積りに費用はかかりますか？',
				'a' => 'ご相談・現地調査・お見積りはすべて無料です。プランのご提案までに費用をいただくことはありませんので、比較検討の段階でもお気軽にご相談ください。',
			),
			array(
				'q' => 'まだ建物の図面しかないのですが、相談できますか？',
				'a' => '図面の段階からご相談いただけます。むしろ着工前の早い段階のほうが、駐車スペースの位置や高低差を建物の計画と合わせて検討でき、結果として費用も抑えやすくなります。',
			),
			array(
				'q' => '他社との相見積りでも相談できますか？',
				'a' => 'もちろん可能です。お見積りの内容や仕様の違いについてもご説明しますので、比較の材料としてお気軽にご利用ください。',
			),
			array(
				'q' => '外構工事の予算はどのくらい必要ですか？',
				'a' => '敷地の広さや工事範囲によって異なりますが、新築の外構では100万円〜300万円程度が目安です。ご予算をお伝えいただければ、その範囲で優先順位をつけたプランをご提案します。',
			),
			array(
				'q' => '工事期間はどのくらいかかりますか？',
				'a' => 'カーポート1台分の設置で2日程度、コンクリート工事で1週間〜10日程度が目安です。工事の範囲によって変わりますので、着工前に工程表でご案内します。',
			),
			array(
				'q' => '一部分だけの工事にも対応していますか？',
				'a' => 'フェンスの設置や物置の追加、駐車スペースの拡張など、一部分のみの工事も承っています。気になる場所からご相談ください。',
			),
			array(
				'q' => '対応エリア外でも相談できますか？',
				'a' => 'まずはご相談ください。エリアによっては出張費を申し受ける場合や、ご対応が難しい場合もありますので、内容をうかがったうえでご案内します。',
			),
			array(
				'q' => '土地や建物に合う商品を提案してもらえますか？',
				'a' => '建物のデザインや周辺環境に加え、雪や風といった地域の条件をふまえて、暮らしに合う商品とプランをご提案します。',
			),
		),
		'arrow' => 'faq-arrow-669-906.svg',
	);
}

/**
 * AREA。620:292 / 628:302 / Group 472（1148:89）/ 628:337 / 628:329 /
 * 628:306 / 1126:34 / 1148:48 / 1148:43 / 1151:93-101。
 *
 * 店舗情報テーブルは支店データ（exterior_exone_stores()）をそのまま並べる。
 *
 * TODO: リード文の「［主要対応地域］」は未支給のため、対応エリア（areas）を
 *       そのまま入れている。主要対応地域の文言が決まったら差し替える。
 * TODO: Local Expertise は青森固有の雪国コピー（決定事項 Q12）。他 6 支店の
 *       地域コピーが届いたら支店ごとに持たせる。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_area( array $store ) {
	return array(
		'title'   => 'AREA',
		'image'   => 'area-bg-store-620-292.jpg',
		// 628:338。パネル上端にかぶる黒帯。
		'banner'  => $store['region'] . 'とその周辺エリアの外構工事に対応しています',
		'lead'    => array(
			'EXone' . $store['name'] . 'では、' . $store['areas'] . 'を中心に外構・エクステリア工事を承っています。',
			'地域ごとの積雪、風、敷地条件、道路環境なども考慮し、長く快適に使える外構をご提案します。',
		),
		// 628:307。ラベルと値の 6 行（区切り線は CSS 側）。
		'rows'    => array(
			array(
				'label' => '所在地',
				'value' => $store['zip'] . ' ' . $store['address'],
			),
			array(
				'label' => '電話番号',
				'value' => $store['tel'],
			),
			array(
				'label' => '営業時間',
				'value' => $store['hours'],
			),
			array(
				'label' => '定休日',
				'value' => $store['closed'],
			),
			array(
				'label' => '駐車場',
				'value' => $store['parking'],
			),
			array(
				'label' => '対応エリア',
				'value' => $store['areas'],
			),
		),
		'photo'   => 'area-store-photo-628-306.jpg',
		// 1126:34。店舗内観 5 枚を弧状に並べた合成 1 枚（個別写真は取り出せない）。
		'gallery' => 'area-gallery-exclude-1126-34.png',
		'logo'    => 'area-logo-1131-38.png',
		'branch'  => '[ ' . $store['name'] . ' ]',
		// Local Expertise（1148:45 / 1148:28 / 1148:29 / 1148:43）。
		'local'   => array(
			'eng'     => 'Local Expertise',
			'heading' => array( '雪国だからこそ、', '暮らしやすさを考える。' ),
			'lead'    => array(
				'青森エリアでは、積雪や凍結を前提とした外構設計が欠かせません。',
				'EXoneでは、雪捨てスペースの確保や落雪対策、耐雪性能、排水計画まで考慮し、冬でも安心して暮らせる外構をご提案します。',
			),
			'photo'   => 'area-snow-photo-1148-43.jpg',
		),
		// カード 4 枚（1151:93-101）。番号 + 円形写真 + ラベル。
		'cards'   => array(
			array(
				'number' => '01',
				'label'  => array( '雪捨てスペースを考慮した配置' ),
				'image'  => 'area-card1-1148-49.jpg',
			),
			array(
				'number' => '02',
				'label'  => array( '落雪・雪庇対策' ),
				'image'  => 'area-card2-1148-69.jpg',
			),
			array(
				'number' => '03',
				'label'  => array( '凍結を考えた排水計画' ),
				'image'  => 'area-card3-1148-74.jpg',
			),
			array(
				'number' => '04',
				'label'  => array( '雪に配慮した', 'フェンス・カーポート提案' ),
				'image'  => 'area-card4-1151-96.jpg',
			),
		),
	);
}

/**
 * お問い合わせ CTA。Group 373（669:964）/ 639:366 / 639:364 / 639:406 /
 * Group 340（645:5）。
 *
 * TOP の RECRUIT（775:982）と骨格は同じだが、文字サイズとボタン 2 個が別物なので
 * 共通化せずここで持つ。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_cta( array $store ) {
	return array(
		'image'   => 'cta-bg-645-3.jpg',
		'eng'     => 'Start Your Exterior Project',
		'heading' => '理想の外構について、私たちと話してみませんか。',
		'lead'    => array(
			'まだ具体的なプランが決まっていなくても問題ありません。',
			'新築外構、カーポート、フェンス、庭づくりなど、気になっていることからお聞かせください。',
			'EXone' . $store['name'] . 'のスタッフが、ご希望やご予算に合わせて分かりやすくご案内します。',
		),
		// 639:401 / 639:413。リンク先は支店データ（当面は仮の「#」）。
		'buttons' => array(
			array(
				'type'  => 'line',
				'label' => 'LINEで相談する',
				'icon'  => 'cta-line-icon-639-411.png',
				'url'   => $store['line_url'],
			),
			array(
				'type'  => 'tel',
				'label' => '電話で相談する',
				'icon'  => 'cta-phone-icon-639-415.png',
				'url'   => $store['tel_url'],
			),
		),
	);
}

/**
 * COLUMN の見出し 3 点セット。639:418 / 687:997 / 687:996。
 *
 * カード 5 枚は TOP の NEWS と同じく既存 CPT news の最新をそのまま出す
 *（支店での絞り込みはしない）。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_column( array $store ) {
	return array(
		'title' => 'COLUMN',
		'jp'    => $store['region'] . 'の外構づくりに役立つ情報。',
		'lead'  => array(
			'外構費用の考え方やカーポートの選び方、目隠しフェンス、庭づくりなど、',
			$store['region'] . 'で外構工事を検討している方に役立つ情報を発信します。',
			'地域の気候や住宅事情も踏まえながら、後悔しない外構づくりのポイントを分かりやすく解説します。',
		),
	);
}

/**
 * 複数行のテキストを <br> でつないだ安全な文字列にする。
 *
 * テンプレートで 1 行ずつ echo すると行頭に空白が入り、カンプの幅に
 * 収まるはずの行が折り返してしまうため、1 本の文字列にしてから出す。
 *
 * @param array<int, string> $lines 行の配列。
 * @return string wp_kses( ..., array( 'br' => array() ) ) に通して出力する。
 */
function exterior_exone_store_lines( array $lines ) {
	return implode( '<br>', array_map( 'esc_html', $lines ) );
}

/**
 * WORKS の写真グリッド 1 枠ぶんを出力する。
 *
 * CPT works の画像があればそれを投稿へのリンク付きで、無ければカンプ写真を出す。
 *
 * @param array{image_id:int, file:string, url:string, title:string} $slot 枠のデータ。
 */
function exterior_exone_store_works_photo( array $slot ) {
	if ( $slot['image_id'] ) {
		$image = wp_get_attachment_image(
			$slot['image_id'],
			'large',
			false,
			array(
				'alt'     => $slot['title'],
				'loading' => 'lazy',
			)
		);
	} else {
		$image = sprintf(
			'<img src="%s" width="1200" height="844" alt="" loading="lazy">',
			esc_url( exterior_exone_store_image( $slot['file'] ) )
		);
	}

	if ( ! $slot['url'] ) {
		echo wp_kses_post( $image );

		return;
	}

	printf(
		'<a class="p-sworks__link" href="%s">%s</a>',
		esc_url( $slot['url'] ),
		wp_kses_post( $image )
	);
}

/**
 * 支店ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * @param string $file images/store/ 配下のファイル名。
 * @return string
 */
function exterior_exone_store_image( $file ) {
	$webp = 'images/store/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/store/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/store/' . $file );
}

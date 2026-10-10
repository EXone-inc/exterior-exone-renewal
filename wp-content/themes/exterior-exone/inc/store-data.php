<?php
/**
 * 支店ページ（STORE）の表示データ。
 *
 * カンプ: PC 534:628（青森支店ベース）/ SP カンプなし
 * 設計メモ: docs/figma-store-page.md
 *
 * 6 拠点の単一の出典。フッター・ハンバーガーメニューの STORE もここから導出する
 *（inc/menus.php の exterior_exone_store_menu_items()）。
 * セクションごとの表示データはスプリントを進めるたびにここへ足していく。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 6 拠点のデータ。キーは固定ページのスラッグ。
 *
 * 並び順はフッター STORE の並び（カンプ 669:836）に合わせる。
 *
 * 6 拠点とも支給データ（2026-10-01〜02）の実値。駐車場だけ仮で 10 台。
 * zip が空の拠点は郵便番号を表示しない。
 * 東京オフィスは不要との指示で外した（2026-10-02。固定ページ /store/tokyo/ は下書きに戻した）。
 *
 * areas は店舗情報の「対応エリア」の表示。areas_lead は AREA のリード文
 * （「…を中心に」）に入れる言い方で、省略時は areas を使う。
 *
 * @return array<string, array<string, string>>
 */
function exterior_exone_stores() {
	return array(
		'sendai'    => array(
			'name'     => '仙台支店',
			'region'   => '仙台',
			'zip'      => '〒980-0014',
			'address'  => '宮城県仙台市青葉区本町二丁目2番7号 トモル広瀬通PhilPark 3階',
			'tel'      => '022-354-8993',
			'hours'    => 'AM10:00～PM18:00',
			'closed'   => '土・日・祝',
			'parking'  => '2台',
			'areas'    => '仙台市、多賀城市、名取市、塩竈市、岩沼市、富谷市、利府町、川崎町',
			'line_url' => 'https://lin.ee/Z9M31bf', // @620vhjqe
			'tel_url'  => 'tel:0223548993',
		),
		'totsuka'   => array(
			'name'     => '戸塚支店',
			'region'   => '戸塚',
			'zip'      => '〒244-0003',
			'address'  => '神奈川県横浜市戸塚区戸塚町6001-5 アイズビル1階',
			'tel'      => '045-443-9592',
			'hours'    => 'AM10:00～PM18:00',
			'closed'   => '火・水・祝',
			'parking'  => '2台',
			'areas'    => '横浜市、藤沢市、大和市、厚木市、海老名市、鎌倉市、茅ヶ崎市、平塚市、綾瀬市、伊勢原市、寒川町、座間市',
			'line_url' => 'https://lin.ee/wxMNCYU', // @349lnmmg
			'tel_url'  => 'tel:0454439592',
		),
		'morioka'   => array(
			'name'     => '盛岡支店',
			'region'   => '盛岡',
			'zip'      => '〒020-0857',
			'address'  => '岩手県盛岡市北飯岡1丁目2-8',
			'tel'      => '019-613-4067',
			'hours'    => 'AM10:00～PM18:00',
			'closed'   => '土・日・祝',
			'parking'  => '5台',
			'areas'    => '盛岡市、雫石町、八幡平市、矢巾町、滝沢市',
			'line_url' => 'https://lin.ee/Ys2O5ED', // @472bhbvl
			'tel_url'  => 'tel:0196134067',
		),
		// 支給データ（2026-10-02）。定休日と対応エリアはカンプ（日・祝 / つがる市なし）から更新。
		'aomori'    => array(
			'name'     => '青森支店',
			'region'   => '青森',
			'zip'      => '〒030-0846',
			'address'  => '青森県青森市青葉3丁目1-8',
			'tel'      => '017-711-8031',
			'hours'    => 'AM10:00～PM18:00',
			'closed'   => '土・日・祝',
			'parking'  => '2台',
			'areas'    => '青森市、五所川原市、つがる市、平内町、野辺地町、外ヶ浜町',
			'line_url' => 'https://lin.ee/Tfvjo0k', // @744ivjdd（弘前支店と共通）
			'tel_url'  => 'tel:0177118031',
		),
		'hachinohe' => array(
			'name'       => '八戸支店',
			'region'     => '八戸',
			'zip'        => '〒031-0072',
			'address'    => '青森県八戸市城下4-16-20',
			'tel'        => '0178-38-9640',
			'hours'      => 'AM10:00～PM18:00',
			'closed'     => '土・日・祝',
			'parking'    => '2台',
			// 「奥入瀬町」は支給データの表記のまま（自治体名は「おいらせ町」。要確認）。
			'areas'      => '青森県：八戸市、三沢市、東北町、奥入瀬町、十和田市、階上町、南部町、三戸町、五戸町、七戸町、田子町、新郷村、六ヶ所村／岩手県：久慈市、二戸市',
			'areas_lead' => '八戸市、三沢市、東北町、奥入瀬町、十和田市、階上町、南部町、三戸町、五戸町、七戸町、田子町、新郷村、六ヶ所村、岩手県久慈市、二戸市',
			'line_url'   => 'https://lin.ee/7JUyeGN',
			'tel_url'    => 'tel:0178389640',
		),
		'hirosaki'  => array(
			'name'       => '弘前支店',
			'region'     => '弘前',
			'zip'        => '〒036-8087',
			'address'    => '青森県弘前市早稲田4丁目3-2',
			'tel'        => '0172-55-7478',
			'hours'      => 'AM10:00～PM18:00',
			'closed'     => '土・日・祝',
			'parking'    => '2台',
			'areas'      => '青森県：弘前市、黒石市、平川市、藤崎町、板柳町、大鰐町、田舎館村／秋田県：大館市、鹿角市、小坂町、北秋田市',
			'areas_lead' => '弘前市、黒石市、平川市、藤崎町、板柳町、大鰐町、田舎館村、秋田県大館市、鹿角市、小坂町、北秋田市',
			'line_url'   => 'https://lin.ee/Tfvjo0k', // @744ivjdd（青森支店と共通）。青森の支給データの URL に合わせた
			'tel_url'    => 'tel:0172557478',
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
 * 支店ページ（6 拠点のいずれか）を表示中かどうか。
 *
 * @return bool
 */
function exterior_exone_is_store_page() {
	return null !== exterior_exone_current_store();
}

/**
 * FV（ヒーロー）の共通コピーと画像。612:761 / 612:166 / 612:760。
 *
 * カンプ上で支店ごとに変わるのはバッジの支店名だけ。背景は全支店共通の
 * 写真 1 枚で固定（決定事項 A）。写真は後日同名の高解像度版に差し替える前提。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_fv() {
	return array(
		'eyebrow' => '外構エクステリア施工専門店',
		// 612:170。2 行組み。
		'titles'  => array( 'DESIGN', 'YOUR LIFE' ),
		'lead'    => '理想の暮らしを、カタチに',
		'logo'    => 'hero-logo-535-951.png',
		// 612:154（SP 638:72 も同じ写真）。
		'image'   => 'hero-bg-535-838.jpg',
		// FV 下端をまたぐ写真 4 枚（612:178 / 612:180 / 612:179 / 612:181）。
		'photos'  => array(
			'hero-band-img1-535-844.jpg',
			'hero-band-img2-535-845.jpg',
			'hero-band-img3-268-348.jpg',
			'hero-band-img4-535-850.jpg',
		),
	);
}

/**
 * 導入コピー。612:227 / 612:182 / 612:183（SP 638:98 / 638:97 / 638:96）。
 *
 * 本文は支店ごとに ［支店名］［地域名］ が入れ替わる。
 * 見出しは SP だけ 2 行（638:97）なので、改行位置ごとに分けて持つ。
 * 本文は PC が 3 行、SP は 2 行目と 3 行目を 1 段落につなぐ（638:96）。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_intro( array $store ) {
	return array(
		'icon'    => 'intro-icon-house-535-966.png',
		'heading' => array( '新築外構からカーポート、', 'フェンス、庭づくりまで' ),
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
 * PACKAGE 下の帯（$variant = 'plans'。612:803）は 03 だけ別の写真（612:580）。
 * カンプの 05 が 294 幅なのは画面の右端で切れているだけなので、施工イメージ帯と同じ幅で持つ。
 *
 * 04（612:570）は PLANS の img list 04 と同じ画像・切り抜き・枠（inc/plans-data.php）。
 * url があれば file より優先、crop（幅 % / 左 % / 上 %）があれば枠で切り抜く。
 *
 * @param string $variant 'plans' なら PACKAGE 下の帯。
 * @return array<int, array<string, mixed>>
 */
function exterior_exone_store_strip_images( $variant = '' ) {
	$plans_house = exterior_exone_plans_package()['imglist'][3];

	$images = array(
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
			'url'    => exterior_exone_plans_image( $plans_house['file'] ),
			'img'    => $plans_house['img'],
			'width'  => $plans_house['w'],
			'height' => $plans_house['h'],
			'crop'   => $plans_house['crop'],
		),
		array(
			'file'   => 'strip-house5-535-871.png',
			'width'  => 364.6,
			'height' => 209,
		),
	);

	if ( 'plans' === $variant ) {
		$images[2] = array(
			'file'   => 'plans-strip-669-954.png',
			'width'  => 342,
			'height' => 210.4,
		);
	}

	return $images;
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
 * 不安提示。612:223 / 612:176 / 612:175 / 612:153（SP 638:148 / 638:145 / 638:144 / 638:150）。
 * 全支店共通。
 *
 * 見出しは SP だけ 2 行（638:148）なので、改行位置ごとに分けて持つ。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_concern() {
	return array(
		'heading' => array( '外構づくりで、', 'こんな不安を感じていませんか？' ),
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
 * 選ばれる理由。PC 612:770 / SP 641:22〜641:52。全支店共通。
 *
 * 6 項目の円周上の配置（座標）は css/store.css 側が持つ（--item-x / --icon-y）。
 * SP は 2 行ラベルの代わりに 1 語（word）を出し、英字の 2 行目も別文言（eng_sub_sp）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_reasons() {
	return array(
		'copy_heading' => array(
			'外構づくりを、',
			'もっと分かりやすく、もっと心地よく',
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
		'eng_sub_sp'   => 'DESIGN, MADE VISIBLE.',
		'items'        => array(
			array(
				'lead'   => '暮らしと建物に調和する',
				'label'  => 'デザイン',
				'word'   => 'デザイン',
				'icon'   => 'reasons-icon-house-535-1014.png',
				'icon_w' => 171.2,
				'icon_h' => 89.9,
			),
			array(
				'lead'   => '完成イメージを確認できる',
				'label'  => 'ビジュアル提案',
				'word'   => 'ビジュアル提案',
				'icon'   => 'reasons-icon-tablet-599-73.png',
				'icon_w' => 144.4,
				'icon_h' => 90.6,
			),
			array(
				'lead'    => '地域の気候や住環境に合わせた',
				'label'   => '提案',
				'word'    => '住環境適応',
				'icon'    => 'reasons-icon-weather-602-89.png',
				'icon_sp' => 'reasons-icon-weather-sp-641-50.png', // SP は PC と別の絵（641:50）。
				'icon_w'  => 134.3,
				'icon_h'  => 100.5,
			),
			array(
				'lead'   => '分かりやすく整理された',
				'label'  => '見積もり',
				'word'   => '見積もり',
				'icon'   => 'reasons-icon-doc-599-69.png',
				'icon_w' => 114.7,
				'icon_h' => 100.6,
			),
			array(
				'lead'   => '工事後も安心できる',
				'label'  => '5年間の工事保証',
				'word'   => '長期保証',
				'icon'   => 'reasons-icon-5years-602-93.png',
				'icon_w' => 89.1,
				'icon_h' => 104.4,
			),
			array(
				'lead'   => '専門スタッフによる',
				'label'  => '施工・品質管理',
				'word'   => '施工・品質管理',
				'icon'   => 'reasons-icon-worker-602-82.png',
				'icon_w' => 96.2,
				'icon_h' => 102.3,
			),
		),
	);
}

/**
 * SERVICE。PC 612:781 / SP 641:59〜641:169。全支店共通。
 *
 * SP だけ写真名が短い 2 枚は label_sp を持つ（「デッキ」「ライティング」）。
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
		// SP は 2 行（641:98）。PC は 1 行につなげて出す。
		'banner'     => array(
			'外構・エクステリアを、',
			'まとめてご相談いただけます',
		),
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
						'label'    => 'ウッドデッキ・タイルデッキ',
						'label_sp' => 'デッキ', // SP カンプ 641:131 の短縮名。
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
						'label'    => '照明・ライティング',
						'label_sp' => 'ライティング', // SP カンプ 641:167 の短縮名。
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
 * 見える安心。PC 612:786 上部 / SP 641:175〜641:208。全支店共通。
 *
 * 写真 4 枚は PC は横 4 枚（429.3x224.7・ピッチ 454.5）、SP は写真と文字の左右交互 4 段。
 * 線画は TOP 理念と同じ SVG を線描画する（js/line-draw.js。決定 B「見える安心の線画」）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_visible() {
	return array(
		// 572:282 / 572:280。
		'eyebrow' => 'Clearer Exterior Experience',
		'heading' => '見えない不安を、見える安心へ',
		// 612:255 / 641:175。線画（テーマ相対パス。opacity は CSS 側で .3 にする）。
		'line'    => 'images/top/line_animation.svg',
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
		// 641:208。SP は 2・3 行目をつないだ 4 段落。
		'lead_sp' => array(
			'EXoneが大切にしているのは、完成した外構だけではありません。',
			'価格、デザイン、工事内容、完成までの流れをできる限り分かりやすくし、お客様が納得しながら外構づくりを進められる体験を提供します。',
			'考えていることをうまく言葉にできなくても大丈夫です。',
			'対話とビジュアルを通じて、理想の暮らしを一緒に形にしていきます。',
		),
	);
}

/**
 * PLANS。599:50 / Group 362（669:933）/ Group 363（669:934）/
 * Group 338（610:283）/ Group 374（669:965）。全支店共通。
 *
 * 特長 4 列のアイコンは「選ばれる理由」（§6）と同じ線画ファイルを使う。
 * icon_w / icon_h はカンプ 1920 での表示サイズ、icon_sp は SP カンプ 375 での
 * 表示サイズ（641:298〜641:306 / 641:332〜641:348。PC との比が 1 枚ずつ違う）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_plans() {
	return array(
		'title'   => 'PLANS',
		'jp'      => '理想に近い外構を、分かりやすいプランから探す',
		// 641:279。SP は「、」の後で 2 行に折る。
		'jp_sp'   => array( '理想に近い外構を、', '分かりやすいプランから探す' ),
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
			// 612:308 / 641:284。PLANS ページと同じ住宅パース（images/plans/。カンプの切り抜きは CSS）。
			'image'    => 'pc-package-simple-modern-586-406.png',
			// 599:59。PLANS ページのパッケージプラン（出力時に exterior_exone_store_link() で ?store= を
			// 引き継ぐ。例 /plans/?store=sendai#package。ユーザー指示 2026-10-06）。
			'more'     => home_url( '/plans/#package' ),
			'features' => array(
				array(
					'icon'    => 'reasons-icon-doc-599-69.png',
					'icon_w'  => 114.7,
					'icon_h'  => 100.6,
					'icon_sp' => array( 71, 63 ),
					'lines'   => array( 'ご予算に合わせた', '分かりやすい価格設計' ),
				),
				array(
					'icon'    => 'reasons-icon-tablet-599-73.png',
					'icon_w'  => 144.4,
					'icon_h'  => 90.6,
					'icon_sp' => array( 96, 60 ),
					'lines'   => array( '豊富なデザインから', '簡単に選べる' ),
				),
				array(
					'icon'    => 'reasons-icon-worker-602-82.png',
					'icon_w'  => 96.2,
					'icon_h'  => 102.3,
					'icon_sp' => array( 58, 61 ),
					'lines'   => array( '打ち合わせから着工まで', 'スピーディー' ),
				),
				array(
					'icon'    => 'reasons-icon-house-535-1014.png',
					'icon_w'  => 171.2,
					'icon_h'  => 89.9,
					'icon_sp' => array( 97, 51 ),
					'lines'   => array( 'コストとデザインの', 'バランスに優れたプラン' ),
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
			// 610:280。PLANS ページのハイエンドプラン（出力時に exterior_exone_store_link() で ?store= を
			// 引き継ぐ。例 /plans/?store=sendai#high-end。ユーザー指示 2026-10-06。ハイエンドページ
			// /plans/high-end/ へは PLANS ページ側の VIEW MORE から）。
			'more'     => home_url( '/plans/#high-end' ),
			'features' => array(
				array(
					'icon'    => 'reasons-icon-tablet-599-73.png',
					'icon_w'  => 144.4,
					'icon_h'  => 90.6,
					'icon_sp' => array( 96, 60 ),
					'lines'   => array( '暮らしに合わせた', 'デザイン提案' ),
				),
				array(
					'icon'    => 'highend-icon-diamond-606-133.png',
					'icon_w'  => 130.6,
					'icon_h'  => 88.2,
					'icon_sp' => array( 78, 53 ),
					'lines'   => array( 'ハイグレードな', '素材・商品を採用' ),
				),
				array(
					'icon'    => 'highend-icon-blueprint-622-298.png',
					'icon_w'  => 128.4,
					'icon_h'  => 90.5,
					'icon_sp' => array( 83, 59 ),
					'lines'   => array( '唯一無二の', 'デザインを実現' ),
				),
				array(
					'icon'    => 'highend-icon-house-606-138.png',
					'icon_w'  => 204.1,
					'icon_h'  => 89.9,
					'icon_sp' => array( 114, 50 ),
					'lines'   => array( '細部までこだわる', '自由設計プラン' ),
				),
			),
		),
	);
}

/**
 * WORKS。新カンプ 612:813（見出し 612:705 / 612:707 / 612:706）/ SP 641:362〜641:391。全支店共通。
 *
 * guide 617:44「DX下部と同じ（上部テキストのみ違いあり）」のとおり、DX ページの WORKS
 *（inc/dx-data.php の exterior_exone_dx_works()。カンプ写真固定・右列を押すとメイン切替・
 * 3D モデル・見積モーダル）をそのまま使い、見出し 3 点の文言だけ支店用に差し替える。
 * 写真・3D・要望 / 提案・見積を二重に持たない。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_works() {
	return array_merge(
		exterior_exone_dx_works(),
		array(
			'title'   => 'WORKS',
			'jp'      => '暮らしの希望から生まれた、外構のご提案',
			'lead'    => array(
				'EXoneが紹介するのは、完成写真だけではありません。',
				'お客様がどのような悩みや希望を持ち、どんな考え方でデザインをご提案したのか。',
				'完成までの背景とともに、外構づくりの事例をご紹介します。',
			),
			// SP は 641:391 のとおり左揃え・337 幅で流し、「…ご提案したのか。」で段落を改める（5 行。css/store.css）。
			'lead_sp' => array(
				'EXoneが紹介するのは、完成写真だけではありません。お客様がどのような悩みや希望を持ち、どんな考え方でデザインをご提案したのか。',
				'完成までの背景とともに、外構づくりの事例をご紹介します。',
			),
		)
	);
}

/**
 * REVIEWS。612:819 / 641:396〜。見出しと写真 3 枚は全支店共通、お客様の声は支店ごと。
 *
 * 写真はユーザー支給（2026-10-10 voice-1 / voice-2 / voice-3。青森・弘前の 3 枚目だけ voice-4）。声の文言も同日に
 * 支店ごとに支給されたもの（6 支店ぶん入力済み）。
 * カンプのカードは「星 5.0 → タイトル → 本文」だが、タイトルは使わず本文の下に右寄せで
 * 名前（地域 / 年代）を出し、本文は省略せず全文を出す（2026-10-10 の決定）。
 *
 * @param array<string, mixed> $store exterior_exone_stores() の 1 件。
 * @return array<string, mixed>
 */
function exterior_exone_store_reviews( array $store = array() ) {
	$images = array( 'reviews-voice-1.jpg', 'reviews-voice-2.jpg', 'reviews-voice-3.jpg' );

	// 青森・弘前だけ 3 枚目を voice-4 に（2026-10-10 の指示。1512×1040 の支給画像を 1020 幅に縮小）。
	$image_overrides = array(
		'aomori'   => array( 2 => 'reviews-voice-4.jpg' ),
		'hirosaki' => array( 2 => 'reviews-voice-4.jpg' ),
	);

	// text は行の配列（支給文の <br> の位置で分ける）か 1 行の文字列。
	$voices = array(
		'sendai'    => array(
			array(
				'text' => '完成するまでイメージが湧かず不安でしたが、バーチャル展示場と3Dパースで細かく確認しながら進められたので安心感がありました。仕上がりも想像以上で、帰るたびに気分が上がります。',
				'name' => '仙台市 / 30代',
			),
			array(
				'text' => '何社か比較しましたが、説明が一番分かりやすく、“自分たちに合う提案”をしてくれたのが印象的でした。価格も明確で、納得しながら進められました。',
				'name' => '仙台市 / 30代',
			),
			array(
				'text' => '新築の家に合う外構にしたかったのですが、自分たちだけでは正解が分からず悩んでいました。建物との統一感まで考えて提案してもらえて、本当に満足しています。',
				'name' => '名取市 / 40代',
			),
		),
		'totsuka'   => array(
			array(
				'text' => '限られた敷地でも諦めたくなくて相談しました。3Dパースで日当たりや車の動線まで確認できたので、完成後のギャップがなく安心して任せられました。',
				'name' => '横浜市 / 30代',
			),
			array(
				'text' => array(
					'数社に見積もりを取りましたが、要望の汲み取り方とプランの提案力が一番でした。',
					'金額の内訳も明確で、納得しながら進められました。',
				),
				'name' => '藤沢市 / 40代',
			),
			array(
				'text' => '古くなった外構のリフォームをお願いしました。既存の雰囲気を活かしつつ使い勝手まで考えてもらえて、毎日の出入りが快適になりました。',
				'name' => '鎌倉市 / 50代',
			),
		),
		'morioka'   => array(
			array(
				'text' => array(
					'雪のことが不安でしたが、カーポートや動線まで地域に合わせて提案してもらえて安心でした。',
					'3Dで完成イメージを確認できたのも決め手になりました。',
				),
				'name' => '盛岡市 / 30代',
			),
			array(
				'text' => array(
					'新築に合う外構が自分たちでは決められず悩んでいました。',
					'建物との統一感まで考えた提案で、家全体が見違えるほど引き締まりました。',
				),
				'name' => '滝沢市 / 40代',
			),
			array(
				'text' => '見積もりの分かりやすさと、予算に寄り添う姿勢が決め手でした。仕上がりも丁寧で、来客時に褒められることが増えました。',
				'name' => '雫石町 / 50代',
			),
		),
		'aomori'    => array(
			array(
				'text' => array(
					'完成までイメージが湧かず不安でしたが、バーチャル展示場と3Dパースで細かく確認できたので安心して任せられました。',
					'仕上がりは想像以上でした。',
				),
				'name' => '青森市 / 30代',
			),
			array(
				'text' => array(
					'雪国ならではの使い勝手まで相談に乗ってもらえました。',
					'除雪のしやすさや動線まで考えた外構で、冬の負担がぐっと減りました。',
				),
				'name' => '五所川原市 / 40代',
			),
			array(
				'text' => '何社か比較しましたが、説明が一番分かりやすく押し付けのない提案が印象的でした。価格も明確で、納得しながら進められました。',
				'name' => 'つがる市 / 50代',
			),
		),
		'hachinohe' => array(
			array(
				'text' => '駐車場まわりと目隠しをまとめてお願いしました。3Dパースで隣家からの視線まで確認でき、暮らしやすさが想像以上に変わりました。',
				'name' => '八戸市 / 30代',
			),
			array(
				'text' => array(
					'新しい家に合う外構にしたくて相談しました。',
					'建物や街並みとの調和まで考えて提案してもらえて、帰宅するたびに気分が上がります。',
				),
				'name' => '三沢市 / 40代',
			),
			array(
				'text' => '見積もりが明確で、要望の汲み取りも丁寧でした。仕上がりにも妥協がなく、長く付き合える会社だと感じています。',
				'name' => '十和田市 / 50代',
			),
		),
		'hirosaki'  => array(
			array(
				'text' => array(
					'庭の使い方が決められず悩んでいましたが、暮らし方に合わせて植栽や動線まで提案してもらえました。',
					'3Dで確認できたので失敗なく進められました。',
				),
				'name' => '弘前市 / 30代',
			),
			array(
				'text' => array(
					'数社で比較して、一番分かりやすく親身だったのが決め手でした。',
					'価格の内訳も明確で、納得して契約できました。',
				),
				'name' => '黒石市 / 40代',
			),
			array(
				'text' => '古い外構のリフォームでしたが、既存を活かしつつ使い勝手まで良くしてもらえました。毎日の出入りが快適になり満足しています。',
				'name' => '平川市 / 50代',
			),
		),
	);

	$slug  = isset( $store['slug'], $voices[ $store['slug'] ] ) ? $store['slug'] : '';
	$cards = array();

	if ( $slug && isset( $image_overrides[ $slug ] ) ) {
		$images = array_replace( $images, $image_overrides[ $slug ] );
	}

	foreach ( $images as $index => $image ) {
		$voice = $slug ? $voices[ $slug ][ $index ] : array(
			// 声が未支給の支店は 658:9 のダミー本文（「テキスト」×22）。名前は出さない。
			'text' => str_repeat( 'テキスト', 22 ),
			'name' => '',
		);

		$cards[] = array(
			'score' => '5.0',
			'text'  => $voice['text'],
			'name'  => $voice['name'],
			'image' => $image,
		);
	}

	return array(
		'title' => 'REVIEWS',
		'jp'    => 'EXoneで外構づくりをされたお客様の声',
		'lead'  => array(
			'デザインのこと、費用のこと、担当者の対応、完成後の暮らし。',
			'実際にEXoneへご相談いただいたお客様の声を通じて、外構づくりの過程や完成後の変化をご紹介します。',
		),
		'stars' => 'reviews-stars-669-223.svg',
		'cards' => $cards,
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
				'number'  => '02',
				'label'   => 'ヒアリング・現地調査',
				// SP（641:478）は 1 段落（改行しない）。
				'sp_join' => true,
				'lines'   => array(
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
 * Quality（施工保証）。PC 612:839 / SP 641:1251〜641:1261。全支店共通。
 *
 * 旧カンプの保証注記（669:265）は新カンプに無いので持たない（決定 A）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_store_quality() {
	return array(
		'image'   => 'quality-bg-669-258.jpg',
		'eng'     => 'Quality You Can Trust',
		'heading' => '完成したあとも続く、安心の品質',
		'lead'    => array(
			'外構工事は、完成時の見た目だけでなく、長く安心して使えることが大切です。',
			'EXoneでは、施工基準や工程管理を整備し、担当者だけに依存しない品質管理に取り組んでいます。',
			'さらに、対象工事には5年間の工事保証を設け、完成後の暮らしも支えます。',
		),
		// 612:584。「5」だけ 2px 大きい（--st-quality-badge-num。SP は同じ大きさ）。
		'badge'   => array(
			'label'  => '施工保証',
			'number' => '5',
			'unit'   => '年',
		),
	);
}

/**
 * FAQ。669:872-874 / Group 372（669:963）/ 669:906（山形アイコン）。全支店共通。
 *
 * TODO: 回答文はカンプに無いため仮文（決定事項 Q11）。公開前に正文へ差し替える。
 *
 * 質問 q は文節ごとの配列。SP で折り返すときに文節の切れ目（<wbr>）でだけ折る
 *（「…もらえます / か？」のような 1〜2 文字の折り返しを出さない）。
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
		// 回答は 2026-10-10 に Figma の 8 問（641:1462・838:35〜838:66）の文言へ差し替え。配列 = 行（<br> で改行）。
		'items' => array(
			array(
				'q' => array( '相談や', '見積りに', '費用は', 'かかりますか？' ),
				'a' => array(
					'ご相談・お見積りは無料です。ご希望やご予算が具体的に決まっていない段階でも、お気軽にご相談ください。',
					'なお、ハイエンドプランのパース制作は有料です。費用が発生する場合は、事前に内容と金額をご案内します。',
				),
			),
			array(
				'q' => array( 'まだ', '建物の', '図面しか', 'ないのですが、', '相談できますか？' ),
				'a' => array(
					'はい、建物が完成する前でもご相談いただけます。配置図や平面図、立面図など、お手元の資料をご用意ください。',
					'建物のデザインや敷地条件、ご入居予定の時期を伺いながら、外構の配置や必要な工事を一緒に考えていきます。',
				),
			),
			array(
				'q' => array( '他社との', '相見積りでも', '相談できますか？' ),
				'a' => array(
					'はい、相見積りの段階でもご相談いただけます。ご希望のデザインや工事内容、ご予算を伺い、EXoneからのご提案と費用を分かりやすくご説明します。',
					'金額だけでなく、使用する建材や工事の範囲も比較しながら、ご納得いただけるプランをご検討ください。',
				),
			),
			array(
				'q' => array( '外構工事の', '予算は', 'どのくらい', '必要ですか？' ),
				'a' => array(
					'必要なご予算は、工事の範囲や敷地条件、使用する建材、既存外構の撤去の有無などによって異なります。',
					'ご予算が決まっている場合は、その金額をもとに、優先したい工事やデザインを整理してご提案します。予算が未定の方にも、ご希望の内容に応じた費用の目安をご案内します。',
				),
			),
			array(
				'q' => array( '工事期間は', 'どのくらい', 'かかりますか？' ),
				'a' => array(
					'工事内容や施工範囲によって異なります。ご相談内容と現地の状況を確認し、着工時期と完成までの期間の目安をご案内します。',
					'天候や施工条件によって日程が変わる場合もありますので、ご入居日など、ご希望の完成時期がある場合はあわせてお知らせください。',
				),
			),
			array(
				'q' => array( '一部分だけの', '工事にも', '対応していますか？' ),
				'a' => array(
					'はい、カーポートの設置やフェンスの交換、駐車場の舗装、庭のリフォームなど、部分的な工事もご相談いただけます。',
					'現在の外構とのつながりや使いやすさも考えながら、必要な箇所に合わせた工事をご提案します。',
				),
			),
			array(
				'q' => array( '対応エリア外でも', '相談できますか？' ),
				'a' => array(
					'掲載している対応エリア外の場合も、まずはお問い合わせください。施工予定地とご希望の工事内容を伺い、対応の可否を確認いたします。',
					'場所や工事内容によっては対応が難しい場合もございますので、事前にご相談ください。',
				),
			),
			array(
				'q' => array( '土地や', '建物に', '合う', '商品を', '提案して', 'もらえますか？' ),
				'a' => array(
					'はい。建物のデザインや敷地の形状、周辺環境に合わせて、カーポートやフェンス、門まわりなどの商品をご提案します。',
					'見た目の調和だけでなく、日々の使いやすさやお手入れ、地域の気候に必要な性能も考慮し、ご希望とご予算に合う外構を一緒に考えます。',
				),
			),
		),
		// 612:538 / 641:1311（開いた状態）。SP（641:1270 / 641:1306）も同じ形を縮小して使う。
		'arrow' => 'faq-arrow-612-538.svg',
	);
}

/**
 * AREA の支店ごとに差し替える素材（決定事項 A）。キーは支店のスラッグ。
 *
 * 店内写真（アーチ状ロール 4 枚）・外観写真・Local Expertise の見出し・本文・写真・
 * カード 4 枚（決定 B-2 Q5）。写真と文章は Figma のボード Group 528（641:1464）から
 * 支店ごとに書き出したもの（2026-10-06）。position は object-position（カンプの切り抜き位置）。
 *
 * ・弘前: 外観と店内はボードの写真。Local Expertise とカードは青森と共通（ボードの注記
 *   「地域セクションのテキストと画像は、青森と共通（エリア名のみ変更）」）
 *
 * 差し替えるときは該当拠点の配列を書き換える。店内写真の枚数は何枚でもよい。
 *
 * @return array<string, array<string, mixed>>
 */
function exterior_exone_store_area_media() {
	// 青森（ボード 1 行目。Local Expertise とカードは弘前・戸塚でも使う）。
	$aomori_local = array(
		'heading' => array( '雪国だからこそ、', '暮らしやすさを考える' ),
		'lead'    => array(
			// {region} は支店の地域名（「青森支店」の「青森」）に置き換える（exterior_exone_store_area()）。
			'{region}エリアでは、積雪や凍結を前提とした外構設計が欠かせません。',
			'EXoneでは、雪捨てスペースの確保や落雪対策、耐雪性能、排水計画まで考慮し、冬でも安心して暮らせる外構をご提案します。',
		),
		'photo'    => 'area-aomori-local-739-121.jpg',
		'position' => '50% 0%',
	);
	$aomori_cards = array(
		array(
			'label'    => array( '雪捨てスペースを考慮した配置' ),
			'label_sp' => array( '雪捨てスペースを', '考慮した配置' ),
			'image'    => 'area-aomori-card1-739-134.jpg',
			'position' => '50% 50%',
		),
		array(
			'label'    => array( '落雪・雪庇対策' ),
			'label_sp' => array( '落雪・雪庇対策' ),
			'image'    => 'area-aomori-card2-739-131.jpg',
			'position' => '61% 50%',
		),
		array(
			'label'    => array( '凍結を考えた排水計画' ),
			'label_sp' => array( '凍結を考えた', '排水計画' ),
			'image'    => 'area-aomori-card3-739-128.jpg',
			'position' => '28% 50%',
		),
		array(
			'label'    => array( '雪に配慮した', 'フェンス・カーポート提案' ),
			'label_sp' => array( '雪に配慮した', 'フェンス・カーポート提案' ),
			'image'    => 'area-aomori-card4-739-125.jpg',
			'position' => '56% 100%',
		),
	);
	$aomori = array(
		'photo'   => 'area-aomori-store-737-44.jpg',
		'bg'       => 'area-bg-aomori-847-143.jpg', // 847:143 上端の背景（店内）
		'bg_position' => '50% 71%', // PC の切り抜き位置（カンプの crop から）
		'gallery' => array(
			array( 'file' => 'area-aomori-gallery1-641-1467.jpg', 'position' => '50% 50%' ),
			array( 'file' => 'area-aomori-gallery2-641-1466.jpg', 'position' => '50% 0%' ),
			array( 'file' => 'area-aomori-gallery3-641-1468.jpg', 'position' => '50% 76%' ),
			array( 'file' => 'area-aomori-gallery4-641-1465.jpg', 'position' => '50% 100%' ),
		),
		'local'   => $aomori_local,
		'cards'   => $aomori_cards,
	);

	return array(
		// 仙台（ボード 2 行目）。
		'sendai'    => array(
			'photo'   => 'area-sendai-store-737-47.jpg',
			'bg'       => 'area-bg-sendai-847-145.jpg', // 847:145 上端の背景（店内）
			'bg_position' => '50% 61%', // PC の切り抜き位置（カンプの crop から）
			'gallery' => array(
				array( 'file' => 'area-sendai-gallery1-737-78.jpg', 'position' => '50% 1%' ),
				array( 'file' => 'area-sendai-gallery2-737-77.jpg', 'position' => '50% 99%' ),
				array( 'file' => 'area-sendai-gallery3-737-79.jpg', 'position' => '50% 1%' ),
				array( 'file' => 'area-sendai-gallery4-737-80.jpg', 'position' => '50% 37%' ),
			),
			'local'   => array(
				'heading'  => array( '暮らしに寄り添う', 'デザインと提案力' ),
				'lead'     => array(
					'{region}エリアでは、住宅地ごとの敷地条件や街並みに合わせたプランニングが重要です。',
					'EXoneでは、デザイン性と使いやすさを両立し、一人ひとりの暮らしに合わせたご提案を行います。',
				),
				'photo'    => 'area-sendai-local-757-137.jpg',
				'position' => '79% 50%',
			),
			'cards'   => array(
				array(
					'label'    => array( '建物と調和するデザイン' ),
					'label_sp' => array( '建物と調和する', 'デザイン' ),
					'image'    => 'area-sendai-card1-757-148.jpg',
					'position' => '44% 0%',
				),
				array(
					// PC は 1 行に収まらないので、青森の 4 枚目と同じく 2 行にする。
					'label'    => array( 'ライフスタイルに合わせた', 'プランニング' ),
					'label_sp' => array( 'ライフスタイルに', '合わせたプランニング' ),
					'image'    => 'area-sendai-card2-757-183.jpg',
					'position' => '50% 50%',
				),
				array(
					'label'    => array( '敷地条件を活かした設計' ),
					'label_sp' => array( '敷地条件を', '活かした設計' ),
					'image'    => 'area-sendai-card3-757-177.jpg',
					'position' => '18% 50%',
				),
				array(
					'label'    => array( 'AIによる完成イメージの可視化' ),
					'label_sp' => array( 'AIによる', '完成イメージの可視化' ),
					'image'    => 'area-sendai-card4-757-180.jpg',
					'position' => '50% 45%',
				),
			),
		),
		// 戸塚（ボード 6 行目。2026-10-06 に追加された素材）。
		'totsuka'   => array(
			'photo'   => 'area-totsuka-store-769-80.jpg',
			'bg'       => 'area-bg-totsuka-847-132.jpg', // 847:132 上端の背景（店内）
			'bg_position' => '50% 57.5%', // PC の切り抜き位置（カンプの crop から）
			'gallery' => array(
				array( 'file' => 'area-totsuka-gallery1-769-83.jpg', 'position' => '50% 73%' ),
				array( 'file' => 'area-totsuka-gallery2-769-91.jpg', 'position' => '50% 100%' ),
				array( 'file' => 'area-totsuka-gallery3-769-92.jpg', 'position' => '50% 79%' ),
				array( 'file' => 'area-totsuka-gallery4-769-93.jpg', 'position' => '50% 58%' ),
			),
			'local'   => array(
				'heading'  => array( 'デザインも、品質も、', '妥協しない外構づくり' ),
				'lead'     => array(
					'{region}エリアでは、住宅地ごとの敷地条件や街並みに合わせたプランニングが重要です。',
					'EXoneでは、デザイン性と使いやすさを両立し、一人ひとりの暮らしに合わせたご提案を行います。',
				),
				'photo'    => 'area-totsuka-local-769-112.jpg',
				'position' => '100% 50%',
			),
			'cards'   => array(
				array(
					'label'    => array( '建物と調和するデザイン' ),
					'label_sp' => array( '建物と調和する', 'デザイン' ),
					'image'    => 'area-totsuka-card1-769-104.jpg',
					'position' => '44% 0%',
				),
				array(
					// PC は 1 行に収まらないので 2 行にする（仙台と同じ）。
					'label'    => array( 'ライフスタイルに合わせた', 'プランニング' ),
					'label_sp' => array( 'ライフスタイルに', '合わせたプランニング' ),
					'image'    => 'area-totsuka-card2-769-126.jpg',
					'position' => '50% 50%',
				),
				array(
					'label'    => array( '年間650件以上の施工実績' ),
					'label_sp' => array( '年間650件以上の', '施工実績' ),
					'image'    => 'area-totsuka-card3-769-123.jpg',
					'position' => '50% 50%',
				),
				array(
					'label'    => array( 'AIによる完成イメージの可視化' ),
					'label_sp' => array( 'AIによる', '完成イメージの可視化' ),
					'image'    => 'area-totsuka-card4-769-101.jpg',
					'position' => '0% 45%',
				),
			),
		),
		// 盛岡（ボード 5 行目）。
		'morioka'   => array(
			'photo'   => 'area-morioka-store-739-101.jpg',
			'bg'       => 'area-bg-morioka-847-136.jpg', // 847:136 上端の背景（店内）
			'bg_position' => '50% 50%', // PC の切り抜き位置（カンプの crop から）
			'gallery' => array(
				array( 'file' => 'area-morioka-gallery1-739-114.jpg', 'position' => '50% 42%' ),
				array( 'file' => 'area-morioka-gallery2-739-115.jpg', 'position' => '50% 100%' ),
				array( 'file' => 'area-morioka-gallery3-739-117.jpg', 'position' => '50% 48%' ),
				array( 'file' => 'area-morioka-gallery4-739-116.jpg', 'position' => '50% 100%' ),
			),
			'local'   => array(
				'heading'  => array( '四季を通して', '快適な暮らしへ' ),
				'lead'     => array(
					'{region}エリアでは、積雪だけでなく、凍害や植栽選びまで考えた外構設計が重要です。',
					'冬の安全性と一年を通じた美しさを両立するプランをご提案します。',
				),
				'photo'    => 'area-morioka-local-759-252.jpg',
				'position' => '0% 50%',
			),
			'cards'   => array(
				array(
					'label'    => array( '越冬できる植栽' ),
					'label_sp' => array( '越冬できる植栽' ),
					'image'    => 'area-morioka-card1-759-261.jpg',
					'position' => '93% 0%',
				),
				array(
					'label'    => array( '凍害に強い建材' ),
					'label_sp' => array( '凍害に強い建材' ),
					'image'    => 'area-morioka-card2-759-265.jpg',
					'position' => '0% 100%',
				),
				array(
					'label'    => array( '雪かきしやすい動線' ),
					'label_sp' => array( '雪かきしやすい動線' ),
					'image'    => 'area-morioka-card3-759-258.jpg',
					'position' => '50% 50%',
				),
				array(
					'label'    => array( '排水・凍結対策' ),
					'label_sp' => array( '排水・凍結対策' ),
					'image'    => 'area-morioka-card4-759-255.jpg',
					'position' => '28% 50%',
				),
			),
		),
		'aomori'    => $aomori,
		// 八戸（ボード 3 行目）。
		'hachinohe' => array(
			'photo'   => 'area-hachinohe-store-802-41.jpg', // 2026-10-07 差し替え（旧 737:81）
			'bg'       => 'area-bg-hachinohe-847-144.jpg', // 847:144 上端の背景（店内）
			'bg_position' => '50% 50.6%', // PC の切り抜き位置（カンプの crop から）
			'gallery' => array(
				array( 'file' => 'area-hachinohe-gallery1-737-82.jpg', 'position' => '50% 48%' ),
				array( 'file' => 'area-hachinohe-gallery2-737-83.jpg', 'position' => '50% 100%' ),
				array( 'file' => 'area-hachinohe-gallery3-737-84.jpg', 'position' => '50% 48%' ),
				array( 'file' => 'area-hachinohe-gallery4-737-85.jpg', 'position' => '50% 0%' ),
			),
			'local'   => array(
				'heading'  => array( '気候と敷地条件に合わせた、', '安心の外構設計' ),
				'lead'     => array(
					'{region}エリアでは、凍上対策や施工条件など、地域特有の環境を考慮することが重要です。',
					'現地調査をもとに、土地条件や法規も踏まえた最適なプランをご提案します。',
				),
				'photo'    => 'area-hachinohe-local-757-217.jpg',
				'position' => '100% 50%',
			),
			'cards'   => array(
				array(
					'label'    => array( '凍上対策' ),
					'label_sp' => array( '凍上対策' ),
					'image'    => 'area-hachinohe-card1-757-220.jpg',
					'position' => '30% 87%',
				),
				array(
					'label'    => array( 'カーポート設置条件' ),
					'label_sp' => array( 'カーポート設置条件' ),
					'image'    => 'area-hachinohe-card2-757-226.jpg',
					'position' => '42% 69%',
				),
				array(
					'label'    => array( '敷地条件に合わせた土留め提案' ),
					'label_sp' => array( '敷地条件に合わせた', '土留め提案' ),
					'image'    => 'area-hachinohe-card3-757-223.jpg',
					'position' => '40% 50%',
				),
				array(
					'label'    => array( '排水・凍結対策' ),
					'label_sp' => array( '排水・凍結対策' ),
					'image'    => 'area-hachinohe-card4-757-229.jpg',
					'position' => '5% 50%',
				),
			),
		),
		// 弘前（ボード 4 行目）。外観と店内だけ差し替え、Local Expertise とカードは青森と共通。
		'hirosaki'  => array(
			'photo'   => 'area-hirosaki-store-739-88.jpg',
			'bg'       => 'area-bg-hirosaki-847-137.jpg', // 847:137 上端の背景（店内）
			'bg_position' => '50% 59.8%', // PC の切り抜き位置（カンプの crop から）
			'gallery' => array(
				array( 'file' => 'area-hirosaki-gallery1-739-91.jpg', 'position' => '50% 48%' ),
				array( 'file' => 'area-hirosaki-gallery2-739-94.jpg', 'position' => '50% 100%' ),
				array( 'file' => 'area-hirosaki-gallery3-739-97.jpg', 'position' => '50% 100%' ),
				array( 'file' => 'area-hirosaki-gallery4-739-119.jpg', 'position' => '50% 48%' ),
			),
			'local'   => $aomori_local,
			'cards'   => $aomori_cards,
		),
	);
}

/**
 * AREA。PC 612:855（店舗ボックス 612:637・Local Expertise 612:853・カード 612:673）/
 * SP 641:1315〜641:1427。
 *
 * 店舗情報テーブルは支店データ（exterior_exone_stores()）、写真と Local Expertise は
 * exterior_exone_store_area_media() の支店ごとの素材を並べる。
 *
 * TODO: リード文の「［主要対応地域］」は未支給のため、対応エリア（areas）を
 *       そのまま入れている。主要対応地域の文言が決まったら差し替える。
 *
 * @param array<string, string> $store 支店データ。
 * @return array<string, mixed>
 */
function exterior_exone_store_area( array $store ) {
	$media = exterior_exone_store_area_media();
	$slug  = isset( $store['slug'], $media[ $store['slug'] ] ) ? $store['slug'] : 'aomori';
	$media = $media[ $slug ];

	// Local Expertise の本文の {region} を、この支店の地域名にする（2026-10-02 の指示）。
	$media['local']['lead'] = str_replace( '{region}', $store['region'], $media['local']['lead'] );

	$cards = array();

	foreach ( $media['cards'] as $exterior_exone_index => $exterior_exone_card ) {
		$cards[] = array_merge(
			array( 'number' => sprintf( '%02d', $exterior_exone_index + 1 ) ),
			$exterior_exone_card
		);
	}

	return array(
		'title'   => 'AREA',
		// 上端の背景は支店ごと（847:132〜847:145。2026-10-10）。無い拠点は旧共通写真。
		'image'   => ! empty( $media['bg'] ) ? $media['bg'] : 'area-bg-store-620-292.jpg',
		'image_position' => ! empty( $media['bg_position'] ) ? $media['bg_position'] : '',
		// 612:660。PC は 1 行、SP は 2 行（641:1320 の改行位置。文言は PC を正とする: 決定 B）。
		'banner'  => array( $store['region'] . 'とその周辺エリアの', '外構工事に対応しています' ),
		'lead'    => array(
			'EXone' . $store['name'] . 'では、' . ( ! empty( $store['areas_lead'] ) ? $store['areas_lead'] : $store['areas'] ) . 'を中心に外構・エクステリア工事を承っています。',
			'地域ごとの積雪、風、敷地条件、道路環境なども考慮し、長く快適に使える外構をご提案します。',
		),
		// 612:640。ラベルと値の 6 行（区切り線は CSS 側）。値が配列の行は SP で改行する。
		'rows'    => array(
			array(
				'label' => '所在地',
				// 郵便番号が未確認の拠点（zip が空）は住所だけを出す。
				'value' => array_values( array_filter( array( $store['zip'], $store['address'] ), 'strlen' ) ),
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
		'photo'   => $media['photo'],
		'gallery' => $media['gallery'],
		'logo'    => 'area-logo-1131-38.png',
		'branch'  => '[ ' . $store['name'] . ' ]',
		// Local Expertise。英字は全支店共通。
		'local'   => array_merge( array( 'eng' => 'Local Expertise' ), $media['local'] ),
		'cards'   => $cards,
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
		// 612:366。PC は 1 行、SP は 2 行（641:1433）。
		'heading' => array( '理想の外構について、', '私たちと話してみませんか' ),
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
		'title'        => 'COLUMN',
		'jp'           => $store['region'] . 'の外構づくりに役立つ情報',
		'lead'         => array(
			'外構費用の考え方やカーポートの選び方、目隠しフェンス、庭づくりなど、',
			$store['region'] . 'で外構工事を検討している方に役立つ情報を発信します。',
			'地域の気候や住宅事情も踏まえながら、後悔しない外構づくりのポイントを分かりやすく解説します。',
		),
		// SP（641:1448）は 1 行目と 2 行目を 1 段落につなぐ（この添字の行のあとの改行を SP で消す）。
		'lead_sp_join' => array( 0 ),
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

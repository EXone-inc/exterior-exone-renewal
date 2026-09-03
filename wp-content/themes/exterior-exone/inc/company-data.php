<?php
/**
 * 企業情報ページの表示データ。
 *
 * カンプ: PC 917:1408 / SP 917:901
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Why We Exist の 3 カード。カンプ 917:1542-1553 / SP 917:942-952。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_why_cards() {
	return array(
		array(
			'eng'   => 'Visibility',
			'jp'    => '外構をもっと透明に。',
			'image' => 'FV-image1.jpg',
		),
		array(
			'eng'   => 'Clearly',
			'jp'    => 'もっと分かりやすく。',
			'image' => 'FV-image2.jpg',
		),
		array(
			'eng'   => 'Inspiring',
			'jp'    => 'もっとワクワクするものへ。',
			'image' => 'FV-image3.jpg',
		),
	);
}

/**
 * 私たちが作る体験の 4 カード（PC のみ）。カンプ 917:1593-1605。
 *
 * TODO: カンプは 4 枚とも別画像だが、支給は price.jpg の 1 枚のみ。
 *       残り 3 枚が支給されたら image を差し替える。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_visible_cards() {
	return array(
		array(
			'eng'   => 'Visible Value',
			'jp'    => '見える価格',
			'desc'  => "項目ごとの内訳まで\nすべて見える、安心の価格設計。",
			'image' => 'price.jpg',
		),
		array(
			'eng'   => 'Visible Design',
			'jp'    => '見える提案',
			'desc'  => "3DパースやAIパースで、\n完成後のイメージを事前に可視化。",
			'image' => 'price.jpg',
		),
		array(
			'eng'   => 'Visible Process',
			'jp'    => '見える施工管理',
			'desc'  => "行程・進捗・職人の作業まで、\nリアルタイムで見える施工管理。",
			'image' => 'price.jpg',
		),
		array(
			'eng'   => 'Visible Quality',
			'jp'    => '見える品質基準',
			'desc'  => "独自の品質基準とチェック体制で、\n高品質な仕上がりを保証。",
			'image' => 'price.jpg',
		),
	);
}

/**
 * 私たちを支える価値観の 3 項目。カンプ 917:1419-1466 / SP 917:919-970。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_principles() {
	return array(
		array(
			'number' => '01',
			'eng'    => 'Challenge',
			'desc'   => "常識に疑問を持ち、\n変化を恐れず挑戦する。",
			'icon'   => 'vlaue-logo1.png',
			'image'  => 'value-image1.jpg',
		),
		array(
			'number' => '02',
			'eng'    => 'Integrity',
			'desc'   => "誠実であることを最優先に。\nコミュニケーションを大切にする。",
			'icon'   => 'vlaue-logo2.png',
			'image'  => 'value-image2.jpg',
		),
		array(
			'number' => '03',
			'eng'    => 'System & Technology',
			'desc'   => '仕組み化、DX、効率化',
			'icon'   => 'vlaue-logo3.png',
			'image'  => 'value-image3.jpg',
		),
	);
}

/**
 * エコシステムの構成要素（SP はリスト表示、PC は円形図の画像）。
 * カンプ SP 917:989-1013。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_ecosystem_items() {
	return array(
		array(
			'eng'  => 'Standardization',
			'jp'   => '教育・人材育成',
			'desc' => "体系的な教育と育成で、\nプロフェッショナルを全国へ算出。",
		),
		array(
			'eng'  => 'Techology',
			'jp'   => 'テクノロジー・DX',
			'desc' => "AI・デジタル技術で、\nすべてのプロセスを可視化・最適化。",
		),
		array(
			'eng'  => 'Knowledge',
			'jp'   => 'ナレッジ・データベース',
			'desc' => "施工事例やノウハウを蓄積し、\n知識を資産として次の品質へつなげる。",
		),
		array(
			'eng'  => 'Standardization',
			'jp'   => '標準化・品質基準',
			'desc' => "独自の基準と仕組みで、\nどこでも同じ品質を再現する。",
		),
		array(
			'eng'  => 'Construction',
			'jp'   => '施工・品質管理',
			'desc' => "DX施工管理で進捗・品質を、\nリアルタイムに可視化し高品質を徹底。",
		),
		array(
			'eng'  => 'Design',
			'jp'   => '設計・デザイン',
			'desc' => "トレンドを取り入れた高品質なデザインで、\n暮らしを彩る価値を創造。",
		),
	);
}

/**
 * パートナーネットワークの 4 カード。カンプ 917:1524-1531 / SP 917:907-1015。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_partners() {
	return array(
		array(
			'label' => 'ハウスメーカー・工務店パートナー',
			'image' => 'ecosystem-image1.jpg',
		),
		array(
			'label' => 'メーカー・建材パートナー',
			'image' => 'ecosystem-image2.jpg',
		),
		array(
			'label' => '協力施工会社ネットワーク',
			'image' => 'ecosystem-image3.jpg',
		),
		array(
			'label' => '地域パートナーネットワーク',
			'image' => 'ecosystem-image4.jpg',
		),
	);
}

/**
 * 会社概要。カンプ 917:1560 / SP 917:1018-1046。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_company_profile() {
	return array(
		array(
			'label' => '会社名',
			'value' => '株式会社Exone',
		),
		array(
			'label' => '代表者',
			'value' => '中村 剣太',
		),
		array(
			'label' => 'TEL',
			'value' => '017-711-8031',
		),
		array(
			'label' => 'FAX',
			'value' => '017-711-0076',
		),
		array(
			'label' => '本社',
			'value' => '〒030-0846 青森県青森市青葉3丁目1-8',
		),
		array(
			'label' => '資本金',
			'value' => '359,723,195円(資本準備金含む)',
		),
		array(
			'label' => '事業内容',
			'value' => '外構工事業',
		),
		array(
			'label' => '従業員数',
			'value' => '45名(2026年8月1日時点)',
		),
		array(
			'label' => '主要取引先',
			'value' => 'タマホーム株式会社、大東建託リーシング株式会社、株式会社ハシモトホーム、株式会社サイトーホーム、株式会社一条工務店',
		),
	);
}

/**
 * 企業情報ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * @param string $file images/company/ 配下のファイル名。
 * @return string
 */
function exterior_exone_company_image( $file ) {
	$webp = 'images/company/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/company/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/company/' . $file );
}

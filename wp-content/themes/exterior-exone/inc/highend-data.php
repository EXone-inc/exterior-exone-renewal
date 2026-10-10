<?php
/**
 * ハイエンドページ（/plans/high-end/。page-high-end.php）の文言・画像の単一出典。
 *
 * カンプ: PC 713:31（1920x13761）/ SP 733:290（375x7164）/ guide 733:415
 * 設計メモ: docs/figma-highend-page.md
 * 仕様書: docs/spec-20261005-highend-page.md（決定事項 docs/spec-20261005-highend-page-decisions.md）
 *
 * 改行は「行の配列」で持つ。行は文字列（次の行との間で PC・SP とも改行）か、
 * array( 文字列, 'pc' | 'sp' | 'both' ) で改行の出し分けを指定する（'pc' は PC だけ改行）。
 * 出力は exterior_exone_highend_lines()。画像は images/highend/ のファイル名。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 行の配列を、改行の出し分け（.u-br-pc / .u-br-sp）付きの HTML にする。
 *
 * @param array $lines 行の配列（ファイル冒頭の書式）。
 * @return string エスケープ済みの HTML（br だけを含む）。
 */
function exterior_exone_highend_lines( $lines ) {
	$lines = array_values( (array) $lines );
	$last  = count( $lines ) - 1;
	$html  = '';
	$brs   = array(
		'both' => '<br>',
		'pc'   => '<br class="u-br-pc">',
		'sp'   => '<br class="u-br-sp">',
	);

	foreach ( $lines as $index => $line ) {
		$text = is_array( $line ) ? (string) $line[0] : (string) $line;
		$br   = ( is_array( $line ) && isset( $line[1] ) ) ? $line[1] : 'both';

		$html .= esc_html( $text );

		if ( $index < $last && isset( $brs[ $br ] ) ) {
			$html .= $brs[ $br ];
		}
	}

	return wp_kses( $html, array( 'br' => array( 'class' => array() ) ) );
}

/**
 * ハイエンドページ用の画像 URL を返す（WebP があれば優先）。
 *
 * images/highend/webp/ に同名の .webp があればそちらを返す。元画像は残してあるので、
 * webp ディレクトリを削除すれば元の配信に戻る（images/plans/ と同じ）。
 *
 * @param string $file images/highend/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_highend_image( $file ) {
	$webp = 'images/highend/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/highend/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/highend/' . $file );
}

/**
 * FV と夕方 → 夜 → 暗幕 → コピーの演出（PC 713:34・713:39・713:116 / SP 733:292〜309）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_fv() {
	return array(
		'evening'   => 'pc-fv-bg-evening-713-35.jpg', // 713:35 / 733:292
		'night'     => 'pc-fv-bg-night-713-39.jpg', // 713:39・713:117 / 733:303・733:304
		'img'       => array( 1672, 941 ),
		'title'     => 'HIGH-END DESIGN', // 713:38 / 733:302
		// 713:37（PC 1 行）/ 733:300（SP 2 行）
		'lead'      => array(
			array( '唯一無二の一邸、', 'sp' ),
			'あなただけの特別を',
		),
		// 暗幕の後のコピー 713:121・713:120・713:122 / 733:308・733:306・733:309
		'statement' => array(
			'title' => 'HIGH-END DESIGN',
			'lines' => array(
				'建築と響き合う佇まい。',
				'素材が生み出す表情。',
				'光と緑に包まれる時間。',
				'住まいへのこだわりを、その外側まで。',
				array( 'EXoneが一邸ごとに描く、', 'sp' ), // SP は 2 行に分ける（733:306）
				'オーダーメイドの外構・エクステリア。',
			),
			'logo'  => 'images/top/logo.svg', // 713:122 の PNG と同じ絵柄
		),
	);
}

/**
 * Design Philosophy（PC 713:207 / SP 733:320〜315）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_philosophy() {
	return array(
		'eng'     => 'Design Philosophy', // 713:42 / 733:320
		'heading' => array( '型ではなく、理想からつくる' ), // 713:41 / 733:321
		// 713:51（PC Bold 30 左）/ 733:336（SP Regular 12 中央）
		'copy'    => array(
			'建築。暮らし。美意識。',
			'その一つひとつを読み解き、',
			'その住まいだけの特別な外構を描く。',
		),
		// 住宅 CG（透過・影込み。枠より上 20 / 左右 32 / 下 44 はみ出す）713:50 / 733:318
		'house'   => array(
			'pc' => array(
				'file' => 'pc-philosophy-house-713-50.png',
				'img'  => array( 2720, 1468 ),
			),
			'sp' => array(
				'file' => 'sp-philosophy-house-733-318.png',
				'img'  => array( 774, 462 ),
			),
		),
		'gallery' => 'DESIGN GALLERY', // 713:60 / 733:317
		// 713:61（PC 2 行）/ 733:66（SP 3 行）
		'body'    => array(
			array( 'その住まいだけの、表情がある。', 'sp' ),
			'門まわり、庭、光、植栽、アプローチ。',
			'一邸ごとに、外構の表情は変わります。',
		),
		// img list 713:80 / 733:316・733:315（js/plans-imglist.js のエンドレスロール）
		'imglist' => array(
			array(
				'file' => 'pc-fv-bg-evening-713-35.jpg', // 713:83（FV と同じ夕景）
				'img'  => array( 1672, 941 ),
			),
			array(
				'file' => 'pc-philosophy-gallery-02-713-82.jpg', // 713:82 / 733:315
				'img'  => array( 1672, 941 ),
			),
			array(
				'file' => 'pc-philosophy-gallery-03-713-81.jpg', // 713:81（PC のみカンプに見える）
				'img'  => array( 1672, 941 ),
			),
		),
	);
}

/**
 * Design Details（PC 713:208 / SP 733:322〜345）。
 *
 * 見出しは PC を正とする（SP の読点なし・コピペ残りは PC に合わせる。決定）。
 * crop は写真の切り抜き（幅 / 高さ / 左 / 上。カンプ PC の画像塗りの値。SP も同じ値で使う）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_details() {
	return array(
		'eng'     => 'Design Details', // 713:45 / 733:322
		'heading' => array( '上質さは、細部のつながりに宿る' ), // 713:44 / 733:334
		'items'   => array(
			array(
				'num'     => '01',
				'eng'     => 'MATERIALS',
				'heading' => '素材の表情を、重ねる',
				'body'    => array(
					'石やタイル、左官など、素材が持つ色や質感を丁寧に組み合わせる。',
					'建物の外壁や周囲の景観とのつながりまで考え、住まいの個性を引き立てます。',
				),
				'file'    => 'pc-details-01-materials-713-124.jpg',
				'img'     => array( 1536, 1024 ),
				'crop'    => array( '100%', '117.73%', '0%', '-17.7%' ), // 713:124（下寄せ）
			),
			array(
				'num'     => '02',
				'eng'     => 'LIGHTING',
				'heading' => '光と影まで、デザインする',
				'body'    => array(
					'足元を照らす灯り、植栽を浮かび上がらせる光、壁に落ちる影。',
					'必要な明るさと夜の美しさを考え、昼とは異なる外構の表情を描きます。',
				),
				'file'    => 'pc-details-02-lighting-713-133.jpg',
				'img'     => array( 1672, 941 ),
				'crop'    => array( '100.63%', '100.01%', '-0.31%', '0%' ), // 713:133
			),
			array(
				'num'     => '03',
				'eng'     => 'GREENERY',
				'heading' => '緑で、空間に奥行きを',
				'body'    => array(
					'窓からの眺め、季節の移ろい、周囲からの視線。',
					'植栽の役割を考えながら、成長や手入れにも配慮した、暮らしに寄り添う緑をご提案します。',
				),
				'file'    => 'pc-details-03-greenery-802-51.jpg', // 2026-10-07 差し替え（旧 713:128）
				'img'     => array( 1672, 941 ),
				'crop'    => array( '103.87%', '100.06%', '0%', '-0.03%' ), // 802:51（ほぼ等倍。幅だけ少し広げて右端を切る）
			),
			array(
				'num'     => '04',
				'eng'     => 'FUNCTION',
				'heading' => '美しさを、使いやすさへ',
				'body'    => array(
					'車の出し入れ、玄関までの移動、庭で過ごす時間。',
					'毎日の動きを丁寧に捉え、美しさと使いやすさが両立する配置を考えます。',
				),
				'file'    => 'pc-details-04-function-713-137.jpg',
				'img'     => array( 1536, 1024 ),
				'crop'    => array( '100%', '117.73%', '0%', '-0.04%' ), // 713:137（上寄せ）
			),
		),
	);
}

/**
 * Design Plans（PC 713:209 / SP 733:323〜371）。
 *
 * カードの名前・英名・写真・価格は exterior_exone_plans_highend()['plans'] を key で引く。
 * ここに持つのはカンプの本文・導入コピー・注記（仕様書 §6 Q1）。
 * cards は両者を key で突き合わせた、出力用のカード（並びは PLANS データの順）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_plans() {
	$data = exterior_exone_highend_plans_text();

	$data['cards'] = array();

	foreach ( exterior_exone_plans_highend()['plans'] as $plan ) {
		if ( ! isset( $data['bodies'][ $plan['key'] ] ) ) {
			continue;
		}

		$data['cards'][] = array(
			'key'   => $plan['key'],
			'name'  => $plan['name'],
			'eng'   => $plan['eng'], // カード 2 は TOP DESIGNER PLAN（PC 713:110 の表記は誤り。決定）
			'file'  => $plan['file'], // images/plans/（exterior_exone_plans_image()）
			'img'   => $plan['img'],
			'price' => $plan['price'],
			'body'  => $data['bodies'][ $plan['key'] ],
		);
	}

	return $data;
}

/**
 * Design Plans のうち、このページだけの文言（カンプの本文・導入コピー・注記）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_plans_text() {
	return array(
		'eng'     => 'Design Plans', // 713:48 / 733:323
		// 713:47（PC 1 行）/ 733:335（SP 2 行）
		'heading' => array(
			array( 'こだわりに応える、', 'sp' ),
			'2つのデザインプラン',
		),
		// 713:84（PC 中央 4 行）/ 733:346（SP 左 2 段落）
		'lead'    => array(
			'住まいや暮らしに合わせて、一から外構を考える。',
			array( 'その想いを共通に、', 'pc' ),
			array( 'デザイナーと理想を形にするプランと、', 'pc' ),
			'トップデザイナーの視点からコンセプトを深めるプランをご用意しています。',
		),
		// key は exterior_exone_plans_highend()['plans'] の key
		'bodies'  => array(
			'designer'     => array(
				'思い描く雰囲気や、外構で実現したい暮らしをお聞かせください。',
				'建物との調和、敷地の使い方、素材の組み合わせを考え、デザイナーが一邸ごとの外構をご提案します。',
				'完成イメージをパースで確かめながら、理想を具体的なデザインへとつなげていくプランです。',
			),
			// SP は 2・3 段落目を結合（733:361）
			'top-designer' => array(
				'理想を形にするだけでなく、まだ気づいていない住まいの可能性まで。',
				array( 'トップデザイナーならではの視点で、建築と外構の関係を捉え直し、一邸を貫くデザインコンセプトからご提案します。素材、植栽、光、空間の余白。', 'pc' ),
				'細部にまで一貫した考え方を通し、その住まいだけの風景を描くプランです。',
			),
		),
		// 713:85（PC のみ）
		'note'    => array(
			'※記載料金はパース制作費の目安です。外構工事費は含まれません。',
			'制作範囲、パースの枚数、修正対応、追加費用などの条件は、お申し込み前にご案内します。',
		),
	);
}

/**
 * DX Experience（PC 713:140 / SP 733:291・733:372〜379）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_dx() {
	return array(
		'eng'     => 'DX Experience', // 713:146 / 733:377
		'heading' => array( 'こだわりを、見える納得へ' ), // 713:145 / 733:378
		// 713:149（PC 6 行）/ 733:379（SP 4 段落）
		'body'    => array(
			array( '建物と外構のバランス。', 'pc' ),
			'素材の組み合わせ。',
			'灯りがつくる、夜の表情。',
			'言葉や図面だけでは捉えにくいイメージを、パースや3Dによって、より具体的に。',
			array( '思い描く理想を共有し、確かめながらデザインを深める。', 'pc' ),
			'EXoneのDXは、そのための体験を支えます。',
		),
		'bg'      => array(
			'pc' => array(
				'file' => 'pc-dx-bg-713-142.jpg',
				'img'  => array( 1622, 2216 ),
			),
			'sp' => array(
				'file' => 'sp-dx-bg-733-291.jpg',
				'img'  => array( 1392, 4096 ),
			),
		),
		'devices' => array(
			'file' => 'pc-dx-devices-713-150.png', // 713:150 / 733:376
			'img'  => array( 1536, 1024 ),
		),
		'button'  => 'DX体験を見る', // 713:147 / 733:374
		'url'     => home_url( '/dx/' ), // 出力時に exterior_exone_store_link() を通す
	);
}

/**
 * Gallery（PC 713:164 / SP 733:380〜404）。
 *
 * items の順序 = タブの並び。02〜05 の英名・コピー・写真は仮（決定 §3「Gallery」。
 * 公開前に差し替え。差し替えはこの配列の書き換えだけで済む）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_gallery() {
	return array(
		'eng'     => 'Gallery', // 713:168 / 733:389
		'heading' => array( '美しい外構には、理由がある' ), // 713:167 / 733:390
		// 713:169（PC 中央 5 行）/ 733:391（SP 左 5 段落）
		'body'    => array(
			'住まいの個性を引き立てる門まわり。',
			'光と影が奥行きを生むアプローチ。',
			'室内からの眺めまで考えた庭。',
			'一枚の景色の中に、素材、植栽、動線、光のすべてが重なっています。',
			'EXoneが描くハイエンドデザインの世界をご覧ください。',
		),
		'items'   => array(
			array(
				'num'  => '01',
				'tab'  => '外観デザイン',
				'eng'  => 'FACADE DESIGN',
				'copy' => '建築の輪郭を引き立てる、静かな住まい',
				'file' => 'pc-gallery-01-facade-713-172.jpg',
				'img'  => array( 1672, 941 ),
			),
			array(
				'num'  => '02',
				'tab'  => 'アプローチ',
				'eng'  => 'APPROACH DESIGN', // 仮
				'copy' => '光に導かれ、玄関へと続く石の道', // 仮
				'file' => 'gallery-02-approach-808-54.jpg', // 2026-10-07 差し替え（808:54。カンプの切り抜きは上 -9.22%・高さ 118.44% = 上下を少し切る）
				'img'  => array( 1536, 1024 ),
			),
			array(
				'num'  => '03',
				'tab'  => 'ガーデン',
				'eng'  => 'GARDEN DESIGN', // 仮
				'copy' => '室内から眺める、季節を映す庭', // 仮
				'file' => 'gallery-cand-713-202.jpg', // 仮
				'img'  => array( 1536, 1024 ),
			),
			array(
				'num'  => '04',
				'tab'  => 'ライティング',
				'eng'  => 'LIGHTING DESIGN', // 仮
				'copy' => '夜に浮かび上がる、もうひとつの表情', // 仮
				'file' => 'gallery-cand-713-203.jpg', // 仮
				'img'  => array( 1536, 1024 ),
			),
			array(
				'num'  => '05',
				'tab'  => '素材・質感',
				'eng'  => 'MATERIAL DESIGN', // 仮
				'copy' => '石と緑が重なる、上質な佇まい', // 仮
				'file' => 'pc-gallery-02-713-175.jpg', // 仮
				'img'  => array( 1672, 941 ),
			),
		),
	);
}

/**
 * CONTACT（PC 713:151 / SP 733:405〜413）。
 *
 * SP の見出しは PC の文言（SP 733:411 はコピペ残り。決定）。ボタンはお問い合わせページ /contact/ へ。
 *
 * @return array<string, mixed>
 */
function exterior_exone_highend_contact() {
	return array(
		'eng'     => 'CONTACT', // 713:156 / 733:412
		'heading' => 'あなたの理想を、デザインの始まりに', // 713:158
		// 713:159（PC 中央 4 行）/ 733:413（SP 鍵括弧を中央 3 行）+ 733:407（SP 左 1 段落）
		'quotes'  => array( '「好きな建築」', '「心に残った庭の風景」', '「家族と過ごしたい時間」' ),
		'body'    => array(
			array( 'まだ、うまく言葉にできなくても構いません。', 'pc' ),
			array( '住まいの外側で、どんな暮らしを実現したいか。', 'pc' ),
			'その想いから、一邸のデザインを一緒に考えます。',
		),
		'bg'      => 'cta-bg-645-3.jpg', // images/store/（exterior_exone_store_image()）
		'button'  => 'ハイエンドプランについて相談する', // 713:163 / 733:410
		'url'     => home_url( '/contact/' ), // 出力時に exterior_exone_store_link() を通す
	);
}

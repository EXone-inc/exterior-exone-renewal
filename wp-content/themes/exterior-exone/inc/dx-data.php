<?php
/**
 * DX EXPERIENCE ページ（固定ページ dx）の表示データ。
 *
 * カンプ: PC 436:95（1920x18029）/ SP 439:1070（本体 439:752 Frame 14・375 基準）
 * 設計メモ: docs/figma-dx-page.md
 *
 * 文言・画像の単一の出典。inc/store-data.php / inc/company-data.php と同じ流儀で、
 * セクションごとの表示データはスプリントを進めるたびにここへ足していく。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FV（436:381 / 439:777-796）。
 *
 * 背景はカンプの「video」枠に TOP の FV 5 枚目の動画を流用する（2026-09-24）。
 * ファイルは videos/top/、ポスターは images/top/<name>-poster.jpg。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_fv() {
	return array(
		// 436:103（PC）/ 439:777（SP）。SP は縦に切り出した版（TOP と同じ）。
		'video'    => 'FV05.mp4',
		'video_sp' => 'FV05-sp.mp4',
		// 436:114 / 439:792。背後に敷く半透明の英字。
		'title'    => 'DX',
		// 436:117 / 439:793。DX に重ねる白の英字。
		'title_en' => 'EXPERIENCE',
		// 436:115 / 439:794。
		'heading'  => '見えない外構を、見える体験へ。',
		// 436:118 / 439:795。PC / SP とも同じ 4 行。
		'body'     => array(
			'図面だけでは想像できない。',
			'言葉だけでは伝わらない。',
			'EXoneはDXによって、',
			'完成までのすべてを"見える体験"へ変えていきます。',
		),
	);
}

/**
 * why dx（436:384 / 439:797-923）。
 *
 * 見出しと円のラベルは PC が 1 行・SP が 2 行なので、改行位置を配列で持つ
 * （PC は CSS で 1 行に繋ぎ、SP は行ごとに折る）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_why() {
	return array(
		// 436:97 / 439:799。見出しの背後に敷く飾り文字。
		'deco'    => 'WHY DX',
		// 436:289 / 439:800-801。2 要素固定で、境目が SP の改行位置（PC は 1 行）。
		'heading' => array(
			'EXoneのDXは、',
			'効率化のためではありません。',
		),
		// 436:272 / 439:798 ほか。円 4 個（PC 横 1 列 / SP 2x2）。
		// label は heading と同じく 2 要素固定（境目が SP の改行位置）。
		'items'   => array(
			array(
				'icon'  => 'pc-whydx-icon1-436-275.png',
				'label' => array( '完成が', '想像できない' ),
			),
			array(
				'icon'  => 'pc-whydx-icon2-436-280.png',
				'label' => array( '打ち合わせ内容を', '忘れる' ),
			),
			array(
				'icon'  => 'pc-whydx-icon3-436-284.png',
				'label' => array( '完成して', '初めて気付く' ),
			),
			array(
				'icon'  => 'pc-whydx-icon4-436-288.png',
				'label' => array( '工事内容が', '分からない' ),
			),
		),
		// 436:101 / 439:923。SP カンプは「見える顧客体験」の閉じ括弧の前で
		// 改行しているが不備のため、PC の改行位置に揃える（決定事項 2026-09-19）。
		'closing' => array(
			'そんな"見えない不安"があります。',
			'私たちはDXによって、',
			'そのすべてを「見える顧客体験」へ変えていきます。',
		),
	);
}

/**
 * 3D section / Exterior Explorer（436:388 / 439:753-755・439:988-989・439:922）。
 *
 * コピーの出典。映像と静止画の構成は exterior_exone_dx_explorer_media() に集約。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_explorer() {
	return array(
		// 436:119 / 439:988。
		'label' => 'Exterior Explorer',
		// 436:121 / 439:989。
		'copy'  => array(
			'見えない完成を、見える体験へ。',
			'完成イメージを、もっとリアルに。',
		),
		// カンプ下部の本文（436:124 / 439:922）はユーザー指示で出さない（2026-09-20）。
	);
}

/**
 * 承認済み本番映像。541フレーム・30fps、06の全景への戻りは含めない。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_explorer_media() {
	$sources = array();
	foreach ( array( 'pc', 'sp' ) as $format ) {
		$sources[ $format ] = array(
			'url'    => exterior_exone_asset_url( 'videos/dx/explorer-' . $format . '.mp4' ),
			'poster' => exterior_exone_asset_url( 'images/dx/explorer/' . $format . '-overview.webp' ),
		);
	}

	return array(
		'sources'  => $sources,
		'fps'      => 30,
		'chapters' => array(
			array( 'id' => 'overview', 'position' => 0, 'label' => '全景', 'en' => 'Overview' ),
			array( 'id' => 'approach', 'position' => 141 / 540, 'label' => 'アプローチ', 'en' => 'Approach' ),
			array( 'id' => 'carport', 'position' => 274 / 540, 'label' => 'カーポート', 'en' => 'Carport' ),
			array( 'id' => 'planting', 'position' => 407 / 540, 'label' => '植栽', 'en' => 'Planting' ),
			array( 'id' => 'gatepost', 'position' => 1, 'label' => '門柱', 'en' => 'Gatepost' ), // 436:393 は「GATEPOST / 門柱」
		),
	);
}

/**
 * DX セクションのタイトル帯とステップ 01〜04（436:394-395・436:125/161/189/216 /
 * 439:765-775・439:758/756/759/760）。
 *
 * body は PC と SP で文言・改行の切り方が違うため、幅ごとにカンプどおり持つ
 * （決定事項 Q1。TOP の DX の --pc / --sp コピーと同じ作法）。配列の 1 要素が
 * 1 ブロックで、ブロックの中は幅なりに折り返す。pc と sp が同じ内容なら
 * テンプレート側は 1 つだけ出力する。
 *
 * 写真・動画枠・3D モデルはすべて差し替え前提の仮素材（決定事項 2026-09-19）。
 * 01 のボタンの遷移先も未定のため # を入れてある。
 *
 * flow_active は exterior_exone_dx_flow() の何番目をアクティブにするか（0 始まり）。
 *
 * 関数名に page が入るのは、TOP の DX セクションが inc/top-data.php で
 * exterior_exone_dx_steps() を使っているため（別物）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_page_steps() {
	// 02〜04 で共通の端末画像（クイックパースのアプリ画面）。
	$exterior_exone_phone = array(
		'image' => 'pc-dx-step-portrait-img-436-166.jpg',
		'alt'   => 'スマートフォンに表示した3Dパースの画面',
	);

	return array(
		// 436:120 / 439:774。
		'label' => 'DX Experience',
		// 436:122 / 439:775。SP カンプの「繋がる」は表記ゆれのため
		// PC の「つながる」に統一（決定事項 2026-09-19）。
		'copy'  => array(
			'DXでつながる、外構づくり。',
			'すべての工程を、ひとつの体験へ。',
		),
		'items' => array(
			array(
				'num'         => '01',
				'name'        => 'VR展示場',
				'en'          => 'VR SHOWROOM',
				'heading'     => '歩いて、確かめる。',
				'body'        => array(
					// PC は 2 文を別行から始める（436:132 の 4 行）。
					'pc' => array(
						'図面では伝わらない、高さ、奥行き、素材感、植栽、ライティング。',
						'VR空間を体験することによって、完成後の暮らしをよりリアルに想像できます。',
					),
					// SP は同じ文を 1 段落で流す（439:817 の 3 行）。
					'sp' => array(
						'図面では伝わらない、高さ、奥行き、素材感、植栽、ライティング。VR空間を体験することによって、完成後の暮らしをよりリアルに想像できます。',
					),
				),
				// 436:134 / 439:769。カンプでは「video」枠なので、動画が届いたらここを差し替える。
				'visual'      => array(
					'image' => 'pc-dx01-video-poster-436-134.jpg',
					'alt'   => 'VR展示場で歩いて確かめられる街並み',
				),
				// 436:133 / 439:767。番号の右に重ねるモックアップ。
				'device'      => array(
					'image' => 'pc-dx01-img-436-133.png',
					'alt'   => 'VRコンテンツを表示したモニターとスマートフォン',
				),
				// 436:396 / 439:772。遷移先が未定のため # （決定事項 Q8）。
				'button'      => array(
					'label' => 'VR展示場を体験する',
					'url'   => '#',
				),
				'flow_active' => 1,
			),
			array(
				'num'         => '02',
				'name'        => 'AIパース',
				// カンプの「AI PARS」は誤記のため「AI PERS」で実装（決定事項 2026-09-19）。
				'en'          => 'AI PERS',
				'heading'     => '完成を、契約前に体験する。',
				'body'        => array(
					// 436:181 は 2 文（6 行）。
					'pc' => array(
						'イメージのラフ案をその場で瞬時に再現できるため、お客様のイメージをさまざまなパターンからすり合わせが可能です。',
						'イメージパースを作成するためのリードタイムを大幅に短縮し、お客様にパースを提示できるため、リアルタイムで完成イメージを確認いただけます。',
					),
					// 439:917 は短縮した別文（4 行）。
					'sp' => array(
						'イメージのラフ案をその場で瞬時に再現できるため、イメージパースを作成するためのリードタイムを大幅に短縮し、お客様のイメージをさまざまなパターンからすり合わせが可能です。',
					),
				),
				'visual'      => array(
					'image' => 'pc-dx02-left-img-436-164.jpg',
					'alt'   => 'AIパースで作成した外構の完成イメージ',
				),
				'device'      => $exterior_exone_phone,
				'button'      => null,
				'flow_active' => 2,
			),
			array(
				'num'         => '03',
				'name'        => '3Dパース',
				'en'          => 'BIMX',
				// SP カンプの見出しは 02 と同文の誤記のため PC を正とする（決定事項）。
				'heading'     => '図面を、もっとリアルに。',
				'body'        => array(
					// 436:211 は導入 1 文 + 本文（6 行）。
					'pc' => array(
						'紙の図面では伝わらない空間を、そのまま手に。',
						'スマートフォンやタブレットから3Dモデルを自由に歩き、完成後の外構をあらゆる角度から確認できます。建物とのバランスや空間の広がり、暮らしの動線までリアルに体感しながら、理想のイメージをより具体的に共有できます。',
					),
					// 439:920 は 1 文目が無く「完成を」表記（4 行）。
					'sp' => array(
						'スマートフォンやタブレットから3Dモデルを自由に歩き、完成をあらゆる角度から確認できます。建物とのバランスや空間の広がり、暮らしの動線までリアルに体感しながら、理想のイメージをより具体的に共有できます。',
					),
				),
				// 436:191 / 439:761。透過 PNG（角丸なし）。3D モデルを読み込むまでの表示と、
				// 動きを減らす設定・読み込み失敗時の代わりを兼ねる。
				'visual'      => array(
					'image' => 'pc-dx03-right-img-436-191.png',
					'alt'   => 'BIMxで確認する外構の3Dモデル',
				),
				// 同じ建物の 3D モデル（支給 sm-012.glb 78MB を Draco 圧縮・テクスチャ WebP 1024px
				// にして 4.6MB）。静止画と同じ大きさ・位置・向きから始めて、ゆっくり回し続ける
				// （2026-09-25）。orbit は「水平角 仰角 距離」、target は注視点（モデル座標。
				// 地面が高さ約 10m）。1920 で静止画の建物の外形（61,80〜1029,605）に合うよう
				// 実測で決めた値。
				'model'       => array(
					'file'     => 'models/dx/bimx-house.glb',
					'orbit'    => '-40deg 84deg 20.5m',
					'fov'      => '42deg',
					'target'   => '25m 11.3m 9.4m',
					'rotation' => '6deg', // 1 秒あたり。1 周 60 秒
				),
				'device'      => $exterior_exone_phone,
				'button'      => null,
				'flow_active' => 3,
			),
			array(
				'num'         => '04',
				'name'        => '施主共有システム',
				'en'          => 'CLIENT PORTAL',
				// SP カンプの「すべての情報をひ、一つに。」は誤記（決定事項 2026-09-19）。
				'heading'     => 'すべての情報を、一つに。',
				'body'        => array(
					'pc' => array( '変更履歴、写真、図面、連絡、工事進捗、すべてを一つの場所で共有。' ),
					'sp' => array( '変更履歴、写真、図面、連絡、工事進捗、すべてを一つの場所で共有。' ),
				),
				'visual'      => array(
					'image' => 'pc-dx04-left-img-436-220.jpg',
					'alt'   => 'タブレットとスマートフォンで施主共有システムの画面を見ているところ',
				),
				'device'      => $exterior_exone_phone,
				'button'      => null,
				'flow_active' => 4,
			),
		),
	);
}

/**
 * ステップの下に置くフローバーの 6 項目（436:399 / 439:839）。
 *
 * カンプは 01 の下にしか描かれていないが、01〜04 すべてに出して
 * そのステップをアクティブ表示する（決定事項 2026-09-19）。両端の
 * 「ご相談」「完成」はアイコン、間の 4 つはドットで表す。
 *
 * @return array<int, array<string, string>>
 */
function exterior_exone_dx_flow() {
	return array(
		array(
			'label' => 'ご相談',
			'icon'  => 'pc-dx01-flow-start-icon-436-151.png', // 436:151 / 439:848
		),
		array(
			'label' => 'VR体験',
			'icon'  => '',
		),
		array(
			'label' => 'AIパース',
			'icon'  => '',
		),
		array(
			'label' => 'BIMx',
			'icon'  => '',
		),
		array(
			'label' => '施工共有',
			'icon'  => '',
		),
		array(
			'label' => '完成',
			'icon'  => 'pc-dx01-flow-end-icon-436-138.png', // 436:138 / 439:849
		),
	);
}

/**
 * summary（437:450 / 439:937-968）。
 *
 * 夕景写真の上のコピー・本文と、円環図（TOP 理念セクションの図を流用）の中身。
 * 円環図のマークアップ・CSS・JS は TOP と共通で、ここが持つのは差分だけ
 * （6 項目の文言とアイコン・円内下部の英字・円径は CSS の .p-diagram--dx）。
 *
 * 「見える完成」のアイコンはフローバーの「完成」（436:138）と同じアセット。
 * ロゴと線画は TOP と同一アセットなので images/top/ のものをそのまま参照する
 * （線画は線描画アニメーションのためインライン SVG で出す）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_summary() {
	return array(
		// 436:241 / 439:939。夕景写真。
		'image'   => 'pc-summary-bg-img-436-241.jpg',
		// 436:245 / 439:941。
		'copy'    => array(
			'外構づくりを、',
			'もっと分かりやすく、もっと心地よく。',
		),
		// 436:244 / 439:943。PC は 1 段落 1 行、SP は成り行きで折り返して 6 行。
		// カンプの 2 行目の行頭にある半角スペースは作図ブレとみなして除いた。
		'body'    => array(
			'EXoneは、デザイン・価格・施工品質のどれか一つだけではなく、相談から完成後までの体験全体を大切にしています。',
			'専門スタッフによる提案力と、テクノロジーを活用した分かりやすい仕組みを組み合わせ、',
			'お客様が安心して選べる外構づくりを実現します。',
		),
		// 436:249 / 439:942。PC は円の中の上部、SP は円の下（CSS で位置だけ変える）。
		'lead'    => array(
			'私たちが届けたいのは、',
			'"安心"という顧客体験です。',
		),
		// 436:260 / 436:259（SP 439:968 / 439:962）。円の中の下部。
		'display' => 'EXTERIOR',
		'tagline' => 'DESIGN, MADE VISIBLE.',
		// 436:261 / 439:956。4 本の帯でマスクした円 = 6 か所で切れたリング。
		// 弧の切れ目の位置が PC と SP で少し違うため、幅ごとに差し替える。
		'rings'   => array(
			'pc' => 'pc-summary-circle-line-436-261.svg',
			'sp' => 'sp-summary-mask-group-439-956.svg',
		),
		// 円周の 6 項目。並び順は左上 → 右上 → 左中 → 右中 → 左下 → 右下
		// （位置は CSS の .p-diagram__icon--N / .p-diagram__copy--N）。
		// カンプでは「見える保証」だけ Bold だが作図ブレとみなして揃える。
		'items'   => array(
			array(
				'label' => '見える完成',
				// 436:256。フローバーの終点アイコン（436:138）と同じ家の線画なので共用する（リネームしない）。
				'icon'  => 'pc-dx01-flow-end-icon-436-138.png',
			),
			array(
				'label' => '見える提案',
				'icon'  => 'pc-summary-icon02-teian-436-269.png',
			),
			array(
				'label' => '見える品質',
				'icon'  => 'pc-summary-icon03-hinshitsu-436-271.png',
			),
			array(
				'label' => '見える価格',
				'icon'  => 'pc-summary-icon04-kakaku-436-268.png',
			),
			array(
				'label' => '見える保証',
				'icon'  => 'pc-summary-icon05-hosho-436-270.png',
			),
			array(
				'label' => '見える進捗',
				'icon'  => 'pc-summary-icon06-shinchoku-436-248.png',
			),
		),
	);
}

/**
 * WORKS（437:460 / 439:992-1019）。
 *
 * 事例の中身（要望・提案・見積・モーダルの文言）は支店ページと完全に同じなので
 * inc/works-data.php の exterior_exone_works_case() を使い、ここが持つのは
 * DX 側の見出し・リード文・写真・3D 枠の差分だけ。表示は共通部品
 *（template-parts/common/section-works.php）。
 *
 * リード文は PC が 3 行、SP が 6 行でカンプの改行位置が違うため別に持つ。
 * カンプの 2・3 行目の行頭にある半角スペースは作図ブレとみなして除いた。
 *
 * @return array<string, mixed>
 */
function exterior_exone_dx_works() {
	$case = exterior_exone_works_case();

	return array_merge(
		$case,
		array(
			// 436:326 / 439:1012。
			'title'   => 'WORKS',
			// 436:328 / 439:1011。
			'jp'      => '描いた未来を、現実へ。',
			// 436:327。
			'lead'    => array(
				'パースで確かめた完成イメージを、確かな施工品質でそのまま形に。',
				'素材の質感や光の入り方まで、完成後の景色をリアルに描き出します。',
				'提案から完成まで、イメージのずれをなくすことも、EXoneが目指すDX体験です。',
			),
			// 439:1010。SP はカンプどおり 6 行で折る。
			'lead_sp' => array(
				'パースで確かめた完成イメージを、',
				'確かな施工品質でそのまま形に。',
				'素材の質感や光の入り方まで、',
				'完成後の景色をリアルに描き出します。',
				'提案から完成まで、イメージのずれをなくすことも、',
				'EXoneが目指すDX体験です。',
			),
			// 436:324（メインと 1 枚目は同じ写真）/ 436:323 / 436:322。DX は CPT works を
			// 使わず、この 3 枚をカンプどおりに出す（gallery。2026-09-25）。
			'photos'  => array(
				'pc-works-main-thumb1-436-324.jpg',
				'pc-works-thumb2-436-323.jpg',
				'pc-works-thumb3-436-322.jpg',
			),
			// 右列に出すときだけの切り抜き。436:323 はカンプで 1.41 倍に拡大して枠に
			// 入れてあるため、その見え方に合わせたもの（押すとメインには全体の写真）。
			'thumbs'  => array(
				1 => 'pc-works-thumb2-crop-436-323.jpg',
			),
			// 437:463。下敷きは支店の灰色パネルではなく空写真（436:99）。
			'render'  => array_merge(
				$case['render'],
				array(
					// 3D モデル（下の model）の初期画角をそのまま描画した画像（透過 PNG、2400x1672）。
					// 右列と、押してから 3D に切り替わるまでのメインに使い、切り替わりで絵が
					// 変わらないようにする（カンプ 436:376 のパース画像から差し替え。2026-09-25）。
					// 画角を変えたら撮り直すこと。
					'image'    => 'works-3d-initial.png',
					'backdrop' => 'pc-3d-section-bg-436-99.jpg',
					// 押したときにメインに出す、同じ提案の 3D モデル（支給 js-007.glb 114MB を
					// Draco 圧縮・テクスチャ WebP 1024px 画質 50 にして 7.7MB）。最初はパース画像と
					// 同じ正面・目線の高さで止まっていて、ドラッグで回せる（2026-09-25）。
					'model'    => array(
						'file'   => 'models/dx/works-proposal.glb',
						'orbit'  => '0deg 88deg 19m',
						'fov'    => '40deg',
						'target' => '15.9m 2.6m 2m',
					),
				)
			),
			// 439:1034。モーダル上部の 3D パース。
			'modal'   => array_merge(
				$case['modal'],
				array( 'image' => 'pc-works-thumb4-3d-436-376.png' )
			),
		)
	);
}

/**
 * DX ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * images/dx/webp/ に同名の .webp があればそちらを返す。元画像は残してあるので、
 * webp ディレクトリを削除すれば元の配信に戻る（images/top/・images/store/ と同じ）。
 *
 * @param string $file images/dx/ 配下のファイル名。
 * @return string バージョンクエリ付き URL。
 */
function exterior_exone_dx_image( $file ) {
	$webp = 'images/dx/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/dx/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/dx/' . $file );
}

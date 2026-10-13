<?php
/**
 * 採用ページ（/recruit/）の文言・画像・並び。
 *
 * カンプは無い。正は現サイト https://exterior-exone.com/recruit/ と参照物 docs/reference-20261013-recruit/
 * 仕様書: docs/spec-20261013-recruit-page.md（決定事項 docs/spec-20261013-recruit-page-decisions.md）
 *
 * PHP 側の文言はここに集める（文言の出典は prod-recruit-body.html。決定 §3 の修正を反映した状態で持つ）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 採用ページの URL（ヘッダー・ドロワー・フッター・TOP の VIEW MORE の出典）。
 *
 * @param string $path /recruit/ 以下のパス（例 'entry/'）。
 * @return string
 */
function exterior_exone_recruit_url( $path = '' ) {
	return home_url( '/recruit/' . ltrim( (string) $path, '/' ) );
}

/**
 * 採用ページの画像 URL を filemtime 付きで返す。
 *
 * images/recruit/ に同名の .webp があればそちらを返す。
 *
 * @param string $file images/recruit/ 配下のファイル名。
 * @return string
 */
function exterior_exone_recruit_image( $file ) {
	$webp = 'images/recruit/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/recruit/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/recruit/' . $file );
}

/**
 * セクションの並び（data-section の値 => template-parts/recruit/section-<名前>.php）。
 *
 * 部品がまだ無いセクションは page-recruit.php が空の枠だけ置く。
 *
 * @return array<string, string>
 */
function exterior_exone_recruit_sections() {
	return array(
		'recruit-fv'           => 'fv',
		'recruit-training'     => 'training',
		'recruit-work'         => 'work',
		'recruit-marquee'      => 'marquee',
		'recruit-requirements' => 'requirements',
		'recruit-benefits'     => 'benefits',
		'recruit-flow'         => 'flow',
		'recruit-faq'          => 'faq',
	);
}

/**
 * FV（現サイト .recruit-first--view）。
 *
 * スライダーは現サイトの並びから重複の m-222079_photo1 の 2 回目（6 枚目）を除いた 7 枚（決定 §3-1）。
 * 見出しは現サイトの「h1 の中に p」を h1（英字 + 和文の 2 段）+ p（サブ）に直す（仕様書 Q3）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_fv() {
	return array(
		'slides' => array(
			array(
				'file' => 'fv-01.jpg', // EXONE_社員紹介-2
				'size' => array( 1280, 853 ),
				'alt'  => 'EXONE 社員紹介',
			),
			array(
				'file' => 'fv-02.jpg', // m-222079_photo1-1-…640x480-1（低解像度のまま。決定 §3-1）
				'size' => array( 640, 480 ),
				'alt'  => '',
			),
			array(
				'file' => 'fv-03.jpg', // EXONE_社員紹介-15
				'size' => array( 1280, 853 ),
				'alt'  => 'EXONE 社員紹介',
			),
			array(
				'file' => 'fv-04.jpg', // EXONE_42
				'size' => array( 1280, 853 ),
				'alt'  => '',
			),
			array(
				'file' => 'fv-05.jpg', // EXONE_社員紹介-9
				'size' => array( 1280, 853 ),
				'alt'  => 'EXONE 社員紹介',
			),
			array(
				'file' => 'fv-06.jpg', // EXONE_社員紹介-10
				'size' => array( 1280, 853 ),
				'alt'  => 'EXONE 社員紹介',
			),
			array(
				'file' => 'fv-07.jpg', // 青森内観-ロゴ
				'size' => array( 1280, 1280 ),
				'alt'  => '青森内観',
			),
		),
		'en'     => 'Creative × Challenge',
		'ja'     => array( '私たちと共に新たな', 'エクステリア業界を創造しよう！' ),
		'sub'    => array(
			'エクステリア業界の古い構造を見直し',
			'新たな時代を創る仲間を募集中！',
			'業界の変革を実現するため、共に挑戦しましょう。',
		),
		'apply'  => '応募する',
	);
}


/**
 * Training（現サイト .rsec--edu）。
 *
 * icon は images/recruit/icons/<名前>.svg（css/recruit.css の .p-rcicon--<名前>）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_training() {
	return array(
		'en'    => 'Training',
		'ja'    => '教育・研修体制',
		'lead'  => array(
			'職種ごとに体系的な研修・マニュアル・OJTを用意し、入社後の成長を手厚くサポート。',
			'「学ぶ → 同行 → 実践 → 独り立ち」の段階を踏みながら、一人ひとりが着実に力をつけていけます。',
		),
		'image' => array(
			'file' => 'training.webp',
			'size' => array( 1086, 1448 ),
			'alt'  => '社員へ提案内容を説明する研修の様子',
		),
		'items' => array(
			array(
				'icon' => 'helmet-safety',
				'cat'  => '現場系',
				'note' => '（施工管理職）',
				'desc' => '標準施工マニュアル・検査チェックリストで、品質を標準化。',
			),
			array(
				'icon' => 'handshake',
				'cat'  => '営業系',
				'note' => '（提案営業職）',
				'desc' => '6ヶ月ロードマップと3ヶ月育成マップで、座学→同行→ロープレ→OJT→独り立ち。',
			),
			array(
				'icon' => 'pen-ruler',
				'cat'  => 'プランナー系',
				'note' => '',
				'desc' => '約24日の短期集中で、基礎知識・CAD・デザイン・AIパースまで習得。',
			),
			array(
				'icon' => 'laptop',
				'cat'  => '事務系',
				'note' => '',
				'desc' => 'Notionマニュアル・月次定例・Q&A蓄積で、属人化を防ぐ体制。',
			),
			array(
				'icon' => 'chart-line',
				'cat'  => '管理職系',
				'note' => '（支店長候補・営業責任者）',
				'desc' => '会議体・KPI・勉強会運営を通じた、実践的なマネジメント育成。',
			),
		),
	);
}

/**
 * Work（現サイト .recruit-work / .work_iv-list / #Modal_menu1〜3）。
 *
 * モーダル 2・3 の Q6（モーダル 1 の Q5 と同文）は削除（決定 §3-3）。
 * 部署表記はカード「工事本部」・モーダル「工事部 施工管理」の食い違いも現サイトのまま（仕様書 Q17）。
 * 回答の行は配列。'gap' => true の行は前に段落の空き（現サイト br.am）を入れる。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_work() {
	return array(
		'en'      => 'Work',
		'ja'      => '働く人を知る',
		'label'   => '働く人を知る',
		'members' => array(
			array(
				'dept'  => '営業部',
				'role'  => '支店長',
				'year'  => '2022年入社',
				'card'  => 'work-card-1.webp',
				'photo' => 'work-modal-1.webp',
				'size'  => array( 1477, 1108 ),
				'name'  => '営業部 支店長',
				'join'  => '2022年中途入社',
				'qa'    => array(
					array(
						'q' => '支店長というポジションにおいて、どのような役割を最も大切にしていますか？',
						'a' => array( '店舗でみんなが話し合いができる、働きやすい雰囲気作りを大切にしております。' ),
					),
					array(
						'q' => 'これまでの現場経験が、マネジメントにどう活きていると感じますか？',
						'a' => array( 'これまでの私自身の失敗の経験から、どのようにしたら効率化できるか、どのような選択が最適か、など営業スタッフに対し明確に答えを伝えることが出来ている点です。' ),
					),
					array(
						'q' => 'チームビルディングや人材育成で意識していることは何ですか？',
						'a' => array( 'チームビルディング・人材育成で私が意識していることは常に相手の事を考え、行動・発言する。を大事にしております。' ),
					),
					array(
						'q' => 'これからの支店運営において、挑戦したいこと・実現したいことはありますか？',
						'a' => array( '挑戦・実現したいことは、弘前エリアでエクステリア・外構工事といえばEXone！！となりたいです！' ),
					),
					array(
						'q' => '入社前と比べて、良い意味でギャップを感じた点はありますか？',
						'a' => array( '従来の外構工事屋さんとのイメージのまま入社したのですが、システム管理やCADのパース・図面のクオリティーが高く良い意味でギャップを感じました。' ),
					),
				),
			),
			array(
				'dept'  => '営業部',
				'role'  => '営業',
				'year'  => '2025年入社',
				'card'  => 'work-card-2.webp',
				'photo' => 'work-modal-2.webp',
				'size'  => array( 1477, 1108 ),
				'name'  => '営業部 営業',
				'join'  => '2025年中途入社',
				'qa'    => array(
					array(
						'q' => '入社の決め手や前職との違いを感じた部分',
						'a' => array( '私自身がEXone弘前店のお客さんだったこともあり、価格や仕上がりに満足した為、信頼できる会社だと思い入社を希望しました。前職は“営業”というポジションではなかった為、売上を意識する部分で違いを感じました。' ),
					),
					array(
						'q' => 'お客様との関係づくりで大切にしていること',
						'a' => array( 'お客様の立場ならどう感じるか”を意識しながら対応するように心がけています。' ),
					),
					array(
						'q' => '提案やプランニングで印象に残っているエピソード',
						'a' => array( '海外の方の住宅でお問い合わせがあり、片言でのお打ち合わせでしたが最後まで親身に対応し、ご成約いただいたことです。' ),
					),
					array(
						'q' => '社内での連携やサポート体制について',
						'a' => array( '協力体制がしっかりとられており、「言いづらい・聞きづらい」がない会社だと感じました。一人だと時間がかかる内容でも、パースや見積など出来る人が協力してくれるので効率よく仕事が出来ていると思います。' ),
					),
					array(
						'q' => 'この仕事を通して成長を感じたことや、今後のビジョン',
						'a' => array( '初めは覚えた内容を説明することに必死でしたが、最近は自分が打ち合わせや見積した内容が形になることにやりがいを感じます。今後は、同じものを提案するだけでなく家の雰囲気に合ったものやコンセプトを意識した提案をしていけたらいいなと思います。' ),
					),
				),
			),
			array(
				'dept'  => '工事本部',
				'role'  => '施工管理',
				'year'  => '2022年入社',
				'card'  => 'work-card-3.webp',
				'photo' => 'work-modal-3.webp',
				'size'  => array( 1920, 1281 ),
				'name'  => '工事部 施工管理',
				'join'  => '2022年中途入社',
				'qa'    => array(
					array(
						'q' => 'この仕事に就いたきっかけや、EXoneを選んだ理由を教えてください。',
						'a' => array( '以前、自宅を建てた際に外構工事の現場を体験する機会があり、プランの打ち合わせや実際の施工を間近で見るなかで、この仕事に興味を持つようになりました。前職の営業所が閉鎖となる為、転職活動をしているタイミングでEXoneの求人を見つけました。元々興味のあった業界である点や、八戸店の立ち上げメンバーとして新しい環境でチームと共に成長できる点に魅力を感じ、入社を決めました。' ),
					),
					array(
						'q' => '1日の仕事の流れや、現場で大切にしていることはなんですか？',
						'a' => array(
							'主な業務は、現場管理・現場調査・発注手配・工程組みです。1日の流れは以下のようなパターンが多いです。',
							array(
								'gap'  => true,
								'text' => '午前：複数現場を巡回し、進捗確認や今後のスケジュール、懸念事項の把握を行います。あわせて現場調査、近隣への工事チラシ配布、完工後の確認なども実施します。',
							),
							'午後：事務所に戻り、工程表の作成や発注書の準備、部材の発注等を進めます。',
							'現場で特に大切にしているのは施工計画の精度と作業指示の明確さです。着工後では修正が難しいため、着工前に現場調査を十分に行い、懸念事項や施工に必要な数値を収集します。得られた情報を図面に記載し、初めて現場に入る作業員でもスムーズに作業ができるような指示の明確さを目指して作成しています。',
						),
					),
					array(
						'q' => '施工管理としてやりがいを感じる瞬間はどんなときですか？',
						'a' => array( '難易度の高い案件を無事に完工できたときに、強いやりがいを感じます。お客様のご希望どおりの施工が難しい現場や、既存の構造物との兼ね合いが複雑な現場においては、どのようにすれば理想的な仕上がりが実現できるかを工夫しながら考えます。私はそうした課題を考えること自体を楽しんでおり、難しいほど完工後の達成感と喜びは大きいです。' ),
					),
					array(
						'q' => 'チームや職人さんたちとの連携で心がけていることは？',
						'a' => array( '日頃から仕事以外の話題も交えてフランクにコミュニケーションをとるように心がけています。気軽に相談できる関係を築くことで、施工の相談やトラブルなどのネガティブな話もしやすくなり、結果として仕事が円滑に進むと考えています。' ),
					),
					array(
						'q' => 'これからEXoneで挑戦したいこと、目標があれば教えてください。',
						'a' => array( 'これからも多くの現場を担当し、外構・エクステリアに関する知識と施工ノウハウの引き出しをさらに増やしていきたいです。そして常に挑戦し続けることで、お客様に「EXoneに依頼して本当によかった」と思っていただける施工やサービスを提供していきたいです。' ),
					),
				),
			),
		),
	);
}

/**
 * 流れる大文字（現サイト .recruit_text--slide）。綴りは Challenge に直す（決定 §3-2）。
 *
 * @return string
 */
function exterior_exone_recruit_marquee() {
	return "Challenge Creative Let's Entry";
}

/**
 * Requirements（現サイト #recruit-req の 6 職種）。
 *
 * slug は要素の id（/recruit/#<slug> で開く。現サイト recruit.js の JOB_SLUGS と同じ）。
 * title は見出しの行（現サイトの <br> の位置）。lines の '' は現サイトの <br>（段落の空き）。
 * 営業責任者の仕事内容は現サイトの span 内の改行をそのまま持つ（表示上は 1 段落）。
 * CJK 互換部首の文字（⻑ ⾶ など）は通常の漢字に置き換え済み（仕様書 Q10）。
 * 営業責任者の注記は ※1 の 2 行を削除（決定 §3-4）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_requirements() {
	return array(
		'en'   => 'Requirements',
		'ja'   => '募集要項',
		'jobs' => array(
			array(
				'slug'  => 'construction',
				'title' => array( '施工管理職' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							'建築施工計画の策定・工程管理',
							'現場での安全管理、品質管理',
							'協力会社との折衝・調整',
							'予算管理・工程管理',
							'施工図面の作成・修正',
							'顧客への進捗報告・連絡',
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・施工管理経験',
							'・高卒以上の学歴',
							'',
							'【歓迎経験】',
							'・エクステリアに関する専門知識',
							'・ハウスメーカーでの経験',
							'・建設・土木ゼネコンでの経験',
							'・CADソフトの操作経験',
							'・造園施工管理技師',
							'・宅地建物取引士',
							'・2級建築施工管理技士以上',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'年俸500万〜※1',
						),
					),
					array(
						'label' => '昇給',
						'lines' => array(
							'年1回',
						),
					),
					array(
						'label' => '賞与',
						'lines' => array(
							'年1回※2',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費・役職手当',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'9:00〜18:00（実働8時間 / 1日）',
							'※残業は月20時間以下',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'年間123日以上（週休2日（土日）・GW・夏季・年末年始）',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'雇用・健康・労災・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（施工管理技士など）',
						),
					),
				),
				'notes' => array(
					'※1:上記給与には45時間分の固定残業代（時間外手当）を含みます。',
					'時間外手当は、時間外労働の有無に関わらず45時間分を支給します。45時間を超える時間外労働分の賃金は、別途支給します。',
					'※2: 賞与は、業績と各判定基準により定められた支給率に応じて支給します。',
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
			array(
				'slug'  => 'sales',
				'title' => array( '提案営業職' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							'・来店されたお客様へのエクステリア商品の提案・販売',
							'・顧客ニーズに合わせたプランニング・設計・見積書作成',
							'・施工管理者への引き継ぎ、施工進捗の管理',
							'・アフターフォロー対応',
							'・支店での接客対応',
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・営業経験2年以上（業界不問）',
							'・基本的なPCスキル（Excel、Wordなど）',
							'',
							'【歓迎経験】',
							'・エクステリアに関する資格（施工管理技士など）',
							'・ハウスメーカーでの経験',
							'・CADソフトの使用経験（Vectorworks、AutoCADなど）',
							'・建設業界での顧客対応経験',
							'・ビジネスマナー資格',
							'・見積もり作成や受発注業務の経験',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'年俸360万〜700万※1',
						),
					),
					array(
						'label' => '昇給',
						'lines' => array(
							'年1回',
						),
					),
					array(
						'label' => '賞与',
						'lines' => array(
							'年1回※2',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費・役職手当',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'9:00〜18:00（実働8時間 / 1日）',
							'※残業は月20時間以下',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'年間123日以上（週休2日（土日）・GW・夏季・年末年始）',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'雇用・健康・労災・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（エクステリアプランナー、施工管理技士など）',
						),
					),
				),
				'notes' => array(
					'※1:上記給与には45時間分の固定残業代（時間外手当）を含みます。',
					'時間外手当は、時間外労働の有無に関わらず45時間分を支給します。45時間を超える時間外労働分の賃金は、別途支給します。',
					'※2: 賞与は、会社業績と各判定基準により定められた支給率に応じて支給します。',
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
			array(
				'slug'  => 'manager',
				'title' => array( '支店長候補' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							'・外構・カーポートなどエクステリアの提案営業（既存顧客中心）',
							'・支店全体の売上・KPI管理',
							'・営業・設計・施工チームのマネジメント',
							'・支店目標達成に向けた戦略立案・実行',
							'・数値分析・課題抽出・改善施策の実行',
							'・本部・エリアマネージャーとの連携',
							'・必要に応じた営業同行・現場把握',
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・営業経験3年以上（業種不問）※エクステリア・ハウスメーカー経験者は歓迎',
							'・マネジメント経験（年数不問）',
							'・売上・粗利・受注率などの数字管理経験',
							'・支店・部署・チーム単位での目標設計・進捗管理経験',
							'・高卒以上',
							'',
							'【歓迎経験】',
							'・数字に強い方',
							'・エクステリア業界での経験',
							'・CADソフトの使用経験（Vectorworks、AutoCADなど）',
							'・建築施工管理技士などの資格',
							'・オリジナリティのある提案ができる方',
							'・デジタルツールを活用した提案経験',
							'・顧客折衝の経験が豊富な方',
							'',
							'【求める人物像】',
							'・若いチームで協調性を大切にできる方',
							'・成長意欲があり、新しいことに挑戦したい方',
							'・お客様との信頼関係を大切にする方',
							'・理想の住まいづくりを楽しみ、喜んで取り組める方',
							'・責任感・向上心を持って仕事に取り組める方',
							'',
							'【管理職としての思考・行動力】',
							'・KPI運用・改善力',
							'・数字を眺めるだけで終わらせず、改善アクションに落とし込める',
							'・メンバーに次の一手を示せる',
							'・感情ではなく構造で人を評価・指導できる',
							'・個人責任ではなく、仕組みで課題を解決できる',
							'・再現性のある組織運営ができる',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'年俸600万〜700万※1',
						),
					),
					array(
						'label' => '昇給',
						'lines' => array(
							'年1回',
						),
					),
					array(
						'label' => '賞与',
						'lines' => array(
							'年1回※2',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費・役職手当',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'フレックスタイム制（実働8時間 / 1日）',
							'（コアタイム：10:00〜15:00）',
							'標準的な勤務時間：9:00〜18:00（休憩1時間）',
							'※残業は月20時間以下',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'年間123日以上（週休2日（土日）・夏季・年末年始）',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'雇用・健康・労災・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（エクステリアプランナー、施工管理技士など）',
						),
					),
					array(
						'label' => '選考プロセス',
						'lines' => array(
							'1.書類選考',
							'2.面接2回（オンライン/対面）',
							'3.適性検査',
							'4.内定',
						),
					),
				),
				'notes' => array(
					'※1:上記給与には45時間分の固定残業代（時間外手当）を含みます。',
					'時間外手当は、時間外労働の有無に関わらず45時間分を支給します。45時間を超える時間外労働分の賃金は、別途支給します。',
					'※2: 賞与は、会社業績と各判定基準により定められた支給率に応じて支給します。',
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
			array(
				'slug'  => 'planner',
				'title' => array( 'エクステリア', 'プランナー' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							'・エクステリア全体のプランニング提案',
							'・デザイン・図面・3Dパース作成',
							'・問い合わせ対応（メール・電話）',
							'・ハウスメーカー／法人案件対応',
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・エクステリアプランニングのご経験（目安2年以上）',
							'',
							'【歓迎経験】',
							'・エクステリアに関する資格（エクステリアプランナーなど）',
							'・エクステリア関連企業での経験',
							'・CADソフトの使用経験（Vectorworks、AutoCADなど）',
							'・建築業界での設計、プランニング経験',
							'・見積もり作成や受発注業務の経験',
							'',
							'【求める人物像】',
							'・若いチームで協調性を大切にできる方',
							'・成長意欲があり、新しいことに挑戦したい方',
							'・お客様との信頼関係を大切にする方',
							'・理想の住まいづくりを楽しみ、喜んで取り組める方',
							'・責任感・向上心を持って仕事に取り組める方',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'月給 33万円 〜 50万円※1',
						),
					),
					array(
						'label' => '昇給',
						'lines' => array(
							'年1回',
						),
					),
					array(
						'label' => '賞与',
						'lines' => array(
							'年1回※2',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費・役職手当・資格手当',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'フレックスタイム制（実働8時間 / 1日）',
							'（コアタイムなし）',
							'標準的な勤務時間：9:00〜18:00（休憩1時間）',
							'※残業は月20時間以下',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'年間123日以上（週休2日（火水）・夏季・年末年始）',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'雇用・健康・労災・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（エクステリアプランナー、施工管理技士など）',
						),
					),
					array(
						'label' => '選考プロセス',
						'lines' => array(
							'1.書類選考',
							'2.面接2回（オンライン/対面）',
							'3.適性検査',
							'4.内定',
						),
					),
				),
				'notes' => array(
					'※1:上記給与には45時間分の固定残業代（時間外手当）を含みます。',
					'時間外手当は、時間外労働の有無に関わらず45時間分を支給します。45時間を超える時間外労働分の賃金は、別途支給します。',
					'※2: 賞与は、会社業績と各判定基準により定められた支給率に応じて支給します。',
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
			array(
				'slug'  => 'office',
				'title' => array( '店舗事務スタッフ' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							'・来客応対（初期対応、お茶だしなど）',
							'・システムへの原価入力・データ管理',
							'・請求書の確認・取りまとめ',
							'・SNS投稿・簡単な画像アップロード',
							'・WEBサイト（施工事例・NEWSページなど）の更新作業',
							'・各支店や取引先とのメール・電話対応',
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・事務職の経験（目安2年以上）',
							'・基本的なPC操作スキル（Word・Excel・メールなど）',
							'',
							'【歓迎経験】',
							'・事務・経理・総務などの実務経験',
							'・SNS更新や簡単な画像編集経験（Canva等）',
							'・CADソフトの使用経験（Vectorworks、AutoCADなど）',
							'・ホームページ更新作業の経験（WordPress等）',
							'',
							'【求める人物像】',
							'・コツコツと正確に仕事を進めることが得意な方',
							'・チームで協力しながら働くことが好きな方',
							'・明るく素直にコミュニケーションが取れる方',
							'・新しいことを学び、成長意欲を持って取り組める方',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'時給1,500円以上',
							'※試用期間は3ヶ月(試用期間中の条件に変更なし)',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'勤務時間：9:00~18:00（休憩1時間）',
							'月～金曜日のシフト制',
							'週3日～柔軟に対応可能',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'年間123日以上（週休2日（火水）・夏季・年末年始）',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'雇用・健康・労災・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（エクステリアプランナー、施工管理技士など）',
						),
					),
					array(
						'label' => '選考プロセス',
						'lines' => array(
							'1.面接1回（対面）',
							'2.内定',
						),
					),
				),
				'notes' => array(
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
			array(
				'slug'  => 'executive',
				'title' => array( '営業責任者', '（執行役員）' ),
				'rows'  => array(
					array(
						'label' => '仕事内容',
						'lines' => array(
							implode(
								"\n",
								array(
									'現在、代表・中村が牽引してきた営業戦略および組織基盤を、次の成長フェーズに向けて再構築する重要な局面にあります。',
									'本ポジションのミッションは、ハウスメーカー各社とのエンタープライズ営業を飛躍的に強化し、全国展開を加速させることです。単なるマネジメントにとどまら',
									'ず、自ら全国を飛び回りながら現場の最前線に立ち、「勝てる営業の型」を構築していただきます。',
									'組織全体にテコ入れを行い、戦略・実行の両面から事業を牽引する「プレイングリーダー（執行役員候補）」を求めています。',
								)
							),
						),
					),
					array(
						'label' => '求める人材',
						'lines' => array(
							'【必須経験】',
							'・法人営業での実績',
							'・日本全国への出張が可能なフットワークの軽さ',
							'・関東圏に居住（または入社時に転居）が可能であること',
							'',
							'【歓迎経験】',
							'・大手ハウスメーカーや、金融業界、ゼネコン、デベロッパーでのタフな営業経験',
							'・営業組織の再建に携わった経験',
							'・エクステリアに関する資格（施工管理技士など）',
							'・CADの使用経験およびプレゼンテーションスキル',
							'',
							'【求める人物像】',
							'・指示を待つのではなく、背景を汲み取って自らアクションを起こせる方',
							'・数字や戦略に強いだけでなく、泥臭い人間関係を構築できる方',
							'・定まりきっていない環境を、自らのテコ入れによって「仕組み」へと変えていくプロセスにやりがいを感じる方',
						),
					),
					array(
						'label' => '勤務地',
						'lines' => array(
							'【東北】青森市、弘前市、八戸市、盛岡市、仙台市',
							'【関東】横浜市',
							'※配属先はご希望により考慮します。',
						),
					),
					array(
						'label' => '給与',
						'lines' => array(
							'年収1,000万円',
							'※能力に応じてSO付与あり',
						),
					),
					array(
						'label' => '昇給',
						'lines' => array(
							'年1回',
						),
					),
					array(
						'label' => '賞与',
						'lines' => array(
							'年1回※2',
						),
					),
					array(
						'label' => '各種手当',
						'lines' => array(
							'交通費・役職手当・資格手当',
						),
					),
					array(
						'label' => '勤務時間',
						'lines' => array(
							'要相談',
						),
					),
					array(
						'label' => '休日休暇',
						'lines' => array(
							'要相談',
						),
					),
					array(
						'label' => '社会保険',
						'lines' => array(
							'健康保険・厚生年金',
						),
					),
					array(
						'label' => '研修制度',
						'lines' => array(
							'研修期間（試用期間）3ヶ月',
							'新人研修・OJT研修など',
							'資格取得支援制度あり（エクステリアプランナー、施工管理技士など）',
						),
					),
					array(
						'label' => '選考プロセス',
						'lines' => array(
							'1.書類選考',
							'2.面接2回（オンライン/対面）',
							'3.適性検査',
							'4.内定',
						),
					),
				),
				'notes' => array(
					'※2: 賞与は、会社業績と各判定基準により定められた支給率に応じて支給します。',
					'その他、採用に関するお問い合わせはrecruit@exone-exterior.jpまでお願いいたします。',
				),
			),
		),
	);
}

/**
 * Benefits（現サイト .rsec--benefit）。
 *
 * value は [文字, 小さく出すか] の並び（現サイトの <small>）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_benefits() {
	return array(
		'en'    => 'Benefits',
		'ja'    => '待遇・福利厚生',
		'image' => array(
			'file' => 'benefits.webp',
			'size' => array( 1448, 1086 ),
			'alt'  => 'EXoneが手がけた外構・エントランスの施工事例',
		),
		'kicker' => 'Work & Life',
		'lead'   => array( '安心して、', '長く働ける。' ),
		'sub'    => '働く人の毎日を支える、待遇と環境を。',
		'cards'  => array(
			array(
				'icon'  => 'calendar-days',
				'label' => '年間休日',
				'value' => array( array( '平均', true ), array( '123', false ), array( '日', true ) ),
				'note'  => '会社カレンダーに基づく休日設定。',
			),
			array(
				'icon'  => 'umbrella-beach',
				'label' => '有給休暇',
				'value' => array( array( '年次有給', false ), array( 'あり', true ) ),
				'note'  => '仕事とプライベートのバランスを大切にできる環境づくりを推進。',
			),
			array(
				'icon'  => 'yen-sign',
				'label' => '賞与・インセンティブ',
				'value' => array( array( '制度', false ), array( 'あり', true ) ),
				'note'  => '職種・役割・業績に応じた評価制度を整備。',
			),
			array(
				'icon'  => 'shield-halved',
				'label' => '保険・手当',
				'value' => array( array( '社保完備', false ), array( '＋通勤手当', true ) ),
				'note'  => '社会保険完備。通勤手当として定期代1ヶ月分を支給。',
			),
		),
	);
}

/**
 * Flow（現サイト .recruit-flow）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_flow() {
	return array(
		'en'    => 'Flow',
		'ja'    => '選考フロー',
		'step'  => 'Step',
		'steps' => array(
			array(
				'num'   => '01',
				'title' => '一次面接',
				'icon'  => 'user',
			),
			array(
				'num'   => '02',
				'title' => '適性検査',
				'icon'  => 'clipboard-check',
			),
			array(
				'num'   => '03',
				'title' => '最終面接',
				'icon'  => 'comments',
			),
			array(
				'num'   => '04',
				'title' => '内定',
				'icon'  => 'square-check',
			),
		),
		'note'  => '※職種によってフローは異なる場合がございます。',
	);
}

/**
 * FAQ（現サイト .rsec--faq）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_faq() {
	return array(
		'en'    => 'FAQ',
		'ja'    => 'よくある質問',
		'items' => array(
			array(
				'q' => '募集している職種を教えてください。',
				'a' => '提案営業職、施工管理職、エクステリアプランナー、店舗事務スタッフ、支店長候補などを募集しています。',
			),
			array(
				'q' => '選考フローを教えてください。',
				'a' => '基本的には、書類選考・面接・適性検査・最終面接を経て内定となります。',
			),
			array(
				'q' => '入社後はどのように仕事を覚えていきますか？',
				'a' => '職種ごとの研修・マニュアル・OJTを通じて、業務の流れを段階的に覚えていきます。',
			),
			array(
				'q' => '休日・有給休暇はありますか？',
				'a' => 'はい。会社カレンダーに基づく休日に加え、年次有給休暇があります。',
			),
			array(
				'q' => '賞与はありますか？',
				'a' => '賞与は、会社業績と各判定基準により定められた支給率に応じて支給します。',
			),
			array(
				'q' => 'どのような人がEXoneに向いていますか？',
				'a' => '新しいことを学ぶ意欲があり、チームで協力しながら仕事を進められる方に向いています。お客様や社内メンバーと誠実に向き合える方を歓迎しています。',
			),
		),
	);
}

/**
 * セクション見出し（英字 + 和文の h2。仕様書 Q3）。
 *
 * @param string $en  英字。
 * @param string $ja  和文。
 * @param string $mod 見出しの種類（'sec' = .rsec-title 系 / 'bg' = .work_title・.flow_title 系）。
 * @param string $id  見出しの id（aria-labelledby 用。空なら付けない）。
 */
function exterior_exone_recruit_heading( $en, $ja, $mod = 'sec', $id = '' ) {
	printf(
		'<h2 class="p-rchead p-rchead--%1$s"%2$s><span class="p-rchead__en">%3$s</span><span class="p-rchead__ja">%4$s</span></h2>',
		esc_attr( $mod ),
		$id ? ' id="' . esc_attr( $id ) . '"' : '',
		esc_html( $en ),
		esc_html( $ja )
	);
}

/**
 * アイコン（images/recruit/icons/<名前>.svg を CSS の mask で白などに塗る空の span）。
 *
 * @param string $name  アイコン名。
 * @param string $class 追加のクラス。
 */
function exterior_exone_recruit_icon( $name, $class = '' ) {
	printf(
		'<span class="p-rcicon p-rcicon--%1$s%2$s" aria-hidden="true"></span>',
		esc_attr( $name ),
		$class ? ' ' . esc_attr( $class ) : ''
	);
}

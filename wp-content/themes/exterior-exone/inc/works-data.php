<?php
/**
 * WORKS（施工事例 1 件ぶん）の共通データとヘルパー。
 *
 * カンプ: 支店 PC 698:1134 ほか（docs/figma-store-page.md §10）/
 *         DX  PC 437:460・SP 439:992-1062（docs/figma-dx-page.md §2.7・§3.8・§3.9）
 *
 * 支店ページと DX ページは同じ事例を出す（設計メモ §2.7「内容は支店 WORKS と
 * 完全一致」）。要望・提案・見積を二重に持たないよう、事例の中身はここだけが持ち、
 * 見出し・リード文・写真・配色はページ側（inc/store-data.php / inc/dx-data.php）が
 * 渡す。表示は template-parts/common/section-works.php。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 事例 1 件ぶんの共通データ（要望・提案・見積・3D パースのラベル・モーダルの文言）。
 *
 * TODO: カンプの 1 事例ぶんの実値。事例ごとの出し分けが必要になったら投稿側へ移す。
 *
 * @return array<string, mixed>
 */
function exterior_exone_works_case() {
	return array(
		// 698:1329 / 436:378。3D パースに重ねるラベル（SP の DX は出さない）。
		'render'   => array(
			'label' => 'ご提案時3Dパース',
		),
		'request'  => array(
			'title' => 'お客様のご要望',
			'lines' => array(
				'・和風の自宅に調和する落ち着いた雰囲気の外構にしたい',
				'・シンプルながらも門まわりにデザイン性を取り入れたい',
				'・予算を350万に抑えたい',
			),
		),
		'proposal' => array(
			'title' => 'EXoneからのご提案',
			'body'  => '建物の雰囲気に合わせ、ツートンカラーの門柱を採用し、和モダンな印象に仕上げました。玄関前には植栽と高さの異なる塗り壁を配置し、立体感とアクセントのあるアプローチをご提案しました。',
		),
		'estimate' => array(
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
					'work'     => '門まわり（その他）', // カンプは PC / SP とも全角括弧
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
				// SP カンプは ¥470,700 だが合計と合う PC の値を正とする（決定事項 2026-09-19）。
				array(
					'work'     => 'その他',
					'material' => '門まわりパッケージ AB46、アートボード門柱、エクスレッズ ウォールライト5型、letter cube',
					'price'    => '¥479,700',
				),
			),
		),
		// 698:1217 / 436:341。全角スペースを含めてカンプどおり。
		'total'    => '合計　¥2,901,080',
		// 439:993 / 439:1029。SP だけの見積モーダル（使用建材の列は出さない）。
		'modal'    => array(
			'button' => '施工内容・内訳を見る',
			'label'  => '施工内容・内訳',
			'close'  => 'CLOSE',
		),
	);
}

/**
 * 写真グリッド 4 枠ぶんのデータを作る。
 *
 * 既存 CPT works の最新（exterior_exone_works_cards()）を先に詰め、足りないぶんは
 * カンプ写真で埋める。同じ画像の投稿が続くとグリッドが同じ写真だらけになるので、
 * 重複した画像は飛ばす。1 件も無いときは空配列を返し、呼び出し側が
 * セクションごと出さない（TOP の NEWS / WORKS と同じ作法）。
 *
 * @param array<int, string> $fallback カンプ写真の URL（解決済み）。
 * @return array<int, array{image_id:int, url:string, src:string, title:string}>
 */
function exterior_exone_works_slots( array $fallback ) {
	$cards = exterior_exone_works_cards();

	if ( ! $cards || ! $fallback ) {
		return array();
	}

	$slots = array();
	$seen  = array();

	foreach ( $cards as $card ) {
		if ( ! $card['image_id'] || in_array( $card['image_id'], $seen, true ) ) {
			continue;
		}

		$seen[]  = $card['image_id'];
		$slots[] = array(
			'image_id' => $card['image_id'],
			'url'      => $card['url'],
			'src'      => '',
			'title'    => $card['title'],
		);

		if ( 4 === count( $slots ) ) {
			break;
		}
	}

	while ( count( $slots ) < 4 ) {
		$slots[] = array(
			'image_id' => 0,
			'url'      => '',
			'src'      => $fallback[ count( $slots ) % count( $fallback ) ],
			'title'    => '',
		);
	}

	return $slots;
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
function exterior_exone_works_lines( array $lines ) {
	return implode( '<br>', array_map( 'esc_html', $lines ) );
}

/**
 * WORKS の写真グリッド 1 枠ぶんを出力する。
 *
 * CPT works の画像があればそれを投稿へのリンク付きで、無ければカンプ写真を出す。
 *
 * @param array{image_id:int, url:string, src:string, title:string} $slot 枠のデータ。
 */
function exterior_exone_works_photo( array $slot ) {
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
			esc_url( $slot['src'] )
		);
	}

	if ( ! $slot['url'] ) {
		echo wp_kses_post( $image );

		return;
	}

	printf(
		'<a class="p-cworks__link" href="%s">%s</a>',
		esc_url( $slot['url'] ),
		wp_kses_post( $image )
	);
}

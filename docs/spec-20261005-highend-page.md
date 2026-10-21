# Spec: ハイエンドページ（HIGH-END DESIGN。`/plans/high-end/`）

- 作成日: 2026-10-05
- 起案: ハイエンドページ（HIGH-END DESIGN。URL `/plans/high-end/`）を新規実装したい。Figma `733:416`（PC `713:31` / SP `733:290` / guide `733:415`）。
- デザイン元: Figma fileKey `msbO5SCyCUBUXQwXLUug4m` / 関連ノード: 親 `733:416`「ハイエンドページ」・PC `713:31`「PC ハイエンドページ」（1920×13761）・SP `733:290`「SPハイエンドページ」（375×7164、フラット構造）・アニメーション注釈 guide `733:415`・gallery 画像候補 `713:210`
- **設計の正**: `docs/figma-highend-page.md`（以下「メモ」。全文言・寸法・色・guide 原文 §5・流用候補 §6・保存済み画像 §8）。本仕様書は文言や寸法を全部は再掲せず、メモの § 番号とノード ID で参照する
- **決定の正**: `docs/spec-20261005-highend-page-decisions.md`（以下「決定」）。**本仕様書と食い違う箇所があれば決定ファイルが優先する**

## 1. 背景・目的

PLANS ページ・TOP・支店ページには「ハイエンドプラン」の紹介があるが、その VIEW MORE はすべて `#` で、こだわりの強い検討者が「ハイエンドで何をしてくれるのか」を深く知る場所が無い。本ページは、夕景から夜景へ移る FV の演出で世界観を見せ、考え方（Design Philosophy）→ 細部（Design Details）→ 2 つのプラン（Design Plans）→ DX 体験 → 実例ギャラリー → 相談、の順に読ませる。対象は「一から自分だけの外構を作り込みたい」高価格帯の検討者。

## 2. スコープ

### やること

- 固定ページ `/plans/high-end/`（親 `plans`・slug `high-end`）と専用テンプレート `page-high-end`（WordPress のテンプレート階層で slug から自動で当たる。`template_include` は使わない）
- 7 セクション: FV（+ 夜景・暗幕の演出）/ Design Philosophy / Design Details / Design Plans / DX Experience / Gallery / CONTACT。PC `713:31`・SP `733:290` の**両方**を忠実再現の対象にする
- guide `733:415` の 5 注釈の動き（FV のスクロール演出 / エンドレスロール / 下からフェードイン / ギャラリーのめくり・自動送り・手動切り替え・進捗下線）
- `inc/highend-data.php`（文言・画像の単一出典）・`css/highend.css`・`js/highend-gallery.js`・`template-parts/highend/` の新設、`images/highend/` の最適化（リサイズ・WebP）
- `js/fv-mask.js` に、**既存ページの挙動を変えないオプトイン**（固定区間の画面数の指定と、動きを減らす設定での無効化）を足す（§3 F-02 の「決定の実装上の読み替え」）
- TOP / PLANS / 支店ページのハイエンド VIEW MORE を `/plans/high-end/` に接続（支店モードの `?store=` 引き継ぎ込み）
- `CLAUDE.md` のフレーム対応表に 1 行追記

### やらないこと（範囲外）

- ギャラリー 02〜05 の本物の英名・コピー・写真（仮データで 5 件そろえる。決定 §4）
- お問い合わせページ（CONTACT ボタンは `#` のまま。決定 §3「ページ・導線」）
- 価格・価格注記の確定（カンプどおりの仮。決定 §5）
- CPT・タクソノミー・カスタムフィールドの追加（管理画面での入力項目は発生しない）
- 画像の最終調達。取り込む画像は **AI 生成プレースホルダを含む差し替え前提の仮画像**
- カンプに無い状態の作り込み（ホバーは既存の作法のみ。ギャラリーのホバー停止はしない。決定 §3「共通」「Gallery」）
- タブレット専用レイアウト（中間幅は PC / SP の比例方式の補間のみ。iPad 縦は `--sp-scale-max` で頭打ちの既存方針）
- 実装しないノード: guide の灰色矩形・矢印、PC `713:86`（カード外の迷子グラデ）、`713:122` の PNG ロゴ（既存 `images/top/logo.svg` を使う）

### 忠実再現の定義（CLAUDE.md と同一文言）

- **一致必須**: セクション構成・配置・配色・タイポグラフィ・余白感
- **求めない**: px 単位の完全一致

### カンプの不備・表記ゆれの扱い（決定どおり）

- Plans カード 2 の英名は **TOP DESIGNER PLAN**（PC `713:110` の「DESIGNER PLAN」は誤り）
- Details の見出しは PC を正（SP `733:330` の読点なし・`733:342`/`733:343` のコピペ残りは PC に合わせる）
- CONTACT の SP 見出しは PC の「あなたの理想を、デザインの始まりに」
- カード 2 の写真下グラデはカード 1 と同じ `#232323` 終端、カード 2 の和名・罫線の 5px ずれは揃える
- 本文中の半角空白（メモ §7 末尾）は入れず、メモ §2・§3 に記載の表示上の改行位置を正とする
- SP のパンフレットは画面内に収め、右ページはビューポート右端で切る

### 動きの受け入れ基準の読み方（Evaluator 向け）

- 主検証幅は **1920×1080** と **375×812**。768×1024 は「破綻しない（はみ出し・重なり崩れ・横スクロールが無い）」のみを見る
- 「動きを減らす設定」は Playwright の `reducedMotion: 'reduce'`、「JS 無効」は `javaScriptEnabled: false` で再現する
- 時間の観測は、トリガ（クリック・スクロール到達）を送った時点からの経過時間。±0.2 秒の幅で判定する
- 「opacity」「transform」は `getComputedStyle` の値、位置・寸法は `getBoundingClientRect()` の値で判定する。寸法の許容は ±3%（または ±4px の大きい方）
- 「`--fv-mask-progress`」は `.l-pin` の `style` に JS が書き込む値（`getComputedStyle(pin).getPropertyValue('--fv-mask-progress')`）
- S1 着手前に、Evaluator は回帰比較用に `/`・`/company/` の 1920×1080 で「スクロール 0 / 540 / 1080 の `--fv-mask-progress`」と、`/`・`/company/`・`/plans/`・`/dx/`・`/store/aomori/` のスクリーンショット（1920 / 375）を記録しておく

## 3. 機能一覧

### F-01: ページの土台（固定ページ・テンプレート・データ・CSS 骨格とトークン・画像最適化・読み込み・body クラス・FV ヘッダー・noscript）

- 概要:
  - WP-CLI で固定ページを作成（親 = 既存 `plans`、slug `high-end`、タイトル「HIGH-END DESIGN」、公開状態）。DB 変更はこの 1 件のみ
  - `page-high-end.php`: `page-company.php` と同じく、FV（`section-fv`）を `.l-pin` で包み、その後ろにセクション配列 `philosophy` → `details` → `plans` → `dx` → `gallery` → `contact` を `get_template_part( 'template-parts/highend/section', <名前>, $args )` で並べる（未実装のセクションは get_template_part が黙って飛ばすので、スプリントごとに積める）
  - **`$args` の渡し方**: ページテンプレート側で各セクションのデータ関数を呼び、`array( 'data' => <データ関数の戻り値> )` を第 3 引数で渡す。テンプレートパーツ側は `$args['data']` が配列でなければ何も出力せずに戻る（データ関数をパーツ内で直接呼ばない）
  - `inc/highend-data.php` を新設し `functions.php` で `require_once`（既存の inc 群と同じ並び）。関数は **`exterior_exone_highend_fv()` / `exterior_exone_highend_philosophy()` / `exterior_exone_highend_details()` / `exterior_exone_highend_plans()` / `exterior_exone_highend_dx()` / `exterior_exone_highend_gallery()` / `exterior_exone_highend_contact()`**、画像ヘルパーは **`exterior_exone_highend_image( $file )`**（`exterior_exone_plans_image()` と同じ作法: `images/highend/webp/` に同名 `.webp` があれば優先、無ければ `images/highend/` の元形式。`exterior_exone_asset_url()` で filemtime 付き）
  - 改行は「行の配列 + 改行の出し分けフラグ（PC のみ / SP のみ / 両方）」でデータに持ち、出力時は既存の `.u-br-pc` / `.u-br-sp` を使う。**エスケープ**: テキストは `esc_html()`、属性は `esc_attr()`、URL は `esc_url()`、改行を含めた組み立て結果を出すときは `wp_kses()` で `br`（`class` 属性のみ）だけを許可する（`template-parts/plans/section-highend.php` 62 行目と同じ作法）
  - `css/highend.css` を新設し、`:root` に §4「トークン」の全トークンを宣言する（幅で変わらない値は素の `:root`、長さはファイル末尾の比例ブロック。PC = 1025px 以上で `--hep-vu: min(1px, 100vw / 1920)`、SP = 1024px 以下で `--hep-su: min(100vw / 375, var(--sp-scale-max))`。`css/plans.css` 冒頭のコメントと同じ方式）。クラス接頭辞は `.p-hep*`、トークン接頭辞は `--hep-*`（既存の `.p-plphe` / `--plp-he-*` とは別物）
  - `functions.php`:
    - `exterior_exone_enqueue_assets()` に `is_page( 'high-end' )` の分岐を追加: `css/highend.css`（依存 `exterior-exone-style`）・`js/fv-mask.js`（既存ハンドル `exterior-exone-fv-mask`）・`js/plans-imglist.js`（既存ハンドル `exterior-exone-plans-imglist`）・`js/highend-gallery.js`（S3 で追加）。いずれも filemtime 版・JS はフッター
    - `exterior_exone_has_fv_header()` に `is_page( 'high-end' )` を追加（PC は既存の半透明ヘッダーを FV に重ねる）
    - `exterior_exone_body_class()` に `is-highend-page` を追加
    - `exterior_exone_plans_noscript_style()` と同型の `exterior_exone_highend_noscript_style()` を `wp_head` に追加（JS 無効時に `[data-reveal]` を最初から表示。`css/highend.css` の `@media (scripting: none)` と併用）
  - 画像最適化: `images/highend/` の写真・透過 PNG を、表示サイズの 2 倍程度（ただし元画像より大きくしない）にリサイズした WebP（品質 76 前後）を `images/highend/webp/` に作る。元形式は残す。SVG はそのまま。Plans の写真は `images/plans/`、CONTACT の背景は `images/store/cta-bg-645-3.jpg` を使うので、`images/highend/` の同一画像（`pc-plan-01-*`・`pc-plan-02-*`・`pc-contact-bg-*`・`pc-logo-*`）は WebP 化不要・ページから参照しない
- Figma ノード: PC `713:31` / SP `733:290`（SP ヘッダー `733:293`）
- ユーザーストーリー: 開発者として ハイエンドページの器を先に作りたい。なぜなら セクションと動きを独立したスプリントで積み上げたいから。
- 受け入れ基準:
  - [x] AC-01-1: `curl -s -o /dev/null -w '%{http_code}' http://exterior-exone-renewal.local/plans/high-end/` が `200`。WP-CLI の固定ページ一覧で slug `high-end` の親が `plans`、状態が公開
  - [x] AC-01-2: `curl` で取得した HTML の `<body>` の class に `is-highend-page` と `has-fv-header` が含まれ、`css/highend.css?ver=`・`js/fv-mask.js?ver=`・`js/plans-imglist.js?ver=` が含まれる。`css/plans.css` は含まれない
  - [x] AC-01-3: HTML の `<head>` に `is-highend-page` を対象にした `<noscript><style>` が 1 つある。`/plans/` の HTML には出ない
  - [x] AC-01-4: 1920×1080 でヘッダーが FV の写真に重なって表示される（ヘッダー直下に黒帯が無く、ヘッダー背後の画素が写真）。375px では既存の 45px の黒いヘッダーが表示される。両幅とも既存の共通フッターが表示される
  - [x] AC-01-5: `/plans/high-end/` の読み込みで CSS・JS・画像・フォントの 404 が 0 件、JS コンソールエラーが 0 件
  - [x] AC-01-6: ページから参照される写真・透過画像はすべて `images/highend/webp/`・`images/plans/webp/`・`images/store/webp/` の WebP か SVG（WebP が無いものだけ元形式）で、1 枚 600KB を超えるものが 0 件
  - [x] AC-01-7: `css/highend.css` の `:root` に §4 の新規トークンがすべて宣言されている（Evaluator は `getComputedStyle(document.documentElement).getPropertyValue('--hep-…')` で 1920 / 375 の値を表と突き合わせる）。テンプレート・`.p-hep*` セレクタに 16 進カラーや `rgba(` の直書きが無い（`grep` で `:root` ブロック外の色直値 0 件）
  - [x] AC-01-8: 375px / 768px / 1920px のいずれでもページが真っ白にならず、`document.documentElement.scrollWidth === clientWidth`（横スクロールなし）

### F-02: FV と夕方 → 夜 → 暗幕 → コピーの演出

- 概要: 夕景の写真の中央に「HIGH-END DESIGN」+「唯一無二の一邸、あなただけの特別を」（PLANS の FV と同じ組版。メモ §2.1 / §3.1）。FV を画面に固定し、**画面 1 つ分のスクロールで夕方 → 夜、もう 1 つ分で黒 50% の暗幕 → コピー（64px 英字 + 本文 5 行（SP 6 行）+ ロゴ）**、その後は通常のスクロールで FV が上へ流れ、Design Philosophy が下から出てくる。上へ戻ると逆再生（決定 §2・§3「FV の演出」）
- Figma ノード: PC FV `713:34`（背景 `713:35`・英字 `713:38`・コピー `713:37`）/ animation img `713:39` / mask animation `713:116`（夜景 `713:117`・暗幕 `713:118`・グラデ `713:119`・英字 `713:121`・本文 `713:120`・ロゴ `713:122`）/ SP `733:292`・`733:302`・`733:300`・`733:303`・`733:304`〜`733:309` / guide `713:213`・`713:218`
- 構造（DOM。クラス名は確定、要素の種類は Generator 判断で可）:
  - `.l-pin.p-hepin`（`data-fv-mask-span="2"` を持つ）
    - 1 つ目の子 `section.p-hepfv`（留まる舞台。sticky、`top: var(--fv-pin-top)`、高さ `var(--fv-pin-height)`）。中の層を下から順に: 夕景 `.p-hepfv__evening` → 夜景 `.p-hepfv__night` → 暗幕 `.p-hepfv__mask`（黒 50%）→ 下端グラデ `.p-hepfv__fade`（PC のみ。SP は出さない）→ FV コピー `.p-hepfv__copy`（`h1` の「HIGH-END DESIGN」+ 和文）→ 暗幕後のコピー `.p-hepfv__statement`（英字は `p`。本文の行・ロゴ `images/top/logo.svg` を `img` で、alt「EXONE」）
    - 2 つ目（最後）の子 `.p-hepfv__runway`（空の走路。`aria-hidden="true"`、高さ = 舞台の高さ × 2）。fv-mask.js の「cover」に当たる
  - Design Philosophy 以降は `.l-pin` の外に置く
- 決定の実装上の読み替え（見た目の結果は決定どおり。Evaluator は結果で判定する）:
  1. **fv-mask.js の進捗の分母**: 現行の fv-mask.js は「cover の上端が画面下端 → 上端」の **1 画面分**で 0 → 1 になる式なので、決定の「固定区間 2 画面分・進捗 0.5 で夜」が作れない。そこで fv-mask.js に**オプトイン**を足す: `.l-pin` に `data-fv-mask-span` があるときだけ、進捗 = 「舞台が留まり始めてからのスクロール量 ÷ 走路の高さ」（0〜1 に丸め）とする。属性が無いとき（TOP・企業情報）の式・挙動は一切変えない
  2. **暗幕の層**: `.l-pin.is-scroll-mask::after`（z-index 1）は sticky の舞台（独自の重ね順を持つ）より上に描かれるため、ここに暗幕を敷くと暗幕後のコピーまで 50% 暗くなる。このページでは `::after` を使わず（ページ CSS で非表示）、暗幕は舞台の中の `.p-hepfv__mask` に持つ。色はページトークン `--hep-mask`（rgba(0,0,0,.5)）で、既存の `--philo-overlay` は使わない
  3. **動きを減らす設定**: fv-mask.js のオプトイン時のみ、`prefers-reduced-motion: reduce` なら `.is-scroll-mask` を付けず購読もしない（TOP・企業情報は従来どおり）
- 進捗 p（`--fv-mask-progress`）と各層の対応（`.is-scroll-mask` のときだけ。CSS 側で算出）:
  - p 0 → 0.5: 夜景の opacity = p ÷ 0.5、FV コピーの opacity = 1 − p ÷ 0.5（夜景と同時に消える）
  - p 0.5 → 0.75: 暗幕とグラデの opacity = (p − 0.5) ÷ 0.25（0 → 1）
  - p 0.75 → 1: 暗幕後のコピーの opacity = (p − 0.75) ÷ 0.25、縦の移動量 = 既存 `--reveal-shift`（32px）× (1 − 同じ比率) で下から上がる
  - いずれも 0〜1 の範囲に収める。イージング無し（スクロール比例。企業情報と同じ）
- `.is-scroll-mask` が無いとき（JS 無効・動きを減らす設定）: 固定せず走路は非表示、舞台は 1 画面分（PC は既存 `--fv-height`、SP は 723 相当から 45 のヘッダーぶんを引いた高さ = PLANS SP FV と同じ考え方）。夜景・暗幕・（PC は）グラデ・暗幕後のコピーを不透明で出し、FV コピーは表示しない（完成状態）
- 写真の切り抜き: PC は cover。SP は夕景・夜景とも横方向を約 39.6% の位置で切り抜く（メモ §3.1 の left −96%。PLANS の `--plp-fv-image-x` 相当の値を `--hep-fv-image-x` として持つ）
- ユーザーストーリー: ハイエンドを検討する施主として ページを開いた瞬間から特別な一邸の世界観に浸りたい。なぜなら 価格帯に見合う提案力があるかを最初の印象で判断したいから。
- 受け入れ基準:
  - [x] AC-02-1: 1920×1080・スクロール 0 で、FV に夕景・「HIGH-END DESIGN」（`h1`、Antonio SemiBold、120px ±3%、#fefefe）・「唯一無二の一邸、あなただけの特別を」（Noto Sans JP Bold 36px ±3%、#f5f5f5）が表示され、2 行とも左右中央。`--fv-mask-progress` が 0、夜景の opacity 0、暗幕の opacity 0
  - [x] AC-02-2: 1920×1080 でスクロール 540 → `--fv-mask-progress` 0.25（±0.02）、夜景の opacity 0.5（±0.05）、FV コピーの opacity 0.5（±0.05）。スクロール 1080 → 進捗 0.5（±0.02）、夜景 1、FV コピー 0、暗幕 0
  - [x] AC-02-3: 1920×1080 でスクロール 1350 → 進捗 0.625（±0.02）、暗幕の opacity 0.5（±0.05）。暗幕の背景色が `rgba(0, 0, 0, 0.5)`。スクロール 1620 → 暗幕 1、暗幕後のコピーの opacity 0
  - [x] AC-02-4: 1920×1080 でスクロール 2160 → 進捗 1、暗幕後のコピーの opacity 1・縦の移動量 0。「HIGH-END DESIGN」64px ±3%、本文 5 行（Medium 24px ±3%、#d9d9d9、行高 60px ±3%）、ロゴ幅 189px ±3% が左右中央。PC の下端グラデ（高さ 443px ±3%、下端が #1a1a1a）が見える
  - [x] AC-02-5: スクロール 0 → 2160 の間、舞台 `.p-hepfv` の `top` が 0 のまま（留まる）。スクロール 2460 で舞台の `top` が −300（±4）になり（通常スクロールで上へ流れる）、その下に Design Philosophy の見出しが入ってくる。舞台の下端と Design Philosophy の地（#1a1a1a）の間に色の段差が無い（PC）
  - [x] AC-02-6: スクロール 2160 → 540 に戻すと、進捗 0.25（±0.02）・夜景 0.5（±0.05）・暗幕 0・FV コピー 0.5（±0.05）に戻る（逆再生）
  - [x] AC-02-7: 375×812 で FV に英字 50px ±3%・和文 Medium 16px ±3% の 2 行（「唯一無二の一邸、」「あなただけの特別を」）が表示され、ヘッダーの直下から舞台が始まる。走路の高さの半分だけスクロールすると進捗 0.5（±0.02）・夜景 1、走路の高さ全部で進捗 1・本文が 6 行（Regular 12px ±3%）・ロゴ幅 99px ±3%。SP では下端グラデが表示されない
  - [x] AC-02-8: `reducedMotion: 'reduce'` で開くと、`.l-pin` に `is-scroll-mask` が付かず、舞台は固定されない（スクロール 300 で舞台の `top` が −300 ±4）。最初から夜景・暗幕・暗幕後のコピーが見え、FV コピーは見えない。スクロールで opacity が変化しない
  - [x] AC-02-9: JS 無効で開くと、AC-02-8 と同じ完成状態が 1 画面分で表示され、その直下に Design Philosophy が続く
  - [x] AC-02-10: 回帰: `/`・`/company/` の 1920×1080 で、スクロール 0 / 540 / 1080 の `--fv-mask-progress` が S1 着手前に記録した値と一致し（±0.02）、`.l-pin.is-scroll-mask::after` の背景色が従来どおり。`/company/` の FV 見出しがスクロールで消える挙動が変わらない
  - [x] AC-02-11: 768×1024 で演出が最後まで進み、文字がはみ出さず横スクロールが無い

### F-03: Design Philosophy（エンドレスロール含む）

- 概要: 見出しブロック（英字小「Design Philosophy」+ 和文「型ではなく、理想からつくる」。以後 5 セクション共通の組み）、コピー 3 行、住宅 CG（透過・影込み）と下端グラデ、「DESIGN GALLERY」（#4d4d4d の沈んだ英字）+ 本文、写真帯。PC は「コピー左・住宅 CG 右」、SP は「CG → コピー（中央）→ DESIGN GALLERY（左）→ 本文 → 写真帯」の縦積み（メモ §2.4 / §3.2）。写真帯は `js/plans-imglist.js` のエンドレスロール
- Figma ノード: PC `713:207`（見出し `713:40`・コピー `713:51`・CG `713:50`・グラデ `713:33`・text box `713:58`・img list `713:80`）/ SP `733:320`・`733:321`・`733:318`・`733:319`・`733:336`・`733:317`・`733:66`・`733:316`・`733:315` / guide `713:221`
- 写真帯: 写真は 3 種（夕景 = FV と同じ / `pc-philosophy-gallery-02` / `pc-philosophy-gallery-03`）。カードは PC 593×334・角丸 10・間隔 40、SP 288×162・角丸 5・間隔 10、影は `--hep-shadow`。列に `data-plp-imglist` を付け、列の親（見える枠）は PC がカンプの左端 x166 から画面右端まで、SP は x25 から画面右端まで（左端で切れる）。列に付く `.is-running` へのアニメーション（`translateX(-var(--plp-imglist-shift))`、時間 `--plp-imglist-duration`、linear、無限）と keyframes は `css/highend.css` に置く（`css/plans.css` 1683〜1693 と同型。keyframes 名は `hep-imglist-flow`）。速さ `--plp-imglist-screen-time: 40s` は plans.css が読まれないので `css/highend.css` の `:root` で宣言する（JS がこの名前を読むため名前は変えない）
- ユーザーストーリー: 施主として EXone のハイエンドの考え方を短く知りたい。なぜなら 型にはめない提案をしてくれるかを確かめたいから。
- 受け入れ基準:
  - [x] AC-03-1: 1920px で見出しブロックが左右中央、英字 SF Pro Medium 32px ±3%・#d9d9d9、和文 Noto Bold 40px ±3%・#fefefe、英字上端 → 和文上端が 55.86px ±3%。375px は英字 14px・#f5f5f5、和文 16px
  - [x] AC-03-2: 1920px でコピー 3 行（Bold 30px ±3%、行高 69px ±3%、左揃え）が左、住宅 CG が右端に接して表示され、CG の下端が背景 #1a1a1a に溶けている（境目に段差が無い）。「DESIGN GALLERY」が 76px・`rgb(77, 77, 77)`
  - [x] AC-03-3: 375px で CG → コピー（中央揃え 12px 3 行）→ 「DESIGN GALLERY」（36px、左寄せ）→ 本文 3 行 → 写真帯の順に縦に並ぶ
  - [x] AC-03-4: 写真帯の列に `is-running` が付き、1 秒後に 1 枚目のカードの x 座標が減っている（右から左へ流れる）。10 秒観測しても隙間（背景色が見えるカード間 40px 以外の空白）が出ない。カードは PC 593×334 ±3%・角丸 10、SP 288×162 ±3%・角丸 5
  - [x] AC-03-5: `reducedMotion: 'reduce'` / JS 無効では列が動かず、複製（`[data-plp-imglist-clone]`）が 0 件。PC は 3 枚目が画面右端で見切れ、SP は 2 枚目が見切れる（カンプの並び）
  - [x] AC-03-6: 375 / 768 / 1920px で横スクロールが無い（写真帯は見える枠の中で切れる）
  - [x] AC-03-7: 回帰: `/plans/` の PACKAGE の img list と `/store/aomori/` の施工イメージ帯が引き続き流れる（`is-running` が付き、1 秒で位置が変わる）

### F-04: Design Details（4 組・テキストのフェードイン）

- 概要: 01 MATERIALS / 02 LIGHTING / 03 GREENERY / 04 FUNCTION の 4 組。PC は写真（800×453・角丸 15）とテキストエリア（744 幅）の左右交互、テキストは写真と上下中央揃え、番号（Antonio 100px）は写真の左上に被せる。SP は「小写真 163×92 + 英名ブロック」の横並び（奇数は写真左・偶数は写真右）→ 本文は全幅で下（メモ §2.5 / §3.3）。各テキストエリアに `data-reveal`（下から）、写真は reveal なし（決定 §3「Design Details」）。03 の写真はメモ §2.5 の拡大切り抜き（窓枠を落とす）を再現する
- Figma ノード: PC `713:208`（見出し `713:43`、写真 `713:124`・`713:133`・`713:128`・`713:137`、番号 `713:126`・`713:135`・`713:130`・`713:139`、text area `713:52`・`713:62`・`713:68`・`713:74`）/ SP `733:322`〜`733:345`・`737:31`・`737:35`・`737:39` / guide `713:224`
- ユーザーストーリー: 施主として 素材・光・緑・機能のどこまで考えてくれるのかを具体的に知りたい。なぜなら 細部の質がハイエンドの価値だと思うから。
- 受け入れ基準:
  - [x] AC-04-1: 1920px で 4 組が「写真左 → 写真右 → 写真左 → 写真右」の順に並び、写真 800×453 ±3%・角丸 15、行の間隔（写真上端どうし）513px ±3%、各テキストエリアの縦中心が隣の写真の縦中心と ±8px で一致
  - [x] AC-04-2: 1920px で番号「01」〜「04」（100px、#fefefe）が各写真の左上に重なり、番号の上端が写真上端より 65px ±3% 上。英名（76px、`rgb(77, 77, 77)`）→ 罫線（1px #d9d9d9、幅 739 ±3%）→ 見出し（Bold 24px、#fefefe）→ 本文（Regular 18px、行高 36px、#d9d9d9）の順
  - [x] AC-04-3: 見出しの文言が 1920 / 375 とも「素材の表情を、重ねる」「光と影まで、デザインする」「緑で、空間に奥行きを」「美しさを、使いやすさへ」
  - [x] AC-04-4: 375px で各組の写真 163×92 ±3%・角丸 5 と英名ブロックが横に並び（01・03 は写真が左、02・04 は写真が右）、本文が写真の下に全幅（左 25px 相当）で入る
  - [x] AC-04-5: 4 つのテキストエリアに `data-reveal` があり、画面に入る前は opacity 0、入ってから 1.5 秒後に opacity 1・transform なし。写真には `data-reveal` が無い
  - [x] AC-04-6: 03 GREENERY の写真に、元画像の窓枠（写真の上端・左端の暗い枠）が写っていない（カンプの切り抜き）
  - [x] AC-04-7: JS 無効 / `reducedMotion: 'reduce'` でテキストエリアが最初から表示されている
  - [x] AC-04-8: 768px で写真とテキストが重ならず、横スクロールが無い

### F-05: Design Plans（プランカード・価格・注記）

- 概要: 見出しブロック + 導入コピー（PC 中央 4 行 / SP 左 2 段落）+ プランカード 2 枚（PC 760×828 横並び・間隔 80 / SP 324×439 縦積み・間隔 20）+ 価格注記（PC のみ）。カードは地 `#232323`・角丸 15（SP 5）・影 `--hep-shadow`、上に写真（影 `--hep-shadow-soft`）と写真下のグラデ（`#232323` へ）、英名・和名・罫線（PC は和名の下 64px / SP は和名の右からカード右端まで）・本文・価格（円記号 SVG + 金額 + 「～」、右寄せ、色 #d9d9d9）（メモ §2.6 / §3.4）。VIEW MORE 等のボタンは無い
- データ: カードの **名前（和名）・英名・写真・価格** は `exterior_exone_plans_highend()['plans']` から `key` で引く（写真は `exterior_exone_plans_image()` で `images/plans/` のもの）。カードの **本文** と導入コピー・注記は `exterior_exone_highend_plans()` に持つ（カンプの文言。決定の読み方は §6 Q1）。円記号は `images/highend/pc-price-yen-713-101.svg`（PC）/ `sp-price-yen-733-359.svg`（SP）を `alt="¥"` で出し分け（`section-highend.php` 90〜96 行と同じ組み方、色だけ #d9d9d9）
- Figma ノード: PC `713:209`（見出し `713:46`・コピー `713:84`・plan list `713:87`・カード `713:88`/`713:102`・注記 `713:85`）/ SP `733:323`・`733:335`・`733:346`・`733:348`〜`733:371`
- ユーザーストーリー: 施主として 2 つのプランの違いと価格感を比べたい。なぜなら 自分に合う関わり方と予算を決めたいから。
- 受け入れ基準:
  - [x] AC-05-1: 1920px でカード 2 枚が横に並び、各 760×828 ±3%・間隔 80 ±3%・地 `rgb(35, 35, 35)`・角丸 15、写真の高さ 424 ±3%。375px では縦積み、各 324×439 ±3%・間隔 20 ±3%・角丸 5
  - [x] AC-05-2: 英名が 1 枚目「DESIGNER PLAN」・2 枚目「TOP DESIGNER PLAN」（1920 / 375 とも）、和名「デザイナープラン」「トップデザイナープラン」、価格の数字「30,000」「100,000」と「～」が表示され、価格の文字色が `rgb(217, 217, 217)`。円記号が画像（alt「¥」）
  - [x] AC-05-3: カード 1・2 の和名の上端の y 座標が一致（±2px）。2 枚とも写真下のグラデの終端色が `#232323`（写真とカード地の境目に明るい帯が出ない）
  - [x] AC-05-4: 1920px でカードの下 80px ±3% に注記 2 行（12px、右揃え、右端がカード 2 の右端と一致 ±4px）が表示される。375px では注記が表示されない（非表示または出力なし）
  - [x] AC-05-5: カードの本文がカンプの文言（メモ §2.6 の表。SP カード 2 は 2 段落）で表示される
  - [x] AC-05-6: 回帰: `/plans/` の HIGH-END の designer plan / top designer plan の写真・名前・価格・本文が変わらない（S1 着手前のスクリーンショットと一致）

### F-06: DX Experience

- 概要: 夕景の白い住宅写真（PC / SP で別アセット）の上部を紺 `#202d43` → 透明の縦グラデで覆い、見出しブロック・本文・iMac/iPhone の透過画像・オレンジのピルボタン「DX体験を見る」を置く静的セクション。PC は「本文左 | 画像右」→ ボタン、SP は「見出し → 画像 → 本文 → ボタン」（メモ §2.7 / §3.5）。ボタンは既存 `.c-btn` の寸法（`--btn-width` / `--btn-height`）に、ページ内のオレンジ塗りの変種（`--color-orange1` 塗り・枠なし。PC 文字 SF Pro Bold 20px #ffffff / SP Medium 12px #fefefe）を `css/highend.css` に足す（`style.css` の `.c-btn` 本体は触らない）。ホバーは既存の `a:hover`（不透明度 0.7）のみ。リンク先は `exterior_exone_store_link( home_url( '/dx/' ) )`
- Figma ノード: PC `713:140`（背景 `713:142`・グラデ `713:143`・見出し `713:144`・本文 `713:149`・画像 `713:150`・ボタン `713:147`）/ SP `733:291`・`733:372`〜`733:379`
- ユーザーストーリー: 施主として 完成イメージを事前に確かめられるかを知りたい。なぜなら 高額な外構で失敗したくないから。
- 受け入れ基準:
  - [x] AC-06-1: 1920px でセクションの高さが 1929 ±3%、上端の画素が紺（#202d43 付近）で下に向かって写真が現れる。本文 6 行（Medium 18px、行高 45px、#d9d9d9）が左、デバイス画像（550×390 ±3%）が右、その下中央にボタン
  - [x] AC-06-2: ボタンの文字「DX体験を見る」、背景 `rgb(210, 131, 47)`、角丸 32、PC 265×64 ±3%・SP 136×33 ±3%。href が `/dx/` で終わり、クリックすると `/dx/` に遷移する
  - [x] AC-06-3: ボタンにマウスを乗せると opacity が 0.7 になり、背景色・文字色は変わらない
  - [x] AC-06-4: 375px でセクションの高さ 747 ±3%、上から 見出し → デバイス画像（247×175 ±3%）→ 本文 4 段落（12px 左揃え）→ ボタン の順
  - [x] AC-06-5: 回帰: `/` と `/plans/` の `.c-btn--white` / `.c-btn--black` の背景色・枠線・ホバーの反転が変わらない

### F-07: Gallery（パンフレットのめくり・自動送り・タブ・クリック・進捗下線）

- 概要: 見出しブロック + 本文（PC 中央 / SP 左）+ パンフレット（左ページ大 + 右ページ見切れ + 綴じ目の影）+ 番号・英名・コピー・進捗下線 + タブ 5 個（PC 横一列 / SP 3 + 2 の 2 段）。5 カテゴリを 5 秒ごとに自動でめくり、タブ・ページ画像のクリック（タップ）で手動で切り替える（メモ §2.8 / §3.6、guide §5 `718:245`、決定 §2・§3「Gallery」）
- データ（`exterior_exone_highend_gallery()`）: 本文の行と、`items` 5 件（番号 / タブの和名 / 英名 / コピー / 写真ファイル / 写真の幅高さ）。順序 = タブの並び = 決定 §3 の表（01 外観デザイン FACADE DESIGN `pc-gallery-01-facade-713-172.jpg` / 02 アプローチ APPROACH DESIGN `gallery-cand-713-200.jpg` / 03 ガーデン GARDEN DESIGN `gallery-cand-713-202.jpg` / 04 ライティング LIGHTING DESIGN `gallery-cand-713-203.jpg` / 05 素材・質感 MATERIAL DESIGN `pc-gallery-02-713-175.jpg`）。02〜05 のコピーは仮文（Generator が 1 行・20 字前後で仮置きし、データに「仮」とコメント）。差し替えはデータの書き換えだけで済むこと
- 構造（DOM。クラス名は確定）:
  - `section.p-hepgal` … 横方向ははみ出しを切る（`overflow-x: clip` 相当。縦はめくり中のページが上下にはみ出しても見えてよい）
  - `.p-hepgal__book[data-hep-gallery]` … パンフレット全体（PC は x276 から画面右端、SP は x25 から画面右端で切る）。奥行きの基準（perspective = `--hep-gal-perspective`）を持つ
    - `.p-hepgal__page--left` … 左ページの枠（PC 1368×770 / SP 314.54×177.02、`overflow: hidden`、影 `--hep-shadow`）。中に **5 件すべての写真**を重ねて置き、現在の 1 枚だけ見せる（`is-current`）。右端に綴じ目の影（幅 PC 99 / SP 22.76、透明 → `--hep-fold-shade`、CSS グラデで描く）
    - `.p-hepgal__page--right` … 右ページの枠（PC 276 幅 / SP 63.46 幅、左ページと同じ高さ、`overflow: hidden`）。中に 5 件すべての写真を**左ページと同じ幅（1368 / 314.54）で左揃え**に重ね、「現在の次」の 1 枚だけ見せる（05 のときは 01）。左端に綴じ目の影（幅 PC 54 / SP 12.42）
    - `.p-hepgal__leaf` … めくれる 1 枚。左ページと同じ位置・寸法、**回転軸は右端（綴じ目）**（transform-origin = 右端の縦中央）、3D を保持。表 `.p-hepgal__leaf-front` と裏 `.p-hepgal__leaf-back`（裏はあらかじめ Y 軸 180° 回しておく）を持ち、両面とも裏向きのときは見せない（backface 非表示）。表には左ページの綴じ目の影、裏には右ページの綴じ目の影を同じく付ける。待機中は非表示。重なり順は左右のページ・綴じ目の影より上
    - `.p-hepgal__num` … 番号（PC 100px・ページ上端の 64px 上 / SP 32px・20.7 上。ページに重なる）。重なり順はめくれる 1 枚より上
    - `button.p-hepgal__hit--next` / `button.p-hepgal__hit--prev` … 透明のクリック領域。next = 左ページの左半分、prev = 左ページの右半分 + 右ページ。`aria-label` は「次の写真へ」「前の写真へ」。カーソルは指
  - `.p-hepgal__caption` … 英名（PC 100px / SP 32px）+ コピー（PC 32px Medium・英名の右 / SP 12px・英名の下）+ 進捗下線 `.p-hepgal__progress`（1px #d9d9d9、PC は x349 から画面右端まで 1571 幅 / SP は x39 から 336 幅。左端を起点に横方向の拡大率 0 → 1 で伸びる）
  - `.p-hepgal__tabs` … `button.p-hepgal__tab` ×5（`type="button"`、`aria-pressed`、`aria-controls` で book を指す）。アクティブは白地 #fefefe + 文字 #1a1a1a Bold、非アクティブは 1px #fefefe の枠 + 文字 #fefefe Medium（メモ §2.8 / §3.6 の寸法）
  - サーバー出力の初期状態は 01（左ページ = 01、右ページ = 02、キャプション = 01、タブ 01 が `aria-pressed="true"`、ほか `false`、進捗下線は全幅）
- めくりの動き（`js/highend-gallery.js`。1 ページに `[data-hep-gallery]` が複数あっても独立に動く書き方）:
  - **次へ（現在 c → 目標 t）**: めくり開始時に、左ページを t に切り替え（めくれる 1 枚の下から現れる）、めくれる 1 枚の表 = c の写真、裏 = t の次の写真を入れて表示し、Y 軸 0° → 180° に回す。回転の向きは「左ページの左端が手前に起き上がり、綴じ目を軸に右へ倒れる」向き（180° で裏面が右ページの位置にちょうど重なり、裏の写真の左端 276px が新しい右ページと同じ見え方になる）。所要 `--hep-gal-flip`（1.2s）、イージング `--hep-gal-flip-ease`。回し終えたら右ページを t の次に切り替え（めくれる 1 枚の裏に隠れているので見た目は変わらない）、めくれる 1 枚を非表示に戻す
  - **前へ（現在 c → 目標 t）**: 次への逆再生。開始時に右ページを t の次に切り替え、めくれる 1 枚の表 = t、裏 = c の次 を入れて 180° の状態で表示し、180° → 0° に回す（右ページ側から綴じ目を軸に左へ倒れてくる）。回し終えたら左ページを t に切り替え（めくれる 1 枚の表に隠れている）、めくれる 1 枚を非表示に戻す
  - 方向: 自動送り・左半分クリックは「次へ」（05 の次は 01）。右半分クリックは「前へ」（01 の前は 05）。タブは目標の並び順が現在より右なら「次へ」、左なら「前へ」（循環は考えず並び順で判定）。現在のタブを押したときは何もしない（タイマーも計り直さない）
  - 文字: めくり開始と同時に番号・英名・コピーを `--hep-gal-text-fade`（0.4s）でフェードアウトし、めくり終わってから t の内容に差し替えて同じ時間でフェードイン。タブの `aria-pressed` はめくり開始時に t へ移す
  - めくり中（文字のフェードインが終わるまで）に来たクリック・タブ操作は無視する
  - めくる前に、めくれる 1 枚に入れる写真の読み込み・デコードが済んでいることを確かめる（5 件の写真はページ内に全部あるので通常は待たない）
  - 終わりの検知は transition / animation の終了イベントを基本とし、来なかったときのために所要時間 + 少しの余裕で必ず後始末する
- 自動送りとタイマー:
  - 1 枚の表示時間は `--hep-gal-interval`（5s。JS は CSS のトークンを読む）。計測は「めくり終わり（初回はギャラリーが画面に入った時点）」から始め、進捗下線を 0 → 1 に線形で伸ばし、1 に達したら「次へ」のめくりを開始し、下線を 0 に戻す。めくり開始の間隔は 1.2 + 5 = 6.2 秒になる
  - 計測は `requestAnimationFrame` の時刻で経過を測る（`setInterval` の積み上げにしない）。手で切り替えたら下線を 0 に戻し、めくり終わりから 5 秒を計り直す
  - ギャラリーが画面外にある間・タブが非表示（`document.hidden`）の間は止め、戻ってきたら 0 から計り直す（§6 Q2）
  - ホバー中も止めない（決定）
- 動きを減らす設定: めくれる 1 枚は使わず、左ページ・右ページの写真とキャプションを `--hep-gal-crossfade`（0.4s）のクロスフェードで切り替える。自動送りはしない。進捗下線は全幅のまま静止。タブ・クリックでの切り替えは可（決定）
- JS 無効: 01 だけを表示（サーバー出力の初期状態のまま。クリック領域・タブは押しても何も起きない）
- SP: 同じめくり・同じタップ操作。パンフレットは 375 幅に収め、右ページは画面右端で切る（`overflow: hidden`。決定）
- Figma ノード: PC `713:164`（背景 `713:165`・見出し `713:166`・本文 `713:169`・pamphlet `713:170`（left `713:171`/`713:172`/`713:173`、right `713:174`/`713:175`/`713:176`、番号 `713:178`）・gallery name `713:195`（`713:199`・`713:197`・下線 `713:196`）・btn list `713:179`〜`713:194`）/ SP `733:380`〜`733:404` / guide `718:245` / 画像候補 `713:210`（`713:200`・`713:202`・`713:203`）
- ユーザーストーリー: 施主として ハイエンドの施工例をパンフレットをめくるように眺めたい。なぜなら カテゴリごとの雰囲気を手軽に比べたいから。
- 受け入れ基準:
  - [x] AC-07-1: 1920px で初期表示が 01（左ページに外観の写真、番号「01」、英名「FACADE DESIGN」、コピー「建築の輪郭を引き立てる、静かな住まい」、タブ「外観デザイン」が `aria-pressed="true"` で白地 `rgb(254, 254, 254)`・文字 `rgb(26, 26, 26)`、ほか 4 個は `false` で透明地・1px の白枠）。左ページ 1368×770 ±3%・左端 x276 ±3%、右ページの右端が画面右端（1920）、右ページに 02 の写真の左端が見える。綴じ目側（左ページ右端・右ページ左端）が暗い
  - [x] AC-07-2: ギャラリーを画面中央までスクロールしてから 2.5 秒後、進捗下線の拡大率が 0.5（±0.1）。5.0 秒後に `.p-hepgal__leaf` が表示されて回転が始まり（computed transform が単位行列でない）、タブ「アプローチ」の `aria-pressed` が `true` になる
  - [x] AC-07-3: AC-07-2 のめくり開始から 0.6 秒時点で、めくれる 1 枚の Y 軸回転がおよそ 90°（transform 行列から求めた角度 60°〜120°）で、番号・英名・コピーの opacity が 0.1 未満。めくり開始から 1.4 秒時点で英名が「APPROACH DESIGN」・番号「02」に替わっており、1.8 秒時点で opacity 1。めくれる 1 枚は非表示に戻り、左ページは 02、右ページは 03 の写真
  - [x] AC-07-4: 05 の表示中に自動送りが来ると 01 に戻る（左ページ 01・右ページ 02・英名「FACADE DESIGN」）。05 表示中の右ページは 01 の写真
  - [x] AC-07-5: 01 表示中に左ページの左 1/4 の位置をクリック → 「次へ」の向き（めくり開始直後の回転角が 0° から増える方向、めくれる 1 枚の表が 01 の写真）で 02 へ。02 表示中に左ページの右 3/4 の位置（または右ページ）をクリック → 「前へ」の向き（開始時の角度が 180° 付近から減る方向）で 01 へ
  - [x] AC-07-6: 01 表示中にタブ「ライティング」を押すと「次へ」の向きで 04 へ、続けて（めくり完了後に）タブ「アプローチ」を押すと「前へ」の向きで 02 へ切り替わり、そのたびに押したタブだけが `aria-pressed="true"`
  - [x] AC-07-7: 自動送りの 3 秒時点でタブ「ガーデン」を押すと、進捗下線が 0 に戻り、次の自動めくりは押してから 6.2 秒（めくり 1.2 秒 + 表示 5 秒。±0.3 秒）後に始まる（押してから 5 秒時点ではめくりが始まらない）
  - [x] AC-07-8: めくり中（開始 0.3 秒後）にタブ「素材・質感」を押しても無視され、めくり完了後の表示は元の目標のまま
  - [x] AC-07-9: ページ読み込み直後にギャラリーまでスクロールせず 10 秒待ち、その後ギャラリーへスクロールすると表示はまだ 01 で、進捗下線は 0 付近から伸び始める
  - [x] AC-07-10: `reducedMotion: 'reduce'` で、ギャラリーを画面に入れて 10 秒待っても 01 のまま、進捗下線は全幅（拡大率 1）で静止。タブ「ガーデン」を押すと `.p-hepgal__leaf` は表示されず（どの要素にも Y 軸回転が掛からない）、押して 0.2 秒後の左ページの 01 と 03 の写真の opacity がどちらも 0 と 1 の間（クロスフェード中）、0.6 秒後に 03 が 1・01 が 0、英名「GARDEN DESIGN」
  - [x] AC-07-11: JS 無効で 01 だけが表示され（左ページで見えている写真が 1 枚、右ページは 02）、タブ「外観デザイン」が `aria-pressed="true"`、進捗下線は全幅
  - [x] AC-07-12: 375px でパンフレットの左ページ 314.5×177 ±3%・左端 x25 ±3%、右ページは画面右端で切れ、`scrollWidth === clientWidth`。タブは 96×29 ±3%・角丸 3 の 3 + 2 の 2 段。左ページ左側をタップすると「次へ」でめくれる
  - [x] AC-07-13: 1920px のめくり中（開始 0.6 秒時点）も `document.documentElement.scrollWidth === clientWidth`（横スクロールが出ない）
  - [x] AC-07-14: クリック領域 2 つが `button` で `aria-label` を持ち、Tab キーでフォーカスでき、Enter で同じ切り替えが起きる。タブ 5 個も Tab キーで順にフォーカスできる
  - [x] AC-07-15: 10 回連続で自動送りさせた後も、左ページで見えている写真が 1 枚・めくれる 1 枚は非表示・コンソールエラー 0 件（後始末漏れが無い）

### F-08: CONTACT

- 概要: 支店 CTA と同じ写真（`images/store/cta-bg-645-3.jpg`、`exterior_exone_store_image()` で WebP 優先）を全面に敷き、左から `--hep-contact-overlay` の暗幕（PC は幅 1270.77 相当・SP は 340 相当の範囲で、0〜85.577% は不透明、その先 100% で透明）。「CONTACT」+ 見出し「あなたの理想を、デザインの始まりに」（PC / SP とも）+ 本文 + オレンジのボタン「ハイエンドプランについて相談する」（PC 597×97・角丸 10・3px 下に `#734413` のオフセット影 / SP 305×40・角丸 5・影なし）。本文は PC 中央 4 行、SP は鍵括弧 3 つを中央 3 行 + 残りを左揃え 1 段落（メモ §2.9 / §3.7）。ボタンの href は `#`（決定）。ホバーは `a:hover` の 0.7 のみ
- Figma ノード: PC `713:151`（写真 `713:153`・暗幕 `713:154`・「CONTACT」`713:156`・text area `713:157`・ボタン `713:160`〜`713:163`）/ SP `733:405`〜`733:413`
- ユーザーストーリー: 施主として ハイエンドプランについて気軽に相談したい。なぜなら 言葉にできていない理想から一緒に考えてほしいから。
- 受け入れ基準:
  - [x] AC-08-1: 1920px でセクション高さ 1104 ±3%、背景写真が表示され、左端付近の画素が写真より暗く、右端付近は写真そのまま。「CONTACT」（64px）・見出し（Bold 40px）・本文 4 行（Medium 18px 行高 45px）・ボタンが左右中央
  - [x] AC-08-2: 1920px のボタンが 597×97 ±3%・背景 `rgb(210, 131, 47)`・角丸 10・文字 Bold 28px #fefefe、ボタンの直下 3px ±1 に `rgb(115, 68, 19)` の帯が見える。375px のボタンは 305×40 ±3%・角丸 5・影なし・文字 Bold 14px #ffffff
  - [x] AC-08-3: 375px の見出しが「あなたの理想を、デザインの始まりに」で、本文は鍵括弧 3 行（中央）+ 1 段落（左揃え）
  - [x] AC-08-4: ボタンの href が `#`、ホバーで opacity 0.7
  - [x] AC-08-5: CONTACT の直下に既存フッターが続き、間に余白や別色の帯が無い
  - [x] AC-08-6: 回帰: `/store/aomori/` の CTA の暗幕・ボタンの見た目が変わらない

### F-09: 導線（TOP / PLANS / 支店からのリンク）と支店モード

- 概要: 既存の 3 か所のハイエンド VIEW MORE（いま `#`）を `/plans/high-end/` に繋ぐ。すべて**出力時に** `exterior_exone_store_link()` を通し、支店モードの `?store=` を引き継ぐ（決定 §3「ページ・導線」）
  - TOP `template-parts/top/section-plans.php` のハイエンドの VIEW MORE（106 行目付近）
  - PLANS `inc/plans-data.php` の `exterior_exone_plans_highend()` の `'more'`（データは `home_url( '/plans/high-end/' )`、`template-parts/plans/section-highend.php` の出力で `exterior_exone_store_link()` を通す）
  - 支店 `inc/store-data.php` の `'highend'` の `'more'`（同上。出力は `template-parts/store/section-plans.php` の 117 行目付近）
  - PACKAGE 側の VIEW MORE は変えない
- 支店モード: `/plans/high-end/?store=<slug>` では既存の仕組みどおり支店用ヘッダー（PC 黒 113・支店名）・支店用フッターになり、ページ内の「DX体験を見る」も `?store=` を引き継ぐ。FV の固定位置・演出は 113 のヘッダーの下でも破綻しない
- Figma ノード: TOP PLANS セクション（既存）/ PLANS HIGH-END `586:569`（btn white `586:402` / SP `586:1300`）/ 支店 PLANS `610:280`
- ユーザーストーリー: 検討者として プラン紹介からハイエンドの詳細へ進みたい。なぜなら もっと詳しく知りたいと思ったときに行き止まりにしたくないから。
- 受け入れ基準:
  - [x] AC-09-1: `curl -s /` の HTML で、ハイエンドの VIEW MORE（`.p-plans__btn` のうち `c-btn--white`）の href が `http://exterior-exone-renewal.local/plans/high-end/`。1920px でクリックすると `/plans/high-end/` に遷移する
  - [x] AC-09-2: `/plans/` の HIGH-END の VIEW MORE の href が `/plans/high-end/` で終わり、クリックで遷移する。PACKAGE の VIEW MORE の href は変わっていない
  - [x] AC-09-3: `/store/aomori/` のハイエンドの VIEW MORE の href が `/plans/high-end/?store=aomori` で終わる。PACKAGE 側は変わっていない
  - [x] AC-09-4: `/plans/high-end/?store=aomori` の HTML の `<html>` に `data-store-mode`、`<body>` に `is-store-mode` が付き、ヘッダーに支店名の要素（`.p-header__store`）がある。1920px でヘッダーの地が黒ベタで高さ 113 ±3%。「DX体験を見る」の href が `/dx/?store=aomori` で終わる
  - [x] AC-09-5: `/plans/high-end/?store=aomori` の 1920×1080 でも F-02 の進捗（スクロール 1080 で 0.5 ±0.02）と完成状態が成立し、FV の英字がヘッダーに隠れない
  - [x] AC-09-6: `/plans/high-end/`（`?store` なし）ではコーポレート用ヘッダー（支店名なし）・コーポレート用フッター

### F-10: ドキュメント追記と全体の回帰確認

- 概要: `CLAUDE.md` のフレーム対応表に「ハイエンド（HIGH-END DESIGN）| PC `713:31` | SP `733:290`。アニメーション注釈は `733:415`（guide）。親 `733:416`」の 1 行を追記。既存ページの回帰とページ全体の品質を確認する
- Figma ノード: `733:416`
- ユーザーストーリー: 運用者として 新ページの追加で既存ページが壊れていないことを確かめたい。なぜなら 公開中のページの品質を落としたくないから。
- 受け入れ基準:
  - [x] AC-10-1: `CLAUDE.md` のフレーム対応表にハイエンドページの行がある（PC `713:31` / SP `733:290` / guide `733:415`）
  - [x] AC-10-2: `/`・`/company/`・`/plans/`・`/dx/`・`/store/aomori/`・`/plans/high-end/` がすべて HTTP 200、1920 / 375 で JS コンソールエラー 0 件・リソースの 404 が 0 件
  - [x] AC-10-3: 上記 6 ページの 375 / 768 / 1920px で `scrollWidth === clientWidth`（横スクロールなし）
  - [ ] AC-10-4: `/`・`/company/`・`/plans/`・`/dx/`・`/store/aomori/` の 1920 / 375 のスクリーンショットが S1 着手前の記録と比べて、VIEW MORE の href 以外に差が無い（FV の暗幕・img list・ボタンの見た目を含む）
  - [x] AC-10-5: `/plans/high-end/` を 1920×1080 で上から下まで通しでスクロールし、FV 演出 → Philosophy → Details（フェードイン）→ Plans → DX → Gallery（自動送り）→ CONTACT → フッターが順に表示され、途中で要素の重なり崩れ・白い帯・見えないまま残る要素が無い。375×812 でも同様
  - [x] AC-10-6: `git status` で変更がテーマ `wp-content/themes/exterior-exone/`・`docs/`・`CLAUDE.md` の中に限られている（DB の固定ページ作成を除く）

## 4. 情報設計

### 表示面 / URL / テンプレート

- `/plans/high-end/` … 固定ページ（親 `plans`・slug `high-end`）→ `page-high-end.php`（テンプレート階層で自動）。`.l-pin`（FV + 走路）→ Design Philosophy → Design Details → Design Plans → DX Experience → Gallery → CONTACT → 既存フッター
- `/plans/high-end/?store=<slug>` … 同じページの支店モード（既存の仕組み）
- 新規テンプレートパーツ: `template-parts/highend/section-fv.php` / `section-philosophy.php` / `section-details.php` / `section-plans.php` / `section-dx.php` / `section-gallery.php` / `section-contact.php`（各パーツは `$args['data']` を受ける）
- 変更する既存ファイル: `functions.php`（require・enqueue・has-fv-header・body クラス・noscript）/ `js/fv-mask.js`（オプトインのみ）/ `template-parts/top/section-plans.php` / `inc/plans-data.php` + `template-parts/plans/section-highend.php` / `inc/store-data.php` + `template-parts/store/section-plans.php` / `CLAUDE.md`

### データ（CPT / タクソノミー / カスタムフィールド）

- 追加なし。文言・画像は `inc/highend-data.php` の配列（§3 F-01 の関数名）。Plans カードの名前・英名・写真・価格は `exterior_exone_plans_highend()` を流用

### トークン（`css/highend.css` の `:root`。接頭辞 `--hep-`）

幅で変わらない値（素の `:root`）:

| トークン | 値 | 用途・出典 |
|---|---|---|
| `--hep-color-bg` | `var(--color-black2)`（#1a1a1a） | ページ地・Gallery 地（`713:32`・`713:165`） |
| `--hep-color-bg-0` | rgba(26,26,26,0) | 地へ溶かすグラデの始点（`713:119`・`713:33`・`733:319`） |
| `--hep-color-card` | #232323 | プランカードの地（`713:89`・`733:348`） |
| `--hep-color-card-0` | rgba(35,35,35,0) | カード写真下グラデの始点（`713:91`） |
| `--hep-color-navy` | #202d43 | DX の紺グラデ（`713:143`・`733:373`） |
| `--hep-color-navy-0` | rgba(32,45,67,0) | 同・終端 |
| `--hep-color-white1` | #ffffff | PC「DX体験を見る」・SP CONTACT ボタンの文字 |
| `--hep-color-eng-dim` | `var(--color-black1)`（#4d4d4d） | 沈んだ英字（DESIGN GALLERY・MATERIALS 等） |
| `--hep-color-btn` | `var(--color-orange1)`（#d2832f） | オレンジのボタン 2 種 |
| `--hep-color-btn-shadow` | #734413 | CONTACT ボタンのオフセット影（`713:161`） |
| `--hep-mask` | rgba(0,0,0,.5) | FV の暗幕（`713:118`・`733:305`） |
| `--hep-fold-shade` | rgba(0,0,0,.5) | 綴じ目の影の終端（`713:173`・`713:176`） |
| `--hep-contact-overlay` | rgba(13,0,0,.5) | CONTACT の左暗幕（`713:154`・`733:406`） |
| `--hep-contact-overlay-stop` | 85.577% | 同・不透明区間の終わり |
| `--hep-shadow-color` | rgba(0,0,0,.8) | 写真・カードの影の色 |
| `--hep-shadow-color-soft` | rgba(0,0,0,.12) | カード内の写真の影の色 |
| `--hep-fv-image-x` | 39.6% | SP の FV 写真の横位置（`733:292` left −96%） |
| `--hep-fv-night-end` | 0.5 | 進捗のしきい値: 夜景の完了 |
| `--hep-fv-mask-end` | 0.75 | 進捗のしきい値: 暗幕の完了（以降コピー） |
| `--hep-display-lh` | 1.29 | Antonio の lh normal の実測（155/120・83/64） |
| `--hep-gal-interval` | 5s | 自動送りの表示時間（決定） |
| `--hep-gal-flip` | 1.2s | めくりの所要時間（決定） |
| `--hep-gal-flip-ease` | cubic-bezier(0.65, 0, 0.35, 1) | めくりのイージング（easeInOutCubic。§6 Q3） |
| `--hep-gal-text-fade` | 0.4s | めくり前後の文字のフェード（§6 Q3） |
| `--hep-gal-crossfade` | 0.4s | 動きを減らす設定のクロスフェード（§6 Q3） |
| `--plp-imglist-screen-time` | 40s | 写真帯の速さ（`js/plans-imglist.js` が読む名前。PLANS・支店と同値） |

長さ（比例ブロック。PC は `calc(<1920 値> * var(--hep-vu))`、SP は `calc(<375 値> * var(--hep-su))`）:

| トークン | 1920 | 375 | 出典 |
|---|---|---|---|
| `--hep-sec-gap`（セクション間・見出し上の余白） | 160 | 80 | メモ §0 |
| `--hep-sec-eng`（英字小見出し） | 32 | 14 | `713:42` / `733:320` |
| `--hep-sec-heading`（和文見出し） | 40 | 16 | `713:41` / `733:321` |
| `--hep-sec-heading-gap`（英字上端 → 和文上端） | 55.86 | 24.23 | 同上 |
| `--hep-fv-title` / `--hep-fv-title-ls` | 120 / −3 | 50 / −3 | `713:38` / `733:302` |
| `--hep-fv-lead` / `--hep-fv-lead-ls` | 36 / 3 | 16 / 1 | `713:37` / `733:300` |
| `--hep-st-title` / `--hep-st-title-ls`（暗幕後の英字） | 64 / −3 | 36 / −2 | `713:121` / `733:308` |
| `--hep-st-body` / `--hep-st-body-lh`（暗幕後の本文） | 24 / 60 | 12 / 24 | `713:120` / `733:306` |
| `--hep-st-logo-w` | 189 | 99.43 | `713:122` / `733:309` |
| `--hep-fv-fade-h`（暗幕下端グラデ） | 443 | 0 | `713:119` |
| `--hep-philo-copy` / 行高 | 30 / 69 | 12 / 24 | `713:51` / `733:336` |
| `--hep-philo-house-w` / `-h` | 1296 / 670 | 323 / 167 | `713:50` / `733:318` |
| `--hep-eng-dim-size`（DESIGN GALLERY） | 76 | 36 | `713:60` / `733:317` |
| `--hep-body` / 行高（本文） | 18 / 36 | 12 / 19.2 | `713:61` / `733:66` |
| `--hep-imglist-w` / `-h` / `-gap` / `-radius` | 593 / 334 / 40 / 10 | 288 / 162 / 10 / 5 | `713:80` / `733:316` |
| `--hep-shadow`（影） | 0 12 32 `--hep-shadow-color` | 同左（比例しない） | メモ §1 |
| `--hep-dt-photo-w` / `-h` / `-radius` | 800 / 453 / 15 | 163 / 92 / 5 | `713:124` / `733:324` |
| `--hep-dt-pitch`（行ピッチ） | 513 | 217 | メモ §2.5 / §3.3 |
| `--hep-dt-num` / 写真上端からのずれ | 100 / −65 | 32 / −21 | `713:126` / `733:333` |
| `--hep-dt-eng` | 76 | 24 | `713:55` / `733:325` |
| `--hep-dt-rule-w` | 739 | 150 | `713:57` / `733:327` |
| `--hep-dt-heading` | 24 | 12 | `713:53` / `733:329` |
| `--hep-dt-text-w` | 744 | 320 | `713:52` / `733:331` |
| `--hep-plan-w` / `-h` / `-gap` / `-radius` | 760 / 828 / 80 / 15 | 324 / 439 / 20 / 5 | `713:88` / `733:348` |
| `--hep-plan-photo-h` / `-fade-h` | 424 / 109 | 181 / 47 | `713:90`・`713:91` / `733:352`・`733:353` |
| `--hep-plan-eng` / `-jp` | 76 / 20 | 36 / 12 | `713:96`・`713:94` / `733:354`・`733:351` |
| `--hep-plan-price` / `-from` / 円記号 | 48 / 20 / 25×28 | 24 / 12 / 11×12 | `713:100`・`713:99`・`713:101` / `733:358`・`733:357`・`733:359` |
| `--hep-plan-inset`（カード内左右余白） | 44 | 20 | メモ §2.6 / §3.4 |
| `--hep-plan-note`（注記） | 12 | —（非表示） | `713:85` |
| `--hep-dx-h` / `--hep-dx-navy-h` | 1929 / 1145 | 747 / 553 | `713:140`・`713:143` / `733:291`・`733:373` |
| `--hep-dx-body` / 行高 | 18 / 45 | 12 / 19.2 | `713:149` / `733:379` |
| `--hep-dx-devices-w` / `-h` | 550 / 390 | 247 / 175 | `713:150` / `733:376` |
| `--hep-gal-h` | 1886 | 736 | `713:164` / `733:380` |
| `--hep-gal-page-x`（左ページの左端） | 276 | 25 | `713:171` / `733:383` |
| `--hep-gal-page-w` / `-h` | 1368 / 770 | 314.54 / 177.02 | `713:172` / `733:383` |
| `--hep-gal-right-w`（右ページ） | 276 | 63.46 | `713:174` / `733:386` |
| `--hep-gal-fold-left` / `-right` | 99 / 54 | 22.76 / 12.42 | `713:173`・`713:176` / `733:384`・`733:387` |
| `--hep-gal-num` / ページ上端からのずれ | 100 / −64 | 32 / −20.7 | `713:178` / `733:388` |
| `--hep-gal-eng` / `--hep-gal-copy` | 100 / 32 | 32 / 12 | `713:199`・`713:197` / `733:392`・`733:393` |
| `--hep-gal-tab-w` / `-h` / `-radius` / `-gap` / 文字 | 240 / 69 / 5 / 11 / 20 | 96 / 29 / 3 / 5 / 10 | `713:180` 他 / `733:395` 他 |
| `--hep-gal-perspective` | 2800 | 640 | 既定（§6 Q3） |
| `--hep-cta-h` / `--hep-cta-overlay-w` | 1104 / 1270.77 | 487 / 340 | `713:151`・`713:154` / `733:405`・`733:406` |
| `--hep-cta-title` | 64 | 32 | `713:156` / `733:412` |
| `--hep-cta-btn-w` / `-h` / `-radius` / `-shadow-y` / 文字 | 597 / 97 / 10 / 3 / 28 | 305 / 40 / 5 / 0 / 14 | `713:160`〜`713:163` / `733:408`〜`733:410` |

- 既存トークンで足りるもの（再定義しない）: `--color-white2`（#fefefe）/ `--color-white3`（#f5f5f5）/ `--color-gray2`（#d9d9d9）/ `--color-black1` / `--color-black2` / `--color-orange1` / `--font-display`（Antonio）/ `--font-base`（Noto Sans JP）/ `--font-ui`（SF Pro）/ `--reveal-shift` / `--fv-pin-top` / `--fv-pin-height` / `--fv-height` / `--btn-width` / `--btn-height` / `--radius-pill` / `--transition-base`
- 字間（ls）は PC / SP の値をメモ §2・§3 から同じ比例ブロックで持つ（例: 和文見出し 2 / 1、本文 1 / 1、価格 −2 / −1）

### 追加アセット

- JS: `js/highend-gallery.js`（ギャラリーのめくり・自動送り・タブ・クリック・進捗下線・reduced-motion のクロスフェード）。`js/fv-mask.js` はオプトインの追加のみ。`js/plans-imglist.js`・`js/scroll-reveal.js` は変更せず読み込むだけ
- 画像: `images/highend/webp/`（新設。写真と透過 PNG の WebP。差し替え前提の仮画像）。流用: `images/plans/` のプラン写真 2 枚・`images/store/cta-bg-645-3.jpg`・`images/top/logo.svg`

## 5. スプリント計画

### S1
- 含む機能: F-01（土台）・F-02（FV と演出）・F-03（Design Philosophy）
- 完了条件: F-01・F-02・F-03 の AC すべて合格
- 前提: なし。着手前に Evaluator が回帰比較用の記録（§2「動きの受け入れ基準の読み方」末尾）を取る。WP-CLI は親フォルダ `CLAUDE.md` の Local 同梱バイナリを使う

### S2
- 含む機能: F-04（Design Details）・F-05（Design Plans）・F-06（DX Experience）
- 完了条件: F-04・F-05・F-06 の AC すべて合格
- 前提: S1（ページ・CSS 骨格・トークン・データファイル）。F-05 は §6 Q1 の回答を反映してから着手（未回答なら本文はカンプの文言で進め、差し替えはデータ 2 か所）

### S3
- 含む機能: F-07（Gallery）
- 完了条件: F-07 の AC すべて合格
- 前提: S1。S2 とは独立だが、ページ通しの確認のため S2 の後に行う

### S4
- 含む機能: F-08（CONTACT）・F-09（導線と支店モード）・F-10（CLAUDE.md 追記・全体回帰）
- 完了条件: F-08・F-09・F-10 の AC すべて合格
- 前提: S1〜S3

## 6. オープン論点（Planner が判断できなかったこと）

- **Q1（要確認・S2 前）Plans カードの本文**: 決定は「文言・価格・写真は PLANS の HIGH-END プランデータと同じ（`exterior_exone_plans_highend()` を流用）」だが、PLANS データの本文は 2 カードとも同文の仮文（「細かなディテールまで設計する…」2 行）で、ハイエンドページのカンプの本文（メモ §2.6。カードごとに 3 段落の別文）と異なる。本仕様書は「名前・英名・写真・価格は PLANS データ、**本文はカンプの文言**（`exterior_exone_highend_plans()` に持つ）」で書いた。推奨はこのまま（カンプが正、PLANS 側は仮文のため）。PLANS の本文に揃えるなら AC-05-5 を差し替える
- **Q2（既定・変えたい場合のみ確認）ギャラリーの画面外の扱い**: 決定に無いため、画面外とブラウザのタブ非表示の間は自動送りを止め、戻ったら 0 から計り直す、とした（画面に入る前にめくり終わっていて 01 を見逃すのを防ぐ。AC-07-9）
- **Q3（既定・変えたい場合のみ確認）動きの細部**: 決定で時間が決まっていない値を既定とした — FV の区切り（夜景 0〜0.5 / 暗幕 0.5〜0.75 / コピー 0.75〜1）、めくりのイージング easeInOutCubic、文字のフェード 0.4 秒、reduced-motion のクロスフェード 0.4 秒、奥行き（perspective）PC 2800 / SP 640 相当、「5 秒ごと」を「めくり終わりから次のめくり開始までの表示時間 5 秒」と解釈（めくり開始の間隔は 6.2 秒。「めくり開始の間隔を 5 秒」と読む場合は表示時間 3.8 秒になる）。いずれもトークン 1 か所の変更で調整できる
- **Q4（既定）写真帯の見える枠**: カンプの初期配置（PC x166 / SP x25 から始まる）を保つため、流れる写真は左端 x166（SP x25）で切れる。画面左端まで流したい場合は、ループの継ぎ目が出ない構成に変える必要があるので確認する
- **Q5（既定）ページタイトル**: 固定ページのタイトルは「HIGH-END DESIGN」（`<title>` に出る）。和文にしたい場合は管理画面で変更するだけ
- 公開前チェックリストは決定 §5 のとおり（02〜05 の本物の差し替え・価格と注記・CONTACT の遷移先・ホバー中停止の要否・実機確認・本番での固定ページ作成）

# 支店ページ（新カンプでの作り直し）決定事項（2026-10-01）

ユーザーと決めた前提。**仕様書 `docs/spec-20261001-store-page-v2.md` のオープン論点より優先する。** planner / generator / evaluator はここに従うこと。

- 新カンプ: 親 `670:240`「支店ページ」= PC `612:99`（1920×27672。FV + section 1〜14）/ SP `670:239`（375 幅 × 15558）/ アニメーション注釈 guide `617:57`
- 設計メモ: `docs/figma-store-page-v2.md`（実測値・guide 原文 §7・**旧カンプ / 既存実装との差分表 §8**・カンプ不備 §9・要確認 §10 の出典）
- 旧カンプ（`534:628`）での実装: `page-store.php` + `template-parts/store/section-*.php` + `inc/store-data.php`（7 拠点）+ `css/store.css`（`--st-*`）+ `js/store-*.js`。旧の設計メモ `docs/figma-store-page.md`、旧の決定事項 `docs/spec-20260911-store-page-decisions.md`
- Figma から取得した画像: `/private/tmp/claude-501/-Users-exone-se-Local-Sites-exterior-exone-renewal-app-public/1a23e357-7a0f-4937-9ce5-db92ebf0e194/scratchpad/figma-store-v2/`（144 ファイル。`assets-all.txt` が一覧。既存 `images/store/` と見た目が同じ写真の対応も載っている）。既存 SP の見た目のスクショは同フォルダ `existing-sp/`

## A. ユーザーが決めたこと（2026-10-01 回答）

| 論点（設計メモ §10 の番号） | 決定 |
|---|---|
| FV の背景（1） | **背景は 1 枚に固定**（カンプどおり）。丸いサムネイル 5 個・進捗リング・9 秒ごとの自動切り替えはやめる（`js/store-fv.js` は不要になる） |
| Quality の保証注記（7） | **カンプどおり消す** |
| AREA の店内写真「アーチ状にゆっくりエンドレスロール」（8） | PC は写真がアーチ（弧）に沿って**右から左へ**止まらず流れる。**1 巡 約 60 秒**（PLANS の帯の 40 秒より遅い）。ホバーでも止めない。**SP も流す**（SP はカンプどおりまっすぐな列で、同じく右から左へ。体感速度は PC と揃える） |
| 支店ごとに変わる部分の素材（9・10） | **全支店に青森の内容を仮置き**。AREA の店内写真・外観写真、Local Expertise の見出し・本文・写真を、`inc/store-data.php` で支店ごとに差し替えられる形にし、素材が届くまでは 7 拠点とも青森の写真・文章を出す（東京も同じ）。公開前に差し替え |

## B. 既定方針で実装側が決めたこと（ユーザー未確認。完了時にまとめて報告する）

| 論点 | 決定 | 根拠 |
|---|---|---|
| 進め方 | 既存の支店ページを**その場で改修**する（URL `/store/<slug>/`・固定ページ・7 拠点データの持ち方・クラス `.p-s*`・トークン `--st-*` は維持）。設計メモ §8 の差分表が作業の起点 | 作り直しでも構造は同じ |
| 旧の決定事項の扱い | `docs/spec-20260911-store-page-decisions.md` のうち、新カンプと矛盾しないものは引き続き有効（REVIEWS の綴り・CAR SPACE・「お引き渡し」・WORKS 本文 18px 統一 など）。矛盾するものは**新カンプと本ファイルが優先** | — |
| DX セクション（2・3） | guide「TOPと同じ内容、アニメーション」→ **TOP の DX（`template-parts/top/section-dx.php` + `js/dx-slider.js`。固定してスクロールで 01→03）を支店でも使う**。03 のタグは TOP と同じ「施主共有システム」（旧決定 Q3 を上書き）。支店専用の `p-sdx` + `js/store-dx.js` はやめる。SP も TOP の SP と同じ。TOP 側の見た目・動きは変えない | guide |
| WORKS（4） | guide「DX下部と同じ（上部テキストのみ違いあり）」→ `template-parts/common/section-works.php` を **DX ページと同じモード**（右列を押すとメイン写真が切り替わる・3D・カンプ写真固定）で使う。違うのは見出し 3 点の文言だけ（支店用）。配色・寸法は DX ページの実装に合わせる。旧決定の「CPT works の最新写真」は上書き。SP の見積モーダルは既存（`js/works-estimate-modal.js`） | guide |
| 施工イメージ帯・PACKAGE 下の住宅パース帯 | guide「エンドレスで自動スクロール(プランページと同じ)」「上部と同じエンドレスロール」→ PLANS の img list と同じ動き・同じ速さ（画面幅 1 つ分を 40 秒・右から左・止めない）。`js/plans-imglist.js` を支店でも使えるようにする（PLANS の動きは変えない） | guide |
| 選ばれる理由の浮遊 | guide「浮遊アニメーション(他と同じ)」→ TOP 理念の円環図（`js/philosophy-diagram.js` の `p-diagram-float`）と同じ動き・同じ周期 | guide |
| 下からフェードイン | `js/scroll-reveal.js` の `[data-reveal]` の既定（32px・0.9 秒）。SERVICE はメインカード 2 枚と、カテゴリの**各行**。REVIEWS は**カード 3 枚だけ**（見出しは動かさない）。遅延は付けない | guide |
| 見える安心の線画（11・12） | guide「SVGアニメーション（薄め）」→ `js/line-draw.js` で線を描く。濃さはカンプの不透明度 .3。SVG は TOP 理念の `images/top/line_animation.svg` を使う（同じ絵） | guide |
| FAQ | guide「プルダウン」= 既存のアコーディオン。開いた状態の見た目を新カンプ（PC `641:1462` / SP `666:32`）に合わせる。回答文は仮のまま | guide |
| 見出しの句点（6） | カンプどおり削除（不安提示は「？」を追加） | カンプ |
| Quality の SP の暗幕（5） | SP カンプの 30%（旧決定の 55% を上書き） | SP カンプができたため |
| FV 左の暗幕（17） | カンプどおり入れる | カンプ |
| 白パネルの影（18） | SERVICE の白パネル・WORKS の見積表パネルは、数値が取れていないので**影を付けない**（スクショで薄く見える件は evaluator がカンプと見比べて報告） | 実測値を正 |
| SP | **新しい SP カンプどおりに組み直す**（設計メモ §5・§8.4）。SP だけ文言・構成が違う箇所（選ばれる理由の構成と英字、SERVICE の写真名の短縮と色、COLUMN・DX の VIEW MORE 無し、COLUMN の背景色）は幅ごとにカンプどおり。ただし DX は TOP の SP を正とする | PLANS・DX と同じ作法 |
| SP の FAQ 質問 2・8 が PC と違う（13） | **PC の文言を正**とする（同じ質問が幅で変わるのは不自然なため） | DX の前例（コピペ差は PC を正） |
| SP の横スクロール（FV 下の写真・REVIEWS・COLUMN） | 指で横にスクロールする列（自動では流さない） | guide に注釈なし |
| アーチ状ロールの細部 | 弧の形は PC カンプの合成画像 `612:661` の並び（写真が弧に沿って傾く）を再現。写真は青森の 4 枚（`641:1464`）を複製して途切れなく回す。カンプに無い動きの細部（傾きの付け方など）は実装後にスクリーンショットでユーザーに確認 | カンプ + 決定 A |
| guide に注釈が無い箇所 | 動きを足さない。既存の動きで新カンプに無いもの（FV の切り替え）は外す | — |
| 動きを減らす設定 / JS 無効 | ロール・浮遊・線の描画は止めて最終状態で表示、フェードインは最初から表示。JS 無効でも内容が読める | 既存ページの作法 |
| カンプの不備（設計メモ §9） | PC: 旧決定どおり（REVIEWS / CAR SPACE / お引き渡し / FLOW 03 は同文のまま / WORKS 本文 18px）。SP: Quality の英字「FLOW」・AREA の帯見出し（SERVICE の文言）は **PC を正**、「VIsible Design」→「Visible Design」、上下反転した雪道写真は正位置、見積モーダルの「その他」は ¥479,700、「外 構」の空白は除去。1〜10px のずれ・不揃いは揃える。行頭行末の半角空白は除去 | 前例 |
| Figma が作業中だった件（19） | 読み取り時点（2026-10-01）の内容を正として進める。その後カンプが変わったら差分で対応 | — |
| 対象外 | `/store/` 親ページ（一覧）、他ページの見た目、CTA の URL・電話番号の実値（`#` のまま）、FAQ 回答・REVIEWS・他支店の素材（仮のまま） | 旧実装と同じ |
| 中間幅 | ブレークポイントは既存どおり 1024 / 1025px。PC は 1920 比例、SP は 375 比例（`--sp-scale-max` で頭打ち） | タブレット方針 |

## B-2. 仕様書のオープン論点 Q1〜Q10 への決定（2026-10-01・実装側で決定。ユーザー未確認）

**仕様書 §6 の各 Q に書かれている「確定までは…」の進め方を、そのまま決定として採用する。** 要約:
Q1 SP の FV 下端の写真は 4 枚を横スクロール／Q2 溶かしグラデの終端は背景色（#f3f3f4）／Q3 実績はリング #f5f5f5・数字 #fefefe に両幅で統一／Q4 選ばれる理由の浮遊は、画面に入ったら出現演出なしで始める／Q5 AREA のカード 4 枚も支店ごとに差し替えられるデータにし、青森を仮置き／Q6 PACKAGE 帯は PC の 5 枚を両幅で流す／Q7 SP の Quality の英字は SP カンプの書体で文言だけ PC に（収まらなければ下げて報告）／Q8 DX の VIEW MORE は TOP と同じ `/dx/`／Q9 SP の選ばれる理由は暗幕を足さず、evaluator が可読性を報告／Q10 アーチ状ロールは合成画像の間隔を再現し、弧を埋める分だけ複製（スクリーンショットでユーザーに確認）

## C. 運用メモ

- generator は **1 スプリントごとに新しく起こす**。引き継ぎはこのファイルの D 節に追記する
- evaluator は Playwright MCP のブラウザを共有するので**同時に 2 つ走らせない**（generator の CDP 確認とは並行可）。generator は確認に使った headless Chrome を必ず終了させる（残すとメモリを食う。kill が拒否されたら報告に PID を書く）
- 検証は青森（`/store/aomori/`）を主にし、他 6 拠点は崩れとデータの出し分けを確認
- 「忠実再現」の基準は `CLAUDE.md` と `.claude/agents/evaluator.md` のとおり
- 確認が要る点が新しく出たら、勝手に決めず「要確認」として報告に書く

## D. スプリントの引き継ぎ

（各スプリントの generator が追記する）

### S1 引き継ぎ（2026-10-01）

- **退行比較の基準（S1 開始前に取得）**: scratchpad `sv2-s1/baseline/`（scratchpad = `/private/tmp/claude-501/-Users-exone-se-Local-Sites-exterior-exone-renewal-app-public/1a23e357-7a0f-4937-9ce5-db92ebf0e194/scratchpad/`）
  - `top|dx|plans|company-{1920,375}-NN.jpg` … 動きを減らす設定で上から 1 画面ずつ撮ったタイル（全体 1 枚は headless で固まるため）
  - `measure-2.json` … PLANS img list（1920: 1 巡 1942.438px・40.467s・画面幅 1 つ分 40.0 秒 / 375: 873.609px・93.185s・40.0 秒 / reduce は静止・複製なし）
  - `measure-34.json` … TOP DX（ピン区間 0〜0.9 の位置ごとの active 添字）・DX ページ WORKS（右列クリックごとのメイン画像）
  - 計測スクリプト `sv2-s1/measure-ref.mjs <出力先> <1234>`（1 = タイル、2 = PLANS、3 = TOP DX、4 = DX WORKS）。S1 後の値は `sv2-s1/after/`（タイルは TOP / DX の動画フレーム以外一致、PLANS・TOP DX・DX WORKS は同値）
- **トークンの変更点（css/store.css :root）**: 新設 `--st-shadow`（0 12 32 .12。統一の影）/ `--st-shadow-photo`（SERVICE カテゴリ写真の例外）/ `--st-on-dark`（= white 2 #fefefe。暗い背景の文字）/ `--st-line`（= gray 2 #d9d9d9。罫線）/ FV 用 `--st-fv-overlay-left`・`--st-fv-shade-left-size`・`--st-fv-image-position`・`--st-fv-lead-color`・`--st-fv-lead-ls`・`--st-fv-badge-color`・`--st-fv-fade-from`・`--st-fv-band-overlap`・`--st-fv-band-pad`・`--st-fv-photo-radius`・`--st-fv-band-item-width` / 帯用 `--st-strip-screen-time`（40s）・`--st-strip-gap`・`--st-strip-align`。値の変更: `--st-line-feature`・`--st-line-service` → `--st-line`、`--st-ring-light` → #f5f5f5、`--st-area-number` → #4d4d4d、`--st-fv-text` → #d9d9d9、`--st-fv-fade-to` → #f3f3f4。削除: `--st-shadow-panel`・`--st-line-label`。`--st-line-plans`（#c8c8c8）は PACKAGE 縦線の例外として残した（AREA の罫線は `--st-line` に変更）。`--st-shadow-card`（0 4 8 1 .05）は AREA 店内写真の例外用に残してある
- **S1 で先に済ませた他セクションの色・影（F-01）**: 影 = SERVICE カード写真 2 枚・WORKS（`.is-store-page .p-cworks__main / __sub img / __render`）・REVIEWS カード（枠線を外した）・FLOW 行（07 含む）・FAQ 行・AREA 白パネル / Local Expertise パネル・写真・COLUMN 画像。文字 #fefefe = 選ばれる理由のコピー・見出し・EXTERIOR・DESIGN FOR A BETTER LIFE・2 行目ラベル / SERVICE 英字・黒帯 / 見える安心の見出し・カード和文 / HIGH-END の和英見出し・特長ラベル / FLOW 矢羽根 / Quality 英字・見出し・バッジ（枠も）/ AREA 黒帯。#1a1a1a = PACKAGE「パッケージプラン」・特長ラベル、AREA カードラベル。#4d4d4d = Local Expertise 本文。罫線 #d9d9d9 = SERVICE・PACKAGE 特長・AREA 店舗情報・WORKS 見積（`--cw-rule` を支店だけ上書き）。見出しの句点 7 か所はデータ側で削除済み。SERVICE リード・選ばれる理由リードは `exterior_exone_store_lines()` で出すようにした（SP で改行を消したとき「。 」の空白が出ていたため）。**レイアウトは触っていない**
- **帯ロールの使い方**: `template-parts/store/section-strip.php` に `'roll' => true` を渡す（既定は variant 無しなら true、`'variant' => 'plans'` なら false）。`p-sstrip--roll` が付き、列 `ul.p-sstrip__list` に **`data-plp-imglist`** が付く。`js/plans-imglist.js` は `[data-plp-imglist]` をすべて独立に動かす（親要素が見える枠。速さは列に効く `--plp-imglist-screen-time`、支店は `--st-strip-screen-time` 40s を渡している）。アニメーションは store.css の `.p-sstrip--roll .p-sstrip__list.is-running`（keyframes `st-strip-flow`）。S4 の PACKAGE 帯は `section-plans.php` の呼び出しに `'roll' => true` を足し、PC の 03 が別画像（`612:580`）・05 が 294 幅で右端切れの件はデータ / CSS 側で対応すること（現状の `exterior_exone_store_strip_images()` は施工イメージ帯の 5 枚）。`p-sstrip--plans` の既存ルール（溶かし先が白・背景 none）は未変更
- **FV**: 背景 1 枚（`exterior_exone_store_fv()['image']`）。`js/store-fv.js` は enqueue から外した（ファイルと `.p-sfv__slide / __thumb* / __ring*` の CSS・`--st-fv-thumb*`/`ring*` トークン・`images/store/hero-thumb-*` は S7 で削除）。SP は `.p-sfv` を `margin-top: -var(--header-height)` でヘッダーの裏から始めている（FV 高さ 723su）。写真 4 枚の列 `.p-sfv__band-list` は SP で横スクロール（4 枚、決定 B-2 Q1）
- **不安提示**: スケッチを本文の中（悩みと本文の間）に移した。PC は絶対配置 + `z-index: -1`、SP は流れの中（悩みと重ねない。カンプより本文が約 35 下がり、以降のセクションが SP で約 50 下にずれる）
- **まだ旧デザイン（レイアウト）のままのセクション**: 選ばれる理由・SERVICE（S2）/ 見える安心・DX（S3）/ PACKAGE・HIGH-END・WORKS（S4）/ REVIEWS・FLOW・Quality・FAQ（S5）/ AREA・CTA・COLUMN（S6）。SP はいずれも補間の縦積み
- 確認スクリプト: `sv2-s1/check.mjs <幅> <高さ> [normal|reduce|nojs] [slug]`（結果 `res-*.json`、スクショ `shot-*`）。2560 幅は PNG が headless で返らないことがあるので JPEG で撮る作りにしてある


### S2 引き継ぎ（2026-10-01）

- **浮遊の使い方**: 浮かせたい要素の親に `data-diagram-float`（`js/diagram-float.js` が画面内の間だけ `.is-floating` を付ける。IO 非対応なら常に付ける）。浮かせる要素には store.css の `.p-sreasons__item` と同じく `animation: var(--diagram-float-name) calc(var(--diagram-float-duration) * 2) var(--diagram-float-ease) infinite; animation-play-state: paused;` + `.is-floating` で running。周期は style.css :root に切り出した **`--diagram-float-duration-1〜6`**（TOP 理念・DX summary も同じトークンを参照。値は変えていない）、向きは奇数 `p-diagram-float` / 偶数 `p-diagram-float-rev`。振れ幅 `--diagram-float`（1.7cqw）を TOP と同じ比率にするため、親に `container-type: inline-size`。中央寄せは `translate`（transform は浮遊が使う）。reduced-motion は `animation: none`
- **data-reveal の JS 無効対策（支店ページ全体に効く）**: css/store.css の SERVICE ブロック末尾の `@media (scripting: none) { .is-store-page [data-reveal] {...} }` と、functions.php の `exterior_exone_store_noscript_style()`（`<noscript><style>`）。S5 の REVIEWS カードは `data-reveal` を付けるだけでよい。reduced-motion / IO 非対応は style.css と scroll-reveal.js が既に最終状態にする
- 選ばれる理由: SP は写真を `<picture>`（`reasons-bg-sp-641-22.jpg` 750 幅 + webp）で差し替え、`.p-sreasons picture { display: contents }`。リング SP は `reasons-ring-sp-641-40.svg`、住環境適応のアイコン SP は `reasons-icon-weather-sp-641-50.png`（データ `icon_sp`）。1 語ラベルは `word`、英字 2 行目 SP は `eng_sub_sp`。SP のリードは S1 の「改行を無効」ルールを SP ブロック内で `br { display: inline }` に戻した（カンプが段落の改行を残した 6 行のため）。中心はカンプより 3 右（図の中心にそろえた）。「長期保証」だけ Bold のカンプ不備は Medium にそろえた
- SERVICE: 帯見出しのデータを 2 行の配列に変更（PC は br を消して 1 行）。写真名の SP 短縮は `label_sp`。SP の NEW BUILD は既存の JPG と同じ写真だったので `sp-service-newbuild-641-72.png` は取り込んでいない（影は RENOVATION と同じ `--st-shadow`）。NEW BUILD の暗幕は `--st-card-shade-strong`（.8。SP は 2 枚とも）
- S2 後の SP の位置: 選ばれる理由 883 高・SERVICE 1269.6 高（カンプ 883 / 1271）。S1 由来の約 52 下ずれはそのまま。退行比較: scratchpad `sv2-s2/before-*.json` / `after-*.json`（TOP・DX の 6 項目の animation 名・周期が一致）、確認スクリプト `sv2-s2/check.mjs <幅> <高さ> [normal|reduce|nojs] [slug] [noshot]`

### S1 検証後の直接修正（2026-10-01）

- S1 は Evaluator 合格。SP の不安提示は、Evaluator の意見（カンプどおり重ねる方が自然）に従い、**スケッチの上端を悩みの最後の行に 29 重ねて文字の背面に敷く**形に変更（`css/store.css` の SP ブロック。本文もスケッチ下端に 5 掛ける）。仕様書 AC-06-2 の「重ならない」は「悩みの文字が読める（スケッチは文字の背面）」と読み替える。これで SP の以降のセクションのずれ（約 +53）が解消する見込み

### S3 引き継ぎ（2026-10-01）

- **DX**: `template-parts/store/section-dx.php` は TOP の `template-parts/top/section-dx.php` を呼ぶだけのラッパーにした（`page-store.php` の並びは不変）。`js/store-dx.js` の enqueue を外し、支店でも `exterior-exone-dx-slider`（`js/dx-slider.js`。TOP と同じハンドル・Swiper 依存なし）を読み込む。TOP の DX の DOM・CSS（style.css の `.p-dx*`）・JS は無変更。支店の DOM に `.p-sdx` は無い。**S7 で削除するもの**: `js/store-dx.js`・css/store.css の `.p-sdx*` ブロック（base / PC / SP の 3 か所）と `--st-dx-*` トークン・`exterior_exone_store_dx()`（inc/store-data.php。docblock に未使用と明記済み）・`images/store/dx-main-image-572-593.*`（他で未使用なら）・`images/store/visible-sketch-572-567.png`（線画を SVG にしたため未使用）
- **DX の前後**: DX は中身を画面の縦中央に置くため、見える安心の `padding-bottom` を「カンプの間隔（PC 204 / SP 54）− DX のピン前の上の空き」で計算している（css/store.css の PC / SP の `.p-svisible`。DX 側の中身の高さ PC 872.4 / SP 613 単位を式に持っている。TOP の DX の寸法が変わったらここも直す）。DX の下は TOP のまま（1920x1080 で中身の下端 → セクション下端 約 108。カンプは PLANS まで 121.4）。**PLANS（S4）は `.p-dx` の直後から始まる**。PLANS の上余白はカンプ 612:804 の y10946 = DX 中身の下端 +121.4 を基準に、DX の下の空き（約 108）を差し引いて決めること
- **JS 無効時の DX（支店のみ）**: SP は 1 ステップずつ JS で入れ替えるため、css/store.css `@media (scripting: none) and (max-width: 1024px)` と `exterior_exone_store_noscript_style()` でピン留めをやめて 3 ステップを縦に並べている（`.is-store-page .p-dx*` に限定。TOP は不変）。PC は TOP と同じ（3 件並び・ピン留めのまま）
- **線画**: `exterior_exone_inline_line_svg( 'images/top/line_animation.svg', 'p-svisible__sketch' )`。色は `--line-draw-color: var(--st-on-dark)`、不透明度 `--st-visible-sketch-opacity`（.3）、位置 `--st-visible-sketch-left/top/width`（コピーの枠に対して -3% / 3% / 104%。カンプ PNG と重ねて合わせた）。**注意**: style.css の線描画は `vector-effect: non-scaling-stroke` + `pathLength="1"` + `stroke-dasharray: 1` で、Chrome は破線を画面上の長さで測るため、SVG を viewBox（737 幅）より大きく出すと線が途中までしか描かれない。支店 PC は最大 1.741 倍なので `--st-visible-sketch-dash: 1.75` で破線を伸ばしている（`svg.p-svisible__sketch[data-line-draw]` の詳細度で上書き。reduced-motion / JS 無効の dashoffset 0 も同じ詳細度で書いてある）。他ページで線画を大きく出す場合も同じ対処が要る
- 見える安心 SP: 本文は `lead_sp`（4 段落）を `.p-svisible__lead--sp` で出し分け。写真と文字は `.p-svisible__card` の grid（偶数段は `:nth-child(even)` で左右反転・右揃え）。英字は PC と同じデータ（「Visible Design」）
- S3 後の位置: PC 見える安心 8404.1〜9847.9（1443.8 高）、DX 9847.9〜14167.9（4320 = 4 画面）、PLANS 14167.9〜。SP 見える安心 4259.2〜5186.2（927 高）、DX 〜7854.3（2668）。S1・S2 対象（FV〜SERVICE）の位置は S3 前と同値（scratchpad `sv2-s3/pre-*.json` / `post-*.json`）。TOP DX の計測値は基準と同値（`sv2-s3/after/measure-3.json`）。確認スクリプト `sv2-s3/lay.mjs`（配置）・`draw.mjs`（線画の描画状態）・`dx.mjs`（ピン留め・01→03・動画・クリック）・`misc.mjs`（他拠点・/・/dx/）

### S2・S3 検証後のメモ（2026-10-01）

- S2 は Evaluator 合格。S7 で直す軽微な差: **SP の SERVICE の本文は、カンプでは「…対応しています。」のあとで段落が改まる 6 行**（実装は続けて流している。768 も同じ）
- S2 の報告事項（ユーザー確認中）: SP の選ばれる理由は暗幕なしで、375 は本文 5〜6 行目のコントラスト約 4:1、**768 は 6 行目が約 2.2〜2.6:1 で読みにくい**
- S3 は AC-10-6 のみ不合格（375・1280 幅で、見える安心と DX の境目に明るい 1px の線）→ **直接修正**: `css/store.css` の `.p-svisible` に同色の `box-shadow: 0 1px 0 0` を足して塞いだ。375 / 390 / 768 / 1280 / 1366 / 1440 / 1920 で境目の前後 9 行がすべて #1a1a1a であることを画素で確認。ほかの 15 基準は合格
- S3 の報告事項（ユーザー確認中）: 見える安心の線画は TOP の SVG を使用。カンプの PNG とは絵が違う（木が葉の無い枝に見える・左の塀が約 55px 短い・1920 では線がカンプより薄くまばら）。比較画像は scratchpad `sv2-s3eval/line-compare-1920.png`・`line-compare-375.png`

### S4 引き継ぎ（2026-10-01）

- **PLANS / PACKAGE（F-11）**: 背景は `--st-bg-light` 一色。灰→白の切り替え・HIGH-END 写真右端の黒フェード・PLANS 帯の白い溶かし（`.p-sstrip--plans::after`）のルールと `--st-plans-light-end / -white-start / -photo-fade` は**削除済み**。パース下・帯下の溶かしは終端 #f3f3f4（Q2）。縦線 y11423.7（`.p-splans__divider` margin 46.8）、PACKAGE ブロックは margin 80.4 で位置不変
- **PACKAGE 帯**: `section-plans.php` から `section-strip.php` に `'variant' => 'plans', 'roll' => true`。写真は `exterior_exone_store_strip_images( 'plans' )`（03 だけ既存 `images/store/plans-strip-669-954.png` = 612:580 と同じ写真、342×210.4）。05 の 294 幅は画面端で切れているだけと判断し 364.6 のまま。PC の間隔は `--st-plans-strip-gap`（35.1。静止時は x33.2 から並べ、`.is-running` 中は左端から＝継ぎ目なし）、SP は施工イメージ帯と同じ間隔・大きさ（Q6: 5 枚を両幅で流す）。`section-strip.php` の変更は `strip_images( $variant )` を渡す 1 行のみ（施工イメージ帯の出力は不変）
- **前後のつながり**: PLANS の上余白は DX の空きを差し引く式（PC `283.3vu − (100vh − 851.2·dx-u)/2`、SP `153su − (dx-pin-height − 595·dx-su)/2`。851.2 / 595 は DX 中身の下端からの実測。1920x1080・1280x720・375x667・768x1024 で「DX の中身の下端 → PLANS」がカンプ値（283.3 / 153 の比例）になることを確認）。TOP の DX の寸法が変わったら直す
- **SP**: `.p-splans` と `.p-splans__highend` を flex 縦並びにし、ブロック・テキストの箱を `display: contents` + `order` で並べ替え（DOM は PC 順のまま）。見出し 2 行は `jp_sp`、特長アイコンの SP 寸法はデータ `icon_sp`（PC との比が 1 枚ずつ違う）。特長の区切り線は SP では無し。アイコン → ラベルの間隔（14〜17）・行間は揃えた
- **WORKS（F-13）**: `store/section-works.php` は DX と同じ引数（`variant => dx`, `gallery => true`, 3D モデル）で共通部品を呼ぶ。データは `exterior_exone_store_works()` = `exterior_exone_dx_works()` + 見出し 3 点（`lead_sp` は DX と同じ 6 行の折り方）。支店だけの上書きは store.css の `.is-store-page .p-cworks`（影 = AC-01-3、罫線 #d9d9d9、提案本文を PC で 18px にそろえる `--cw-box-text2`）。/dx/ の WORKS は基準値と同一（`sv2-s4/after/measure-24.json`）。JS / CSS の enqueue 変更は不要だった（works.css・works-gallery.js・works-estimate-modal.js は既に支店で読み込み済み、3D ライブラリは works-gallery.js が押したときに読む）
- **S7 で消せるもの（このスプリントでは残した）**: `images/store/works-main-698-1181.jpg`・`works-sub2-698-1178.jpg`・`works-sub3-698-1180.jpg`・`works-3d-698-1197.png`（各 webp 含む）・`works-table-line-698-1241.svg`・`works-table-line2-698-1245.svg`・`plans-bg-669-975.svg`・`plans-vline-602-90.svg`・`highend-vline-602-102.svg`・`plans-line144-620-286.svg`（いずれも参照 0）/ store.css `:root` の `--st-white-0`（参照 0）/ 共通部品の **variant=store 用の分岐**（`common/section-works.php` の `gallery => false` 経路 = `exterior_exone_works_slots()`・CPT works の写真・`exterior_exone_works_photo()` のリンク、works.css の既定（支店旧 SP の縦積み・`--cw-render-bg #bcbcbc`・ラベル 152.7×42 等）。呼び出し元は 0 になった。消す場合は DX の見た目が変わらないことを `measure-ref.mjs 4` で確認）
- 確認スクリプト: scratchpad `sv2-s4/` の `lay.mjs`（PLANS〜WORKS の配置・DX→PLANS の間隔）・`roll.mjs`（帯 2 本の速さ・独立・reduce / nojs）・`works.mjs <幅> <高さ> <パス>`（切替・3D・SP モーダル。/dx/ と同じ値か）・`misc.mjs`（7 拠点・各幅）・`pos.mjs`（S1〜S3 の位置。`pre-*` / `post-*` は PLANS の高さ以外同値）

### S4 検証後のメモ（2026-10-01）

- S4 は受け入れ基準 17 件すべて合格、デザイン再現で 1 件差し戻し（PACKAGE の住宅パースがカンプより小さく、下に灰色の台が見える）→ **直接修正**: 住宅パースを PLANS ページと同じ 3:2 の透過 PNG（`images/plans/pc-package-simple-modern-586-406.png`。`exterior_exone_plans_image()`）に替え、カンプの切り抜き（w121.75% / h160.32% / left -11.1% / top -30.64%）を `css/store.css` の `.p-splans__block--package .p-splans__media` で再現。1920 / 375 のスクリーンショットで、家が枠いっぱいに入り台が見えないことを確認。`images/store/plans-package-house-599-13.*` は参照が無くなった（S7 で削除候補）。次の検証（S5）で再確認する
- S7 で確認・調整する候補:
  - 施工イメージ帯・PACKAGE 帯の 04（黒い箱形の住宅）は、既存の書き出し画像のため家が小さく下に灰色の台が見える。カンプ（`612:570`。PLANS ページの img list 04 と同じ切り抜き 121.75% / 165.19% / -11.1% / -34.33%）と見比べ、違うなら PLANS ページと同じ画像・切り抜きに合わせる
  - SP の WORKS のリード: カンプは左揃え・337 幅・5 行、実装は DX ページに合わせて中央揃え・6 行。Evaluator の意見は「支店はカンプどおり左揃えが自然（同じページの PLANS・HIGH-END の SP リードも左揃え）」→ **支店だけ左揃えにする**（DX ページは変えない）
  - 見積モーダルに `role="dialog"` / `aria-modal` が無い（DX ページと共通の部品。支店の作り直しの範囲外。報告のみ）
- 報告事項（ユーザーに伝える）: WORKS の写真の影は支店だけ付いている（新カンプどおり。DX ページは無し）／PC の「EXoneからのご提案」本文は支店 18px（旧決定）・カンプと DX ページは 16px

### S5 引き継ぎ（2026-10-01）

- **REVIEWS（F-14）**: カード 3 枚の `li` に `data-reveal`（既定 32px / 0.9s・遅延なし。見出しは無し）。SP は `.p-sreviews__list` を `overflow-x: auto` の flex 列（280 幅・間隔 20・自動では動かない）。影とフェードインのずれが列の枠で切れないよう、列の上下に `--st-reviews-shadow-top/bottom`（20 / 48su）の padding を足して同じ負マージンで戻している。左右の余白は `max(19su, (100% − 337su) / 2)`（中間幅は見出しの箱にそろえて中央）。画面外（右）のカードは IntersectionObserver が列の枠で切られるため、横にスクロールして見えたときにフェードインする（375 で 3 枚目は opacity 0 → 横スクロール後 1 を確認）
- **SP の見出し 3 点セット**: REVIEWS・FLOW・FAQ は SP ブロックの `:is(.p-sreviews, .p-sflow, .p-sfaq)` で上書き（英字 +80 → 和文 Medium 14 / 1.5 / 1px → 本文 Regular 12 / 2 / 1px 左揃え 337 幅）。本文の幅は **337su + 字間 1**（Figma は行末の字間を幅に数えないので、これが無いと 26 文字の行が 1 文字あふれる）。**S6 の AREA / COLUMN が同じ型なら、このセレクタに足せば使える**
- **FLOW（F-15）**: 07 行の V 字は、clip-path が box-shadow まで切っていた（S1 時点で 07 に影が無かった）ので、白い形を `::before` で切り抜き、行に `filter: drop-shadow(var(--st-shadow-drop))`（新トークン 0 12px 16px .12。06 行の box-shadow と画素で同じ濃さ）。PC / SP 共通で基本ルール側に移した。SP は矢羽根を消し、縦カード（336 幅・角丸なし・間隔 10・番号 +16 / タイトル +24 / 説明 +63 / 下 28 に統一。カードの高さは中身で決まる）+ 縦矢印（`::before` 軸 2px + `::after` 矢じり、#d9d9d9、番号の下 +63 から次のカードの番号の上端まで）。02 だけ SP で 1 段落（データ `sp_join`）。07 は Quality に 60su 食い込む（`--st-flow-bleed`）
- **Quality（F-16）**: 注記はテンプレート・データ・CSS・トークン（`--st-note` / `--st-quality-note`）とも削除。SP の暗幕 55% のトークン `--st-photo-shade-strong` も削除（PC / SP とも `--st-photo-shade` 30%）。PC の本文・バッジはページ中央（カンプの x935.1 は揃えた）。SP は写真の横位置 `--st-quality-image-position: 70.67%`（641:1255 の切り抜き）、英字は Semibold 16 / 2px で「Quality You Can Trust」（1 行に収まる）
- **FAQ（F-17）**: 開いた状態は PC 行 264 / 回答 +131・895 幅（`.p-sfaq__answer` を −18.3vu 引き上げ・下 77）、SP は回答 +40・270 幅・下 34、質問が Bold。回答の罫線は削除、色 #1a1a1a。山形は新しい書き出し `images/store/faq-arrow-612-538.svg`（SP も同じ形を 9.16×4.78 に縮小）。**JS 無効時は回答を開いた状態で出す**（store.css の FAQ ブロック末尾 `@media (scripting: none)` と `exterior_exone_store_noscript_style()`）。開くのは 1 つだけ・閉じるときは即時・キーボード（Enter / Space）・`aria-expanded` は既存のまま（`js/store-faq.js` は無変更）
- **S7 で消せるもの**: `images/store/faq-arrow-669-906.svg`（参照 0）・`images/store/reviews-bg-620-289.svg`（参照 0）/ store.css `:root` の `--st-flow-chevron` の clamp 既定値は PC のみで使用（SP の値は削除済み）
- S5 後の位置: PC REVIEWS 19641.3（1154.7）・FLOW 20796.1（1738.4）・Quality 22534.5（960）・FAQ 23494.5（1979.6）。SP REVIEWS 11078.4（691.9。カンプ 692）・FLOW 11770.3（1318.8。カンプ 1323）・Quality 13089.1（509）・FAQ 13598.1（846.6。カンプ 794 との差は質問 2・8 が 2 行になる分）。FV〜WORKS の位置は 1920 / 1280 / 768 / 375 で S5 前と同値（scratchpad `sv2-s5/pre-*.json` / `post-*.json`）
- 確認スクリプト: scratchpad `sv2-s5/` の `lay.mjs <幅> <高さ> [mode]`（4 セクションの配置・書体）・`act.mjs <幅> <高さ> [normal|reduce|nojs]`（フェードインの前後・SP の横スクロール・07 の影の画素・FAQ の開閉 / 1 つだけ / キーボード）・`misc.mjs`（7 拠点）・`pos.mjs`（FV〜WORKS の位置）

### ユーザー回答（2026-10-01 夜）

- **見える安心の線画は TOP の線画（`images/top/line_animation.svg`）のまま**（カンプの絵との違い = 木・塀・濃さ は了承）。差し替えは不要
- **SP の選ばれる理由はこのまま**（暗幕を足さない。375 の本文 5〜6 行目・768 の後半のコントラストが低い件は了承）

### S5 検証後のメモ（2026-10-01）

- S5 は Evaluator 合格（22 基準すべて ○）。S4 の差し戻し（PACKAGE の住宅パース）も解消を確認（屋根の幅 1920 で 416・375 で 154 = カンプと一致）
- S7 で直すもの（確定）:
  - **帯の 04（黒い箱形の住宅）**: 支店の 2 本の帯は `strip-house4-669-920` で、家がカンプ（`612:570`）より約 2 割小さく、下に灰色の台が見え、枠の高さも 193.2（カンプ 180）。PLANS ページの img list 04 と同じ画像（`images/plans/pc-package-simple-modern-586-406`）・切り抜き（121.75% / 165.19% / -11.1% / -34.33%）・枠の高さ（180）にそろえる
  - **SP の FAQ 質問 8**: 「…もらえます / か？」と 2 文字だけ 2 行目に落ちるのが不自然 → 文節で折る（`word-break: auto-phrase` など。「…提案して / もらえますか？」）。質問 2 も同じ仕組みで自然な位置に。768 でも同じ
  - SP の SERVICE 本文の段落の切れ目、SP の WORKS のリードの左揃え（既出）
- S6 の作業中に PHP の Warning（`Array to string conversion`。`inc/store-data.php` 1202・1204・1208 行付近）が 181 行出た（一時的。S6 の実装途中の状態と思われる）。**S6 の検証で PHP ログに新しい Warning が出ないことを必ず確認する**
- FAQ は「開けるのは 1 つだけ」「行の高さは即時に変わり、回答がフェードで出る」（既存の動きのまま）

### S6 引き継ぎ（2026-10-01）

- **アーチ状ロール（F-18）**: `template-parts/store/section-area.php` が 12 枠（`$exterior_exone_arch_slots`、30° おき）の `li.p-sarea__arch-item` に支店の写真を繰り返して入れる（先頭 N 枚 = 元、残りは `aria-hidden="true"`・alt 空）。初期位置は inline の `--st-arch-a`（PC: 円筒上の角度。0 が正面・正が右。2 枚目が正面）と `--st-arch-s`（SP: 列の何枚目か。-1〜10）。`js/store-arch.js`（functions.php の支店ブロックで enqueue）が requestAnimationFrame で両方を毎フレーム書き換え、置き方は CSS: PC は `rotateX(-tilt) rotateY(a) translateZ(R)`（遠近なし＝正射影）・`z-index` と `opacity`（±95〜110° で消える）も `--st-arch-a` から calc、SP は `left: (9 + s·150)su`・s<-0.99 で opacity 0。画面外は IntersectionObserver で停止、`[data-store-arch].is-running` が動作中の印。タブが裏の間はブラウザが rAF を止め、戻ると続きから（1 フレーム 0.1 秒で頭打ち）。動きを減らす設定 / JS 無効は初期位置で静止。トークン（css/store.css :root）: `--st-arch-loop-time` 60s（写真 1 組 = 4 枚ぶん進む時間 = 120°）・`--st-arch-radius` 941.6・`--st-arch-tilt` 19.6・`--st-arch-center-y` 404・`--st-arch-width/height` 470/340・`--st-arch-limit` 110・`--st-arch-fade` 15・`--st-arch-comp-width` 1920・SP `--st-arch-sp-left/width/height/pitch` 9/142/85/150。SP の速さは PC の正面の速さで 1920 を横切る時間（58.4 秒）で SP の画面幅を横切るよう JS が計算（375 で 6.4px/s・4 枚 93.5 秒）。写真の枚数が 12 の約数でない支店は 1 周に 1 回だけ並びの継ぎ目が出る（抜けは出ない）
- **支店別データ**: `inc/store-data.php` の新関数 `exterior_exone_store_area_media()`（キー = スラッグ）に店内写真（`gallery`: file + object-position）・外観写真（`photo`）・Local Expertise（`local`: heading / lead / photo）・カード 4 枚（`cards`: label / label_sp / image）。7 拠点とも `$aomori` を仮置き（コメント「仮（素材が届いたら差し替え）」）。差し替えは該当拠点の配列で上書き。`exterior_exone_store_area()` がこれと `exterior_exone_stores()` を合わせる（所在地は値を配列 [郵便番号, 住所] にして SP で改行）。住所は半角表記に変更済み（全拠点の仮データも）
- **その他**: 店舗ボックスは黒帯の負マージンがパネル外へ相殺されていた（パネル上端が 48.3 上にずれていた）のを `display: flow-root` で修正 → PC の AREA は 3359.2 高でカンプと一致。PC の店舗ボックスは中央（カンプは x192.6 で 32.6 右寄り。揃えた）。1920 超ではギャラリー・Local Expertise も 1920 枠で中央に。CTA SP は見出し 2 行（データを配列にし PC は br を消す）・本文 3 段落 6 行・暗幕 308 幅のグラデ（`--st-cta-overlay-sp`）・写真位置 `--st-cta-image-position`。COLUMN は PC 背景 #fefefe、SP は REVIEWS と同じ型（`:is()` には足さず `.p-scolumn` で別に書いた）+ `lead_sp_join`（SP で 1・2 行目をつなぐ）・VIEW MORE 非表示・列の上に影の余白
- S6 後の位置: PC AREA 25474（3359.2）・CTA 28833.2（983.5）・COLUMN 29816.7（1087.8）。SP AREA 14444.7（2063.6。カンプ 2035 との差はリードが 5 行（カンプは［主要対応地域］のプレースホルダで 4 行）と店舗情報の行）・CTA 16508.3（549.7）・COLUMN 17058（619.9）。FV〜REVIEWS の位置は 1920 / 1280 / 768 / 375 で S5 後と同値（scratchpad `sv2-s6/pos-*.json` と `sv2-s5/post-*.json`）
- **S7 で消せるもの**: `images/store/area-gallery-exclude-1126-34.png`（+ webp。合成画像、参照 0）・`area-line-628-307.svg`（参照 0）・store.css `:root` の `--st-cta-overlay-flat`（参照 0）・`--st-area-bg`（PC のみ使用、SP は `--st-bg-light`）はそのまま可
- 確認スクリプト: scratchpad `sv2-s6/` の `lay.mjs <幅> <高さ> [mode] [slug] [noshot]`（配置・書体・アーチの各枠の角度 / 回転 / z / 不透明度）・`arch.mjs <幅> <高さ> [normal|reduce|nojs] [long]`（10 秒の進み・1 巡の見積もり・ホバー・`long` で 62 秒の抜け監視 + リサイズ。環境変数 `SHORT=1 NOFREEZE=1` で抜け監視と freeze を省略。**`Page.setWebLifecycleState` frozen は headless で rAF が戻らないので使わない**）・`vis.mjs`（別タブを開いて裏に回す → 戻す）・`misc.mjs`（7 拠点）。スクリーンショット: `arch-{1920,375}-{normal,reduce,nojs}-{0s,3s,6s}.png`・`arch-*-80s.png`・`arch-resize-*.png`・`lay-*`

### S6 検証後のメモ（2026-10-01）

- S6 は Evaluator 合格（F-18〜F-21 の 23 基準すべて ○）。PHP エラーログは検証前後とも 184 行で増えず、S6 の作業中に出ていた Warning は再発しない
- アーチ状ロールのユーザー確認用スクリーンショット: scratchpad `sv2-s6eval/arch-{1920,375}-normal-{0,5,10,20,75}s.png`・`arch-*-reduce-*.png`
- アーチ状ロールでユーザーに確認する点（提示済み・返事待ち）: ① 端の写真が 90° を過ぎて奥側へ回り込み、95〜110° で薄くなって消える（消える途中の半透明で裏返った写真が、白パネルより高い位置に見える瞬間がある。カンプは両端に奥側の細い写真が見えている）② SP の速さ（6.4px/秒。写真 1 枚ぶん進むのに約 23 秒で、止まって見える心配）③ 動作中、端の写真が画面の左右端で切れる（カンプは両端に 20〜37px の余白）
- 店舗情報 6 行と「主要対応地域」は 7 拠点とも青森の値（仮データ。公開前に差し替え）

### S7 引き継ぎ（2026-10-01。担当エージェントが 2 回途中で止まったため、残りは呼び出し側が直接実施）

- 済み（前任の途中までの作業 + 直接実施）:
  - 帯の 04（黒い箱形の住宅）: `exterior_exone_store_strip_images()` が PLANS ページの img list 04（`exterior_exone_plans_package()['imglist'][3]`）の画像・切り抜き・枠（366.3×180）を使う（`.p-sstrip__item--crop`）。1920 / 375 で灰色の台が消えたことを確認
  - SP の FAQ: 質問を文節の配列で持ち `<wbr>` で連結、`.p-sfaq__q-text` は `word-break: keep-all`。質問 8 は「…提案して / もらえますか？」、質問 2 は「…ないのですが、/ 相談できますか？」
  - SP の SERVICE 本文: 段落の切れ目あり（6 行・144 高）
  - SP の WORKS のリード: 支店だけ左揃え（`.is-store-page .p-cworks--dx .p-cworks__lead`。DX ページは中央のまま）
  - WORKS の共通部品の整理: `template-parts/common/section-works.php` から variant=store / `gallery => false` の分岐を削除（`inc/works-data.php` の CPT 写真の経路も）。**/dx/ と /store/aomori/ の WORKS の出力 HTML は S7 作業前と同一**（空白の差のみ）
  - 削除: `js/store-fv.js`・`js/store-dx.js`、`css/store.css` の死んだルール 68 件（`.p-sdx*`・`.p-sfv__ring*` / `__slide` / `__thumb*`）と未使用トークン 25 個（`--st-dx-*`・`--st-fv-ring-*` ほか）、`images/store/` の未参照の画像 40 点 + webp 17 点（一覧は scratchpad `sv2-s7/deleted-images.txt`。git には残っている）
  - 整理の前後で `/store/aomori/` の全要素の位置・寸法・色・影・不透明度のハッシュが 1920 / 375 / 768 で一致（見た目の変化なし）
  - 7 拠点 + / + /dx/ + /plans/#package + /company/ を 1920 と 375 で全スクロール: 404 なし・コンソールエラー 0・横スクロール 0・壊れた画像 0。PHP エラーログは 184 行のまま
- 未実施（最終検証で確認）: 2560 と中間幅（600〜1440）の全セクションの見え方、通しの操作

### 公開前チェックリスト（支店ページ v2）

- [ ] **店舗情報**（`inc/store-data.php` の `exterior_exone_stores()`）: 仙台・戸塚・盛岡・弘前は 2026-10-01 支給の実値を反映済み（所在地・電話・営業時間・定休日・対応エリア・LINE の URL・発信先。駐車場は仮で 10 台）。**八戸・東京は未支給で青森の値のまま**。青森の LINE の URL・発信先も未支給（`#`）。未確認の点: 仙台・戸塚の郵便番号（空にして非表示）／仙台の電話（022-354-8993 を採用。LP の店舗一覧は 0800-170-5955）／定休日（戸塚「火・水・祝」・盛岡と弘前「土・日・祝」を採用。旧の支店個別ページは「日・祝」）／LINE の URL は LP 用の `?from=first_LP` を外し、コーポレートサイト掲載用の URL があるもの（戸塚・弘前）はそれを採用／メールアドレスはページに載せる場所が無いので未使用
- [ ] **支店ごとの素材が 7 拠点とも青森の仮置き**: AREA の店内写真（アーチ状ロール）・外観写真、Local Expertise の見出し・本文・写真、カード 4 枚（雪捨てスペース等）。`exterior_exone_store_area_media()` の該当拠点の配列を上書きする。雪国でない拠点（仙台・戸塚・東京）の文言は要用意。写真の枚数が 12 の約数でないと、1 周に 1 か所だけ並び順の切れ目が出る
- [ ] CTA の LINE URL・電話番号（全拠点 `#`）
- [ ] VIEW MORE のリンク先: PACKAGE / HIGH-END / COLUMN は `#`。DX は `/dx/`（TOP と同じ）
- [ ] FAQ の回答文（仮）・REVIEWS の口コミ（ダミー）
- [ ] 画像は AI 生成を含む仮画像。差し替えは `images/store/` に同名で置き、`images/store/webp/` の同名 .webp も作り直す
- [ ] アーチ状ロールの細部（ユーザーに提示済み。「そのまま進めて」との返事 = 現状のまま）: 端の写真が奥側へ回り込みながら薄くなって消える／SP は 6.4px/秒（写真 1 枚ぶんに約 23 秒）／動作中は端の写真が画面の左右端で切れる。公開前にもう一度見てもらう
- [ ] 見える安心の線画は TOP の SVG（カンプと絵が違う。ユーザー了承済み 2026-10-01）
- [ ] SP の選ばれる理由は暗幕なし（375 の本文 5〜6 行目・768 の後半のコントラストが低い。ユーザー了承済み 2026-10-01）
- [ ] DX ページとの違い（新カンプ・旧決定による）: WORKS の写真の影は支店だけあり／PC の「EXoneからのご提案」本文は支店 18px（新カンプと DX ページは 16px）／SP の WORKS のリードは支店だけ左揃え
- [ ] FAQ は「開けるのは 1 つだけ」（既存の動きのまま）。SP の質問 2・8 は PC の文言（SP カンプの短い文言は不採用）
- [ ] DX の 03 のタグは TOP と同じ「施主共有システム」（旧決定の「高精度3Dパース」を上書き）
- [ ] 見積モーダルに `role="dialog"` / `aria-modal` が無い（DX ページと共通の部品。今回の範囲外）
- [ ] 1025〜1279 の幅では 1920 比例で本文が 10〜12px まで小さくなる（他ページと同じ方針）
- [ ] iPhone / Android 実機での確認（帯のロール・浮遊・DX の固定スクロール・アーチ状ロール・横スクロールの列・FAQ・見積モーダル。ここまでは PC 上のブラウザのみ）
- [ ] `/store/` 親ページ（一覧）は今回の対象外
- [ ] Figma のカンプは読み取り時点（2026-10-01）の内容。その後の変更があれば差分で対応

### S7 最終検証後のメモ（2026-10-01）

- S7 の Evaluator は AC-22-7（写真の WebP 配信）の 1 件だけ不合格、ほかはすべて合格（S7 の修正 1〜4、通しの操作、S1〜S6 の退行なし、他ページの退行なし、13 幅で横スクロール・はみ出しなし、PC の Figma 照合で意図的な差以外の乖離なし）
- **直接修正**:
  - AC-22-7: `images/store/` で WebP が無かった写真 13 枚（hero-band ×2・stats ×2・service ×7・area ×2）の WebP を `images/store/webp/` に追加（品質 76。JPEG 比 65〜76%）。`/store/aomori/` を上から下まで開いて、`images/store/` の配信が WebP と SVG だけになったことを確認。TOP と共通の DX 部品の `dx_share.jpg` と、COLUMN のアイキャッチ（uploads）は今回の範囲外
  - 2560 での寄せ方: 不安提示（`.p-sconcern__body` を 1920 幅で中央）と COLUMN（カード列の始まりを中央の 1920 幅に合わせる）を修正。2560 で見出し x498・カード x479（= 1920 のときの値 + 320）。1920 以下は不変（178 / 159）。**FV の文字は画面左寄せのまま**（ヘッダーのロゴも左寄せで、TOP と同じ作法のため変えていない）
- 未実施: SP カンプ（`670:239`）との最終の見比べ（Figma の画像取得が縮小されすぎて判読できないため。S1〜S6 の各検証では設計メモの実測値と照合済み）
- `.playwright-mcp/` の一時ファイルは削除済み

### 支店データの更新（2026-10-02 ユーザー支給）

- 仙台（〒980-0014・所在地の表記を「本町二丁目2番7号」に）・戸塚（〒244-0003・LINE を `https://lin.ee/wxMNCYU` に）・盛岡（LINE `https://lin.ee/Ys2O5ED` で確定）・**青森（定休日 土・日・祝／対応エリアに「つがる市」追加／LINE `https://lin.ee/Tfvjo0k`／電話ボタン `tel:0177118031`）**を反映。弘前は今回の支給に無いが、同じアカウントなので LINE を青森と同じ `https://lin.ee/Tfvjo0k` にそろえた
- **八戸は未支給**（旧の青森の値のまま: 所在地・電話・対応エリアが青森、LINE / tel は `#`）
- **東京オフィスは不要**との指示で、ヘッダーの STORE 一覧から外した（`inc/menus.php` の `exterior_exone_store_menu_items( array( 'tokyo' ) )`）。SP のドロワー・フッター・`/store/` の一覧・ページ `/store/tokyo/` には残っている（外すかはユーザーに確認中）

### 八戸の支給データと東京オフィスの削除（2026-10-02 ユーザー指示）

- 八戸を実値に（〒031-0072 青森県八戸市城下4-16-20／0178-38-9640／土・日・祝／対応エリアは青森県 13 市町村 + 岩手県 2 市／LINE `https://lin.ee/7JUyeGN`）。「奥入瀬町」は支給の表記のまま（自治体名は「おいらせ町」。ユーザーに確認中）。これで **6 拠点すべて実値**（駐車場だけ仮の 10 台）。仮データ用の `$provisional` は削除
- **東京オフィスはすべてから外した**: `exterior_exone_stores()` と `exterior_exone_store_area_media()` から削除（ヘッダー・SP ドロワー・フッター・`/store/` の一覧から消える）。固定ページ `/store/tokyo/`（ID 43）は WP-CLI で**下書き**に変更（URL は 404。戻すなら公開に戻してデータを足す）。支店は 6 拠点になった

## 2026-10-06 AREA / Local Expertise の支店別写真・文章（Figma ボード Group 528 `641:1464`）

- ユーザー指示「Area セクションと Local Expertise セクションの各支店ページごとに選択されている画像に変更したい」。ボードの行 = 支店（青森・仙台・八戸・弘前・盛岡）。各行: 店舗外観 516×343 → AREA の外観写真、店内 450×269 ×4 → アーチ状ロール、848×578 → Local Expertise の写真、円 364 ×4 → POINT カードの写真、右端のテキスト画像 → Local Expertise の見出し・本文・POINT のラベル
- `exterior_exone_store_area_media()` を支店ごとの配列に書き換え（`inc/store-data.php`）。写真は `images/store/area-<slug>-<用途>-<node>.jpg`（+ `webp/`）。Local Expertise の写真とカードの写真に `position`（object-position）を足し、`section-area.php` で出力
- **弘前**: 外観・店内はボードの写真、Local Expertise とカードは青森と共通（ボードの注記「地域セクションのテキストと画像は、青森と共通（エリア名のみ変更）」）
- **戸塚**: 当初ボードに素材が無く青森の仮置きとしたが、同日に戸塚の行（ノード 769:*）が追加されたため差し替え済み（見出し「デザインも、品質も、妥協しない外構づくり」、POINT: 建物と調和するデザイン / ライフスタイルに合わせたプランニング / 年間650件以上の施工実績 / AIによる完成イメージの可視化）。これで 6 支店すべて本物の素材
- 見出しの末尾の「。」はボードには付いているが、カンプ（612:670）と既存の青森に合わせて付けない。仙台の「ライフスタイルに合わせたプランニング」は PC で 1 行に収まらないため 2 行にした
- 旧ファイル（`area-gallery-photo*`・`area-store-photo-628-306`・`area-snow-photo-1148-43`・`area-card1〜4`）は参照が無くなったため削除
- Local Expertise の写真（AI 生成 1448〜1672px）は元画像が小さく原寸のまま（WebP 110〜370KB）
- **REVIEWS の写真（2026-10-06）**: ユーザー指示「仮で良いので何か入れて」。カード 3 枚に AREA の Local Expertise の施工写真（仙台・八戸・戸塚）を仮で入れた（`exterior_exone_store_reviews()` の `image`。無ければ従来の灰色のまま）。本物のお客様の写真が届いたら差し替える（公開前チェックリスト）
- **PLANS セクションの VIEW MORE（2026-10-06）**: ユーザー指示で、パッケージ → `/plans/?store=<slug>#package`、ハイエンド → `/plans/?store=<slug>#high-end`（PLANS ページを該当プランが選ばれた状態で開く）。`inc/store-data.php` の `'more'` を `home_url( '/plans/#package' )` / `home_url( '/plans/#high-end' )` にし、`section-plans.php` で両方とも `exterior_exone_store_link()` を通す。ハイエンドページ `/plans/high-end/` へは PLANS ページ側の VIEW MORE から辿る（決定 Q9 の仮置き `#` は解消）
- **WORKS 見積モーダル（SP）の写真の切り抜き（2026-10-06）**: 3:2 の写真を 203 高に cover で収めるとき上下中央で切っていたため屋根が欠けていた。カンプ 439:1034 は屋根まで見せて下（道路側）を切る構図なので `object-position: 50% 0`（上揃え）に。`css/works.css`（支店・DX 共通）
- **AREA の円筒ロールを円に（2026-10-07）**: ユーザー指摘「軌跡が正 16 角形のようになっている。円にしたい」。写真 1 枚を平らな板として円筒に置いていたため多角形に見えていた。PC では `js/store-arch.js` が写真 1 枚を縦の短冊 `--st-arch-strips`（10）本に分け、短冊ごとに角度（写真の角度 + ずれ。1 枚が占める角度 `--st-arch-span` = 幅 / 半径）を変えて円筒に沿わせる（`.p-sarea__arch-strip` / `.p-sarea__arch-slice`、`css/store.css`）。短冊は GPU のレイヤーに乗せる（`will-change`）。影は短冊ごとに重なって縞になるので付けない。SP（直線の列）と JS 無効時は従来どおり平らな写真
- **駐車場の台数（2026-10-07、ユーザー支給）**: 仙台 2 台 / 盛岡 5 台 / 八戸 2 台 / 青森 2 台 / 弘前 2 台 / 戸塚 2 台（仮の 10 台を置き換え。`inc/store-data.php`）
- **八戸の店舗外観（2026-10-07）**: ボードの八戸行の外観が 737:81 → 802:41 に差し替わったため `area-hachinohe-store-802-41.jpg`（+ webp）に更新。旧ファイルは削除。店内 4 枚・Local Expertise・カードは変更なし
- **FAQ の文言差し替え（2026-10-10）**: ユーザー指示で、Figma の 8 問（641:1462・838:35〜838:66）の文言に差し替え。質問 8 問と並びは従来と同じで、回答文がすべて新しくなった（各 2 段落。`'a'` を行の配列にし、`section-faq.php` で `<br>` 改行）。回答は Figma のテキストから取得（スクリーンショットの転記ではない）
- **AREA の上端の背景を支店ごとに（2026-10-10）**: ユーザー指示で Figma の `<支店>_bg`（847:132〜847:145。各 1920×934 の枠に大判写真を crop）を 6 支店の AREA 背景に差し替え。`area-bg-<slug>-847-<id>.jpg`（1920 幅に縮小 + webp）。切り抜き位置はカンプの crop から `bg_position` に（PC のみ適用。SP は従来どおり上端）。盛岡だけカンプが 1.68 倍にズームしていたため、書き出し時にその範囲で切り抜いた。旧共通写真 `area-bg-store-620-292.jpg` は `bg` が無い拠点の予備として残す
- **REVIEWS を本物の文言・写真に（2026-10-10）**: ユーザー支給の写真 3 枚（voice-1 / voice-2 / voice-3 → `images/store/reviews-voice-{1,2,3}.jpg` + webp。3 枚目は voice-3（510×351）。青森・弘前だけ 3 枚目を voice-4（最初の支給は 255×175 で甘かったが、同日に 1512×1040 を再支給 → 1020 幅に縮小））を全支店共通で使い、お客様の声は支店ごと（`exterior_exone_store_reviews( $store )`）。ユーザー決定: 名前（地域 / 年代）は**本文の下に右寄せ**（カンプの「タイトル」行は使わない。`--st-reviews-title` → `--st-reviews-name` 10 → 14）、本文は**全文を出す**（3 行省略をやめ、カードは min-height で伸びる）。盛岡・青森の声は最初の貼り付けで欠けていたが、同日に再支給され 6 支店ぶん入力済み
- **アーチ状ロールの奥側の消し方（2026-10-10）**: ユーザー指示「後ろへ行くほど薄くなって消えるのをやめ、白背景（パネル）と重なった時点で消す」。`--st-arch-limit` / `--st-arch-fade`（95〜110° で opacity を落とす）を廃止し、`--st-arch-hide: 110` を追加。`.p-sarea__panel`（PC）に `z-index: calc(200 − --st-arch-hide)` = 90 を与え、写真の `z-index: 200 − |角度|` が 110° を越えた分だけパネルの背面に回るようにした（正面側の写真はパネル下端にかぶるので、パネルを一律に前には出せない）。110° で写真の内側の端がちょうどパネルの端（±800）に達するので、そこから先はパネルに隠れて消え、反対側でパネルの端から現れる。90〜110° の奥側の細い写真はカンプどおりパネルの横に見える。opacity は常に 1。提示済みの確認点①（半透明で裏返った写真が見える瞬間）はこれで解消
- **アーチの写真が追従ヘッダーの上に出ていた（2026-10-10）**: 写真の `z-index`（最大 200）がページ全体の重なりに参加し、ヘッダー（`--z-header: 100`）より前に描かれていた。`.p-sarea` に `isolation: isolate` を付けてセクション内に閉じ込めた（PC・SP ともヘッダーが前）
- **SP の FV 下の写真 4 枚（`.p-sfv__band-list`）が指でスクロールできなかった（2026-10-10）**: FV の文字を載せる透明な層 `.p-sfv__body`（absolute・inset 0・z-index 2）が、FV の下端に 62 食い込む写真の列の上まで覆い、タッチを遮っていた。`.p-sfv__body { pointer-events: none }` + `.p-sfv__copy { pointer-events: auto }` で素通しに（見た目は変えない。PC も同じ指定だが影響なし）。CDP のタッチスワイプで scrollLeft が動くことを確認
- **iPad（PC の組み・縦長）で PLANS の上の余白が消えていた（2026-10-10）**: `.p-splans` の padding-top と `.p-svisible` の padding-bottom は「前後の DX（画面の縦中央に置く固定区間）の空きを差し引く」式で、縦長の画面では DX の空き（黒）が大きくなって 0 まで減り、「PLANS」が黒い縁に接していた。1920×1080 の値（168.9 / 100.2 vu）を下限にした（基準幅では変化なし。iPad 縦 1032 で 91px）

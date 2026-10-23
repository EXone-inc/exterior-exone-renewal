# PLANS ページ 決定事項（2026-09-30）

ユーザーと決めた前提。**仕様書 `docs/spec-20260930-plans-page.md` のオープン論点より優先する。** planner / generator / evaluator はここに従うこと。設計メモは `docs/figma-plans-page.md`（Figma の実測値・guide の原文・カンプの不備はそちらが出典）。

- カンプ: PC `586:390`（1920×10871）/ SP `586:850`（375×5949）/ アニメーション注釈 guide `586:620`
- Figma から取得した画像: `/private/tmp/claude-501/-Users-exone-se-Local-Sites-exterior-exone-renewal-app-public/1a23e357-7a0f-4937-9ce5-db92ebf0e194/scratchpad/figma-plans/`（41 ファイル。`assets-all.txt` が一覧。PNG は大判なので WebP 化・リサイズして `images/plans/` に置く）

## A. ユーザーが決めたこと（2026-09-30 回答）

| 論点（設計メモ §6 の番号） | 決定 |
|---|---|
| プラン選択後の画面構成（1・10） | PLAN select（左右 2 分割の全画面）でプランを押すと、**その場で選んだプランの詳細に変わる**（guide ①②③）。表示するのは**選んだプランだけ**。もう一方のプランへは**タブ**（パッケージプラン / ハイエンドプラン）で切り替える。その下に**共通 2 セクション**（Design Process → Beyond the Plan）が続く。カンプで PACKAGE PLAN と HIGH-END PLAN が縦に並んでいるのは 2 状態を並べて描いたもの |
| まだ選んでいないとき | PLAN select のすぐ下に共通 2 セクション（Design Process → Beyond the Plan）をそのまま表示する |
| SP の動き（4） | **PC と同じ**（選んだプランだけ表示）。SP カンプに無いタブを追加する |
| タブの追従（2） | プランを選んだ後は、**ページのどこにいてもヘッダーの下に出し続ける**（FV・共通セクションでも）。まだ選んでいない間は出さない |
| SP のタブの見た目 | ヘッダー（45px）の**直下に、画面幅いっぱいで 2 つを半分ずつ**並べる。高さ約 32px（375 基準。他と同じ SP 比例ユニットに載せる）、文字 12px。色と角丸は PC のタブと同じ（選択中 = 白地 #ffffff・文字 #1a1a1a / もう一方 = rgba(0,0,0,.3) 地・文字 #f5f5f5 / 角丸 5） |
| 「背景が丸く広がる」（3） | **選んだ半分の中心**から、その半分の背景色の円が画面全体へ広がる。**1.5 秒・easeOutCubic**（現行サイト exterior-exone.com の FV の輪と同じ速さ・減速。参照: `exterior-exonecom/.../affinger-child/js/main-script.js` 74–95 行）。②プラン名の移動・③プラン名の下の入れ替わりも**同じタイミング・同じ 1.5 秒** |
| デザインテイストの件数と内容（6） | **TOP の PLANS と同じ 5 件**（シンプル / ナチュラル / アーバン / クール / スタイリッシュ モダン。`exterior_exone_plan_cards()` の並び）。画像は **TOP の 5 枚**（`plans-card-1〜5.png`、1800×909 透過）を style view・カードの両方で使う。英名は「SIMPLE MODERN」の形に揃える（NATURAL MODERN / URBAN MODERN / COOL MODERN / STYLISH MODERN）。説明文はシンプルモダンだけカンプの文、**他 4 件は仮の文章**（公開前に差し替え。データ側に「仮」とコメントを残す） |
| img list の自動スクロール（7） | **右から左へ**、一定の速さで止まらず流れ続ける。5 枚が一巡するのに**約 40 秒**。ホバーでも止めない。SP も同じ（SP は一巡の時間を画面幅に合わせて同じ体感速度に） |

## B. 既定方針で実装側が決めたこと（ユーザー未確認。完了時にまとめて報告する）

| 論点 | 決定 | 根拠 |
|---|---|---|
| URL・構成 | 固定ページ **`/plans/`**（slug `plans`）を WP-CLI で作成。`page-plans.php` + `template-parts/plans/section-*.php` + `inc/plans-data.php` + `css/plans.css`（+ JS）。トークン接頭辞は **`--plp-`**、クラスは **`.p-plp*`**（`.p-plans*` / `--plans-*` は TOP の PLANS セクションが使用中、`.p-splans` は支店が使用中のため衝突させない） | DX・支店ページと同じ 4 点セット |
| メニューのリンク | `inc/menus.php` の「PLANS」（現在 `#`）と、フッター・ドロワーの「プラン一覧」に当たる項目を `/plans/` にする。TOP・支店の PLANS セクション内の VIEW MORE は今回触らない（完了報告に「未接続」と書く） | DX ページの前例 |
| ヘッダー | 既存ヘッダーをそのまま使い、PC は FV に重ねる（`exterior_exone_has_fv_header()` に plans を足す）。PC カンプの FV にヘッダーが描かれていないのは不備扱い。カンプの PACKAGE 上端の header（612:56）は既存ヘッダーと同一 | 設計メモ §0「既存流用可否」 |
| PC のタブの位置 | カンプどおりヘッダーの下端に揃えて右寄せ（307×55 ×2、間隔 13、右端 x1842）。ヘッダーと一緒に画面上部に固定。重なり順はヘッダーと同じ層 | 611:16 |
| タブの選択状態 | 選んでいるプランのタブ = 白地。HIGH-END 選択時は色を入れ替えるだけ（カンプに無い状態）。**暗い背景（#1a1a1a）の上で非選択タブ rgba(0,0,0,.3) が見えにくい場合は、実装後にスクリーンショットを付けてユーザーに確認する（勝手に色を変えない）** | カンプに無い状態 |
| タブ切り替えの動き | guide に指定が無いので、丸の広がりは使わず**ふわっとクロスフェード**（既存の `--transition-base` 系）。切り替え時はプラン詳細の上端へスクロール位置を合わせる（共通セクションを見ているときにタブを押しても、プラン詳細の上端へ移動して切り替える） | 「その場で変わる」の延長 |
| PLAN select の操作対象 | PC は**半分全体**が押せる（ボタンは描かれていない）。SP も半分全体が押せ、VIEW MORE はその中の見た目。キーボードでも操作できる（button 要素・フォーカス表示は既存の作法） | guide「クリック時」 |
| ホバー（PC） | 半分にマウスを乗せると、その側の**画像だけ**少し拡大（1.05 倍・0.6 秒）。guide「ホバー時画像を少しズームイン」 | 倍率・速度はカンプ無し |
| ②プラン名の移動先 | PLAN select の位置（セクション上端 +218 / +242）から、**詳細冒頭の中央**（x 中央・上端 +270 / +294）へ。HIGH-END のカンプ値 +160 はヘッダー無しで描いた値とみなし、**両プランとも PACKAGE と同じ +270 に揃える**（タブが常にヘッダー下に出るため） | 設計メモ §2.2 の位置表 |
| ③プラン名の下 | PLAN select の画像・コピー・本文（SP は VIEW MORE も）がフェードアウトし、詳細の項目（img list 以降 / box 以降）がフェードイン。押さなかった側は円に覆われて消える | guide ③ |
| 選択時のスクロール位置 | 押した時点で PLAN select が画面からずれていても、演出の開始と同時に PLAN select の上端へ位置を合わせる | 全画面表示の前提 |
| URL のハッシュ | `/plans/#package` / `/plans/#high-end` で開くと、演出なしでそのプランを選んだ状態にする（他ページからの直リンク用）。選択・タブ切り替えのたびにハッシュを書き換える（履歴は増やさない） | 直リンクの必要 |
| style view + style list | 「コーポレートTOPと同じ動き」= TOP の PLANS と同じ: 横にドラッグできるカード列（`js/scroll-row.js` の作法）、選択中のカードは少し持ち上げ（TOP の `--plans-card-lift` 相当）、ホバーでさらに持ち上げ + 影。カードを押すと、上の style view の**画像・和名・英名・説明文**がふわっと切り替わる（フェード。TOP の切り替えと同じ時間）。初期選択は 01 シンプルモダン | guide / 調査結果 |
| designer plan / top designer plan（9） | **ブロック全体**を、画面に入った時点で 1 回だけフェードイン。designer plan は右から左へ、top designer plan は左から右へ。移動量・時間・イージングは既存の `[data-reveal]`（32px・0.9 秒・`--reveal-ease`）。既存に無い「右から」の variant を足す。SP も同じ向き | guide / 既存トークン |
| Beyond the Plan の point | リスト全体を `[data-reveal]`（下から）で 1 回フェードイン | guide |
| guide に注釈が無い箇所 | 動きを付けない（FV・PACKAGE の copy と point・HIGH-END の box と point・Design Process） | カンプに答えが無いものは足さない |
| VIEW MORE のリンク先（8） | PACKAGE 下・HIGH-END 下とも **`#`**（仮）。SP の PLAN select の VIEW MORE はプランを選ぶ操作そのもの | DX・支店の前例 |
| 価格（11） | カンプどおり「¥30,000～」「¥100,000～」を表示。2 プランの同文の説明文もカンプどおり（仮テキスト扱い。公開前確認） | — |
| FV（12） | 静止画。暗幕は足さない | カンプどおり |
| Beyond the Plan の下（13） | そのまま既存フッター。CTA は置かない | DX の前例 |
| Antonio Regular 400（14） | テーマ同梱の Antonio は可変フォント（300 と 600 が同じ woff2 を指している）。**同じファイルを指す 400 の @font-face を足して使う**。可変でなかった場合は 300 で代用し、報告に書く | `css/fonts.css` |
| 動きを減らす設定 | 丸の広がり・プラン名の移動・クロスフェードは即時切り替え、img list の自動スクロールは止めて静止、フェードインは最終状態で表示 | 既存ページの作法 |
| JS が動かないとき | カンプの並びのまま縦に全部出す（PLAN select → PACKAGE PLAN → HIGH-END PLAN → 共通 2 セクション）。タブは出さない | 内容が読めることを優先 |
| PC / SP で文言が違う箇所 | 幅ごとにカンプどおり出し分ける（PACKAGE 本文・PLAN select 本文の段落の切り方など） | DX の Q1 と同じ作法 |
| 中間幅 | ブレークポイントは既存どおり 1024 / 1025px。PC は 1920 比例、SP は 375 比例（`--sp-scale-max` で頭打ち） | タブレット方針 |

## B-2. 仕様書のオープン論点 Q1〜Q8 への決定（2026-09-30・既定方針に沿って実装側で決定。ユーザー未確認）

| 論点 | 決定 |
|---|---|
| Q1 PLAN select の高さ | **PC は画面の高さいっぱい**（guide「全画面表示」。中身は DX ページのステップと同じく「1920×1080 が幅と高さの両方に収まる縮尺」で置き、横長・縦長の画面でも中身がはみ出さない）。**SP はカンプ比**（375:723 を SP 比例ユニットで。画面の高さには合わせない） |
| Q2 `#package` / `#high-end` で開いたときの位置 | プラン詳細の上端へ移動した状態で表示（演出なし） |
| Q3 タブが出るタイミング | 1.5 秒の演出が終わった時点でふわっと出す（既存の `--transition-base`）。ハッシュ付きで開いたときは最初から出ている |
| Q4 タブのホバー・フォーカス | ホバーで色は変えない（カーソルだけ指に）。フォーカス表示は既存ボタンの作法 |
| Q5 未選択の状態へ戻る手段 | 置かない（ハッシュ無しで開き直せば選択画面に戻る） |
| Q6 JS が動かないときの PLAN select | 左右それぞれを**ページ内リンク**にして、縦に並んだ該当プランの位置へ移動できるようにする（JS が動くときは同じ要素でプラン選択の演出を行う） |
| Q7 タブを切り替えて戻ったときの style list の選択 | 保つ |
| Q8 ページタイトル・ナビのカレント表示など | 既存の下層ページ（DX・企業情報）と同じ作法 |

## C. カンプの不備の扱い（設計メモ §6 の列挙に対して）

- SP の HIGH-END 見出し「パッケージプラン / PACKAGE PLAN」→ **「ハイエンドプラン / HIGH-END PLAN」**で実装
- フレーム外の残置（SP `586:989`「施工内容・内訳を見る」・`586:1223`）と、PC Beyond the Plan の重複見出し `586:1383` → 実装しない
- 画像下のグラデの終端色 #f5f5f5（背景は #f3f3f4）→ **背景色に合わせる**
- 1〜10px の位置ずれ・寸法の不揃い（PACKAGE 背景 x-3、Design Process のカード寸法と x+10、designer plan の x163、ボタンの 1px、Beyond の項目間隔・04 の中心ずれ、SP の縦矢印の長さ など）→ **作図ブレとみなして揃える**
- Design Process のカード 02 だけ影が無い → 他と同じ影を付ける
- HIGH-END 英名の色 #f5f5f5（選択画面）/ #fefefe（詳細）→ **#fefefe に統一**（移動中に色が変わらないように）
- Beyond the Plan の線画の不透明度 .5 / .8 / .74 / .9 → カンプどおり（意図の可能性があるため触らない）
- 本文中の不要な半角空白（「最適化した パッケージプラン」「 一からデザインする フルオーダープラン。」「住まいを 実現します」「こだわり 」「提案 」「AI / DX EXPERIENCE 」末尾）→ 除去
- SP の「TOP DESIGNER PLAN」と写真の 24px の重なり → 重ならないよう写真側を詰める（英名を折り返さない）
- style list の 03・04 が同じ画像 → A の決定（TOP の 5 枚）で解消

## D. 運用メモ

- generator は **1 スプリントごとに新しく起こす**（続けると文脈が膨らんで止まる）。各スプリントの引き継ぎはこのファイルに追記する
- 「忠実再現」の基準は `CLAUDE.md` と `.claude/agents/evaluator.md` のとおり（セクション構成・配置・配色・タイポグラフィ・余白感が一致。px 単位の完全一致は求めない）
- 確認が要る点が新しく出たら、勝手に決めず「要確認」として報告に書く

### S1 引き継ぎ（2026-09-30 generator）

- 固定ページ: ID **50**「PLANS」/ slug `plans` / 公開・親なし。作成コマンド `wp post create --post_type=page --post_title=PLANS --post_name=plans --post_status=publish` → `wp rewrite flush` 実行済み
- テンプレート: `page-plans.php` の `$exterior_exone_plans_sections = fv / select / package / highend / process / beyond` を `template-parts/plans/section-<名前>.php` で読む（未作成のファイルは黙って飛ばされる）。S1 で作ったのは `section-fv.php` のみ
- データ: `inc/plans-data.php`（functions.php で require 済み）。関数は `exterior_exone_plans_<セクション>()` の形で足す（S1 は `exterior_exone_plans_fv()`）。画像ヘルパーは **`exterior_exone_plans_image( $file )`**（`images/plans/webp/` に同名 .webp があればそちらを filemtime 付きで返す）。テイストの和名・画像は `exterior_exone_plan_cards()`（TOP）を参照し、画像 URL は `exterior_exone_top_image()` で取る
- CSS: `css/plans.css`（`is_page('plans')` で `exterior-exone-style` の後に enqueue 済み）。冒頭 `:root` = 幅で変わらない値（色・行高・太さ）。長さは末尾の比例ブロックで定義する: PC `@media (min-width:1025px)` の `--plp-vu`（= min(1px, 100vw/1920)）/ SP `@media (max-width:1024px)` の `--plp-su`（= min(100vw/375, --sp-scale-max)）。**新しいセクションの長さトークンは両ブロックに足し、セクションの CSS は FV ブロックの後・比例ブロックの前に置く**
- 用意済みの色トークン: `--plp-color-white1`(#fff) / `--plp-color-white4`(#f3f3f4) / `--plp-color-band`(#272727) / `--plp-color-process-num`(#b1b1b1) / `--plp-tab-off-bg`(rgba(0,0,0,.3)) / `--plp-shadow-color`(rgba(0,0,0,.12))。#1a1a1a・#fefefe・#f5f5f5・#dadada・#d9d9d9・#4d4d4d は style.css の `--color-black2` / `--color-white2` / `--color-white3` / `--color-gray1` / `--color-gray2` / `--color-black1` を使う
- JS: S1 は無し。PLANS 用 JS は functions.php の `is_page( 'plans' )` ブロック（plans.css の enqueue の直後）に filemtime・footer で足す。`scroll-row.js` の PLANS での読み込みも S6 でここに足す
- body クラス: `is-plans-page` + `has-fv-header`（PC はヘッダーが FV に重なる fixed。SP は 45px の sticky 黒帯の下から FV）
- FV の組み: PC 高さ 1080vu・コピー上端 428vu。SP は「ヘッダー + FV = 723su」（FV = 723su − `--header-height`）、写真はヘッダーの裏まで伸ばして切り抜き位置 39.58% をカンプに合わせた
- Antonio 400: `css/fonts.css` 末尾の 600 の後に 400 の @font-face 2 本（latin-ext / latin）を追加。woff2 は fvar / gvar を持つ可変フォントであることを確認済み（300 での代用はしていない）
- 画像（`images/plans/` + `images/plans/webp/`。ファイル名は取得時のまま `<pc|sp>-<用途>-<ノードID>`。PNG は元形式のままリサイズ、配信は webp）:
  - FV / SP FV: `pc-fv-bg-586-403.png`(1672) / PLAN select 左・img list 04・style view: `pc-package-simple-modern-586-406.png`(1536) / PLAN select 右: `pc-select-highend-img-586-579.png`(1358)・`pc-select-highend-gradation-586-578.svg`・SP `sp-select-highend-img-586-1038.png`(462)
  - img list: `pc-imglist-01-586-412.png`(976) / `-02-586-414.png`(680) / `-03-586-411.png`(712) / `-05-586-413.png`(736)
  - `pc-stylelist-02-586-498.png`(656) … 取り込んだが決定 A（TOP の 5 枚を使う）により**使わない想定**
  - point 円: `pc-package-point-circle-586-463.svg` / `pc-highend-point-circle-586-473.svg` / `sp-package-point-circle-586-1081.svg` / `sp-highend-point-circle-586-1272.svg`
  - HIGH-END: `pc-highend-main-img-586-505.png`(1498) / `pc-highend-sub-img-586-542.png`(535) / `pc-designer-plan-img-586-540.png`(1580) / `pc-top-designer-plan-img-586-400.png`(1580) / `pc-price-yen-586-451.svg` / `sp-price-yen-586-1293.svg` / `pc-plan-line-586-500.svg`
  - Design Process: `pc-process-img-586-397.png`(597) / `pc-process-card-01-586-512.png`・`-02-586-396`・`-03-586-399`・`-04-586-398`(770) / `pc-process-arrow-586-517.svg` / `sp-process-arrow1-586-1322.svg`・`arrow2-586-1330`・`arrow3-586-1345`
  - Beyond: `pc-beyond-bg-586-393.png`(1280) / `sp-beyond-bg-586-1423.png`(900×2058。webp 40KB) / `pc-beyond-01-dx-586-535.png`(564)・`-02-design-586-536`(636)・`-03-quality-586-394`(468)・`-04-support-586-541`(624)
  - （）内はリサイズ後の幅。取り込んでいないもの: `guide-arrow-*`・`sp-leftover-*`・`pc-header-*`・`sp-header-*`

### S2 引き継ぎ（2026-09-30 generator）

- 追加ファイル: `template-parts/plans/section-select.php` / `section-package.php`。データ関数: `exterior_exone_plans_select()`（左右 2 件。key `package` / `high-end`・href・和名・英名・画像・copy[2]・body = 段落ごとの行配列）/ `exterior_exone_plans_package()`（imglist 5 件の枠寸法と切り抜き・copy・body_pc / body_sp・points・more）/ `exterior_exone_plans_styles()`（`exterior_exone_plan_cards()` の和名・画像 + 英名・説明文。02〜05 の説明文は「仮」コメント付き）
- PLAN select の HTML: `section.p-plpselect` > `a.p-plpselect__half.p-plpselect__half--{package|high-end}[href="#package|#high-end"][data-plp-select="package|high-end"]` > `.p-plpselect__stage`（PC 960x1080 / SP 半分x723 の舞台。中身は absolute でカンプ位置）> 部品ごとに `data-plp-select-part` = `name`（`.p-plpselect__name` の中に `.p-plpselect__jp` + `.p-plpselect__eng`）/ `media`（`.p-plpselect__media` > img。S5 のホバー拡大は img に掛ける）/ `fade`（PC の画像下グラデ）/ `copy` / `body` / `more`（SP の VIEW MORE。`span.c-btn` で見た目のみ・aria-hidden）。S5 は「name だけ移動、他の part はフェードアウト」
- PC の PLAN select は `.p-plpselect` 上で `--plp-vu` を `min(1px, 100vw/1920, 100vh/1080)` に取り直し（トークンも同じルールの中）、高さ `--plp-select-h: 100vh`。SP は `723su`。左の背景 `--plp-color-white4`・右 `--color-black2`（円の広がりの色に使える）
- a:hover の不透明度 .7 は `.p-plpselect__half:hover { opacity: 1 }` で打ち消し済み（ホバー拡大は S5）
- PACKAGE の HTML: `section#package.p-plppkg[data-plp-plan="package"]` > `.p-plppkg__name[data-plp-plan-name]`（`.p-plppkg__jp` + `h2.p-plppkg__eng`。上端 +270 / +294 は section の padding-top）> `.p-plppkg__imglist` > `ul.p-plppkg__imglist-row[data-plp-imglist]` > `li.p-plppkg__imglist-item`（style に `--w/--h/--crop-w/--crop-x/--crop-y`）> copy（`.p-plppkg__body--pc` / `--sp` を幅で出し分け）> `ul.p-plppkg__points` > style view `.p-plppkg__view[data-plp-view-root]`（差し替え対象: `img[data-plp-view="image"]`・`[data-plp-view="name"]`・`[data-plp-view="eng"]`・`[data-plp-view="desc"]`（中に段落 p））> `ul.p-plppkg__list[data-plp-style-list]` > `li.p-plppkg__card(.is-active)` > `button.p-plppkg__card-button[aria-pressed]`（`data-plp-style-image` = URL / `-name` / `-eng` / `-desc` = 段落を改行 `\n` で連結）> `.p-plppkg__more > a.c-btn.c-btn--black[href="#"]`
- img list（S6）: 列は `width: max-content` の flex、間隔 `--plp-imglist-gap`（PC 22.525vu で 5 枚ちょうど 1920。一巡の長さ = 5 枚の幅 + 間隔 5 つ = 1942.5 単位）。単位 `--plp-imglist-unit`（PC `--plp-vu` / SP `--plp-su × 94/209`）。4 枚目の下角丸は `:nth-child(5n + 4)` なので列を丸ごと複製すれば崩れない。外側 `.p-plppkg__imglist` が overflow: hidden
- style list（S6）: `.p-plppkg__card.is-active` = `translateY(var(--plp-card-lift))`（PC -6vu / SP -6su）。ホバー・ドラッグ・transition はまだ無い（TOP の `.p-plans__card:hover` / `.is-dragging` の作法で足す。持ち上げ用の余白は list の padding-top で確保済み: PC 60 / SP 32、影は padding-bottom）。`data-scroll-row` は未付与（scroll-row.js を PLANS で読むときに足す）
- `--plp-color-white4-0` / `--plp-color-white1-0` / `--plp-color-black2-0`（グラデの透明端）・`--plp-display-lh`(1.29)・`--plp-copy-lh`・`--plp-body-lh` を :root に追加。SP のボタン色（黒の文字 #4d4d4d・白の線と文字 #fff）は SP カンプに合わせて `.p-plpselect__more` / `.p-plppkg__more` だけ上書き（`:not(:hover)`）
- `.p-plppkg` は `overflow-x: clip`（ページの横スクロール防止）

### S3 引き継ぎ（2026-09-30 generator）

- 追加ファイル: `template-parts/plans/section-highend.php`。データ関数 `exterior_exone_plans_highend()`（name / eng / main / sub / heading / body[3 行] / points（`lines` + SP だけ改行する項目は `'br' => 'sp'`）/ plans 2 件（key `designer` / `top-designer`・name・eng・file・body（同文・仮）・price（仮））/ more `#`）
- HTML: `section#high-end.p-plphe[data-plp-plan="high-end"]` > `.p-plphe__name[data-plp-plan-name]`（`.p-plphe__jp` + `h2.p-plphe__eng`。PC +270 / +294、SP +190 / +206 = PACKAGE と同じ。section の padding-top）> `.p-plphe__box`（`.p-plphe__band`・`.p-plphe__main` は PC のみ / `.p-plphe__sub` / `.p-plphe__copy` > `h3.p-plphe__heading` + `p.p-plphe__body`）> `ul.p-plphe__points` > `li.p-plphe__point`（`br.p-plphe__br-sp` は PC で非表示）> `.p-plphe__plans` > `.p-plphe__plan.p-plphe__plan--designer` / `.p-plphe__plan.p-plphe__plan--top-designer`（各ブロック = 1 要素。中は grid-area で `media` / `head`（jp + `h3.p-plphe__plan-eng`）/ `line` / `body` / `price`）> `.p-plphe__more > a.c-btn.c-btn--white[href="#"]`
- S5（③プラン名の下の入れ替わり）: HIGH-END でフェードインさせる「box 以降」は `.p-plphe__box` / `.p-plphe__points` / `.p-plphe__plans` / `.p-plphe__more` の 4 つ（name の兄弟要素すべて）。背景 `--color-black2` は section 自身
- S6（F-14）: `data-reveal` を付けるのは `.p-plphe__plan--designer`（右から → 新 variant）と `.p-plphe__plan--top-designer`（`data-reveal="left"`）。`.p-plphe` は `overflow-x: clip` 済みなので右へずらしてもページの横スクロールは出ない。ブロックは display: grid なので、variant の CSS で display を上書きしないこと
- PC 実測（1920、section 上端から）: 高さ 2838（カンプ 2728 + 110）/ box +493（帯 +760〜+1131、main img 160,+493 1017x572、sub 1264,+574、見出し 1254,+885）/ 円 +1211（x320 起点。PACKAGE と同じ並び）/ designer +1563・top designer +2053（写真 x160 / x970、文字 x1031 / x163）/ VIEW MORE +2614。1920 を超える画面では box・plans を 1920 幅で中央寄せ（`--plp-he-canvas-w`。帯は画面幅いっぱいのまま）
- SP 実測（375）: 高さ 1370（カンプ 1364。プラン名を +182 → +190 に揃えたぶん +8）/ sub 316x205 +264 / 見出し +497 / 円 +619 / designer +862・top designer +1054 / VIEW MORE +1257。top designer の写真は「英名 1 行 + 間隔 14」を除いた幅に縮む（375 で 141x79、x210〜351。右端は本文の右端に揃えた）
- 価格の右端: designer は本文右端より 28 内側（カンプ x1732）、top designer は右端揃え（カンプ x892）。カンプどおりで揃えていない

### S4 引き継ぎ（2026-09-30 generator）

- 追加ファイル: `template-parts/plans/section-process.php` / `section-beyond.php`。データ関数 `exterior_exone_plans_process()`（title / heading / image / steps 4 件 = num・label・file・img・crop_sp）/ `exterior_exone_plans_beyond()`（bg_pc / bg_sp / title / heading / body = 段落ごとの行配列 / points 4 件 = eng・label・file・img・size_pc・size_sp・crop（null = object-cover）・crop_sp（04 のみ）・opacity）
- Design Process の HTML: `section.p-plpprocess[data-section="plans-process"]` > `.p-plpprocess__inner`（1920 幅で中央。背景は section）> `.p-plpprocess__title`（`p.p-plpprocess__eng` + `h2.p-plpprocess__heading`）> `.p-plpprocess__image`（::after が下端グラデ）> `ol.p-plpprocess__steps` > `li.p-plpprocess__step`（grid-area で `num` / `label` / `card`。PC 縦積み・SP 左カード右番号）> `span.p-plpprocess__num` / `p.p-plpprocess__label` / `.p-plpprocess__card > img`（SP は `--crop-*` で縦切り抜き）/ `span.p-plpprocess__arrow`（01〜03 のみ。中に `--pc` / `--sp` の img。SP は 90° 回転）。動きは付けない
- Beyond the Plan の HTML: `section.p-plpbeyond[data-section="plans-beyond"]` > `picture.p-plpbeyond__bg`（source max-width 1024 = SP 縦構図）> `.p-plpbeyond__inner` > `.p-plpbeyond__title`（`p.p-plpbeyond__eng` + `h2.p-plpbeyond__heading`）> `.p-plpbeyond__body`（p 2 段落。PC は `br.u-br-pc` で 4 行）> **`ul.p-plpbeyond__points`（S6 はここに `data-reveal` を 1 つだけ付ける）** > `li.p-plpbeyond__point` > `.p-plpbeyond__art > .p-plpbeyond__art-frame(.is-crop)[style=--pc-w/--pc-h/--sp-w/--sp-h/--art-opacity/--crop-*]` > img / `p.p-plpbeyond__point-eng` / `p.p-plpbeyond__point-label`。ul は display: grid なので、reveal の CSS で display を上書きしないこと。`.p-plpbeyond` は overflow: hidden 済み（下からずらしても横スクロールは出ない）
- S5: 共通 2 セクションは出し分けの対象外（未選択・PACKAGE・HIGH-END のどれでも常に出す）。Beyond の直後は既存 `footer.p-footer`
- トークン: 見出しは `--plp-sec-*`（2 セクション共通）、Design Process は `--plp-proc-*`、Beyond は `--plp-by-*`。中身の幅 `--plp-sec-canvas-w`（PC 1920vu / SP 375su で中央。768 など su が頭打ちの幅でも中央に置く）
- PC 実測（1920、section 上端から）: Design Process 高さ 1470 / 英字 +160・和文 +216・パース 655,+341 610x266・番号 +648（中心 352.5 / 757.5 / 1162.5 / 1567.5 = カードの中心）・矢印 +696 x399/804/1209 幅 312・工程名 +754・カード 160〜1760 +828 385x482（4 枚同じ影）。Beyond 高さ 1844 / 英字 +160・和文 +216・本文 +330（4 行）・point +530（4 列 368 幅、x224〜1696）・英字 +690・和文 +752
- SP 実測（375）: Design Process 999 / パース 37.5,+152 300x130 / カード x20 +322 ピッチ 153 218x138 / 番号 x253（カード +33）/ 矢印 3 本とも長さ 70（工程名の 7 下 → 次の番号の 7 上）。Beyond 734 / 本文 +149 x24 w328 / point 英字 x119 +311 ピッチ 54 / 線画は幅 92 の枠の中央（中心 x66）
- 1920 を超える画面: 両セクションとも中身は 1920 幅で中央、背景は画面幅いっぱい。Beyond は写真を削らないよう `min-height: 100vw * 1844 / 1920`（2560 で高さ 2459。本文・point の位置は 1920 と同じで、写真の下に余白が伸びる）
- 揃えたもの（決定 C）: カード x+10・寸法・間隔 20 / 19 / 20・カード 02 の影 / SP 矢印の長さ / point の中心間隔（368 等間隔）・04 の中心ずれ / 線画の縦位置（中心を揃えた。03 は枠の上へ 2 はみ出す）/ SP の行ピッチ 54。本文・英字の不要な半角空白は除いた

### S5 引き継ぎ（2026-09-30 generator）

- 追加ファイル: `js/plans-select.js`（functions.php の `is_page('plans')` ブロックで plans.css の直後に filemtime・footer で enqueue。ハンドル `exterior-exone-plans-select`）/ `template-parts/plans/tabs.php`（page-plans.php の get_header() 直後で読む。`nav.p-plptabs[data-plp-tabs][hidden]` > `button.p-plptabs__tab[data-plp-tab="package|high-end"][aria-pressed][aria-controls]`）。CSS は plans.css の「プランの出し分け・選択の演出・タブ」ブロック（Beyond の後・比例ブロックの前）と、:root の `--plp-select-duration`(1.5s) / `--plp-select-ease`(easeOutCubic) / `--plp-hover-*` / `--plp-tab-*`、比例ブロックの `--plp-scroll-offset`（PC 0 / SP ヘッダー高）・`--plp-tab-*`・PC の `--plp-select-h: 100vh`
- **状態**: `body.is-plp-enhanced`（JS が動くときだけ）+ `body[data-plp-selected="none|package|high-end"]`。非表示は `display: none`（未選択 = 詳細 2 つ / 選択後 = PLAN select ともう一方の詳細）。JS 無効時は何も隠れない・タブは `hidden` のまま
- **イベント**: 詳細が表示された時点で `document` に **`plp:planchange`**（`detail: { plan, previous, animated }`）。選択の演出開始時（animated: true。中身はここから 1.5 秒でフェードイン）、タブ切り替えの開始時、ハッシュ付きで開いたとき・動きを減らす設定（animated: false）に投げる。S6 の img list の自動スクロール・style list（scroll-row）・`[data-reveal]` はこれを合図に「表示されたので測り直す / 動かし始める」とよい（IntersectionObserver は display:none → 表示で自然に発火する）
- 演出中だけ付くクラス: `body.is-plp-selecting` → 次フレームで `.is-plp-selecting-run`（選んだ半分 `.is-picked` / もう一方 `.is-covered`、名前の写し `.p-plpselect__flyer`（body 直下））。タブ切り替え中は切り替え先の詳細に `.is-plp-incoming(-run)`（absolute で今の詳細に重ねてフェード）。**演出中、詳細の直下の子（名前以外）は opacity 0 → 1 のトランジションを受ける**ので、S6 で詳細の直下の子に opacity のトランジションを付ける場合はぶつからないようにすること（`[data-reveal]` は孫以下に付ければ干渉しない）。終了後は演出用の要素・インラインスタイル・クラスは残らない
- 演出中は PLAN select に `margin-bottom: -高さ` を掛けて詳細を同じ上端に重ね、詳細には「PLAN select の範囲 + 円」の mask、共通 2 セクションは `--plp-shift`（body のインライン。終了時に消す）で引き上げて、SP で画面が PLAN select より縦長のときも下の帯が円に覆われるまで元の Design Process を見せる
- 円の広がりは `@property --plp-circle-r`（`<length>`）のトランジション + mask-image（radial-gradient）。@property 非対応ブラウザでは円が即時に全面になり、ほかの動きはそのまま
- スクロール位置合わせは `scroll-margin-top: var(--plp-scroll-offset)` を読んで `window.scrollTo({behavior:'instant'})`（html の smooth を効かせない）。`history.scrollRestoration = 'manual'`
- ホバー拡大は `@media (hover: hover) and (prefers-reduced-motion: no-preference)` で `.p-plpselect__media img` に scale(1.05)・0.6s
- 選択後、PLAN select にフォーカスがあった場合は選んだ詳細の section（tabindex="-1"、枠なし）へフォーカスを移す
- タブ: PC 1215,99 307x55 x2（右端 1842・下端 154）/ SP 0,45 188x32 x2（12px）。z-index は `--z-header`（SP ドロワーの下になることを確認済み）。**要確認（ユーザー）**: HIGH-END 選択時の非選択タブ（rgba(0,0,0,.3) on #1a1a1a）。PC はヘッダーのグラデの上なので読めるが、SP は地がほぼ背景と同化する（文字 #f5f5f5 は読める）。スクリーンショット: scratchpad `s5/1920-tab-highend.png`・`s5/375-tab-highend.png`

### S6 引き継ぎ（2026-09-30 generator）

- 追加ファイル: `js/plans-imglist.js`（img list の自動スクロール）/ `js/plans-style.js`（style view の切り替え）。functions.php の `is_page('plans')` ブロックで plans-select.js の後に `plans-imglist.js` → `scroll-row.js`（ハンドル `exterior-exone-scroll-row` は TOP と同名。PLANS では TOP 側の読み込みは走らない）→ `plans-style.js` を filemtime・footer で enqueue。TOP の `plans-switch.js` / `scroll-row.js` / `scroll-reveal.js` 本体は無変更
- CSS: plans.css の「残りの動き（F-12〜F-15）」ブロック（S5 ブロックの後・比例ブロックの前）。:root に `--plp-imglist-screen-time`(40s。画面幅 1 つ分を流れる時間) / `--plp-style-fade`(= `--transition-base`) / `--plp-card-transition` / `--plp-shadow-color-hover`(.18)、比例ブロックに `--plp-card-lift-hover`(-12) / `--plp-card-shadow-hover`(0 16 40)。style.css に `[data-reveal="right"]`（`[data-reveal="left"]` の直後）
- img list: JS が列の li を丸ごと複製（`[data-plp-imglist-clone]`・aria-hidden）し、`ul[data-plp-imglist].is-running` に `--plp-imglist-shift`（1 巡 = 複製 1 組目の先頭位置。小数まで実測）と `--plp-imglist-duration`（= 40s × 1 巡 ÷ 枠の幅）をインラインで書いて `@keyframes plp-imglist-flow`（linear・infinite）で流す。組数は「1 巡ずらしても右端が埋まる」まで足す（PC 1025〜1920 は 3 組、2560 は 3 組、SP は 2 組）。詳細が display:none の間は枠の幅 0 で何もしない → ResizeObserver（+ `plp:planchange`）で表示時に開始。動きを減らす設定では複製もせず静止。実測: 1920 で 1 巡 1942.4px / 40.47s、375 で 873.6px / 93.2s（どの幅でも画面幅 1 つ分 ≒ 39.6s）、継ぎ目のずれ 0
- style list: `ul.p-plppkg__list` に `data-scroll-row` を付与。ホバー（`@media (hover: hover)`）で -12 + 濃い影。ドラッグ中（`.is-dragging`）はホバーの持ち上げだけ消し、選択中の -6 は残す（TOP は選択中も 0 に戻す。ここだけ TOP と違う）
- style view: カードを押すと `[data-plp-view-root].is-switching`（4 要素 `[data-plp-view]` が opacity 0 へ `--plp-style-fade`）→ 消え終わり + 画像の読み込みの両方を待って差し替え → 次フレームでクラスを外してフェードイン。連打は最後の 1 回だけ反映。選択中のカードを押しても何もしない。動きを減らす設定では即時
- フェードイン: `.p-plphe__plan--designer[data-reveal="right"]` / `.p-plphe__plan--top-designer[data-reveal="left"]` / `ul.p-plpbeyond__points[data-reveal]`。display:none → 表示の後も IntersectionObserver が発火することを 3 経路（#high-end・未選択から選択・PACKAGE からタブ）で確認済み（plp:planchange での手当ては不要だった）
- **JS 無効時**: `[data-reveal]` は style.css で最初 opacity 0 のため、S6 で付けた 3 つが JS 無効時に消えたままになる（AC-11-4 違反）ので、plans.css に `@media (scripting: none) { .is-plans-page [data-reveal] { 最終状態 } }` を追加した（PLANS ページだけ。他ページの JS 無効時の挙動は従来どおり）
- スクリーンショット・検証スクリプト: scratchpad `s6/`（imglist.mjs / seam.mjs / style.mjs / reveal.mjs / hs.mjs / top.mjs）

### S7 引き継ぎ（2026-09-30 generator）

- 変更ファイル: `css/plans.css` / `js/plans-select.js` / `functions.php`（PLANS 以外のページ・DX・TOP・支店・企業情報のファイルは無変更）。1920×1080・375×667 の主要要素の位置（3 状態）は修正前と一致（img list の流れの位相のみ差）
- 1920 を超える幅の寄せ方: PACKAGE にも `--plp-pkg-canvas-w`（PC 1920vu / SP 100%）を足し、style view を 1920 幅で中央、style list は `padding-left` を「中身の幅の左端 + 160」にした（右は画面端まで流れる）。img list の列は `margin: 0 auto`（流さないとき = 動きを減らす設定・JS 無効は中央、流れる列は画面より長いので左端から。流れ方は S6 のまま）。2560 実測: style view の文字 x504・写真 874〜2080、カード 01 x480（HIGH-END の内容の左端 480 と一致）
- 演出中の 1px の線: `--plp-shift` を「PLAN select の下端（画素に丸めた位置）− 共通セクションの上端」の実測にした（詳細の高さの整数丸めが原因だった）。あわせて演出中の詳細の地（下の層）を 1px 上から始め、SP で PLAN select の下端が端数の画素に来るとき（390×844 など）に出ていた横線も消した。1440×900・1366×768・375×667・390×844・768×1024 の両プランで確認
- 演出中（1.5 秒）はホイール・タッチ（touchmove）のスクロールを止める（`lockScroll`。キーボード・スクロールバーは止めない）
- SP の Beyond the Plan: 600 超の幅（`--sp-scale-max` が効く）では `--plp-by-pad-bottom` に「屋根から下 186 × (100vw/375 − su)」を足す（375〜600 は増分 0）。768 で和文下端と屋根の間 ≒ 35（375 の 22 × 1.6）
- JS 無効時の `[data-reveal]`: `@media (scripting: none)` に加え、`exterior_exone_plans_noscript_style()`（wp_head・`is_page('plans')` のときだけ）で `<noscript><style>` を出す
- 375 の Beyond 右端の白い縦線: 375×667・390×844 で Beyond を 150px ずつ送って右端 14px を画素で調べたが再現せず（写真の車庫の右の明るい空の帯が候補）
- 検証スクリプト: scratchpad `s7/`（measure.mjs = 位置の前後比較 / peek.mjs・peekshot.mjs = 演出中の線とホイール / touch.mjs / grid.mjs = 13 幅 × 3 状態 / flow.mjs = 通し / regress.mjs・antonio.mjs = 退行）。**lib.mjs のポートを 11000 番台の空き探しに変えた**（9300〜9900 は同時に動く別エージェントの Chrome とぶつかって止まった）。2560 幅の PNG のビューポート撮影は止まるので jpeg か clip で撮る

### 公開前チェックリスト（PLANS ページ）

- [ ] テイスト 02〜05（ナチュラル / アーバン / クール / スタイリッシュ モダン）の説明文を本番の文に差し替え（`inc/plans-data.php` の「仮」コメント箇所）
- [ ] デザイナープラン / トップデザイナープランの本文（2 件同文の仮）を差し替え
- [ ] 価格「¥30,000～」「¥100,000～」の意味と表記（税込 / 税抜・何に対する価格か・単位）を確認
- [ ] PLANS ページ内の VIEW MORE の遷移先。**PACKAGE 下は `https://www.exone-package.com/`（別タブ）に決定・反映済み（2026-09-30 ユーザー指示）**。HIGH-END 下は未定（現在 `#`）
- [ ] TOP・支店の PLANS セクションの VIEW MORE を `/plans/`（または `/plans/#package` 等）へ接続（現在は未接続）
- [ ] HIGH-END 選択時の非選択タブ（rgba(0,0,0,.3) on #1a1a1a。SP は地が背景とほぼ同化）の見え方。2026-09-30 にユーザーへ提示し「そのまま進めて」との返事だったため**カンプの色のまま**にしてある。公開前にもう一度見てもらう（scratchpad `s5/1920-tab-highend.png`・`s5/375-tab-highend.png`）
- [ ] 画像はすべて Figma から取得した仮画像（AI 生成を含む）。本番写真に差し替える場合は `images/plans/` に同名で置き、`images/plans/webp/` の同名 .webp も作り直す（`exterior_exone_plans_image()` が webp を優先）
- [ ] フォールバック用の PNG（`images/plans/*.png`）が大きい（配信は webp）。本番前に圧縮するか、webp だけにするかを決める
- [ ] Antonio 400: 同梱の可変フォント（wght 軸あり）で出せている（300 での代用なし）。`css/fonts.css` を再生成したら 400 の 2 ブロックを足し直す。支店ページの STATS の数字の太さが 400 に変わる点を確認
- [ ] 仕様書の Q1〜Q8（決定事項 B-2）をユーザーが確定（SP の PLAN select の高さ・ハッシュで開いたときの位置・タブの出るタイミング・タブのホバー / フォーカス・未選択へ戻る手段・JS 無効時のリンク・style list の選択の保持・タイトル / ナビのカレント）
- [ ] iPhone / Android 実機での確認（選択の演出・タブの追従・img list の流れ・カードのスワイプ。ここまでは headless Chrome のみ）
- [ ] 1025〜1279 の幅では 1920 比例で本文が 10px 前後まで小さくなる（他ページと同じ方針）。問題ないか確認
- [x] `.playwright-mcp/` の検証の残骸の削除（2026-09-30 削除済み）
- [ ] SP でテイストを切り替えると、下のカード列が 16px 上下に動く（01 の説明文だけ 4 行、仮文の 02〜05 は 3 行のため）。本番の説明文が決まったら、説明文の欄に最大行数ぶんの高さを確保する
- [ ] 検証用に起動した headless Chrome が残っていたら終了する（`pkill -f "headless=new --remote-debugging-port"`）

### S7 検証後の直接修正（2026-09-30）

- **演出中の 1px の線（S7 Evaluator の B-2。1366×768・390×844 DPR1 で残っていた）を解消**。原因は 2 つ: ① スクロール位置が端数（例 768.5）になると、マスクの掛かった要素（詳細・覆われる側の半分）とそうでない要素で描画位置の丸め方が食い違う ② PLAN select の上端・高さが端数（SP）。対処: `js/plans-select.js` の `alignTo()` がスクロール位置を整数に丸める／演出の間だけ `snapSelect()` が PLAN select の上端（`--plp-snap`）と高さ（`--plp-select-h`）を画素ちょうどに揃える（`css/plans.css` の `.is-plp-selecting .p-plpselect` と `[data-plp-plan]` に `top: var(--plp-snap)`）。終了時に body のインラインスタイルは消える
- 確認: 1366×768 / 1440×900 / 1280×720 / 1920×1080 / 1536×864 / 1600×900 / 390×844 / 375×667 / 412×915 / 360×800 / 768×1024 / 820×1180 × 両プランで、演出 0.25 秒時点の PLAN select 下端の前後の行に線なし。演出後の状態（プラン名 x960・+294、SP は x188・+206、演出用要素 0、タブ切り替え）は不変、コンソールエラー 0
- `.playwright-mcp/`（検証の一時ファイル）は削除済み（AC-16-9）
- S1〜S7 すべて Evaluator 合格。Figma との最終の見比べ（S7 の E）は、Figma デスクトップでファイルが開かれていなかったため未実施（S1〜S4 の各検証では照合済み）

### 未選択時の構成の変更（2026-09-30 ユーザー指示。A の「まだ選んでいないとき」と B-2 Q1 の SP を上書き）

- `/plans/` を開いたときは **FV → PLAN select の順で、PLAN select でページを終える**。プランを選ぶまで、共通 2 セクション（Design Process / Beyond the Plan）とフッターは出さない（＝それより下へスクロールできない）。選ぶと詳細 + 共通 2 セクション + フッターが現れる
- **SP も PLAN select を画面いっぱい**（ヘッダーの下〜画面の下端。`100dvh - ヘッダー`）。中身はカンプの寸法のまま上下中央
- 実装: `css/plans.css` の「プランの出し分け」ブロック（`[data-plp-selected="none"]` で `.p-plpprocess` / `.p-plpbeyond` / `.p-footer` を非表示、SP は `.is-plp-enhanced` で `--plp-select-h` を上書き）。JS 無効時は従来どおり全部縦に並ぶ。`#package` / `#high-end` で開いたときは従来どおり

### 2026-10-02 の変更（ユーザー指示）

- **ヘッダーの STORE ドロップダウンがプランのタブに隠れる** → タブを JS（`js/plans-select.js`）でヘッダーの中へ移し、ヘッダーの層の中で「地より上・ドロップダウン（z-index 1）より下」（`.p-header .p-plptabs { z-index: 0 }`）に。タブの位置・見た目・動きは不変、SP のドロワーの下に隠れる点も不変
- **FV の写真を差し替え**: カンプが `718:244`（背景 `718:242`。夕暮れの住宅と前庭、1920×1080）に変わったため、`images/plans/pc-fv-bg-718-242.jpg`（+ webp）に差し替え。SP カンプ（`586:1018`）は旧写真のままだが、指示により **SP も同じ写真**（切り抜き位置は従来の 39.58% で玄関まわり）。旧 `pc-fv-bg-586-403.*` は削除。PC はコピーの「暮らしに合わせて、外構を選ぶ」が明るい外壁に掛かる（カンプどおり暗幕なし）


## 2026-10-21 ラスティックモダンの追加

- ユーザー依頼により、TOP PLANS・PLANS PACKAGE の共有カードを6件に拡張。既存5件の後に「ラスティックモダン / RUSTIC」を配置し、PACKAGE 側は番号06・英名 RUSTIC MODERN。
- Figmaの追加ノード TOP `1071:40`・PACKAGE `1071:49`（ファイル `xtDW74NJNDD8ZmF63fIsCd`）を確認。元画像は1500×1049の透過PNGで `images/top/plans-card-6.png` に保存。TOPは342×221、PACKAGEは322×208の枠で上端基準のトリミング。
- Figmaに説明文が未掲載のため、ラスティック選択時の説明文は空とし、前のプランの文章を残さない。独自の仮文は追加していない。
- 本番用style.cssのテーマ名・バージョン・Requires PHP:7.4は保持。DB更新・テーマ切り替え・PHP変更なし。
- ローカルで両ページの6枚目表示、名称・画像切り替えとPC/SP表示を確認。PHP7.4構文チェックと差分の空白チェックに合格。
- 本番へ画像1点・関連6ファイルを反映し、配置後のハッシュ一致を確認。匿名アクセスでもTOP・PLANSとも6件、ブラウザでも両ページのラスティック選択と画像読み込み（1500px）を確認済み。

### 2026-10-21 ラスティックモダンの説明文追記

ユーザーより説明文の作成を一任されたため、未設定の説明文を次の2段落で補完。

> 落ち着いた色合いに、素朴な素材感と豊かな植栽を合わせたラスティックモダンプランです。
> 自然の温もりとすっきりとしたデザインが調和し、住まいをやさしく引き立てます。

PLANSのラスティック選択時に表示。TOPは従来どおりパッケージ共通の説明文。

### 2026-10-21 PACKAGE のスクロール画像にもラスティックを追加

ユーザー依頼により、住宅画像の自動スクロール列の末尾へ `images/top/plans-card-6.png` を追加して6枚構成にした。カード画像を再利用し、原版の縦横比を保って高さ209（1920基準）に揃える。既存のJSは枚数から一巡の距離・時間を計算するため変更不要。移動速度は維持し、一巡の時間のみ延びる。4枚目の下角の丸みを維持するCSS周期は6枚に更新。PHP7.4構文チェック、ローカルPC/SPで6枚＋複製の表示とアニメーション進行を確認。

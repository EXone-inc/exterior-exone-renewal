# Spec: WORKS（施工事例）一覧ページ `/works/` と詳細ページ `/works/<投稿名>/`

- 作成日: 2026-10-08
- 起案: 「WORKS（施工事例）の一覧ページ `/works/` と詳細ページ `/works/<投稿名>/` を新規実装したい。Figma `821:338`（PC 一覧 `821:113` / PC 詳細 `821:174` / SP `836:37` / guide `821:337`・`821:336`）。施工事例は既存の投稿タイプ `works` で管理画面から登録・更新・削除する。」
- デザイン元: Figma fileKey `msbO5SCyCUBUXQwXLUug4m` / 関連ノード:
  - 親 `821:338`「WORKS」
  - PC 一覧 `821:113`（FV `821:114` / 一覧ブロック Group 558 `821:120` / COLUMN section 14 `821:148`）
  - PC 詳細 `821:174`（FV `821:176` / 見出し Group 524 `821:181` / pamphlet `821:209` / 名前 `821:218`・サムネ `821:226`〜`821:228`・キャプション `821:223`〜`821:225`・下線 `821:219` / 本文 `821:222` / 一覧ブロック Group 559 `821:231` / COLUMN section 15 `821:184`）
  - SP `836:37`（中身は**詳細**。FV `836:38`・`836:45`〜`48` / 見出し `836:86`・`836:87` / pamphlet `836:77` / 名前 `836:84`・下線 `836:85`・サムネ Group 563 `836:90` / 本文 `836:76` / 絞り込み `836:88`・`836:89`・Group 564 `836:63` / 写真 Group 565 `836:50` / COLUMN Group 566 `836:95`）。一覧専用の SP カンプは無い
  - guide 一覧 `821:337`（`821:262` 色反転・画像クリックで詳細へ / `821:266` 支店ページと同じもの / `836:110` 二列に変更）、guide 詳細 `821:336`（`821:271` 右からフェードイン・ハイエンドと同じ・重ね順前面・サムネ＆右ページで切替 / 並び順の図 Group 561 `821:334` / `821:335` 以下遷移前と同じ）
- **正とする資料**: `docs/spec-20261008-works-page-decisions.md`（以下「決定」）。本仕様書と食い違う場合は決定が正。カンプの数値・文言・guide 原文は `docs/figma-works-page.md`（以下「設計メモ」）。画像は `wp-content/themes/exterior-exone/images/works/` に保存済み（設計メモ §9）

---

## 1. 背景・目的

- コーポレートサイトの「WORKS」「事例一覧」（メニュー・TOP の VIEW MORE・支店メニュー）の行き先 `/works/` が、現在はテーマの `index.php` の仮表示に落ちている。カンプどおりの一覧ページと詳細ページを用意し、外構を検討している来訪者がデザインテイストで事例を絞り込み、気になる事例のパンフレット風の詳細を見られるようにする
- 施工事例は担当者が WordPress 管理画面（既存の投稿タイプ `works`）で登録・更新・削除する。ページは投稿から組み立て、固定データにしない
- 詳細のパンフレット（めくり）はハイエンドページ `/plans/high-end/` の Gallery と同じ動きにする。めくりの部品を共通部品に切り出し、両ページで使う
- COLUMN は支店ページの部品を共通化して流用する

## 2. スコープ

### やること
- 一覧 `/works/`（`archive-works.php`）と詳細 `/works/<投稿名>/`（`single-works.php`）の新規作成。PC（1920 カンプ）・SP（375。一覧は詳細カンプからの補間）
- 一覧の全件表示（ページ送りなし）と、デザインテイスト（タクソノミー `works_package`）によるその場の絞り込み
- 詳細のパンフレット（4 枚・めくり・自動送り・進捗下線・サムネ 3 枚・キャプション）・名前・本文と、詳細内の一覧ブロック
- ハイエンドの Gallery のめくり部品を共通部品に切り出し、ハイエンドを共通部品に置き換える（見た目・動きは変えない）
- 支店ページの COLUMN を共通部品に切り出し、WORKS 一覧・詳細に配置する（支店ページの見た目は変えない）
- 管理画面の施工事例「画像（複数枚）」メタボックスの案内文の更新（入力欄は増やさない）
- ローカルのサンプルデータ投入（WP-CLI。12 件・各 4 枚・テイスト 6 種 × 2 件）
- ヘッダー（FV 重ね）・body クラス・JS 無効時の表示・支店モード（`?store=`）対応
- `CLAUDE.md` のフレーム対応表への WORKS 行の追記

### やらないこと（範囲外）
- ページ送り / もっと見る / 無限スクロール（決定 §4。件数が増えたら検討）
- 所在地（`_exterior_exone_works_area`）・ハッシュタグ（`works_tag`）・カテゴリータイプ（`works_category`）の表示
- 本物の事例データ・画像の調達（本番で管理画面から登録。カンプの画像は AI 生成の**差し替え前提の仮画像**）
- 3D パースの 3D 表示（model-viewer 等）。3Dパースは静止画
- タクソノミーのアーカイブ（`/works-package/<term>/` 等）のデザイン。今回は手を付けない
- 投稿タイプ `works`・タクソノミーの登録内容の変更、メタ項目（入力欄）の追加
- 既存の `css/works.css`（`.p-cworks`。支店 / DX の 1 事例部品）・`inc/works-data.php`・`template-parts/common/section-works.php`・`js/works-gallery.js` の変更
- TOP の WORKS セクション（`template-parts/top/section-works.php`）の変更（データ関数も含め触らない）

---

## 3. 機能一覧

> 検証幅: 主検証幅は **1920（PC カンプ）** と **375（SP カンプ）**。**768** は「破綻しない（横スクロール・はみ出し・重なり崩れが無い）」ことだけを見る。S1・S2 は PC を主に、SP の受け入れは S3 でまとめて行う（S1・S2 でも 375 で横スクロールが出ないことだけは確認する）。
> 「カンプ一致」は CLAUDE.md の「忠実再現」の定義（セクション構成・配置・配色・タイポグラフィ・余白感は一致必須、px 完全一致は求めない）で判定する。
> 以下の AC の投稿・画像の対応は §4「サンプルデータ」の表に従う（S1 で投入）。

---

### F-01: サンプルデータの投入と基準（ベースライン）の保存

- 概要: 既存 6 件 + 新規 6 件 = 12 件に、カンプの写真（一覧 12 枚 + 詳細 3 枚）を割り当て、テイストを 6 種 × 2 件で振る。あわせて、回帰確認用に変更前の基準（ハイエンド Gallery・支店 COLUMN）を保存する
- Figma ノード: 一覧の写真 `821:136`〜`821:147`（並び順は設計メモ §2.2 の表）/ 詳細の写真 `821:212`・`821:216`・`821:226`・`821:230`
- ユーザーストーリー: 開発者として、カンプと同じ 12 枚・同じ詳細写真で一覧と詳細を確認したい。なぜなら、ローカルの既存データは 6 件・各 1 枚しか無くカンプと比べられないから。
- 内容:
  - 手順と投入後の対応表は §4「サンプルデータ」に記載（Generator はコマンドを実行し、表の「投稿 ID」「添付 ID」欄の空欄を実際の値で埋める）
  - **S1 のコード変更より前に**、`docs/baseline-20261008-works/` に基準を保存する:
    - `/plans/high-end/` の Gallery セクション（`[data-section="highend-gallery"]`）のスクリーンショット: 1920 / 375、`prefers-reduced-motion: reduce` をエミュレートした状態（自動送りが止まり進捗下線が全幅で静止 = 毎回同じ絵になる）
    - 同セクションの左ページ・右ページ・下線・タブ 5 個の位置と寸法（getBoundingClientRect の JSON）: 1920 / 375
    - `/store/aomori/` の COLUMN（`[data-section="store-column"]`）のスクリーンショット（1920 / 375）と outerHTML
- 受け入れ基準:
  - [x] AC-01-1: `wp post list --post_type=works --post_status=publish --orderby=date --order=DESC --fields=ID,post_name,post_date` の結果が 12 行で、並び順・投稿名・日時が §4 の表と一致する（予約投稿 `future` が 0 件）
  - [x] AC-01-2: 12 件それぞれの `_exterior_exone_works_gallery` が 4 つの添付 ID のカンマ区切りで、1 枚目が表の「1 枚目」の画像、2〜4 枚目が全件共通の 外観写真 / 3Dパース / 図面 の添付 ID（`wp post meta get <ID> _exterior_exone_works_gallery`）
  - [x] AC-01-3: `wp post term list <ID> works_package --fields=name` が各投稿で表の「テイスト」1 件だけを返し、6 ターム × 2 件になっている
  - [x] AC-01-4: 「トップページに記載する」（`_exterior_exone_works_on_top`）は既存 6 件で投入前と同じ（ID 26〜30 = 1、ID 31 = 無し）、新規 6 件は無し
  - [x] AC-01-5: 既存 6 件（ID 26〜31）のタイトル・投稿名・本文（post_content）が投入前と変わっていない
  - [x] AC-01-6: `docs/baseline-20261008-works/` に上記の基準ファイル（スクリーンショット 4 枚・寸法 JSON 2 本・outerHTML 1 本）が存在し、作成日時が S1 のテーマ変更より前

---

### F-02: 一覧ページ（FV・絞り込み・写真グリッド）と、データ・読み込みの土台

- 概要: `/works/` を `archive-works.php` で表示する。FV（写真 +「WORKS」+ コピー）→ 見出し「Filter by Design Style / デザインテイストで絞り込む」→ 絞り込みボタン 6 個 → 写真グリッド（PC 3 列）。全件表示・日付の新しい順。ボタンを押すとページ内でそのテイストの事例だけを表示する
- Figma ノード: PC 一覧 `821:113`（FV `821:114`〜`118` / 見出し `821:127`〜`129` / ボタン `821:121`〜`126`・文言 `821:130`〜`135` / 写真 `821:136`〜`821:147`）/ guide `821:262`
- ユーザーストーリー: 外構を検討している来訪者として、好みのデザインテイストで施工事例を絞り込んで一覧したい。なぜなら、自分の家に合う雰囲気の事例だけを効率よく見比べたいから。
- 含むもの（構造の宣言。実装方法は Generator が決める）:
  - **一覧の取得**: `works` の投稿タイプアーカイブのメインクエリだけ、全件（件数無制限）・日付の新しい順にする（`pre_get_posts`。管理画面・他のクエリ・TOP の WORKS カード・お知らせ一覧には影響させない）
  - **データ**: `inc/works-page-data.php` を新設し `functions.php` から読み込む。持つもの（関数名は推奨）:
    - テイストの並び順表（クールモダン → アーバンモダン → シンプルモダン → ラスティックモダン → スタイリッシュモダン → ナチュラルモダン）と英名対応表（Cool Modern / Urban Modern / Simple Modern / Rustic Modern / Stylish Modern / Natural Modern）。表に無いタームはボタンの末尾に名前順、英名は名前そのまま（決定 §3）
    - ボタン用のテイスト一覧 `exterior_exone_works_page_tastes()`: `works_package` の全ターム（**公開事例が 0 件のタームも含める** = 0 件表示に到達できるように）を上の順で、各 {slug, name, eng}
    - 投稿 → カード `exterior_exone_works_page_card( $post )`: {id, title, url, image_id, tastes（その投稿に付いたタームの slug の配列）}。url は `exterior_exone_store_link( get_permalink() . '#works-detail' )`（支店モードでは `?store=<slug>` が付き、`#works-detail` はその後ろに残る）。image_id = ギャラリー 1 枚目（無ければ 0）
    - 全件のカード `exterior_exone_works_page_cards()`（詳細ページ内の一覧ブロック用。並びはアーカイブと同じ）
    - FV `exterior_exone_works_page_fv()`: 画像 `images/works/pc-fv-bg-821-116.jpg`、英字「WORKS」、和文「理想の暮らしを描く、外構のご提案事例」（SP は「理想の暮らしを描く、」/「外構のご提案事例」の 2 行）
    - 絞り込み `exterior_exone_works_page_filter()`: 英字「Filter by Design Style」、和文「デザインテイストで絞り込む」、0 件の文言「該当する事例はありません」
    - COLUMN のコーポレート文言 `exterior_exone_works_page_column()`（使うのは S3。形は `exterior_exone_store_column()` と同じ {title, jp, lead[], lead_sp_join[]}。文言は F-05）
    - 詳細用 `exterior_exone_works_page_detail( $post )` とキャプション表（使うのは S2。F-04）
  - **テンプレート**: `archive-works.php`、`template-parts/works/section-fv.php`（`$args['data']` = FV データ、`$args['title_tag']` = `h1` | `p`）、`template-parts/works/heading.php`（`$args['eng']` / `$args['heading']` / `$args['tag']`。英字小 + 和文大の見出し。ハイエンドの `heading.php` と同じ組みだが highend.css に依存しない WORKS 用）、`template-parts/works/section-list.php`（見出し + 絞り込み + グリッド + 0 件表示。`$args['tastes']` / `$args['cards']` / `$args['filter']`）
  - 一覧の見出し階層: FV の「WORKS」= `h1`、「デザインテイストで絞り込む」= `h2`
  - **CSS**: `css/works-page.css`（プレフィックス `.p-wkp*` / `--wkp-*`）。`:root` に幅で変わらない値、ファイル末尾の比例ブロックに長さ（PC `--wkp-vu = min(1px, 100vw / 1920)` 1025px 以上 / SP `--wkp-su = min(100vw / 375, var(--sp-scale-max))` 1024px 以下）。トークンは §4「新規トークン」の表。S1 で PC・SP 両方のトークンを置く（SP の受け入れは S3）
  - **JS**: `js/works-filter.js`（絞り込み。1 ページに複数の一覧ブロックがあっても独立に動く）
  - **読み込み・判定**（`functions.php`）:
    - `css/works-page.css`・`js/works-filter.js` は `is_post_type_archive('works') || is_singular('works')` のときだけ読む（CSS はテーマ本体の後）
    - body クラス `is-works-page`（一覧・詳細とも）を足す
    - `exterior_exone_has_fv_header()` に一覧・詳細を足す（PC は既存の半透明ヘッダーを FV に重ねる。支店モードは既存どおり黒い追従ヘッダー）
    - JS 無効時に `[data-reveal]` を最初から表示する `<noscript>` スタイル（PLANS / ハイエンドと同じ作法。対象 `.is-works-page`）
  - **メタボックスの案内文**（`inc/meta-boxes.php` の「画像（複数枚）」の説明のみ。入力欄は増やさない）: 「1 枚目 = メイン（トップページ・一覧のカード・詳細の左ページ）/ 2 枚目 = 外観写真 / 3 枚目 = 3Dパース / 4 枚目 = 図面。4 枚登録してください（5 枚目以降は詳細ページに出ません）。ドラッグで並び替えできます。」
- 表示仕様:
  - FV: 写真を全面に敷き（PC 1920×1080 相当、暗幕なし）、中央に「WORKS」（Antonio SemiBold 120 / ls-3 / white2）と和文（Noto Bold 36 / lh1.5 / ls3 / white3）。組版は PLANS / ハイエンドの FV と同じ（`template-parts/plans/section-fv.php` + `css/plans.css` 85〜143 の構造を流用し、写真と文言を替える）。FV の画像は `fetchpriority="high"`・`alt=""`
  - 見出し: FV 下端 +160 に英字（SF Pro Medium 32 / lh1.12 / ls1 / gray2）、+55.86 に和文（Noto Bold 40 / lh1.6 / ls2 / white2）。中央揃え
  - ボタン 6 個: 見出し下端 +80、コンテンツ幅 1600（x160〜1760）を 6 等分・間隔 16、高さ 69、角丸 5、透明地 + 1px white2 枠、文字 Noto Medium 20 / lh1.6 / ls1 / white2。文言は**タームの名前**（「ナチュラル」ではなく「ナチュラルモダン」）。押下中（`aria-pressed="true"`）は色反転 = 白地（white2）+ 黒文字（black2）
  - グリッド: ボタン下端 +60、3 列、各 510×288 相当（列間 35・行間 36）、角丸 5、影 0 12 32 rgba(0,0,0,.8)、写真は cover。**文字は出さない**。写真はギャラリー 1 枚目（`wp_get_attachment_image( $id, 'large', false, [ 'alt' => タイトル, 'loading' => 'lazy', class ] )`。リンクの中身が画像だけなので alt に投稿タイトルを入れてリンク名にする）。画像が 0 枚の投稿は同じ大きさのグレー（gray2）のプレースホルダ（リンクには `aria-label` = タイトル）
  - ページ地: FV の下から COLUMN の上まで black2。グリッド下端 → 次のセクション 160
  - 絞り込みの挙動（決定 §3）: 単一選択のトグル。初期は全件・全ボタン `aria-pressed="false"`。押したボタンだけ `true` にし、そのタームが付いていないカードを非表示（短いフェード。トークン `--wkp-filter-fade`）、残ったカードは詰めて並ぶ。同じボタンをもう一度押すと解除して全件。別のボタンを押すと切り替わる。表示が 0 件のときは「該当する事例はありません」を出す（`aria-live="polite"` の領域）。JS 無効時は全件表示でボタンは効かない
  - ホバー: 既存の作法（カードのリンクは `a:hover` の不透明度 0.7。ボタンは押下前のホバー表現なし）
  - エスケープ: 文字列は `esc_html`、URL は `esc_url`、属性は `esc_attr`。改行を入れる文言は 1 行ずつ `esc_html` して `<br>` でつなぎ、`wp_kses`（`br` のみ許可）で出す
- 受け入れ基準:
  - [x] AC-02-1: `curl -s -o /dev/null -w '%{http_code}' http://exterior-exone-renewal.local/works/` が 200。body に `post-type-archive-works` と `is-works-page` と `has-fv-header` のクラスが付く
  - [x] AC-02-2: 1920 で `/works/` を開くと、`h1` の文字が「WORKS」、その下に「理想の暮らしを描く、外構のご提案事例」、FV の写真が `pc-fv-bg-821-116.jpg`。ヘッダーが FV の写真に重なる（ヘッダーの下端が FV の上端より下）
  - [x] AC-02-3: 見出し「Filter by Design Style」と `h2`「デザインテイストで絞り込む」が表示される
  - [x] AC-02-4: 絞り込みボタンが 6 個あり、文言が左から「クールモダン / アーバンモダン / シンプルモダン / ラスティックモダン / スタイリッシュモダン / ナチュラルモダン」、すべて `button` 要素で `aria-pressed="false"`、6 個が 1 行に並ぶ（6 個の top が同じ）
  - [x] AC-02-5: グリッドのカード（リンク）が **12 個**（`wp option get posts_per_page` の値より多いこと = 全件表示を確認）。ページ送りのリンクが無い。`/works/page/2/` は 404
  - [x] AC-02-6: カードの並びが §4 の表の順（1 枚目の画像のファイル名 grid-01 → grid-12）。1920 で 3 列 × 4 行（1 行目 3 個の top が同じ、4 個目が 2 行目の左端）
  - [x] AC-02-7: 各カードの href が `http://exterior-exone-renewal.local/works/<投稿名>/#works-detail`（例: 1 個目 = `/works/works-sample-01/#works-detail`）。カード内の img の alt = 投稿タイトル、カードに文字（テキストノード）が無い
  - [x] AC-02-8: 「クールモダン」を押すと、そのボタンが `aria-pressed="true"`・白地 + 黒文字（computed の background-color が rgb(254,254,254)、color が rgb(26,26,26)）になり、他 5 個は `false`。1 秒以内に、表示中のカードが表の「クールモダン」の 2 件だけになる（他 10 件は display:none または hidden）
  - [x] AC-02-9: 続けて「クールモダン」をもう一度押すと全ボタン `false`・カード 12 件表示に戻る。「クールモダン」→「アーバンモダン」と押すと、押下はアーバンモダンだけに移り、アーバンモダンの 2 件だけになる
  - [x] AC-02-10: 0 件表示: `wp term create works_package テスト --slug=test-empty` でタームを足すと `/works/` の 7 個目（末尾）に「テスト」ボタンが出る。押すとカードが 0 件になり「該当する事例はありません」が表示される。もう一度押すと消えて 12 件に戻る（確認後 `wp term delete works_package <ID>` で削除し、ボタンが 6 個に戻る）
  - [x] AC-02-11: JS を無効にして `/works/` を開くと、カード 12 件がすべて見え、FV・見出しなど `[data-reveal]` の要素が表示されている（opacity 1）
  - [x] AC-02-12: 画像 0 枚の投稿: 01 の投稿のギャラリーを一時的に空にすると（`wp post meta delete <ID> _exterior_exone_works_gallery`）、1 個目のカードが同じ大きさのグレーの枠になり、リンクの accessible name が投稿タイトル。確認後 §4 の値に戻す
  - [x] AC-02-13: 回帰: TOP `/` の WORKS カードは「トップページに記載する」の付いた投稿が新しい順に最大 5 件（ID 30 → 29 → 28 → 27 → 26）で、各カードの href はハッシュ無しの詳細 URL。`/news/` の件数は `posts_per_page` どおり
  - [x] AC-02-14: `/works/` の HTML に `css/works-page.css` と `js/works-filter.js` が含まれ、`/`・`/plans/`・`/plans/high-end/`・`/store/aomori/` の HTML には含まれない
  - [x] AC-02-15: 管理画面の施工事例の編集画面（または `wp eval 'exterior_exone_render_works_gallery_box( get_post( 26 ) );'` の出力）に、案内文「1 枚目 = メイン（トップページ・一覧のカード・詳細の左ページ）/ 2 枚目 = 外観写真 / 3 枚目 = 3Dパース / 4 枚目 = 図面。」が含まれ、入力欄の数は変更前と同じ
  - [ ] AC-02-16: 1920 のスクリーンショットで、FV・見出し・ボタン・グリッドの構成・配置・配色・タイポグラフィ・余白感が `821:113` の y0〜2989 と一致（px 完全一致は求めない）。375 で横スクロールが出ない（`document.documentElement.scrollWidth <= 375`）
  - [x] AC-02-17: `/works/` のコンソールにエラーが出ない

---

### F-03: めくり部品の共通化（ハイエンド Gallery の切り出し）

- 概要: ハイエンドの Gallery のうち「本」（左右のページ・写真の重ね・綴じ目の影・落ち影・めくれる紙と短冊・当たり判定・自動送り・進捗下線・動きを減らす設定）を共通部品に切り出し、ハイエンドをその部品に置き換える。**ハイエンドの見た目と動きは変えない**。タブ・番号・英名・コピーはハイエンド側に残し、共通部品とつなぐ
- Figma ノード: ハイエンド PC `713:164`（pamphlet `713:170`・gallery name `713:195`・btn list `713:179`）/ SP `733:380`〜`404` / guide `718:245`（WORKS 側は F-04）
- ユーザーストーリー: 開発者として、めくりの部品を 1 か所で保守したい。なぜなら、ハイエンドと WORKS で同じ動きを 2 重に持つと、片方だけ直して食い違うから。
- 構成（Generator が迷わないための対応表）:

  **ファイル**

  | 役割 | 共通部品（新規） | ハイエンド側（変更後） | WORKS 側（F-04） |
  |---|---|---|---|
  | 本のマークアップ | `template-parts/common/flipbook.php` | `template-parts/highend/section-gallery.php` が呼ぶ | `template-parts/works/section-detail.php` が呼ぶ |
  | 本の CSS | `css/flipbook.css` | `css/highend.css` から本の部分（現 800〜1038 のうち本・ページ・写真・綴じ目・落ち影・めくれる紙・短冊・当たり判定、1259〜1262 の写真のクロスフェード）を移す | — |
  | 本の JS | `js/flipbook.js` | `js/highend-gallery.js` は残し、タブ・番号/英名/コピーの差し替えとフェード・タブの押下状態だけを受け持つ連携用に縮める | `js/works-detail.js`（新規。サムネ・キャプション） |

  **クラス・属性の対応（据え置き / 改名）**

  | 現行（highend） | 変更後 | 置き場 |
  |---|---|---|
  | `.p-hepgal__book[data-hep-gallery]` | 本の要素に `.c-flipbook` と `[data-flipbook]` を付ける。ハイエンドは配置用に `.p-hepgal__book` を**併記して残す**（余白・左位置は highend.css に残す） | 共通 / highend.css |
  | `.p-hepgal__page` `--left` `--right` | `.c-flipbook__page` `--left` `--right`（改名） | flipbook.css |
  | `.p-hepgal__photo`（`.is-current`） | `.c-flipbook__photo`（`.is-current` は据え置き） | flipbook.css |
  | `.p-hepgal__fold` `--left` `--right` | `.c-flipbook__fold` `--left` `--right` | flipbook.css |
  | `.p-hepgal__cast` / `__leaf` / `__strip` / `__leaf-front` / `__leaf-back` / `__leaf-img` | `.c-flipbook__cast` / `__leaf` / `__strip` / `__leaf-front` / `__leaf-back` / `__leaf-img` | flipbook.css |
  | `.p-hepgal__hit` `--next` `--prev` | `.c-flipbook__hit` `--next` `--prev` | flipbook.css |
  | `.p-hepgal__num`（本の中） | `.c-flipbook__num`（共通部品の任意要素。`$args['num']` があるときだけ出す）。見た目（大きさ・位置）は highend.css に残す | 共通 / highend.css |
  | `.p-hepgal__caption` `__name` `__eng` `__copy` `__progress` `__tabs` `__tab`、`[data-hep-text]` | **据え置き**（ハイエンド固有） | highend.css |
  | `data-hep-flip` / `data-hep-progress`（検証用） | `data-flipbook-flip`（`next` / `prev`）/ `data-flipbook-t`（0〜1）に改名。加えて現在の写真の番号 `data-flipbook-current`（0 始まり）を常に付ける | flipbook.js |
  | `--hep-leaf-w` / `--hep-leaf-front` / `--hep-leaf-back`（JS が書く） | `--flipbook-leaf-w` / `--flipbook-leaf-front` / `--flipbook-leaf-back` | flipbook.js / flipbook.css |

  **トークンの置き場**

  | トークン | 値 | 置き場 |
  |---|---|---|
  | `--flipbook-interval` | 5s（現 `--hep-gal-interval`） | flipbook.css `:root` |
  | `--flipbook-flip` | 1.2s（現 `--hep-gal-flip`） | 同上 |
  | `--flipbook-text-fade` | 0.4s（現 `--hep-gal-text-fade`。ハイエンドの文字と WORKS のサムネのフェードで参照） | 同上 |
  | `--flipbook-crossfade` | 0.4s（現 `--hep-gal-crossfade`） | 同上 |
  | `--flipbook-cast` / `--flipbook-cast-blur` | rgba(0,0,0,.33) / 12px（現 `--hep-gal-cast*`） | 同上 |
  | `--flipbook-fold-shade` | rgba(0,0,0,.5)（現 `--hep-fold-shade`） | 同上 |
  | `--flipbook-shadow` | 0 12px 32px rgba(0,0,0,.8)（左ページの影。既定値） | 同上 |
  | `--flipbook-page-w` / `--flipbook-page-h` / `--flipbook-fold-left` / `--flipbook-fold-right` / `--flipbook-perspective` | インスタンスごとの寸法。共通 CSS は値を持たず、各ページの CSS が本の祖先要素で与える | ハイエンド: `.p-hepgal` で `var(--hep-gal-page-w)` 等を橋渡し（`--hep-gal-*` の値は highend.css の比例ブロックに据え置き）/ WORKS: `.p-wkpdetail` で `--wkp-book-*` を橋渡し |

  - 移した `--hep-gal-interval` / `--hep-gal-flip` / `--hep-gal-cast` / `--hep-gal-cast-blur` / `--hep-gal-text-fade` / `--hep-gal-crossfade` / `--hep-fold-shade` は highend.css から削除し（使っているのは Gallery だけ）、`[data-hep-text]` のフェードは `--flipbook-text-fade` / `--flipbook-crossfade` を参照する（値の定義を 1 か所にする）
  - JS は時間のトークンを本の要素の computed style から読む（ページ側で上書きできるように）

  **共通部品の `$args`**（`template-parts/common/flipbook.php`）
  - `id`（必須。本の要素の id。当たり判定・タブ・サムネの `aria-controls` の参照先）
  - `photos`（必須。配列。各要素は **添付 ID**（`attachment_id`）か **テーマ内画像**（`src` + `width` + `height`）のどちらか + `alt`）。添付は `wp_get_attachment_image()`（サイズは `$args['size']`、既定 `full`）、テーマ内画像は `esc_url` した `img`
  - `size`（添付のサイズ。既定 `full`）
  - `loading`（先頭の左ページ・右ページの写真は `eager`、それ以外は `lazy`。既定はこの振り分け）
  - `right`（右ページを押したときの動き。`prev` = 左へめくって前へ（ハイエンド。既定）/ `next` = 右へめくって次へ（WORKS））
  - `progress`（進捗下線の要素の id。本の外にある下線を JS が伸ばす）
  - `num`（任意。ハイエンドの番号「01」）
  - `labels`（当たり判定の `aria-label`。既定「次の写真へ」「前の写真へ」）
  - サーバー出力は 1 枚目の状態（左ページ = 1 枚目、右ページ = 2 枚目に `.is-current`、左ページの非表示の写真は `aria-hidden="true"`、右ページの写真は `alt=""`）

  **JS の受け渡し**（`js/flipbook.js` ⇔ ページ側の連携 JS）
  - 本の要素に対して、ページ側から「この番号の写真へ、この向きでめくる」を指示できる（カスタムイベント `flipbook:go`、detail = {index, dir}。dir 省略時はハイエンドのタブと同じ規則 = 番号が大きければ next、小さければ prev）
  - めくり開始・終了を本の要素で通知する（`flipbook:start` detail = {from, to, dir} / `flipbook:end` detail = {current}）。動きを減らす設定のクロスフェードでも同じ順で通知する
  - めくり中（＋文字のフェードが終わるまで）の操作は受けない（現行と同じ）
  - 写真が 2 枚以上で動く。1 枚なら当たり判定・自動送りは無効で、下線は全幅で静止
  - ハイエンドの発動条件「写真の枚数 = タブの数」は highend-gallery.js（連携側）に移す
- 読み込み（`functions.php`）:
  - `css/flipbook.css`（ハンドル `exterior-exone-flipbook`）と `js/flipbook.js`（同名ハンドル）は `is_page('high-end') || is_singular('works')` のときだけ読む
  - `css/highend.css` と `css/works-page.css` は flipbook.css の後に読む（依存に入れる）
  - `js/highend-gallery.js` は `is_page('high-end')` のときだけ、`js/flipbook.js` に依存して読む
- 受け入れ基準:
  - [x] AC-03-1: `/plans/high-end/` の HTML に `css/flipbook.css` と `js/flipbook.js` と `js/highend-gallery.js` が含まれ、`.c-flipbook[data-flipbook]` が 1 個あり、`[data-hep-gallery]` と `.p-hepgal__page` は 0 個
  - [x] AC-03-2: `/plans/high-end/` を `prefers-reduced-motion: reduce` で開いた Gallery のスクリーンショットが、1920 / 375 とも基準（F-01）と見た目で一致する（差分が出る場合は差分画像を添えて理由を説明できること。px 完全一致は求めない）
  - [x] AC-03-3: 左ページ・右ページ・下線・タブ 5 個の位置と寸法が、1920 / 375 とも基準の JSON と ±1px 以内で一致
  - [x] AC-03-4: 動き（1920、動きを減らす設定なし）: Gallery を画面に入れて 5 秒前後（±1 秒）で `data-flipbook-flip="next"` が付き、約 1.2 秒後に外れて `data-flipbook-current="1"`、番号「02」・タブ 2 個目が `aria-pressed="true"`、英名・コピーが 2 件目の文言になる。待機中は下線の `transform` の scaleX が 0 → 1 に増えていく
  - [x] AC-03-5: タブ 4 個目を押すと 1 回のめくり（`next`）で `data-flipbook-current="3"`、番号「04」。右の見切れページを押すと `data-flipbook-flip="prev"` でめくれて 1 つ前へ戻る。左ページを押すと `next` で 1 つ進む
  - [x] AC-03-6: 動きを減らす設定では、タブを押すと `data-flipbook-flip` が付かずに写真が切り替わり（クロスフェード）、自動送りしない
  - [x] AC-03-7: `/plans/high-end/` のコンソールにエラーが出ない。`css/highend.css` に `.p-hepgal__leaf` `.p-hepgal__strip` `.p-hepgal__hit` `--hep-gal-flip` の定義が残っていない（定義が flipbook.css の 1 か所だけ）
  - [x] AC-03-8: `/`・`/plans/`・`/store/aomori/`・`/works/` の HTML に `css/flipbook.css` / `js/flipbook.js` が含まれない

---

### F-04: 詳細ページ（見出し・パンフレット・名前・サムネ・キャプション・下線・本文・一覧ブロック）

- 概要: `/works/<投稿名>/` を `single-works.php` で表示する。FV（一覧と同じ）→ 見出し（英字 = テイスト英名、和文 = 投稿タイトル。`#works-detail`）→ パンフレット（F-03 の共通部品。左ページ 1172 + 右ページの見切れ 472）→ 名前（テイスト英名の大文字）+ サムネ 3 枚 + キャプション + 進捗の下線 → 本文 → 一覧ブロック（F-02 と同じ部品）。COLUMN は S3
- Figma ノード: PC 詳細 `821:174`（`821:176` FV / `821:181` 見出し / `821:209` pamphlet / `821:218`・`821:221` 名前 / `821:226`〜`228` サムネ / `821:223`〜`225` キャプション / `821:219` 下線 / `821:222` 本文 / `821:231` 一覧ブロック）/ guide `821:271`・並び順の図 `821:334`・`821:335`
- ユーザーストーリー: 来訪者として、気になった事例の外観・3Dパース・図面をパンフレットをめくる感覚で見たい。なぜなら、完成イメージと計画の中身をまとめて確かめたいから。
- 含むもの:
  - `single-works.php`、`template-parts/works/section-detail.php`（`$args['data']` = 詳細データ）、`js/works-detail.js`（サムネ・キャプション。`js/flipbook.js` に依存）。`js/works-detail.js` は `is_singular('works')` のときだけ読む
  - 詳細データ `exterior_exone_works_page_detail( $post )`（`inc/works-page-data.php`）: {eng（テイスト英名。無ければ空）, title（投稿タイトル）, name（英名の大文字。無ければ空）, photos（ギャラリーの **1〜4 枚目**。5 枚目以降は使わない。各 {attachment_id, caption}）, paragraphs（本文の段落）}
  - キャプション表（位置で固定）: 1 枚目 = なし（空）/ 2 枚目 =「外観写真」/ 3 枚目 =「3Dパース」/ 4 枚目 =「図面」
  - テイスト: 投稿に付いた `works_package` のうち並び順表で先頭のもの（オープン論点 Q6）。テイスト未設定なら英字の見出しと名前を出さない
  - 本文: post_content（素のテキスト）。空行で段落、段落内の改行は `<br>`。段落ごとに `esc_html` して組み、`wp_kses`（`p`・`br` のみ）で出す（オープン論点 Q5）
- 表示仕様（PC 1920。値は設計メモ §3）:
  - FV: 一覧と同じ写真・文言。ただし「WORKS」は `p`（詳細の `h1` は投稿タイトル）
  - 見出し（`id="works-detail"` のブロック）: FV 下端 +160（オープン論点 Q4）。英字 = テイスト英名（例「Cool Modern」SF Pro Medium 32 / gray2）、+55.86 に和文 = 投稿タイトル（`h1`。Noto Bold 40 / ls2 / white2）。中央揃え。アンカーで開いたとき見出しがヘッダーに隠れない
  - パンフレット: 見出し下端 +80、左端 x276、左ページ 1172.45×662.5（影 0 12 32 .8）、右ページは画面の右端まで（472 相当）で、次の写真を左ページと同じ幅で左揃えにして左端だけ見せる、綴じ目の影（左ページ右端 84.85・右ページ左端 46.28、透明 → 黒 50%）。写真は `wp_get_attachment_image( $id, 'full' )`、左ページの表示中の写真の alt =「<投稿タイトル>（<キャプション>）」（1 枚目は「<投稿タイトル>」）
  - 写真の並び（決定・guide の並び順の図）: **左ページ = 現在 N、右ページ = N+1、サムネ = N+1 / N+2 / N+3**（循環）
  - 操作（決定）: **サムネを押すと、その写真が左ページに来るように 1 回だけ右へめくる**（途中は飛ばす）。**右ページを押すと N+1 へ 1 回右へめくる**（ハイエンドの「前へ」とは逆。共通部品の `right = next`）。**左ページを押すと次へ**（ハイエンドと同じ）。自動送り 5 秒・めくり 1.2 秒・進捗の下線はハイエンドと同じ
  - 名前: テイスト英名の大文字（例「COOL MODERN」Antonio SemiBold 88 / ls-3 / white2）、左端 x315、左ページの下端を 61 またいで重なる
  - サムネ 3 枚（PC）: 211×120・角丸 5・影 .8、右端を本文の右端（x1602）にそろえ、間隔 15〜18、左ページ下端に 100 重なって 20 はみ出す。押せる（`button`。`aria-label` =「<キャプション>を表示」、キャプションが空なら「メインの写真を表示」、`aria-controls` = 本の id）。**図面（4 枚目）のサムネだけは白地（#ffffff）に写真全体を収める**（左右に余白。他は cover）。画像は `wp_get_attachment_image( $id, 'large', false, [ 'alt' => '', 'loading' => 'lazy' ] )`
  - キャプション（PC のみ）: 各サムネの下 +8、中央揃え、Noto Regular 14 / lh2 / ls1 / gray2。サムネに 1 枚目が回ってきたときは空欄（高さは保つ）
  - 下線: キャプション下端 +21、左端 x319 から画面の右端まで 1px gray2。自動送りの進捗を左から伸ばす（共通部品の `progress`）
  - 本文: 下線 +42、x319・幅 1283、Noto Regular 18 / 行高 36 / ls1 / gray2、左揃え
  - 重ね順（guide）: 名前・サムネ・キャプション・下線・本文はめくれる紙より**前面**（めくり中も隠れない・押せる）
  - 右からフェードイン（guide）: 名前・サムネ・キャプション・下線・本文に `[data-reveal="right"]`（既存 `js/scroll-reveal.js`）
  - サムネ・キャプションの差し替え: めくり開始でフェードアウト、めくり終了で新しい N+1〜N+3 に差し替えてフェードイン（ハイエンドの文字と同じ `--flipbook-text-fade`。オープン論点 Q1）。動きを減らす設定ではクロスフェード時間の半分ずつ
  - 写真の枚数が 4 未満（決定 §3）: ある枚数で動かす。サムネの数 = 写真の枚数 − 1（最大 3）。2 枚以上ならめくれる。1 枚なら静止（サムネなし・自動送りなし・下線は全幅で静止）。0 枚なら左ページ・右ページの位置にグレー（gray2）のプレースホルダ。キャプションは位置で固定のまま
  - 一覧ブロック: 本文下端 +120 に、F-02 と同じ部品（見出し・ボタン 6 個・全件のカード）。表示中の事例も含む（オープン論点 Q8）。絞り込みはこのページ内で動く
  - ページ地: FV の下から COLUMN の上まで black2
- 受け入れ基準:
  - [x] AC-04-1: `/works/works-sample-01/` が 200。body に `single-works` と `is-works-page` と `has-fv-header`。HTML に `css/flipbook.css`・`js/flipbook.js`・`js/works-detail.js`・`css/works-page.css`・`js/works-filter.js` が含まれ、`css/highend.css`・`js/highend-gallery.js` は含まれない
  - [x] AC-04-2: 1920 で `/works/works-sample-01/#works-detail` を開くと、`#works-detail` の要素の上端がヘッダーの下端以上・画面内にあり（FV は上にスクロールすると見える）、英字「Cool Modern」と `h1`「北欧風の住まいに映えるブラックのエクステリア」が表示される。ページ内の `h1` は 1 個
  - [x] AC-04-3: 初期状態: `[data-flipbook]` が `data-flipbook-current="0"`、左ページの `.is-current` の画像が grid-01、右ページの `.is-current` が 外観写真（detail-page-02）。サムネ 3 枚が左から 外観写真 / 3Dパース / 図面 の画像で、キャプションが「外観写真」「3Dパース」「図面」。名前が「COOL MODERN」
  - [x] AC-04-4: サムネ 2 枚目（3Dパース）を押すと、`data-flipbook-flip` が**1 回だけ**付いて外れ（`next`。MutationObserver で付いた回数 = 1）、めくり終了後に `data-flipbook-current="2"`、左ページ = 3Dパース、右ページ = 図面、サムネ = 図面 / grid-01 / 外観写真、キャプション =「図面」/ 空 /「外観写真」
  - [x] AC-04-5: 初期状態で右ページを押すと `next` のめくり 1 回で `current="1"`（左ページ = 外観写真、右ページ = 3Dパース、サムネ = 3Dパース / 図面 / grid-01）。左ページを押すと `next` で 1 つ進む
  - [x] AC-04-6: 自動送り: 操作せずにパンフレットを画面に入れておくと、5 秒前後（±1 秒）でめくれて `current` が 1 進み、4 枚目の次は 0 に戻る（循環）。待機中に下線の scaleX が 0 → 1 に増える。押した後も、めくり終了から 5 秒で次へ進む
  - [x] AC-04-7: 重ね順: めくり中（`data-flipbook-flip` がある間）に、サムネ 1 枚目の中心・名前の中心で `document.elementFromPoint` がそれぞれサムネ（またはその子）・名前（またはその子）を返す
  - [x] AC-04-8: めくり中に別のサムネや右ページを押しても無視され、めくりは 1 回で終わる
  - [x] AC-04-9: 動きを減らす設定では、サムネを押すと `data-flipbook-flip` が付かずに写真とサムネが切り替わり、自動送りしない。下線は全幅
  - [x] AC-04-10: 名前・サムネ・キャプション・下線・本文の要素が `data-reveal="right"` を持ち、スクロールで画面に入ると `is-inview` が付いて表示される。JS 無効では最初から表示され、パンフレットは 1 枚目の状態で静止する
  - [x] AC-04-11: 本文が 3 段落（`p` が 3 個）で、1 段落目が「ブラウンとホワイトのツートンカラーが印象的な住まいに、ブラックのカーポートと門柱を合わせた外構プラン。」で始まる。本文の HTML に `p`・`br` 以外のタグが無い
  - [x] AC-04-12: 写真 1 枚の投稿: 01 のギャラリーを一時的に grid-01 の 1 枚だけにすると、サムネが 0 個、左ページを押してもめくれない（`data-flipbook-flip` が付かない）、7 秒待っても `current="0"` のまま、下線は全幅。2 枚にするとサムネ 1 枚（外観写真）でめくれる。確認後 §4 の値に戻す
  - [x] AC-04-13: テイスト未設定: 01 のテイストを一時的に外すと（`wp post term remove`）、英字の見出しと名前が出ず（要素が無い）、`h1` は出る。確認後に戻す
  - [x] AC-04-14: 詳細内の一覧ブロック: 見出し「デザインテイストで絞り込む」・ボタン 6 個・カード 12 個があり、1 個目の href が `/works/works-sample-01/#works-detail`。「ナチュラルモダン」を押すとそのページ内で 2 件だけになり、URL は変わらない
  - [x] AC-04-15: 一覧 `/works/` の 3 個目のカードを押すと `/works/works-sample-03/#works-detail` に移動し、見出しが画面上部に見える状態で開く
  - [ ] AC-04-16: 1920 のスクリーンショットで、見出し〜本文〜一覧ブロックの構成・配置・配色・タイポグラフィ・余白感が `821:174` の y1080〜4244 と一致（px 完全一致は求めない）。375 で横スクロールが出ない
  - [x] AC-04-17: F-03 の AC-03-2〜03-6（ハイエンドの回帰）が S2 の最後にも合格する。`/works/works-sample-01/` のコンソールにエラーが出ない

---

### F-05: COLUMN の共通化と WORKS への配置

- 概要: 支店ページの COLUMN（`template-parts/store/section-column.php` + `.p-scolumn` + 見出し 3 点セット `.c-store-title` / `-jp` / `-lead`）を共通部品に切り出し、支店ページと WORKS 一覧・詳細の両方で使う。**支店ページの見た目・HTML は変えない**
- Figma ノード: PC 一覧 `821:148` / PC 詳細 `821:184` / SP `836:95`（いずれも guide `821:266`「支店ページと同じもの」。支店 PC `612:869` / SP `641:1447` と同値）
- ユーザーストーリー: 来訪者として、事例を見たあとに外構づくりのコラムへ進みたい。なぜなら、費用や選び方などの判断材料もあわせて知りたいから。
- 構成:
  - 部品: `template-parts/common/section-column.php`。`$args`: `column`（必須。{title, jp, lead[], lead_sp_join[]}。`exterior_exone_store_column()` と同じ形）、`section`（`data-section` の値。既定 `column`）。お知らせ（`news`）の最新 5 件を `exterior_exone_news_cards()` で出す（1 件も無ければ出さない）。カード・VIEW MORE のリンクは `exterior_exone_store_link()` 経由（現行どおり）。カード画像は現行どおり `wp_get_attachment_image( $id, 'medium_large', false, [ 'alt' => '', 'loading' => 'lazy', class ] )`。リードは 1 行ずつ `esc_html` して `<br>`（SP で消す改行は印付き）でつなぎ `wp_kses`（`br` + `class` のみ）で出す（現行どおり）
  - 支店ページ: `template-parts/store/section-column.php` は共通部品を `column = exterior_exone_store_column( $store )`・`section = store-column` で呼ぶだけにする（支店が無いと出さない挙動は据え置き）。**出力される HTML（クラス・`data-section="store-column"`・文言）は変更前と同一**
  - WORKS: 一覧・詳細とも一覧ブロックの下（グリッド下端 +160）に置く。`column` は支店モードなら `exterior_exone_store_column( exterior_exone_store_mode() )`、そうでなければ `exterior_exone_works_page_column()`。`section = works-column`
  - CSS: `css/column.css`（新規・ハンドル `exterior-exone-column`）へ、`.c-store-title` / `.c-store-jp` / `.c-store-lead`、`.p-scolumn*`（PC・SP・比例ブロックの COLUMN 部分）と、それらが使うトークン（`--st-vu` / `--st-su`、`--st-heading-gap` / `--st-heading-jp` / `--st-heading-lead` の基本値と PC・SP 値、`--st-column-title`、`--st-card-radius`、`--st-shadow`、`--st-bg-light`）を**移す**。**クラス名・トークン名は据え置き**（支店ページの他セクションが同じトークン・見出しクラスを使っているため）。store.css 側の定義は削除し、定義を 1 か所にする。各支店セクションでの `--st-heading-*` の上書き（例 store.css 4008〜4010）は store.css に残す
  - 読み込み: `css/column.css` は支店ページ・WORKS 一覧・詳細で読む。`css/store.css` は column.css に依存させて後に読む（支店ページの上書きが効く順）
- 文言:
  - コーポレート（`?store=` なし）: 英字「COLUMN」/ 和文「外構づくりに役立つ情報」/ リード 2 行:「外構費用の考え方やカーポートの選び方、目隠しフェンス、庭づくりなど、外構工事を検討している方に役立つ情報を発信します。」「地域の気候や住宅事情も踏まえながら、後悔しない外構づくりのポイントを分かりやすく解説します。」（SP は 2 段落。つなぎ無し）
  - 支店モード（`?store=<slug>`）: 支店ページと同じ「<地域>の外構づくりに役立つ情報」「…<地域>で外構工事を…」
  - VIEW MORE は PC のみ（SP は出さない。支店と同じ）
- 受け入れ基準:
  - [x] AC-05-1: `/store/aomori/` の `[data-section="store-column"]` の outerHTML が基準（F-01）と同一。1920 / 375 のスクリーンショットが基準と見た目で一致
  - [x] AC-05-2: `/store/aomori/` の他セクション（PLANS・REVIEWS・FLOW・FAQ・AREA など `.c-store-title` を使う見出し）の 1920 / 375 の見出しのフォントサイズ・余白（computed の font-size / margin-top）が変更前と同じ（Evaluator は S3 着手前に値を記録して比較）
  - [x] AC-05-3: `/store/aomori/` の HTML に `css/column.css` が `css/store.css` より前に含まれる。`css/store.css` に `.p-scolumn` と `.c-store-title` の定義が残っていない
  - [x] AC-05-4: 1920 で `/works/` と `/works/works-sample-01/` の一覧ブロックの下に COLUMN があり、「COLUMN」「外構づくりに役立つ情報」、リード 2 行（上記文言）、お知らせのカード（`news` の公開件数、最大 5 件）、VIEW MORE（href = `/news/`）が表示される。背景が白（#fefefe）
  - [x] AC-05-5: `/works/?store=aomori` の COLUMN の見出しが「青森の外構づくりに役立つ情報」、リードに「青森で外構工事を検討している方に」を含み、VIEW MORE の href が `/news/?store=aomori`、カードの href に `store=aomori` が付く
  - [x] AC-05-6: `/`・`/plans/`・`/plans/high-end/` の HTML に `css/column.css` が含まれない

---

### F-06: SP（一覧の補間・詳細）

- 概要: SP カンプ `836:37`（詳細）どおりに詳細を組み、一覧は詳細カンプからパンフレット部分を除いた形（FV → 絞り込み → グリッド 2 列 → COLUMN）で補間する。768 は SP の組みを `--sp-scale-max` で頭打ちにして破綻させない
- Figma ノード: SP `836:37`（FV `836:38`・`836:46`・`836:48` / 見出し `836:86`・`836:87` / pamphlet `836:77`〜`83` / 名前 `836:84` / 下線 `836:85` / サムネ `836:90`〜`94` / 本文 `836:76` / 絞り込み `836:88`・`836:89`・`836:63`〜`75` / 写真 `836:50` / COLUMN `836:95`）/ guide `836:110`
- ユーザーストーリー: スマートフォンの来訪者として、同じ内容を片手で見やすく見たい。なぜなら、外構の検討は移動中にスマホで行うことが多いから。
- 表示仕様（SP 375。値は設計メモ §4）:
  - FV: 375×723（上 45 はヘッダー）。写真は PC と同じ画像を横 33%・縦中央で切り抜き。「WORKS」Antonio 50 / ls-3 を y296、和文 Noto Medium 16 / lh1.8 / ls1 / white3 の 2 行「理想の暮らしを描く、」/「外構のご提案事例」を y370。PLANS / ハイエンドの SP FV と同じ扱い（ヘッダーは黒帯、写真の上端はヘッダーの裏）
  - 見出し: FV 下端 +80、英字 SF Pro Medium 14 / white3、+24.23 に和文 Noto Bold 16 / ls1（詳細は 2 行に折り返してよい）
  - パンフレット: 見出し下端 +40、左端 x25、左ページ 314.54×177.73、右ページは画面の右端まで（35.46）、綴じ目の影 22.76 / 12.42。めくり・自動送り・操作は PC と同じ
  - 名前: Antonio 32 / ls-1、x38、左ページ下端を 22 またぐ
  - 下線: 名前下端 +7、x39 から画面右端まで
  - サムネ: **下線の下** +12、x39、各 94.5×53.4・角丸 2・影 .8、間隔約 7。**キャプションは出さない**
  - 本文: サムネ下端 +12、x38・幅 320、Noto Regular 12 / lh1.6 / ls1 / gray2
  - 一覧ブロック: 本文下端 +80（一覧ページでは FV 下端 +80）に見出し（14 / 16）、+39 にボタン **2 列 × 3 段**（161×29・間隔 5・角丸 3・文字 Medium 10 / ls1）、+20 に写真グリッド **2 列**（決定。幅 327 を 2 列・間隔 7、カードは 510:288 の比率、角丸 2）、グリッド下端 +80 に COLUMN
  - COLUMN: 支店 SP と同じ（地 #f3f3f4、VIEW MORE なし、カードは横スクロール）
  - 768: SP の組みのまま `--sp-scale-max` で拡大が止まり、中央の 1 カラムに収まる。パンフレットの右ページは画面右端まで届く
- 受け入れ基準:
  - [x] AC-06-1: 375 で `/works/` を開くと、FV（「WORKS」+ 2 行の和文）→「Filter by Design Style」「デザインテイストで絞り込む」→ ボタン 6 個（2 列 × 3 段: 左列の 3 個の left が同じ、1 段目 2 個の top が同じ）→ カード 12 個（2 列 × 6 行）→ COLUMN の順に並ぶ。パンフレットは無い
  - [x] AC-06-2: 375 で絞り込みが PC と同じに動く（AC-02-8・02-9 と同じ操作で同じ結果）
  - [x] AC-06-3: 375 で `/works/works-sample-01/#works-detail` を開くと、見出し → パンフレット（左ページの右に見切れた右ページがあり、右ページの右端 = 画面右端 ±1px）→ 名前「COOL MODERN」→ 下線 → サムネ 3 枚（下線より下）→ 本文 → 一覧ブロック → COLUMN の順。キャプション「外観写真」等は表示されない（非表示または要素なし）
  - [x] AC-06-4: 375 でサムネ 2 枚目を押すと AC-04-4 と同じ結果になる。右ページ（見切れ部分）を押すと 1 つ進む
  - [x] AC-06-5: 375 の COLUMN の地が #f3f3f4、VIEW MORE が非表示、見出し「外構づくりに役立つ情報」
  - [x] AC-06-6: 375 / 768 / 1920 の `/works/`・`/works/works-sample-01/` で横スクロールが出ない（`scrollWidth <= innerWidth`）。768 で要素の重なり崩れ・はみ出しが無い
  - [ ] AC-06-7: 375 のスクリーンショットで、詳細の構成・配置・配色・タイポグラフィ・余白感が `836:37` と一致（写真グリッドは 2 列にした差分を除く）。一覧は AC-06-1 の構成で、`836:37` の FV・絞り込み・COLUMN と同じ組み

---

### F-07: 支店モード・仕上げ・回帰

- 概要: `?store=<slug>` で一覧・詳細を支店モードにし（既存の仕組み）、リンクに `?store=` を引き継ぐ。`CLAUDE.md` のフレーム対応表に WORKS を追記する。全体の回帰とコンソールエラー・横スクロールを確認する
- Figma ノード: —（支店ヘッダーは既存 `670:58`）
- ユーザーストーリー: 支店サイトから来た来訪者として、事例を見ている間も支店のヘッダーと支店への導線のままでいたい。なぜなら、問い合わせ先の支店が変わると迷うから。
- 受け入れ基準:
  - [x] AC-07-1: `/works/?store=aomori` で、`<html>` に `data-store-mode`、body に `is-store-mode`、ヘッダーに支店名のリンク（`.p-header__store`、href = `/store/aomori/`）がある（支店用のヘッダー）
  - [x] AC-07-2: `/works/?store=aomori` のカードの href が `/works/<投稿名>/?store=aomori#works-detail`。押すと詳細も支店モード（`.p-header__store` あり）で、見出しが画面上部に見える状態で開く
  - [x] AC-07-3: `/works/works-sample-01/?store=aomori` の一覧ブロックのカード・COLUMN のリンクに `store=aomori` が付く。絞り込みはページ内で動き、URL は変わらない
  - [x] AC-07-4: `/works/?store=unknown`（存在しない支店）はコーポレート表示（`data-store-mode` 無し、COLUMN の見出し「外構づくりに役立つ情報」）
  - [x] AC-07-5: `CLAUDE.md` のフレーム対応表に「WORKS（一覧・詳細）| PC 一覧 `821:113` / PC 詳細 `821:174` | `836:37`（詳細。一覧 SP は補間）。guide `821:337`・`821:336`」の行がある
  - [ ] AC-07-6: 回帰: `/plans/high-end/` の Gallery が基準と一致し動く（AC-03-2〜03-6 を再実行）、`/store/aomori/` の COLUMN が基準と同一（AC-05-1・05-2 を再実行）、TOP の WORKS カード（AC-02-13 を再実行）
  - [x] AC-07-7: `/`・`/company/`・`/dx/`・`/plans/`・`/plans/high-end/`・`/store/aomori/`・`/works/`・`/works/works-sample-01/`・`/works/?store=aomori` を 1920 / 375 で開き、コンソールエラーが 0 件、横スクロールが無い
  - [x] AC-07-8: `/works/` と詳細に `index.php` 由来の仮表示（`.l-section .entry` の記事リスト）が出ていない

---

## 4. 情報設計

### 表示面 / URL / テンプレート
- `/works/` … `archive-works.php`（投稿タイプ `works` のアーカイブ）。FV → 一覧ブロック（見出し・絞り込み・グリッド）→ COLUMN
- `/works/<投稿名>/` … `single-works.php`（投稿タイプ `works` の個別）。FV → 詳細（`#works-detail` の見出し・パンフレット・名前・サムネ・キャプション・下線・本文）→ 一覧ブロック → COLUMN
- `/plans/high-end/` … 既存 `page-high-end.php`。Gallery を共通部品に置き換え（見た目・動きは不変）
- `/store/<slug>/` … 既存 `page-store.php`。COLUMN を共通部品に置き換え（HTML・見た目は不変）
- 両面とも `?store=<slug>` で支店モード（既存 `inc/store-mode.php`）

### テンプレート部品
- `template-parts/works/section-fv.php`（`$args['data']`、`$args['title_tag']`）
- `template-parts/works/heading.php`（`$args['eng']` / `$args['heading']` / `$args['tag']`）
- `template-parts/works/section-list.php`（`$args['tastes']` / `$args['cards']` / `$args['filter']`）
- `template-parts/works/section-detail.php`（`$args['data']`）
- `template-parts/common/flipbook.php`（F-03 の `$args`）
- `template-parts/common/section-column.php`（`$args['column']` / `$args['section']`）
- `template-parts/store/section-column.php`（共通部品を呼ぶだけに縮める）
- `template-parts/highend/section-gallery.php`（本の部分を共通部品の呼び出しに置き換え）

### データ（CPT / タクソノミー / カスタムフィールド。すべて既存。新規登録なし）
- CPT `works`: 施工事例。タイトル = 詳細の和文見出し（`h1`）、説明文（post_content）= 詳細の本文
- タクソノミー `works_package`（管理画面「パッケージタイプ」）: デザインテイスト = 絞り込みボタン・詳細の英字見出し・名前
- メタ `_exterior_exone_works_gallery`: 写真（添付 ID の並び）。1 枚目 = メイン（一覧カード・詳細左ページ・TOP）、2 = 外観写真、3 = 3Dパース、4 = 図面
- メタ `_exterior_exone_works_area`（所在地）・`_exterior_exone_works_on_top`（TOP 掲載）: 今回のページでは使わない（TOP は既存どおり）
- テーマ内の表: テイストの並び順・英名対応・キャプション・FV / 絞り込み / COLUMN の文言（`inc/works-page-data.php`）

### 管理画面で行う操作
- 施工事例の登録: タイトル・説明文・パッケージタイプ（テイスト）・画像 4 枚（メイン / 外観写真 / 3Dパース / 図面 の順）。案内文は F-02 で更新
- ローカルのサンプルは WP-CLI で投入（下記）。本番では管理画面で登録

### サンプルデータ（F-01。ローカルのみ）

**投入コマンド**（zsh。サイト起動中に実行。WP-CLI はこのサイトでは DB_HOST をソケットで上書きする必要がある）

- **日時は必ず実行時刻より過去にする**（未来の日時で `publish` を指定すると予約投稿 `future` になり、一覧に出ない）。下では前日 2026-10-07 を使う

```sh
PHP=/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php
WPCLI=/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar
WPPATH="/Users/exone-se/Local Sites/exterior-exone-renewal/app/public"
SOCK="$HOME/Library/Application Support/Local/run/GRbmaPCLC/mysql/mysqld.sock"
wp() { "$PHP" "$WPCLI" --path="$WPPATH" --exec="define('DB_HOST','localhost:$SOCK');" "$@"; }
IMG="$WPPATH/wp-content/themes/exterior-exone/images/works"

# 0) 投入前の確認（結果を控える）
wp post list --post_type=works --post_status=any --fields=ID,post_title,post_name,post_date
wp post meta list 26 --keys=_exterior_exone_works_on_top   # 26〜31 それぞれ
wp term list works_package --fields=term_id,name,slug,count
wp option get posts_per_page

# 1) 画像の取り込み（一覧 12 枚 + 詳細 3 枚）
for n in 01 02 03 04 05 06 07 08 09 10 11 12; do
  f=( "$IMG"/grid-$n-*.jpg )
  typeset "G$n=$(wp media import "${f[1]}" --title="WORKS サンプル $n" --porcelain)"
done
P2=$(wp media import "$IMG/detail-page-02-right-821-216.jpg" --title="WORKS サンプル 外観写真" --porcelain)
P3=$(wp media import "$IMG/detail-page-03-3dpers-821-226.jpg" --title="WORKS サンプル 3Dパース" --porcelain)
P4=$(wp media import "$IMG/detail-page-04-plan-821-230.png" --title="WORKS サンプル 図面" --porcelain)

# 2) テイストのターム ID
T_COOL=$(wp term list works_package --name=クールモダン --field=term_id)
T_URBAN=$(wp term list works_package --name=アーバンモダン --field=term_id)
T_SIMPLE=$(wp term list works_package --name=シンプルモダン --field=term_id)
T_RUSTIC=$(wp term list works_package --name=ラスティックモダン --field=term_id)
T_STYLISH=$(wp term list works_package --name=スタイリッシュモダン --field=term_id)
T_NATURAL=$(wp term list works_package --name=ナチュラルモダン --field=term_id)

# 3) 新規 6 件（01〜06）
N01=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-01 \
  --post_date="2026-10-07 12:00:00" --post_title="北欧風の住まいに映えるブラックのエクステリア" \
  --post_content=$'ブラウンとホワイトのツートンカラーが印象的な住まいに、ブラックのカーポートと門柱を合わせた外構プラン。\n\n木目調のスリットと植栽を添え、シャープな印象の中に温かみを取り入れています。\n\n玄関へ続くアプローチも落ち着いた色合いでまとめ、建物との一体感を演出。夜には足元の灯りが植栽と門まわりを照らし、穏やかな表情で家族を迎えるエントランスをご提案します。' \
  --porcelain)
N02=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-02 \
  --post_date="2026-10-07 11:00:00" --post_title="白と黒のコントラストが際立つ木製門扉の外構" \
  --post_content=$'黒い箱形の 2 階と白い 1 階を組み合わせた住まいに、木製の門扉でやわらかさを添えた外構プラン。\n\n門まわりの照明が夕暮れの外観を引き立てます。' --porcelain)
N03=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-03 \
  --post_date="2026-10-07 10:00:00" --post_title="縦格子とガレージで整えるシンプルな外構" \
  --post_content=$'縦格子のフェンスとガレージを直線的にそろえ、すっきりとした印象にまとめました。\n\n素材の色数を抑え、建物の輪郭を引き立てています。' --porcelain)
N04=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-04 \
  --post_date="2026-10-07 09:00:00" --post_title="切妻屋根の住まいに寄り添うカーポートのある外構" \
  --post_content=$'切妻屋根の住まいに合わせ、黒いカーポートで外観を引き締めた外構プラン。\n\n素朴な素材感の舗装で、落ち着いた表情に仕上げています。' --porcelain)
N05=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-05 \
  --post_date="2026-10-07 08:00:00" --post_title="石張りの塀を照らす夜景の美しい外構" \
  --post_content=$'石張りの塀をライトアップし、夜に映える外観をつくりました。\n\n陰影のある照明計画で、邸宅の重厚感を引き出しています。' --porcelain)
N06=$(wp post create --post_type=works --post_status=publish --post_name=works-sample-06 \
  --post_date="2026-10-07 07:00:00" --post_title="白い住まいと植栽が調和する明るい外構" \
  --post_content=$'白い箱形の住まいに、植栽と明るい舗装を合わせた外構プラン。\n\n昼の光の中で、建物と緑がやさしく調和します。' --porcelain)

# 4) 既存 6 件（07〜12）は日時・画像・テイストだけ更新（タイトル・投稿名・本文・TOP 掲載は変えない）
wp post update 31 --post_date="2026-10-07 06:00:00"
wp post update 30 --post_date="2026-10-07 05:00:00"
wp post update 29 --post_date="2026-10-07 04:00:00"
wp post update 28 --post_date="2026-10-07 03:00:00"
wp post update 27 --post_date="2026-10-07 02:00:00"
wp post update 26 --post_date="2026-10-07 01:00:00"

# 5) ギャラリー（1 枚目 = 表の画像、2〜4 枚目は共通）
wp post meta update $N01 _exterior_exone_works_gallery "$G01,$P2,$P3,$P4"
wp post meta update $N02 _exterior_exone_works_gallery "$G02,$P2,$P3,$P4"
wp post meta update $N03 _exterior_exone_works_gallery "$G03,$P2,$P3,$P4"
wp post meta update $N04 _exterior_exone_works_gallery "$G04,$P2,$P3,$P4"
wp post meta update $N05 _exterior_exone_works_gallery "$G05,$P2,$P3,$P4"
wp post meta update $N06 _exterior_exone_works_gallery "$G06,$P2,$P3,$P4"
wp post meta update 31 _exterior_exone_works_gallery "$G07,$P2,$P3,$P4"
wp post meta update 30 _exterior_exone_works_gallery "$G08,$P2,$P3,$P4"
wp post meta update 29 _exterior_exone_works_gallery "$G09,$P2,$P3,$P4"
wp post meta update 28 _exterior_exone_works_gallery "$G10,$P2,$P3,$P4"
wp post meta update 27 _exterior_exone_works_gallery "$G11,$P2,$P3,$P4"
wp post meta update 26 _exterior_exone_works_gallery "$G12,$P2,$P3,$P4"

# 6) テイスト（set = 既存の割り当てを置き換える）
wp post term set $N01 works_package $T_COOL    --by=id
wp post term set $N02 works_package $T_URBAN   --by=id
wp post term set $N03 works_package $T_SIMPLE  --by=id
wp post term set $N04 works_package $T_RUSTIC  --by=id
wp post term set $N05 works_package $T_STYLISH --by=id
wp post term set $N06 works_package $T_NATURAL --by=id
wp post term set 31 works_package $T_COOL    --by=id
wp post term set 30 works_package $T_URBAN   --by=id
wp post term set 29 works_package $T_SIMPLE  --by=id
wp post term set 28 works_package $T_RUSTIC  --by=id
wp post term set 27 works_package $T_STYLISH --by=id
wp post term set 26 works_package $T_NATURAL --by=id

# 7) 確認（値を §4 の表に転記する）
echo "N01..N06 = $N01 $N02 $N03 $N04 $N05 $N06"
echo "G01..G12 = $G01 $G02 $G03 $G04 $G05 $G06 $G07 $G08 $G09 $G10 $G11 $G12 / P2 P3 P4 = $P2 $P3 $P4"
wp post list --post_type=works --post_status=publish,future --orderby=date --order=DESC --fields=ID,post_name,post_status,post_date
```

- 注意: WP-CLI で作った投稿は管理画面の nonce が無いので、テーマの保存処理（`save_post_works`）はメタを書き換えない（上のコマンドで入れた値がそのまま残る）。既存 6 件の相対的な日時の順（31 → 26）は保つので、TOP の WORKS カードの順は 30 → 29 → 28 → 27 → 26 のまま
- コマンドが途中で失敗したときは、取り込んだ添付と作った投稿を `wp post delete <ID> --force` で消してからやり直す（同じ画像の二重取り込みをしない）

**投入後の対応表**（Evaluator はこの表と照合する。「投稿 ID」の新規分と「添付 ID」は Generator が投入後に実際の値で埋める）

| 一覧の順 | 投稿 ID | 投稿名（URL） | タイトル | テイスト | 1 枚目（添付 ID） | 2〜4 枚目 | 日時（2026-10-07） | TOP 掲載 |
|---|---|---|---|---|---|---|---|---|
| 01 | 69（新規） | `works-sample-01` | 北欧風の住まいに映えるブラックのエクステリア | クールモダン | grid-01（54） | P2 / P3 / P4 | 12:00 | なし |
| 02 | 70（新規） | `works-sample-02` | 白と黒のコントラストが際立つ木製門扉の外構 | アーバンモダン | grid-02（55） | 同上 | 11:00 | なし |
| 03 | 71（新規） | `works-sample-03` | 縦格子とガレージで整えるシンプルな外構 | シンプルモダン | grid-03（56） | 同上 | 10:00 | なし |
| 04 | 72（新規） | `works-sample-04` | 切妻屋根の住まいに寄り添うカーポートのある外構 | ラスティックモダン | grid-04（57） | 同上 | 09:00 | なし |
| 05 | 73（新規） | `works-sample-05` | 石張りの塀を照らす夜景の美しい外構 | スタイリッシュモダン | grid-05（58） | 同上 | 08:00 | なし |
| 06 | 74（新規） | `works-sample-06` | 白い住まいと植栽が調和する明るい外構 | ナチュラルモダン | grid-06（59） | 同上 | 07:00 | なし |
| 07 | 31 | （既存の日本語スラッグ） | 【トップ非掲載】物置とフェンスの目隠し外構 | クールモダン | grid-07（60） | 同上 | 06:00 | なし（既存どおり） |
| 08 | 30 | （既存） | コンクリートとガレージで魅せる外構 | アーバンモダン | grid-08（61） | 同上 | 05:00 | あり |
| 09 | 29 | （既存） | カーポートとタイルテラスのある住まい | シンプルモダン | grid-09（62） | 同上 | 04:00 | あり |
| 10 | 28 | （既存） | ウッドデッキと人工芝で作る快適なエクステリア | ラスティックモダン | grid-10（63） | 同上 | 03:00 | あり |
| 11 | 27 | （既存） | ロックガーデンを活用した個性的なエントランス | スタイリッシュモダン | grid-11（64） | 同上 | 02:00 | あり |
| 12 | 26 | （既存） | グレー基調のモダンエクステリアと植栽との調和 | ナチュラルモダン | grid-12（65） | 同上 | 01:00 | あり |

- P2 = 外観写真 `detail-page-02-right-821-216.jpg`（66）/ P3 = 3Dパース `detail-page-03-3dpers-821-226.jpg`（67）/ P4 = 図面 `detail-page-04-plan-821-230.png`（68）
- 投入メモ（S1）: 取り込んだのは `images/works/webp/` の WebP 版（cwebp -q 76。ファイル名は同じで拡張子 .webp）。添付 54〜68
- 既存 6 件の TOP 掲載は投入前の確認（手順 0）の結果と一致していること（2026-10-02 時点の基準 HTML では ID 31 だけ非掲載）
- 既存 6 件のタイトル・本文は TOP / 支店で使われている仮文のまま残す（詳細ページでは既存の短い本文が出る）

### 新規トークン

**`css/works-page.css`**（`.p-wkp*`。`:root` = 幅で変わらない値 / PC = 1025px 以上の比例ブロック（`--wkp-vu`）/ SP = 1024px 以下（`--wkp-su`））

| トークン | 1920（PC） | 375（SP） | 出典 |
|---|---|---|---|
| `--wkp-vu` / `--wkp-su` | `min(1px, 100vw / 1920)` | `min(100vw / 375, var(--sp-scale-max))` | 比例方式 |
| `--wkp-canvas-w` | 1920 | 375 | |
| `--wkp-color-bg` | `var(--color-black2)` | 同 | 821:119 / 836:49 |
| `--wkp-color-paper` | #ffffff | 同 | 図面サムネの地 821:229 / 836:93 |
| `--wkp-shadow` | 0 12px 32px rgba(0,0,0,.8) | 同 | 写真・サムネ |
| `--wkp-display-lh` | 1.29 | 同 | Antonio の lh normal |
| `--wkp-filter-fade` | 0.3s | 同 | 絞り込みのフェード（Q2） |
| `--wkp-fv-height` | 1080 | 723 − ヘッダー高 | 821:114 / 836:38 |
| `--wkp-fv-bg-top` | 0 | −ヘッダー高 | PLANS と同じ |
| `--wkp-fv-image-position` | 50% 50% | 33% 50% | 836:38 |
| `--wkp-fv-copy-top` | 428 | 296 − ヘッダー高 | 821:118 / 836:48 |
| `--wkp-fv-title` / `-ls` | 120 / −3 | 50 / −3 | |
| `--wkp-fv-lead` / `-fw` / `-lh` / `-ls` | 36 / bold / 1.5 / 3 | 16 / medium / 1.8 / 1 | 821:117 / 836:46 |
| `--wkp-fv-lead-gap` | 11 | 9 | 英字下端 → 和文 |
| `--wkp-sec-gap` | 160 | 80 | FV → 見出し、グリッド → COLUMN |
| `--wkp-head-eng` / `-ls` / `-color` | 32 / 1 / gray2 | 14 / 1 / white3 | 821:129 / 836:88 |
| `--wkp-head-eng-lh` / `--wkp-head-jp-lh` | 1.12 / 1.6 | 同 | |
| `--wkp-head-jp` / `-ls` | 40 / 2 | 16 / 1 | 821:128 / 836:89 |
| `--wkp-head-gap` | 55.86 | 24.23 | 英字上端 → 和文上端 |
| `--wkp-content-w` | 1600 | 327 | ボタン・グリッドの幅 |
| `--wkp-filter-top` | 80.14 | 39 | 見出し下端 → ボタン |
| `--wkp-filter-cols` | 6 | 2 | |
| `--wkp-filter-gap` | 16 | 5 | |
| `--wkp-filter-btn-h` | 69 | 29 | |
| `--wkp-filter-radius` | 5 | 3 | |
| `--wkp-filter-text` / `-ls` / `-lh` | 20 / 1 / 1.6 | 10 / 1 / 1.6 | |
| `--wkp-grid-top` | 60 | 20 | ボタン下端 → 写真 |
| `--wkp-grid-cols` | 3 | 2 | 決定（SP 2 列） |
| `--wkp-grid-gap-x` / `-y` | 35 / 36 | 7 / 7 | |
| `--wkp-card-ratio` | 510 / 288 | 同 | |
| `--wkp-card-radius` | 5 | 2 | |
| `--wkp-empty-text` | 20 | 12 | 0 件の文言（カンプ外。Q7） |
| `--wkp-detail-book-top` | 80.14 | 40 | 見出し下端 → パンフレット |
| `--wkp-book-x` | 276 | 25 | パンフレット左端 |
| `--wkp-book-page-w` / `-page-h` | 1172.45 / 662.5 | 314.54 / 177.73 | 821:210 / 836:78 |
| `--wkp-book-fold-left` / `-fold-right` | 84.85 / 46.28 | 22.76 / 12.42 | 821:213・217 / 836:80・83 |
| `--wkp-book-perspective` | 4542 | 1218 | 左ページ幅の約 3.87 倍（ハイエンドと同じ比） |
| `--wkp-name` / `-ls` | 88 / −3 | 32 / −1 | 821:221 / 836:84 |
| `--wkp-name-x` | 315 | 38 | |
| `--wkp-name-overlap` | −61 | −22 | 左ページ下端 → 名前上端 |
| `--wkp-thumb-w` / `-h` | 211 / 120 | 94.5 / 53.4 | |
| `--wkp-thumb-gap` | 16.5 | 7.25 | 15・18 / 6.6・7.9 の平均 |
| `--wkp-thumb-radius` | 5 | 2 | |
| `--wkp-thumb-right` | 318 | — | PC: サムネ右端 x1602 |
| `--wkp-thumb-x` | — | 39 | SP: 左端 |
| `--wkp-thumb-overlap` | −100 | — | PC: 左ページ下端に重なる量 |
| `--wkp-thumb-top` | — | 12 | SP: 下線 → サムネ |
| `--wkp-cap` / `-lh` / `-ls` / `-gap` | 14 / 2 / 1 / 8 | 非表示 | 821:223〜225 |
| `--wkp-line-x` | 319 | 39 | 821:219 / 836:85 |
| `--wkp-line-top` | 21 | 7 | PC: キャプション下端 → 下線 / SP: 名前下端 → 下線 |
| `--wkp-body-top` | 42 | 12 | PC: 下線 → 本文 / SP: サムネ下端 → 本文 |
| `--wkp-body-x` / `-w` | 319 / 1283 | 38 / 320 | |
| `--wkp-body` / `-lh` / `-ls` | 18 / 36 / 1 | 12 / 19.2 / 1 | |
| `--wkp-detail-list-gap` | 120 | 80 | 本文下端 → 一覧ブロック |

（長さはすべて `calc(<値> * var(--wkp-vu | --wkp-su))`。色は既存の `--color-*` を参照し、直値は `:root` のトークンにだけ書く）

**`css/flipbook.css`**: F-03「トークンの置き場」の表（`--flipbook-*`）
**`css/column.css`**: 新規の値なし（`--st-*` を store.css から移すだけ。名前・値とも据え置き）

### 追加アセット
- JS: `js/works-filter.js`（絞り込み）/ `js/flipbook.js`（めくりの本体。ハイエンドから移す）/ `js/works-detail.js`（詳細のサムネ・キャプション）。`js/highend-gallery.js` はハイエンドの連携用に縮める
- CSS: `css/works-page.css` / `css/flipbook.css` / `css/column.css`
- 画像: `images/works/pc-fv-bg-821-116.jpg`（FV。PC・SP 共用・テーマ内）。一覧・詳細の写真は投稿の添付（サンプルは `images/works/grid-*.jpg`・`detail-page-*.{jpg,png}` を WP-CLI で取り込み）。いずれも AI 生成の**差し替え前提の仮画像**。綴じ目の SVG 4 枚は使わない（CSS のグラデーションで描く）

---

## 5. スプリント計画

### S1: データと一覧
- 含む機能: F-01、F-02
- 内容: 基準の保存（コード変更前）→ サンプルデータ投入 → `pre_get_posts` / `inc/works-page-data.php` / `archive-works.php` / `template-parts/works/`（FV・見出し・一覧ブロック）/ `css/works-page.css` の骨格とトークン（PC・SP とも）/ `js/works-filter.js` / enqueue・body クラス・has-fv-header・noscript / メタボックスの案内文。COLUMN は S3
- 完了条件: F-01・F-02 の AC すべて合格。§4 の対応表の空欄（新規投稿 ID・添付 ID）が埋まっている
- 前提: なし（サイトが起動していること）

### S2: 詳細とめくりの共通部品
- 含む機能: F-03、F-04
- 内容: ハイエンドの Gallery を `template-parts/common/flipbook.php` + `css/flipbook.css` + `js/flipbook.js` に切り出し、ハイエンドを置き換える（F-03 の対応表どおり）→ `single-works.php` / `template-parts/works/section-detail.php` / `js/works-detail.js`（見出し・パンフレット・名前・サムネ・キャプション・下線・本文の右からフェードイン・重ね順）→ 詳細内の一覧ブロック（S1 の部品）
- 完了条件: F-03・F-04 の AC すべて合格（ハイエンドの回帰 AC-03-2〜03-6 を含む）
- 前提: S1（サンプルデータ・一覧ブロック・works-page.css・基準ファイル）

### S3: COLUMN・SP・支店モード・仕上げ
- 含む機能: F-05、F-06、F-07
- 内容: COLUMN の共通化（`template-parts/common/section-column.php` + `css/column.css`。コーポレート文言 / 支店モード文言）と一覧・詳細への配置 → SP（一覧の補間・詳細）と 768 → 支店モードのリンク → `CLAUDE.md` 追記 → 回帰（ハイエンド Gallery・支店 COLUMN と見出し・TOP の WORKS カード・`/works/?store=` のヘッダー）・コンソールエラー・横スクロール
- 完了条件: F-05・F-06・F-07 の AC すべて合格
- 前提: S1・S2。COLUMN の回帰用に、S3 着手前に `/store/aomori/` の各セクション見出しの computed 値を記録しておく（AC-05-2）

---

## 6. オープン論点（Planner が判断できなかったこと。仮の既定で進め、呼び出し側でユーザーに確認する）

決定ファイルに無く、カンプからも決まらない点だけを挙げる。いずれも括弧内の既定で実装してよい（変わったら本文を直す）。

- Q1（要確認・アニメーション）: 詳細のサムネ 3 枚とキャプションの差し替えタイミング。**既定: めくり開始でフェードアウト → めくり終了で差し替えてフェードイン（ハイエンドの番号・英名・コピーと同じ 0.4s）**。代案: めくり開始と同時に差し替える / フェードせず即時
- Q2（要確認・アニメーション）: 絞り込みのフェードの時間。**既定 0.3s（`--wkp-filter-fade`）**。カードが消えたあとの並び替えは動かさず即時に詰める
- Q3（要確認・カンプに無い状態）: 図面（4 枚目）が左ページ・右ページに来たときの見せ方。**既定: 他の写真と同じく cover（上下が切れる）**。代案: 白地に全体を収める（めくりの紙も同じ見せ方にする改修が要る）
- Q4（カンプの不備の確認）: 詳細の見出しの上の余白。カンプは FV 下端 +169（一覧は +160）。**既定: 一覧と同じ 160**
- Q5（カンプどうしの矛盾）: 本文の段落分けが PC（3 段落）と SP（1・2 段落目を結合、3 段落目を分割）で違う。**既定: 管理画面の入力（空行で段落）を正とし、PC・SP で同じ段落**。サンプルは PC の 3 段落
- Q6（データにある状態）: 1 つの事例に複数のテイストが付いている場合（`works_package` は複数選択できる）。**既定: 英字の見出し・名前は並び順表で先頭のテイスト。絞り込みは付いているどのテイストでも表示**
- Q7（カンプに無い状態）: 0 件の文言「該当する事例はありません」の見た目（文言は決定で既定済み。公開前チェックリストで確認）。**既定: グリッドの位置に中央揃え、Noto Regular、ボタンと同じ大きさ（PC 20 / SP 12）、gray2**
- Q8（カンプから決まらない）: 詳細ページ内の一覧ブロックに、表示中の事例自身も含めるか。**既定: 含める（カンプは一覧と同一の 12 枚）**
- Q9（運用）: ハイエンドの旧仕様書 `docs/spec-20261005-highend-page.md` の AC にある検証用属性 `data-hep-flip` / `data-hep-progress` は、本仕様で `data-flipbook-flip` / `data-flipbook-t` に改名される。**既定: 本仕様書の F-03 を正とし、旧仕様書は変更しない**

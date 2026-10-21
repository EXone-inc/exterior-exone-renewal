# Spec: サンクスページ（送信完了）`/contact/thanks/`・`/recruit/thanks/`

- 作成日: 2026-10-15
- 起案（ユーザー指示 2026-10-15 の転記）:
  - お問い合わせページ `/contact/` のフォーム（個人・法人）送信後に、Figma のサンクスページ（PC `964:47` / SP `964:78`）を `/contact/thanks/` として表示する
  - 採用応募フォーム `/recruit/entry/` も同じサンクスページを `/recruit/thanks/` として使い、文言だけ変える
  - 直接アクセスされたときも普通に表示し、`noindex` を付ける
- 決定事項: `docs/spec-20261015-thanks-page-decisions.md`（**本仕様書と食い違う場合は決定事項ファイルが正**。文言は同ファイル §2 で確定済み）
- 設計メモ: `docs/figma-contact-thanks-page.md`（以下「メモ」。§番号はメモの節）。アセット `docs/figma-contact-thanks-assets/`
- デザイン元: Figma fileKey `msbO5SCyCUBUXQwXLUug4m`
  - PC `964:47`「CONTACTページ　サンクスページ」（1920×2864。ヘッダー・フッター無し）
  - SP `964:78`「SP サンクスページ」（375×3130。中身は y2127 まで）
  - どちらもボード「CONTACTページ」`853:582` の中。guide・注釈は無い
  - 関連（流用元）: CONTACT FV PC `860:137`（デフォルト画面 `860:142` 内）/ SP `869:618`、ヘッダー / フッター コーポレート PC `670:45` / `670:88`・SP `670:288`、支店 PC `670:58` / `670:193`・SP `670:289`

### 確定済みの決定事項（決定事項 §2・§3 の要点。聞き直さない）

1. URL は `/contact/thanks/`・`/recruit/thanks/`。固定ページ `thanks` を `contact`（ID 78）・`recruit`（ID 105）の子として **WP-CLI で作る**。テンプレートは共通の `page-thanks.php` 1 本。どちらのサンクスかは**親ページの slug** で判定する（`exterior_exone_is_recruit_entry_page()` と同じ作法）。文言はデータ関数（`inc/thanks-data.php` を想定）で出し分ける
2. 遷移: CF7 の AJAX 送信成功（`wpcf7mailsent`）で、テーマの JS がサンクスページへ遷移する（履歴に残る遷移）。遷移先 URL はサーバー側で組み、フォームのテンプレートの data 属性で渡す。お問い合わせは支店モードの `?store=` を引き継ぐ（`exterior_exone_store_link()` 経由）、採用は引き継がない
3. CF7 の JS が動かない通常 POST のときは、CF7 が同じページに出す現状の完了表示を残す（フォールバック。作り込まない）
4. 直接アクセスは普通に表示（`/contact/` へ戻さない）。`noindex` を付ける（follow は既定のまま）。WordPress 標準のサイトマップから除外。メニュー・パンくず・内部リンクからは張らない
5. ヘッダー: CONTACT と同じ扱い（コーポレートは写真に重ねるオーバーレイヘッダー = `exterior_exone_has_fv_header()` の対象に加える）。支店モードは支店ヘッダー
6. FV: CONTACT の FV と同一（写真・切り抜き・暗幕・文字位置）→ `template-parts/contact/section-fv.php` / `.p-contactfv` を流用し、文言だけ「THANK YOU / 送信完了」。背面に隠れている `966:118` は使わない
7. Explore の 3 枚は既存画像を流用（WORKS `images/works/pc-fv-bg-821-116.jpg`、PLANS `images/plans/pc-fv-bg-718-242.jpg`、DX `images/top/dx_video-poster.jpg`）。**カード全体**を `/works/` `/plans/` `/dx/` へのリンクにする（支店モードは `exterior_exone_store_link()`）。DX は**静止画**（動画は読み込まない）。採用版も同じ 3 枚
8. カンプが指定する値はカンプどおり: SP 左右余白 **22**、暗幕は **DX カードだけ 10%**、PC の PLANS カードの影なしは不備として他 2 枚に揃える（影あり）、SP の Next Step 03 の説明文は PC の文言を使う。「お問い合わせ、ありがとうございます。」の色はカンプどおり black1
9. 新規に置く画像は Next Step のアイコン 3 点と丸チェックだけ（すべて AI 生成の**差し替え前提の仮画像**）

---

## 1. 背景・目的

現状、お問い合わせ（`js/contact-form.js`）と応募フォーム（`js/recruit-entry.js`）は、送信完了時に同じページ内で CF7 のメッセージを出しているだけで、送信後の案内（この後どうなるか・次に見てほしいページ）が無い。Figma に送信完了ページのカンプが用意されたので、送信後は専用のサンクスページへ遷移させ、①送信できたことの確認、②今後の流れ（Next Step）、③サイト内の回遊（Explore EXone）を見せる。採用応募も同じデザインを文言違いで使い、デザインの重複を避ける。サンクスページは検索結果に出す必要が無いので `noindex`・サイトマップ除外にする。

## 2. スコープ

### やること
- 固定ページ `thanks` 2 件の作成（WP-CLI。親 `contact` / 親 `recruit`）
- 共通テンプレート `page-thanks.php`（FV → 完了メッセージ → Next Step → Explore EXone）と、お問い合わせ版 / 採用版の文言の出し分け
- サンクスページ用 CSS `css/thanks.css`（新規）とトークンの追加（§4「トークン」）
- 新規アセット: 丸チェック（Figma の SVG）、Next Step のアイコン 3 点（形式は §6 Q5 の回答による）
- `/contact/`（個人・法人）と `/recruit/entry/` の送信成功時の遷移（既存の同ページ内完了表示を遷移に置き換え）
- `noindex`、サイトマップ除外、ヘッダーのオーバーレイ判定・body クラスへの追加、支店モードの引き継ぎ（お問い合わせのみ）
- 回帰確認（`/contact/`・`/recruit/entry/`・`/recruit/` の見た目と挙動が変わらないこと）
- `CLAUDE.md` のフレーム対応表にサンクスページの行を追記

### やらないこと（範囲外）
- `/contact/` と `/recruit/entry/` の見た目・フォーム項目・メール文面・宛先の変更（変わってよいのは「送信成功時の遷移」と、遷移先 URL を渡すための属性だけ）
- 送信を経由しない直接アクセスの検知・制限（ユーザー指示どおり普通に表示）
- `966:118`（FV の背面に隠れた住宅街イラスト）の利用
- DX カードでの動画再生
- CF7 の JS が動かない通常 POST 時の作り込み（現状のまま）
- 採用版の Explore を採用向けのリンクに変えること（お問い合わせ版と同じ 3 枚）
- 「通常2営業日以内」の支店ごとの出し分け（文言は決定事項の確定文どおり）
- パンくず・SNS シェア・コンバージョン計測タグ（GA 等）の設置
- 画像の最終調達（アイコン・写真は差し替え前提の仮画像）

---

## 3. 機能一覧

> 共通の前提（全機能）
> - 「忠実再現」の判定は `CLAUDE.md` の定義どおり（セクション構成・配置・配色・タイポグラフィ・余白感は一致必須、px 完全一致は求めない）。AC の座標の許容差は、特記が無ければ ±4px
> - 主検証幅は 1920（PC カンプ `964:47`）と 375（SP カンプ `964:78`）。768 は破綻が無いこと（SP の組みのまま `--sp-scale-max` で拡大が頭打ち。既存ページと同じ振る舞い）
> - ブレイクポイントは既存どおり 1024 / 1025 のみ（中間幅の扱いは §6 Q4 の回答に従う）
> - 値はトークン経由（§4「トークン」）。出力はすべてエスケープ。内部リンクは `exterior_exone_store_link()` を通す。DB 操作は WP-CLI のみ
> - ローカルの URL は `http://exterior-exone-renewal.local`。メールの確認は Mailpit（API `http://127.0.0.1:10010/api/v1/messages`）
> - Evaluator は並行実行しない（同じフォームを同時に送ると Mailpit の件数判定が崩れるため）

### F-01: お問い合わせ版サンクスページ `/contact/thanks/`（ページ作成・テンプレート・PC / SP の見た目）

- 概要: 固定ページ `thanks`（親 `contact`）を WP-CLI で作り、共通テンプレート `page-thanks.php` でカンプどおりに表示する。構成は上から [1] FV（CONTACT と同一の写真帯 + 「THANK YOU」ゴースト + 「送信完了」）→ [2] 完了メッセージ（丸チェック + 太字の一文 + 本文 2 段落）→ [3] Next Step（「Next Step / 今後のご案内」+ 白カード 3 枚。番号 01〜03 がカード上端にかぶる）→ [4] Explore EXone（「Explore EXone / 理想の外構づくりを、もっと身近に」+ 写真カード 3 枚。カード全体がリンク）。[1]〜[3] の地は white4、[4] の地は white2。PC は [3][4] のカードが横 3 列、SP は縦 3 段（SP の Next Step カードは「左に番号 + アイコン、右に題 + 説明」の横組み）
- Figma ノード:
  - PC `964:47`: 地 `964:48` / FV `964:59`（写真 `964:60`・`964:61`、暗幕 `964:62`、THANK YOU `964:64`、送信完了 `964:63`）/ 丸チェック `964:72` / 一文 `966:111` / 本文 `966:114` / Next Step `966:121`・`966:120`、カード `966:124`・`966:130`・`966:132`、番号 `966:127`・`966:131`・`966:133`、アイコン `966:152`・`966:150`・`966:187`、題 `966:134`・`966:140`・`966:142`、説明 `966:137`・`966:139`・`966:141` / Explore 地 `966:160`、見出し `966:157`・`966:156`、カード `966:164`・`966:177`・`966:172`（暗幕 `966:183`）、英字 `966:168`・`966:170`・`966:171`、和文 `966:174`・`966:175`・`966:176`
  - SP `964:78`: ヘッダー `964:95` / FV `964:79`（`964:80`・`964:81`、THANK YOU `964:94`、送信完了 `964:92`）/ 丸チェック `964:102` / 一文 `966:190` / 本文 `966:194` / 地 `966:218` / Next Step `966:196`・`966:197`、カード `966:199`・`966:205`・`966:219`、番号 `966:202`・`966:208`・`966:222`、アイコン `966:203`・`966:210`・`966:224`、題 `966:201`・`966:207`・`966:221`、説明 `966:200`・`966:206`・`966:220` / Explore 地 `964:82`、見出し `966:226`・`966:227`、カード `966:229`・`966:233`・`966:243`（暗幕 `966:244`）、英字 `966:231`・`966:236`・`966:250`、和文 `966:230`・`966:237`・`966:251`
- ユーザーストーリー: お問い合わせをした来訪者として、送信が受け付けられたことと、この後どう連絡が来るのかを一目で確認したい。なぜなら、送れたかどうか不安なまま待ちたくないから。また、待つ間に施工事例やプランを見に行きたい。
- 表示仕様:
  - 文言は決定事項 §2「文言（確定）」のお問い合わせ列どおり。改行位置・細部の表記はメモ §4 の原文に従う（「おまちください」はひらがな、「2営業日」の 2 は半角）
  - 本文: 2 段落。段落間は 1 行分（PC 27 / SP 16.5）の空き。カンプの空行に入っているゼロ幅スペース（U+200B）は入れない。2 段落目の「通常2営業日以内に担当スタッフよりご連絡いたします。」だけ太字（同サイズ・同色）。改行は PC 5 行（段落 1 が 2 行・段落 2 が 2 行）/ SP 7 行（段落 1 が 3 行・段落 2 が 3 行。太字部分が 1 行）でカンプの行割りに合わせる
  - 見出しの構造: `h1` は FV の「THANK YOU」の 1 個だけ（`/contact/` の「CONTACT」と同じ扱い）。「送信完了」は h1 の補足。「Next Step / 今後のご案内」「Explore EXone / 理想の外構づくりを、もっと身近に」は見出し要素、Next Step の各カードの題も見出し要素（レベルは Generator が文書構造から決める）
  - 丸チェック: Figma の SVG の図形（円 + ✓）。円の線は black2、チェックの線は black0 に寄せる（カンプの #1D1D1F / #000 はトークンに無いため。見た目の差は無い）。線の太さ PC 円 1・チェック 3 / SP 円 1・チェック 2
  - Next Step カード: 白地・角丸 PC 10 / SP 5・影 PC `0 12px 32px rgba(0,0,0,.12)` / SP `0 6px 16px rgba(0,0,0,.12)`。番号（Antonio・gray2）はカードの白地と影より前面で、上部がカードの外（地の上）にはみ出す。PC はアイコンの**下端揃え**・各カードの横中央、題と説明は中央揃え。SP はアイコン中心がカード左 +50、題と説明は左揃え（カード左 +97）でブロックごとカードの上下中央。カードはリンクではない
  - Explore カード: 16:9。角丸・影は Next Step カードと同じ（PC の PLANS も影あり）。写真は cover・中央。**DX だけ** 10% の黒の暗幕。英字（Antonio・white2）と和文（Medium・white2）がカードの横中央。カード全体が 1 つのリンク（WORKS → `/works/`、PLANS → `/plans/`、DX → `/dx/`）。リンクのアクセシブルな名前は英字 + 和文
  - カンプの不備（メモ §7）は正しい方に寄せる: PC の Explore カードの x は Next Step カードと同じ 160 / 706 / 1251、DX の暗幕は写真と同寸、PC の見出し・カード文字の中心はすべて 960、SP の暗幕高さは 332
  - 地: FV の下〜Next Step カード下 +160（SP +80）までが white4、その下が white2。境界に線・グラデは無い。最後のカードの下の余白 PC 160 / SP 80 の後にフッター
  - ヘッダー / フッター: コーポレートは `/contact/` と同じ（PC は写真に重なるオーバーレイ、SP は黒帯 45 の下から写真。写真の上端はヘッダーの裏）。フッターは既存の `footer.php` のまま
  - 画像: FV は `/contact/` と同じファイル（webp があれば webp）。Explore は決定事項 7 の 3 ファイル（各 `webp/` を優先）。DX の動画ファイル `videos/top/dx_video.mp4` は読み込まない
  - 動き: カンプに指示が無い。ホバー・出現・丸チェックのアニメーションは §6 Q1〜Q3 の回答どおり（回答が出るまでは静止表示で実装を進めてよい）。JS 無効時・`prefers-reduced-motion: reduce` 時はすべて最初から表示され、動きが無い
- CSS: `css/thanks.css`（新規。サンクスページだけで読む）。FV は `css/contact.css` の `.p-contactfv` を流用するため、サンクスページでは `contact.css` も読む。**ただし `/contact/` の表示結果は 1px も変えない**（F-03 で比較）。`contact.css` のページ単位の指定（`.is-contact-page` のページ地・`noscript` 用の指定など）がサンクスページに必要なら、サンクスページ側で同等の指定を持つ（`/contact/` 用の判定クラスをサンクスページに付けない）
- 受け入れ基準:
  - [x] AC-01-0: **実装前に**回帰用のベースラインを `docs/baseline-20261015-thanks/` に保存してある（F-03 の比較元）。内容:
    - 375 / 1920 のフルページスクリーンショットと HTML: `/contact/`（初期表示）、`/contact/` で「個人のお客様」→ 任意の支店 →「メール」と選んでフォームが出た状態、同じく「法人のお客様」→「メール」でフォームが出た状態、`/contact/?store=sendai`（初期表示）、`/recruit/entry/`、`/recruit/`（スクロール出現の演出は出し切った状態。FV に動きがあるページは静止するまで待つ）
    - 各ページの読み込まれている CSS / JS のハンドル（`<link>` / `<script>` の id）の一覧
    - `wp option get blog_public` の値、`/`・`/contact/`・`/recruit/entry/` の `meta[name="robots"]` の content、`/wp-sitemap-posts-page-1.xml`（固定ページのサイトマップ）の URL 一覧
    - JS 無効で `/contact/` と `/recruit/entry/` から送信を試みたときの結果（遷移先 URL・画面に出る文言・Mailpit の増加件数。送信できない場合は「送信不可」と記録）
  - [x] AC-01-1: `wp post list --post_type=page --name=thanks --fields=ID,post_parent,post_status` が 1 件で、`post_parent` = 78・`post_status` = publish。テーマに `page-thanks.php` があり、`/contact/thanks/` が 200
  - [x] AC-01-2: `/contact/thanks/` の body に `has-fv-header` とサンクスページ固有の判定クラスがあり、`is-contact-page`・`is-recruit-entry-page`・`is-recruit-page` は無い。`index.php` の仮表示（`.l-section .entry`）が無い
  - [x] AC-01-3: 1920 で `h1` が 1 個で文字が「THANK YOU」。ページ内の文字が上から順に「THANK YOU → 送信完了 → お問い合わせ、ありがとうございます。→ 本文 2 段落 → Next Step → 今後のご案内 → 01 お問い合わせ完了 / 02 担当スタッフよりご連絡 / 03 ご相談内容の確認・ご案内（各説明つき）→ Explore EXone → 理想の外構づくりを、もっと身近に → OUR WORKS / OUR PLANS / DX EXPERIENCE（各和文つき）」で、各文言が決定事項 §2 のお問い合わせ列・メモ §4 と一致する（空白・改行の差を除く）。ページの DOM テキストに U+200B が含まれない
  - [x] AC-01-4: **FV は `/contact/` と同一**: 1920 と 375 で、`/contact/thanks/` の FV（ページ上端〜「送信完了」の下端）のスクリーンショットを `/contact/` の同じ範囲と並べ、写真・切り抜き位置・暗幕の濃さが同じ。「THANK YOU」と「CONTACT」の computed font-family・font-size・letter-spacing・color・top が同じ（top は ±1px）、「送信完了」と「お問い合わせ」の computed font-size・font-weight・letter-spacing・color・top が同じ。写真の `img` の src が `/contact/` の FV と同じファイル
  - [x] AC-01-5: **1920 の配置**（ページ上端基準・カンプ `964:47`）: 「THANK YOU」top 428 / 丸チェック 146×146・top 737・横中心 x960 / 一文 top 963 / 本文 top 1028・幅 849 前後で中央揃え / 「Next Step」top 1323 / 「今後のご案内」top 1379 / Next Step カード 509×341・top 1566・左端 x160・706・1251 / 番号 top 1493（カード上端の 73 上）・左端がカード左 +29 / アイコンの下端が 3 枚とも 1732（±2）/ 題 top 1762 / 説明 top 1810 / white4 → white2 の切り替え y2067 / 「Explore EXone」top 2224 / Explore カード 509×286・top 2418・左端 x160・706・1251 / 英字 top 2555 / 和文 top 2643 / フッターの上端 2864（±8）
  - [x] AC-01-6: **1920 の書式**（computed）: 一文 = Noto Sans JP・700・24px・letter-spacing 2px・color rgb(77,77,77) / 本文 = 400・18px・行高 27px・2px・rgb(77,77,77)、太字部分だけ 700 / 「Next Step」「Explore EXone」= font-family が `--font-ui` と同じ・32px・1px・rgb(77,77,77) / 「今後のご案内」「理想の外構づくりを、もっと身近に」= 700・36px・2px・rgb(26,26,26)・1 行 / 番号 = Antonio・600・100px・-3px・rgb(217,217,217) / カード題 = 700・22px・2px / カード説明 = 400・16px・2px、01 が 1 行・02 と 03 が 2 行 / Explore 英字 = Antonio・600・64px・-3px・rgb(254,254,254) / Explore 和文 = 500・18px・2px・rgb(254,254,254)
  - [x] AC-01-7: **375 の配置**（ページ上端基準・カンプ `964:78`）: ヘッダー高さ 45（黒帯）/ 「THANK YOU」top 296 / 丸チェック 54×54・top 444・横中心 x188（±2）/ 一文 top 538 / 本文 top 574・7 行 / 「Next Step」top 773 / Next Step カード 331×94・左端 x22・top 863・981・1099 / 番号の top がカード上端の 24 上・左端 x37 / アイコンの横中心 x72（±2）・カードの上下中央（±3）/ 題と説明の左端 x119・題 + 説明のブロックがカードの上下中央（±3）/ white4 → white2 の切り替え y1273 / 「Explore EXone」top 1353 / 「理想の外構づくりを、/ もっと身近に」が 2 行（「、」の後で改行）/ Explore カード 331×186・左端 x22・top 1469・1665・1861 / フッターの上端 2127（±6）
  - [x] AC-01-8: **375 の書式**（computed）: 「THANK YOU」50px、「送信完了」500・16px / 一文 = 500・14px・1px / 本文 = 400・11px・1px / 「Next Step」14px / 「今後のご案内」700・16px・1px / 番号 36px・-1px / カード題 700・13px・1px・左揃え / カード説明 400・11px・1px・左揃え / Explore 英字 40px・-2px / Explore 和文 500・12px・1px
  - [x] AC-01-9: SP の Next Step 03 の説明文が「ご要望を詳しくお伺いし、内容に応じて今後の進め方をご案内いたします。」（02 と同文ではない）
  - [x] AC-01-10: **カードの装飾**: Next Step カードの computed background-color が rgb(255,255,255)、border-radius が 1920 で 10px / 375 で 5px、box-shadow が 1920 で `0 12px 32px rgba(0,0,0,.12)` / 375 で `0 6px 16px rgba(0,0,0,.12)` 相当。Explore カード 3 枚とも同じ角丸・影（1920 の PLANS にも影がある）。DX カードの写真の上にだけ rgba(0,0,0,0.1) の層があり、WORKS / PLANS には無い。番号がカードより前面に描画されている（番号の中心点で `elementFromPoint` が番号の要素を返す）
  - [x] AC-01-11: **Explore のリンク**: カードごとに `a` が 1 個で、カードの写真・英字・和文の全域がその `a` の中。href が順に `http://exterior-exone-renewal.local/works/`・`/plans/`・`/dx/`。それぞれ押すと該当ページに移動し 200。各 `a` のアクセシブルな名前に英字と和文が含まれる。Next Step カードの中に `a` が 0 個
  - [x] AC-01-12: ページに `video` 要素が無く、ネットワークに `dx_video.mp4` へのリクエストが無い。Explore の `img` の src が決定事項 7 の 3 ファイル（またはその `webp/` 版）。FV 以外の `img` に空でない `alt` または装飾として空の `alt` が付いている（リンク内の写真は空 `alt` でよい）
  - [x] AC-01-13: **ヘッダー**: 1920・コーポレートで、ヘッダーの computed background と position が `/contact/` と同じ（写真に重なるオーバーレイ）。375 でハンバーガーを押すとドロワーが開き、もう一度押すと閉じる（`/contact/` と同じ挙動）
  - [x] AC-01-14: **中間幅**: 768 / 1024 / 1025 / 1280 で横スクロールが無い（`scrollWidth <= innerWidth`）、要素の重なり・カード外への文字のはみ出しが無い。768 は SP の組み（縦 3 段）で中央寄せ、1025 以上の組みは §6 Q4 の回答どおり
  - [x] AC-01-15: **トークン**: `css/thanks.css` があり、色・長さ・影・角丸・字間・行高は §4「トークン」の値がカスタムプロパティとして定義され、規則側はそれを `var(--...)` で参照している（`thanks.css` のカスタムプロパティ定義以外の行に 16 進カラー・`rgba(`・カンプ由来の px 直値が無い。`0`・`1px` の線・`100%` 等の汎用値は除く）
  - [x] AC-01-16: **読み込み**: `/contact/thanks/` で `thanks.css` と `contact.css` が読まれ、CF7 のスクリプト・`contact-select.js`・`contact-form.js`・`contact-zip.js`・`recruit-entry.js` は読まれない。`thanks.css` は `/`・`/contact/`・`/recruit/entry/`・`/works/`・`/store/aomori/` の HTML に含まれない
  - [x] AC-01-17: JS を無効にして `/contact/thanks/` を開くと、全要素が見える（opacity 1・transform なし）。`prefers-reduced-motion: reduce` で開くと、出現・丸チェックのアニメーションが無い
  - [x] AC-01-18: `/contact/thanks/` を 375 / 1920 で開いてコンソールエラーが 0 件
  - [ ] AC-01-19: 1920 / 375 のフルページスクリーンショットを Figma `964:47` / `964:78`（SP は y0〜2127 の範囲）の get_screenshot と並べ、構成・配置・配色・タイポグラフィ・余白感が一致する（差はメモ §7 の不備を直した点だけ）
  - [x] AC-01-20: §6 Q1〜Q3・Q5 の回答が出ていれば、その内容どおりになっている（Q1: Explore カードにホバーしたときの computed transform / box-shadow / 写真の transform / 暗幕の opacity の変化が回答どおりで、`(hover: none)` の端末では変化しない。Next Step カードはホバーで変化しない / Q2: 丸チェックの出現が回答どおり / Q3: ブロックの出現が回答どおり / Q5: アイコンの形式が回答どおりで、375 と 1920 の両方で線が途切れず見える）。回答前にこの AC を判定する場合は「静止表示・ホバー変化なし」で合格とし、回答後に再判定する
    - Q8 補足（2026-10-15 回答・対応済み）: 一文と本文（`.p-thanksmsg__text`）はスクロール出現（`data-reveal`）をやめ、開いた時点で ✓ の描き始め（0.65s）からフェードインする（`css/thanks.css` の `p-thanksmsg-in`）。スクロールせず 2 秒後に opacity 1・transform none。JS 無効・reduced-motion では最初から表示（AC-01-17 と同じ扱い）。Next Step・Explore は従来どおりスクロール出現

---

### F-02: フォームからの遷移・採用版・noindex・サイトマップ除外

- 概要:
  - 固定ページ `thanks`（親 `recruit`）を WP-CLI で作り、`/recruit/thanks/` で採用版の文言を出す（テンプレート・見た目・Explore の 3 枚はお問い合わせ版と同じ）
  - `/contact/` の個人・法人フォームと `/recruit/entry/` で、CF7 の送信成功時にサンクスページへ遷移する（同ページ内の完了表示を置き換える）。遷移先 URL はサーバー側で組んでフォームのテンプレートに data 属性で持たせる。お問い合わせは支店モードなら `?store=<slug>` 付き、採用は常に付けない
  - 入力エラー・送信失敗・スパム判定などの成功以外の結果は、これまでどおり同じページで表示し、遷移しない
  - CF7 の JS が動かない通常 POST は現状のまま（AC-01-0 の記録と同じ結果）
  - 遷移先 URL を組めないとき（その環境に `thanks` の固定ページが無い・非公開など。本番で固定ページを作る前の状態）は、遷移せずに現状の同ページ内完了表示を出す（§7 Q-A。既定）
  - 2 つのサンクスページに `noindex` を付け、WordPress 標準のサイトマップから外す。直接アクセスは普通に表示する（転送しない）
- Figma ノード: PC `964:47` / SP `964:78`（採用版も同じ。採用版の文言はカンプに無く、決定事項 §2 が正）
- ユーザーストーリー:
  - お問い合わせ・応募をした来訪者として、送信ボタンを押したら送信完了のページで結果と今後の流れを確認したい。なぜなら、フォームの位置に短いメッセージが出るだけでは送れたか分かりにくいから。
  - 採用応募者として、応募向けの言葉（「ご応募」「採用担当」）で案内されたい。なぜなら、お問い合わせ用の文言だと応募が届いたのか不安になるから。
  - サイト運営者として、サンクスページが検索結果に出ないようにしたい。なぜなら、検索から直接来た人に「送信完了」を見せるのは誤解を招くから。
- 表示仕様（採用版）:
  - 文言は決定事項 §2 の採用列どおり。FV・Next Step 見出し・Explore はお問い合わせ版と同じ
  - 本文の改行位置・太字の位置は §6 Q6 の回答どおり（推奨: お問い合わせ版と対応する位置。PC は段落 1 が「この度は、EXoneの採用にご応募いただき、誠にありがとうございます。/ ご応募を正常に受け付けました。」、段落 2 が「ご入力いただいた内容を確認のうえ、**追って採用担当よりご連絡いたします。** / ご連絡まで、今しばらくおまちください。」。SP は「…ご応募いただき、/ 誠にありがとうございます。」で割る）
  - Next Step の説明の改行（PC）も §6 Q6 の回答どおり（推奨: 01「ご応募を正常に受け付けました。」1 行 / 02「内容を確認のうえ、/ 追って採用担当よりご連絡いたします。」/ 03「書類選考のうえ、/ 面接日程など今後の流れをご案内いたします。」）
  - ヘッダー: コーポレートでは `/recruit/entry/` と同じオーバーレイヘッダー。`/recruit/thanks/?store=<slug>` に直接来た場合は既存の支店モードの規則どおり（特別扱いしない）
- ページのタイトル（`<title>` に出る固定ページ名。既定。変える場合は報告）: お問い合わせ版「送信完了（お問い合わせ）」/ 採用版「送信完了（ご応募）」
- 受け入れ基準:
  - [x] AC-02-0: 実装前に Mailpit のメッセージ件数を記録している（以後の AC の「件数の増加」はこの時点からの差で判定する）。AC-01-0 のベースラインがある
  - [x] AC-02-1: `wp post list --post_type=page --name=thanks --fields=ID,post_parent,post_status` が 2 件で、`post_parent` が 78 と 105、どちらも publish。`/recruit/thanks/` が 200 で、body に `has-fv-header` とサンクスページ固有の判定クラスがあり、`is-recruit-page`・`is-recruit-entry-page`・`is-contact-page` は無い
  - [x] AC-02-2: `/recruit/thanks/` の文言が決定事項 §2 の採用列と一致する（太字の一文「ご応募、ありがとうございます。」、本文 2 段落、Next Step 01「ご応募完了」/ 02「採用担当よりご連絡」/ 03「選考のご案内」と各説明。日数の記載が無い）。FV（THANK YOU / 送信完了）・見出し・Explore の 3 枚（文言・画像・href）は `/contact/thanks/` と同じ。`/contact/thanks/` の文言は AC-01-3 のまま変わっていない
  - [x] AC-02-3: 1920 / 375 で `/recruit/thanks/` を `/contact/thanks/` と並べ、文言以外の構成・配置・配色・書式が同じ（各ブロックの top の差は、文言の行数の差によるもの以外 ±4px）。375 / 768 / 1920 で横スクロールが無い
  - [x] AC-02-4: **遷移先 URL の受け渡し**: `/contact/` の HTML の個人・法人フォームの部分に `http://exterior-exone-renewal.local/contact/thanks/` が属性値として含まれる。`/contact/?store=sendai` では `http://exterior-exone-renewal.local/contact/thanks/?store=sendai`。`/recruit/entry/` と `/recruit/entry/?store=aomori` ではどちらも `http://exterior-exone-renewal.local/recruit/thanks/`（`store=` が付かない）
  - [x] AC-02-5: **個人フォーム**: 1920 で `/contact/` →「個人のお客様」→ 支店 →「メール」→ 全項目に正しい値を入力 → 確認 → 送信を押すと、10 秒以内に URL が `http://exterior-exone-renewal.local/contact/thanks/` になり、`h1` が「THANK YOU」、一文が「お問い合わせ、ありがとうございます。」。Mailpit の件数が、変更前の同じ操作と同数（通知 + 自動返信）だけ増える。遷移前に同ページ内の完了表示（`[data-contact-form-view="done"]`）が見えた状態で止まらない
  - [x] AC-02-6: **法人フォーム**: 375 で AC-02-5 と同じ流れ（「法人のお客様」→「メール」→ 入力 → 確認 → 送信）を行うと、URL が `/contact/thanks/` になり、Mailpit の件数が変更前の同じ操作と同数増える
  - [x] AC-02-7: **支店モード**: `/contact/?store=sendai` から個人フォームで送信すると、URL が `http://exterior-exone-renewal.local/contact/thanks/?store=sendai` になり、`<html>` に `data-store-mode`、ヘッダーに支店名のリンク（`.p-header__store`）がある。ヘッダー・フッターの見た目が `/contact/?store=sendai` と同じ。Explore の 3 枚の href がそれぞれ `/works/?store=sendai`・`/plans/?store=sendai`・`/dx/?store=sendai`。通知メールの宛先が変更前と同じ（仙台の宛先）
    - S3 再確認（2026-10-15）: `docs/baseline-20261015-thanks/s3/flow.cjs`（personal-sendai）で URL `/contact/thanks/?store=sendai`・`data-store-mode` あり・`.p-header__store`「仙台支店」・Explore の href 3 本に `?store=sendai`・通知メールの宛先 `sendai@exterior-exone.com`（件名「【お問い合わせ】個人（仙台支店）」）。ヘッダー・フッターは `/contact/?store=sendai` と画素比較で PC 差分 0。SP はフッターの文字に描画位置の端数（0.25px）による差のみで目視同一（`docs/baseline-20261015-thanks/s3/footer-sp-store-compare.png`）
  - [x] AC-02-8: **採用**: `/recruit/entry/` で必須項目を入力して送信すると、URL が `http://exterior-exone-renewal.local/recruit/thanks/` になり、採用版の文言が出る。Mailpit の件数が 1 増え、宛先・件名が変更前と同じ。`/recruit/entry/?store=aomori` から送信しても遷移先は `/recruit/thanks/`（`store=` が付かない）で、`<html>` に `data-store-mode` が無い
  - [x] AC-02-9: **戻る**: AC-02-5・AC-02-8 の遷移後にブラウザの「戻る」を押すと、URL が `/contact/`（または `/recruit/entry/`）に戻り、その後 5 秒待っても Mailpit の件数が増えない（再送信されない）。戻った後の画面は §6 Q7 の回答どおり（回答前は「送信ボタンが押せないまま固まっていない・確認画面のまま送信中の表示が残っていない」ことだけを確認する）
  - [x] AC-02-10: **遷移しない場合**: `/contact/` の個人フォームで必須項目を空のまま確認へ進もうとすると、これまでどおり欄の下にエラーが出て URL は `/contact/` のまま。`/recruit/entry/` で必須項目を空のまま送信すると、これまでどおり欄の下にエラーが出て URL は `/recruit/entry/` のまま。どちらも Mailpit の件数が増えない
  - [x] AC-02-11: **サンクスページが無い環境**: `/contact/thanks/` を WP-CLI で下書きにすると、`/contact/` の HTML から遷移先 URL が消え、個人フォームで送信すると URL は `/contact/` のままで、同ページ内の完了表示（CF7 の送信完了メッセージ）が出る（変更前と同じ）。`/recruit/thanks/` でも同様に `/recruit/entry/` の完了表示が出る。確認後はどちらも公開に戻す
    - S3 再確認（2026-10-15）: 固定ページ 122・124 を WP-CLI で下書き → `/contact/`・`/recruit/entry/` の HTML から遷移先の属性が消え、送信すると URL はそのままで同ページ内の完了表示（CF7 の完了文面）が出た（Mailpit +2 / +1。`docs/baseline-20261015-thanks/s3/flow-draft.json`）。確認後どちらも publish に戻し、2 URL とも 200
  - [x] AC-02-12: **JS 無効**: JS を無効にした `/contact/` と `/recruit/entry/` での送信の結果が、AC-01-0 の記録と同じ
  - [x] AC-02-13: **直接アクセス**: 未ログインで `curl -s -o /dev/null -w '%{http_code} %{redirect_url}'` を `/contact/thanks/`・`/recruit/thanks/`・`/contact/thanks/?store=sendai` に対して実行すると、いずれも `200`（転送先が空）。Referer 無しの Playwright で開いても同じ内容が表示される
  - [x] AC-02-14: **noindex**: `/contact/thanks/` と `/recruit/thanks/` の `meta[name="robots"]` の content に `noindex` が含まれる。AC-01-0 で `blog_public` = 1 だった場合は `nofollow` を含まない（follow は既定のまま）。`/`・`/contact/`・`/recruit/entry/` の `meta[name="robots"]` が AC-01-0 の記録と同じ
  - [x] AC-02-15: **サイトマップ**: `/wp-sitemap.xml` から辿れるサイトマップに `/contact/thanks/` と `/recruit/thanks/` が無い。固定ページのサイトマップの URL 一覧が、AC-01-0 の記録と同じ（増えた URL が無い）。`blog_public` = 0 でサイトマップが無効な環境の場合は、その旨を報告し、除外の仕組みが入っていることをコードの確認で代える
  - [x] AC-02-16: **内部リンクが無い**: `/`・`/contact/`・`/recruit/`・`/recruit/entry/`・`/contact/thanks/` の HTML（ヘッダー・ドロワー・フッターを含む）に、`/contact/thanks/`・`/recruit/thanks/` への `a[href]` が 0 個（AC-02-4 の data 属性は除く）
  - [x] AC-02-17: AC-02-5〜AC-02-11 の操作中、コンソールエラーが 0 件

---

### F-03: 回帰（お問い合わせ・採用ページ不変・SP・支店モード・コンソール）

- 概要: 遷移の追加と FV の流用で、`/contact/`・`/recruit/entry/`・`/recruit/` の見た目と既存の挙動が変わっていないことを、AC-01-0 のベースラインとの比較で確かめる。あわせて SP・中間幅・支店モード・コンソールエラーを総点検し、`CLAUDE.md` のフレーム対応表にサンクスページの行を追記する
- Figma ノード: PC `964:47` / SP `964:78`（最終の見比べ）。CONTACT PC 親 `853:581` / SP `869:436`・`869:618`（`/contact/` 不変の確認用）
- ユーザーストーリー: サイト運営者として、サンクスページを足したことでお問い合わせや応募のページが崩れていないことを確かめたい。なぜなら、問い合わせ導線の不具合は売上に直結するから。
- ベースラインの使い方:
  - **画素比較の比較元は AC-01-0 で実装直前に取った `docs/baseline-20261015-thanks/`**（同じ条件で撮り直して比較する）
  - 既存の `docs/baseline-20261010-contact/`（`s3/pc-personal-form-853-94.png`・`s3/pc-corporate-form-853-198.png`・`s3/sp-personal-confirm.png` など）と `docs/baseline-20261013-recruit/shots/s4/`（`local-1920-entry.png`・`local-375-entry.png` など）は、その後のコミット（ヘッダー・郵便番号の自動入力など）で変わっている可能性があるため**画素比較には使わない**。構成が同じであることの参考として目視で並べるだけにする。差があった場合は「今回の変更によるものか、以前からの差か」を AC-01-0 のベースラインで切り分けて報告する
  - 同ページ内の完了表示（`s3/pc-personal-done.png`・`s4/local-1920-entry-done.png`）は、JS が動く環境では遷移に置き換わるため比較対象にしない（AC-02-11 の「サンクスページが無い環境」でのみ同じ表示になることを確認する）
- 受け入れ基準:
  - [x] AC-03-1: **お問い合わせ不変**: AC-01-0 と同じ 4 状態（初期表示・個人フォーム表示・法人フォーム表示・`?store=sendai` 初期表示）を 375 / 1920 でフルページ撮影し、ベースラインと画素比較で差分 0（差分が出たら箇所と原因を報告し不合格）。HTML の差は遷移先 URL の属性だけ。body クラス・CSS / JS のハンドルがベースラインと同じ
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/`（`shoot.cjs` で撮り直し）を `compare.py` で比較し 8 枚すべて same（差 40 超の画素 0。SP 個人・法人フォームに 40 以下の揺らぎ 309 / 371 画素）。HTML（`?ver=` 除外）の差は (a) 個人・法人フォームの `data-contact-thanks-url`（`/contact/` は `/contact/thanks/`、`?store=sendai` は `/contact/thanks/?store=sendai`）と (b) body クラスの `page-parent` だけ。(b) は子ページ `thanks` ができたため WordPress 本体の `get_body_class()` が付けるもので、テーマの変更ではないため**許容差**とする（テーマの CSS / JS に `page-parent` を参照する箇所は無い）。CSS / JS のハンドル一覧はベースラインと同一
  - [x] AC-03-2: **採用不変**: `/recruit/entry/` と `/recruit/` を 375 / 1920 でフルページ撮影し、ベースラインと画素比較で差分 0。HTML の差は `/recruit/entry/` の遷移先 URL の属性だけ。body クラス・CSS / JS のハンドルがベースラインと同じ
    - S3（2026-10-15）: `/recruit/entry/` は 375 / 1920 とも差分 0、HTML の差は `data-recruit-entry-thanks-url` だけ（`/recruit/` は採用ページ自体に子ページ `entry` が以前からあり、body クラスも同一）。`/recruit/` 1920 は FV の粒子（`js/recruit.js` の `Math.random`）の canvas 内だけに差（40 超 1,346 画素・bbox 401,95〜1462,1222）。撮り直し 3 回の相互比較でも canvas 内に 868〜1,187 画素の同程度の差が出て、canvas の矩形（`docs/baseline-20261015-thanks/s3/jitter/canvas-rect.json`）を除くと全組み合わせで差分 0（`docs/baseline-20261015-thanks/s3/compare-recruit.py`）→ 撮影ごとの揺らぎとして**許容差**。375 は 40 超 0。CSS / JS ハンドル・body クラスは同一
  - [x] AC-03-3: **お問い合わせの既存挙動**: `/contact/` で個人 / 法人の切り替え・支店の選択・方法の選択（メール / LINE / 電話）・入力チェック・確認表示・「修正する」で入力に戻る・郵便番号から住所の自動入力が、変更前と同じに動く（1920 と 375。375 では電話が `tel:` 直リンクのまま）
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/contact-behavior.cjs` → `contact-behavior.json`。1920 / 375 とも 種別 → 支店（aria-pressed・隠しフィールド `store-slug`=sendai・LINE の href・電話番号の差し替え）→ 方法 → 空で確認すると欄の下にエラー 5 件 → 郵便番号 9800021 で「宮城県仙台市青葉区中央」→ 確認表示 →「修正する」で入力表示（入力は保持）→ 法人へ切り替え（法人の郵便番号 0300801 で「青森県青森市新町」）→ 個人へ戻すと支店・方法・入力を保持。1920 の電話は電話ブロックを表示、375 の電話は `a[href="tel:0223548993"]`。コンソールエラー 0・Mailpit 増加 0
  - [x] AC-03-4: **参考比較**: `docs/baseline-20261010-contact/s3/` と `docs/baseline-20261013-recruit/shots/s4/` の同条件の画像と、現在の `/contact/`・`/recruit/entry/` を目視で並べ、構成が同じ。差があれば AC-01-0 のベースラインにも同じ差があること（= 今回の変更によらない）を確認して報告する
    - S3（2026-10-15）: 並べた画像 `docs/baseline-20261015-thanks/s3/ref-*.png`。法人フォーム（1920）と `/recruit/entry/`（1920）は旧画像と画素一致。個人フォーム（1920）は旧画像と差があるが、撮影条件の違い（旧は青森支店を選択・ヘッダーの位置が異なる時点）で、AC-01-0 のベースラインにも同じ差（40 超 58,182 画素）があり今回の変更によらない。SP の確認表示・`/recruit/entry/` 375 は構成が同じ
  - [x] AC-03-5: **SP**: 375 で `/contact/thanks/` と `/recruit/thanks/` を開き、ドロワーの開閉・ドロワー内のリンクの移動ができる。Explore のカードをタップすると該当ページへ移動する。375 / 768 / 1024 / 1025 / 1280 / 1920 で 2 ページとも横スクロールが無い
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/checks.cjs` → `checks.json`。375 で 2 ページとも ハンバーガーで開く → × で閉じる → Esc で閉じる、ドロワーの WORKS で `/works/` へ移動、Explore 3 枚のタップで `/works/`・`/plans/`・`/dx/` へ移動（200）。375 / 768 / 1024 / 1025 / 1280 / 1920 × 2 ページの 12 件で `scrollWidth <= innerWidth`。支店モード 375 も `docs/baseline-20261015-thanks/s3/sp-store.cjs`（ドロワーに「仙台支店」、内部リンクに `?store=sendai`）
  - [x] AC-03-6: **支店モード**: `exterior_exone_stores()` の全 slug について `/contact/thanks/?store=<slug>` を 1920 で開くと、ヘッダーに該当支店名のリンクがあり、Explore の href に `store=<slug>` が付く。`/contact/thanks/?store=unknown` はコーポレート表示（`data-store-mode` 無し）で、リンクに `store=` が付かない
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/checks.json` の `store`。sendai / totsuka / morioka / aomori / hachinohe / hirosaki の 6 支店とも `data-store-mode` あり・`.p-header__store` が該当支店名で href `/store/<slug>/`・Explore の href 3 本に `?store=<slug>`。`?store=unknown` は `data-store-mode` 無し・ヘッダーの支店リンク無し・`store=` を含むリンク 0
  - [x] AC-03-7: **コンソール**: `/`・`/contact/`・`/contact/?store=sendai`・`/contact/thanks/`・`/contact/thanks/?store=sendai`・`/recruit/`・`/recruit/entry/`・`/recruit/thanks/`・`/works/`・`/plans/`・`/dx/` を 1920 / 375 で開き、コンソールエラーが 0 件
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/checks.json` の `console`。11 URL × 1920 / 375 の 22 件すべて 200・コンソールエラー 0（最後までスクロールして確認）。PHP のエラーログ（`logs/php/error.log`）に 2026-10-15 の Warning / Notice / Fatal は 0 件
  - [x] AC-03-8: **読み込みの範囲**: `thanks.css` がサンクスページ 2 つ以外の HTML に含まれない。`contact.css` を読むのは `/contact/` とサンクスページ 2 つだけ。CF7 のスクリプトを読むページが変更前と同じ（`/contact/` と `/recruit/entry/` だけ）
    - S3（2026-10-15）: `docs/baseline-20261015-thanks/s3/assets-scope.txt`（固定ページ全件・WORKS / コラムの一覧と詳細・お知らせ・支店ページ 6 件・`/contact/?store=` 6 件などの 36 URL を curl）。`css/thanks.css` を含むのはサンクスページ 2 つ（`?store=` 付きを含む）だけ。`css/contact.css` は `/contact/`（`?store=` 付きを含む）とサンクスページ 2 つだけ。CF7 のスクリプトは `/contact/` と `/recruit/entry/`（各 `?store=` 付き）だけ
  - [ ] AC-03-9: **最終の見比べ**: `/contact/thanks/` と `/recruit/thanks/` の 1920 / 375 のスクリーンショットを Figma `964:47` / `964:78` と並べ、構成・配置・配色・タイポグラフィ・余白感が一致する（採用版は文言の差を除く）
    - S3（2026-10-15）: Generator 環境では Figma MCP が使えないため未判定。比較用のスクリーンショット `docs/baseline-20261015-thanks/s3/thanks-pc-contact-thanks.png`・`thanks-pc-recruit-thanks.png`・`thanks-sp-*.png`（支店モード版も）を用意。数値の再確認は AC-03-11 で合格 → **evaluator の見比べ待ち**
  - [x] AC-03-10: `CLAUDE.md` のフレーム対応表に「サンクス（送信完了。`/contact/thanks/`・`/recruit/thanks/`）| `964:47`（CONTACT サンクスページ。親 `853:582`）| `964:78`（SP サンクスページ。中身は y2127 まで）」の行がある
    - S3（2026-10-15）: `CLAUDE.md` の「フレーム対応表」の CONTACT の次の行に追加（noindex・採用版の文言の正は決定事項 §2 の旨も記載）
  - [ ] AC-03-11: F-01・F-02 の AC を 375 / 1920 で再確認し、すべて合格のまま
    - S3（2026-10-15）: F-01 は `docs/baseline-20261015-thanks/s3/recheck-f01.cjs` → `recheck-f01.json`（2 ページ × 1920 / 375 で AC-01-3・5〜8・10〜12 の数値・書式と JS 無効の表示。NG 0）と `docs/baseline-20261015-thanks/s3/q8.cjs`（Q8 の読み込み時フェードイン・reduced-motion・JS 無効）。F-02 は `docs/baseline-20261015-thanks/s3/flow.cjs`（個人 1920・法人 375・支店 sendai・採用 1920・採用 `?store=aomori` 375・空送信 2 種、戻る 2 種。Mailpit 46 → 54 で各 +2 / +2 / +2 / +1 / +1、空送信・戻る +0）、`flow-draft.json`（AC-02-11）、`nojs-submit.json`（AC-02-12。遷移先 URL・文言・Mailpit 増加がベースラインと同じ。JS 無効の文脈で Playwright の安定待ちが終わらないことがあるため送信ボタンは force で押した `nojs-submit-s3.cjs`）、noindex・内部リンク 0・直接アクセス 200 を curl で確認。**AC-01-19（Figma 見比べ）だけは AC-03-9 と同じく evaluator 待ち**のため未チェック

---

## 4. 情報設計

### 表示面 / URL / テンプレート
- `/contact/thanks/` … 固定ページ `thanks`（親 `contact` ID 78）。テンプレート `page-thanks.php`（テンプレート階層の `page-<slug>` で当たる）。お問い合わせ版
- `/recruit/thanks/` … 固定ページ `thanks`（親 `recruit` ID 105）。同じ `page-thanks.php`。採用版
- テンプレート内の構成: FV（`template-parts/contact/section-fv.php` を流用）→ 完了メッセージ → Next Step → Explore EXone。テンプレートパーツの分け方・置き場所は Generator が決める（既存の `template-parts/<ページ>/section-*.php` の作法に倣う）
- どちらのサンクスかは親ページの slug で判定する。親が `contact` / `recruit` 以外の `thanks` ページ（将来誤って作られた場合）はお問い合わせ版にせず、Generator が安全な扱い（例: 通常の固定ページ表示）を選んで報告する
- 支店モード: `/contact/thanks/?store=<slug>`（既存 `inc/store-mode.php`）
- 既存で変更するもの: `/contact/`（個人・法人フォームの送信成功時の遷移と、遷移先 URL の属性）、`/recruit/entry/`（同じ）、`functions.php` のヘッダーのオーバーレイ判定・body クラス・CSS の読み込み
- 既存で変更しないもの: `/contact/`・`/recruit/entry/`・`/recruit/` の見た目、フォームの項目・メール・宛先

### データ（名前と用途のみ）
- 固定ページ `thanks` × 2（本文は使わない。表示はすべてテンプレートとデータ関数）
- CPT・タクソノミー・カスタムフィールド: なし
- テーマ内のデータ（`inc/thanks-data.php` を想定。`inc/contact-data.php` の配列を返す関数の作法に倣う）: サンクスページかどうか・どちらの版かの判定、版ごとの文言（決定事項 §2）、FV のデータ（`exterior_exone_contact_fv()` と同じ写真・文言だけ差し替え）、Next Step 3 枚、Explore 3 枚（画像・リンク先。リンクは `exterior_exone_store_link()` 経由）、遷移先 URL（お問い合わせは支店モード付き、採用は無し。ページが無ければ空）、`noindex`、サイトマップからの除外（`inc/news-page-data.php` の除外の書き方に倣う）

### トークン（`css/thanks.css` の `:root` と幅ごとの比例ブロックに追加。接頭辞 `--thanks-`）

既存プロジェクトの作法（`css/contact.css`）に倣い、**幅で変わらない値（色・影・行高）は `thanks.css` の `:root`**、**長さは PC（1025 以上）/ SP（1024 以下）の比例ブロック**に置く。比例の単位は `contact.css` の `--contact-vu`（1920 比例）/ `--contact-su`（375 比例・上限 `--sp-scale-max`）を共用してよい（サンクスページは `contact.css` を読むため）。サイト共通の `style.css` の `:root` に足す必要がある値は**無い**（色はすべて既存の `--color-*`、書体は既存の `--font-*` / `--fw-*` で足りる）。

既存トークンで足りる値（新規に作らない）:

| 値 | 用途 | 既存トークン |
|---|---|---|
| #f3f3f4 / #fefefe / #ffffff | 上段の地 / 下段の地・Explore の白文字 / Next Step カードの地 | `--color-white4` / `--color-white2` / `--color-white1` |
| #d9d9d9 | THANK YOU・番号・アイコンの線 | `--color-gray2` |
| #4d4d4d / #1a1a1a / #000 | 本文・小見出し・カード文字 / 送信完了・和文見出し・丸チェックの円 / 丸チェックの ✓ | `--color-black1` / `--color-black2` / `--color-black0` |
| rgba(0,0,0,.2)、FV の高さ・切り抜き・THANK YOU・送信完了の書式と位置 | FV | `contact.css` の `--contact-mask`・`--contact-fv-*`・`--contact-title-*`・`--contact-sub-*` |
| 160 / 80 | 最後のカードの下の余白 | `contact.css` の `--contact-end` |
| 1600 | PC のカード列の幅（x160〜1760） | `--content-width` |
| Antonio SemiBold / Noto Sans JP / SF Pro Medium | 英字 / 和文 / 「Next Step」「Explore EXone」 | `--font-display` / `--font-base` / `--font-ui` + `--fw-*` |

新規に追加する値（PC 1920 / SP 375 のカンプ値。比例ブロックで単位を掛ける）:

| 用途 | PC | SP | 区分 |
|---|---|---|---|
| DX カードの暗幕 | rgba(0,0,0,.1) | 同左 | 色（`:root`） |
| カードの影 | 0 12px 32px rgba(0,0,0,.12) | 0 6px 16px rgba(0,0,0,.12) | 影（PC / SP で切替。SP は `--plans-card-shadow` と同値だが参照の独立のため別名で持つ） |
| カードの角丸 | 10 | 5 | 長さ |
| SP の左右余白 | — | 22 | 長さ |
| 丸チェックの径 / ✓ の線の太さ / 円の線の太さ | 146 / 3 / 1 | 54 / 2 / 1 | 長さ |
| 「送信完了」下 → 丸チェック | 89 | 45 | 長さ |
| 丸チェック → 一文 / 一文 → 本文 | 80 / 29 | 40 / 15 | 長さ |
| 一文の書式 | 24・Bold・ls 2 | 14・Medium・ls 1 | 長さ + 太さ |
| 本文の書式 / 幅 / 段落間 | 18・Regular・ls 2・lh 1.5 / 849 / 27 | 11・ls 1 / 306 / 16.5 | 長さ（行高 1.5 は `:root`） |
| 本文 → 「Next Step」 | 160 | 80 | 長さ |
| 英字小見出し（Next Step / Explore EXone） | 32・ls 1・lh 1.12 | 14 | 長さ（行高は `:root`） |
| 英字小見出し → 和文見出し | 20（19.86） | 8 | 長さ |
| 和文見出し（今後のご案内 / 理想の外構づくりを…） | 36・Bold・ls 2・lh 1.6 | 16・ls 1 | 長さ（行高は `:root`） |
| 和文見出し → Next Step カード上端 | 129 | 40 | 長さ |
| Next Step カード | 509×341・列間 37（3 列で 1600） | 331×94・段間 24 | 長さ |
| 番号 | 100・ls -3、カード上端の 73 上・カード左 +29 | 36・ls -1、カード上端の 24 上・カード左 +15 | 長さ（Antonio の行高 1.29 は `contact.css` の `--contact-display-lh` を共用可） |
| アイコン（封筒 / オペレーター / 家）の表示枠 | 143×103 / 100×119 / 194×101、下端 = カード上端 +166 | 56×40 / 44×52 / 68×35、中心 = カード左 +50・上下中央 | 長さ |
| カード題 | 22・Bold・ls 2、カード上端 +196 | 13・ls 1、左端 = カード左 +97 | 長さ |
| 題 → 説明 / カード説明 | 15 / 16・Regular・ls 2 | 1〜2 / 11・ls 1 | 長さ |
| Next Step カード下 → 地の切り替え | 160 | 80 | 長さ |
| 地の切り替え → 「Explore EXone」 | 157 | 80 | 長さ |
| 和文見出し → Explore カード上端 | 80 | 40 | 長さ |
| Explore カード | 509×286（16:9）・列間 37 | 331×186（16:9）・段間 10 | 長さ（高さは幅と 16:9 から決めてよい） |
| Explore 英字 | 64・ls -3、カード上端 +137 | 40・ls -2、カード上端 +93 | 長さ |
| Explore 和文 | 18・Medium・ls 2、英字下 +5 | 12・ls 1、英字下 +3 | 長さ |

（ホバー・出現の動きの値は §6 Q1〜Q3 の回答後に追加する。既存の `--reveal-*`・`--plans-card-*-hover` を使う場合は新規不要）

### 追加アセット
- CSS: `css/thanks.css`（サンクスページだけで読む。`contact.css` の FV 部分と併用）
- JS: 新規ファイルは不要の見込み。遷移は既存の `js/contact-form.js` と `js/recruit-entry.js` の送信成功時の処理を置き換える。丸チェックの描画・出現を JS で行う場合は §6 Q2・Q3 の回答後に追加（既存の共通の出現の仕組みがあれば再利用）
- 画像（新規。いずれも AI 生成の**差し替え前提の仮画像**）:
  - 丸チェック: `docs/figma-contact-thanks-assets/icon-check-964-72.svg`（PC）/ `sp-icon-check-964-102.svg`（SP）の図形。インライン SVG を推奨（線色をトークンで扱うため）
  - Next Step アイコン 3 点: `icon-mail-966-152.png`・`icon-operator-966-150.png`・`icon-house-966-187.png`（透過 PNG・線 ≈ #d8d8d8）。形式は §6 Q5 の回答どおり。置き場所は `images/thanks/`（メモは `images/contact/` を挙げているが、採用版でも使うためサンクス専用の場所を既定とする。変える場合は報告）
- 画像（流用。新規配置なし）: FV `images/contact/pc-fv-bg-864-390.jpg`、Explore `images/works/pc-fv-bg-821-116.jpg`・`images/plans/pc-fv-bg-718-242.jpg`・`images/top/dx_video-poster.jpg`（各 `webp/` 版あり）

### 管理画面 / WP-CLI で行う操作
- 固定ページ `thanks` を 2 件作成（親 78 / 親 105。公開。本文は空でよい。タイトルは F-02 の既定）。メディアの追加は無い
- 管理画面での設定は不要（文言はテーマのデータ関数。管理画面で `thanks` の本文を書いても表示されない）

---

## 5. スプリント計画

### S1: お問い合わせ版サンクスページ（ページ作成 + テンプレート + PC / SP の見た目）
- 含む機能: F-01
- 完了条件: F-01 の AC すべて合格（AC-01-20 は §6 の回答状況に従う）
- 前提: AC-01-0 のベースラインを**最初に**取得すること（テーマの変更前）。§6 Q1〜Q5 の回答があれば反映、無ければ静止表示・ホバー変化なし・アイコンは推奨案で進め、回答後に差分を当てる

### S2: フォームからの遷移・採用版・noindex・サイトマップ除外
- 含む機能: F-02
- 完了条件: F-02 の AC すべて合格、かつ F-01 の AC が引き続き合格
- 前提: S1（遷移先のページと見た目ができていること）。§6 Q6（採用版の太字・改行）・Q7（戻ったときの表示）の回答があれば反映

### S3: 回帰（お問い合わせ・採用ページのベースライン比較・SP・コンソール・支店モード）
- 含む機能: F-03
- 完了条件: F-03 の AC すべて合格
- 前提: S2。§6 の要確認がすべて回答済みで、その内容が S1・S2 に反映されていること（未回答が残る場合は、未回答の項目を明記したうえで残りを判定する）

---

## 6. 要確認（ユーザーに聞く。勝手に決めない）

各項目とも先頭が推奨案。呼び出し側がまとめてユーザーに確認し、回答を決定事項 §4 に転記する。

- **Q1: Explore カード（リンク）のホバー表現**（カンプに指示なし。メモ §8-8）
  1. **カードが少し浮き、影が深くなる（TOP の PLANS カードのホバーと同じ量・速さ。既存トークン `--plans-card-lift-hover`・`--plans-card-shadow-hover` を流用）** … 推奨。理由: 角丸 + 影のカードという見た目が TOP の PLANS カードと同系統で、サイト内の操作感が揃う
  2. 写真だけがゆっくり少し拡大する（枠はそのまま）
  3. 暗幕が濃くなる（3 枚とも。DX 以外はホバー時だけ暗幕が出る）
  4. 変化なし
  - 共通: タッチ端末（`hover: none`）では変化させない。Next Step カードはリンクではないので変化なし（既定）
- **Q2: 丸チェックの出現アニメーション**（メモ §8-14）
  1. **ページを開いたとき 1 回、円 → ✓ の順に線を描く（速さ・緩急は支店ページの線画の描画と同じ）** … 推奨。理由: 支店ページに同種の線画の描画が既にあり、送信完了の手応えを最小の追加で出せる
  2. フェードインだけ
  3. 動きなし（最初から表示）
  - 共通: `prefers-reduced-motion: reduce`・JS 無効では最初から表示
- **Q3: 各ブロック（完了メッセージ・Next Step・Explore のカード）のスクロール出現**（メモ §8-14）
  1. **他ページと同じ「下からフェードイン」（既存の `--reveal-*`。カード 3 枚は順に少しずつ遅らせる）** … 推奨。理由: `/contact/` を含む他ページと同じ演出で、遷移前後で体験が揃う
  2. 動きなし（最初から表示）
- **Q4: 中間幅（1025〜1919px）での Next Step / Explore の 3 列**（メモ §8-11）
  1. **1025px 以上は 3 列のまま `/contact/` と同じ比例縮小、1024px 以下で SP の縦 3 段に切り替える（ブレイクポイントは既存の 1024 / 1025 のみ）** … 推奨。理由: 他ページと同じ規則でブレイクポイントを増やさない。ただし 1025px ではカード幅約 272px・カード説明が約 8〜9px 相当まで縮む
  2. 1 と同じだが、文字サイズに下限（例: 本文・説明は 12px 未満にしない）を設ける（カード内の行数が増える場合あり）
  3. 新たな切り替え幅（例: 1280px）を設け、それ未満は SP の縦 3 段にする
- **Q5: Next Step のアイコン 3 点の素材形式**（メモ §8-13）
  1. **描画部分でトリミングした WebP（`images/thanks/` に PC 用の解像度で置き、SP は縮小表示）** … 推奨。理由: AI 生成の線画をそのまま忠実に出せ、他の画像と同じ `webp/` の扱いで差し替えも容易（SVG 化は手作業のトレースになり形が崩れやすい）
  2. SVG にトレースし、線色を `--color-gray2` で管理する（SP の縮小でも線が潰れにくいが、トレースの品質確認が要る）
  3. 書き出した PNG をそのまま縮小表示（余白込みの大判のため容量が大きい）
- **Q6: 採用版の本文の太字と改行位置**（カンプに採用版が無い）
  1. **お問い合わせ版と対応する位置: 段落 2 の「追って採用担当よりご連絡いたします。」を太字にし、改行も対応させる（F-02「表示仕様（採用版）」の推奨どおり）** … 推奨。理由: 2 つのページで見た目のリズムが揃い、利用者が最も知りたい「連絡が来る」ことが目立つ
  2. 太字なし（改行だけ対応させる）
- **Q7: 送信後にブラウザの「戻る」でフォームのページに戻ったときの表示**（カンプに無い状態）
  1. **フォームを入力前の状態（`/contact/` は選択前の初期表示、`/recruit/entry/` は空のフォーム）に戻す** … 推奨。理由: 送信済みの内容で確認画面が残ると、二重送信や「送れていないのでは」という誤解を招く
  2. 入力内容を残した入力画面に戻す（確認画面ではない。送信ボタンは押せる）
  3. ブラウザに任せる（戻り方によって状態が変わる。作り込まない）

## 7. オープン論点（Generator は括弧内の既定で進め、変えた点・気づいた点を報告する）

- Q-A: 遷移先 URL を組めない環境（`thanks` の固定ページが無い・非公開）… 既定: 遷移せず現状の同ページ内完了表示（AC-02-11）。404 ページへ飛ばさないため。本番で固定ページを作る前にテーマだけ反映しても問い合わせが壊れない
- Q-B: アイコンの置き場所 … 既定 `images/thanks/`（採用版でも使うため。メモの `images/contact/` 案から変更）
- Q-C: 固定ページのタイトル … 既定「送信完了（お問い合わせ）」「送信完了（ご応募）」（画面には出ず `<title>` にだけ出る）
- Q-D: `contact.css` の FV 部分の共有方法 … 既定: サンクスページでも `contact.css` を読み、ページ単位の指定（ページ地・JS 無効時の表示）はサンクス側で持つ。FV 部分を別ファイルに切り出す方式を選ぶ場合は理由を報告し、`/contact/` の表示結果が不変であること（AC-03-1）を満たす
- Q-E: 丸チェックの線色 … 既定: 円 `--color-black2`（カンプ #1D1D1F）、✓ `--color-black0`（カンプ #000）
- Q-F: 「通常2営業日以内」の支店ごとの定休日との整合（メモ §8-15）… 決定事項の確定文どおり出す（範囲外）。問題があれば報告のみ

## 8. 公開前チェックリスト（本番反映時。決定事項 §5 が正。今回のスプリントでは実施しない）

- 本番でも `thanks` の固定ページ 2 件（親 `contact` / `recruit`）を作成する（テーマだけでは URL ができない。作る前はフォームの同ページ内完了表示のまま動く = Q-A）
- `/contact/thanks/`・`/recruit/thanks/` が `noindex` で、`/wp-sitemap.xml` に出ないこと（本番 `blog_public=1`）
- 送信 → 遷移 → 戻るで再送信にならないこと
- 支店モード（`/contact/?store=sendai` 等）で送信後も支店ヘッダーのままか
- 計測（GA 等）でコンバージョンを取る場合は、サンクスページの URL を目標に設定する（範囲外。必要なら別途）

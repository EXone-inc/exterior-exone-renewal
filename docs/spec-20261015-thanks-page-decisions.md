# サンクスページ（送信完了）— 決定事項

- 作成: 2026-10-15
- カンプ: Figma `964:47`「CONTACTページ　サンクスページ」（PC 1920×2864）/ `964:78`「SP サンクスページ」（SP 375×3130）。設計メモ `docs/figma-contact-thanks-page.md`、アセット `docs/figma-contact-thanks-assets/`
- 本ファイルは、ユーザーの指示・回答と、それを受けて呼び出し側が決めた既定をまとめたもの。仕様書（`docs/spec-20261015-thanks-page.md`）と食い違う場合は**本ファイルが正**
- 関連: お問い合わせ `docs/spec-20261010-contact-page-decisions.md`、採用 `docs/spec-20261013-recruit-page-decisions.md`（どちらも「完了は同じページにメッセージ表示」だったが、本件で**別ページへ遷移**に変わる）

## 1. ユーザーの指示（2026-10-15）

- 「コンタクトページでフォーム送信後は、選択しているサンクスページを表示させてください。採用応募フォームも同じサンクスページを使って文章を変えてください。URLは `/contact/thanks/`, `/recruit/thanks/` にしてください。直接アクセス時は、普通に表示させて noindex をつけて」

## 2. ユーザーの回答（2026-10-15）

| 質問 | 回答 |
|---|---|
| 採用版の文言 | 呼び出し側の案をベースに、**日数を書かない**（「通常2営業日以内に」を外し「追って採用担当よりご連絡いたします」にする） |
| 採用版の下部「Explore EXone」の 3 枚 | **お問い合わせ版と同じ 3 枚**（OUR WORKS / OUR PLANS / DX EXPERIENCE。= 推奨） |

### 文言（確定）

| 箇所 | お問い合わせ `/contact/thanks/`（カンプどおり） | 採用 `/recruit/thanks/` |
|---|---|---|
| FV | THANK YOU / 送信完了 | 同じ |
| 太字の一文 | お問い合わせ、ありがとうございます。 | ご応募、ありがとうございます。 |
| 本文 1 | この度は、EXoneへお問い合わせいただき、誠にありがとうございます。お問い合わせを正常に受け付けました。 | この度は、EXoneの採用にご応募いただき、誠にありがとうございます。ご応募を正常に受け付けました。 |
| 本文 2 | ご入力いただいた内容を確認のうえ、通常2営業日以内に担当スタッフよりご連絡いたします。ご連絡まで、今しばらくおまちください。 | ご入力いただいた内容を確認のうえ、追って採用担当よりご連絡いたします。ご連絡まで、今しばらくおまちください。 |
| Next Step 見出し | Next Step / 今後のご案内 | 同じ |
| Next Step 01 | お問い合わせ完了 — お問い合わせを正常に受け付けました。 | ご応募完了 — ご応募を正常に受け付けました。 |
| Next Step 02 | 担当スタッフよりご連絡 — 通常2営業日以内に、担当スタッフよりご連絡いたします。 | 採用担当よりご連絡 — 内容を確認のうえ、追って採用担当よりご連絡いたします。 |
| Next Step 03 | ご相談内容の確認・ご案内 — ご要望を詳しくお伺いし、内容に応じて今後の進め方をご案内いたします。 | 選考のご案内 — 書類選考のうえ、面接日程など今後の流れをご案内いたします。 |
| Explore EXone | Explore EXone / 理想の外構づくりを、もっと身近に。OUR WORKS（理想の暮らしを形にした事例を見る）/ OUR PLANS（あなたに合った外構プランを探す）/ DX EXPERIENCE（EXoneの新しい外構体験を知る） | 同じ |

改行位置・細部の表記（「おまちください」等）は設計メモの原文に従う。カンプ原文と上表が食い違う場合はカンプが正。

## 3. 既定（呼び出し側が決めた。変えたい場合はユーザーに確認）

- **ページ**: 固定ページ `thanks` を `contact`（ID 78）と `recruit`（ID 105）の子として WP-CLI で作成（→ `/contact/thanks/`・`/recruit/thanks/`）。同じ slug でも親が違うので WP 上は共存できる。テンプレートは共通の `page-thanks.php` 1 本（slug `thanks` にマッチ）。どちらのサンクスかは親ページの slug で判定する関数を用意し（`exterior_exone_is_recruit_entry_page()` と同じ作法）、文言は `inc/` のデータ関数で出し分ける
- **遷移**: CF7 の AJAX 送信成功（`wpcf7mailsent`）で JS がサンクスページへ遷移する（`contact-form.js`・`recruit-entry.js` の完了表示を置き換える）。遷移先 URL はサーバー側で組んでテンプレートの data 属性で渡す。お問い合わせは**支店モードの `?store=` を引き継ぐ**（`exterior_exone_store_link()` 経由。支店ヘッダーのままサンクスページを出す）。採用は引き継ぎ無し
- **CF7 の JS が動かないとき**（通常 POST）: CF7 が同じページに完了メッセージを出す現状の挙動を残す（フォールバック。作り込まない）
- **直接アクセス**: ユーザー指示どおり普通に表示（`/contact/` へ戻さない）。`wp_robots` で `noindex`（follow は既定のまま）。サイトマップから除外。メニュー・パンくず・内部リンクからは張らない
- **ヘッダー**: お問い合わせ・採用と同じ扱い（コーポレートではオーバーレイヘッダー + FV 写真帯。`exterior_exone_has_fv_header()` に追加）。支店モードは支店ヘッダー
- **共通 CSS/JS の再利用**: 見出し（英字 + 日本語）・ボタン・写真帯は既存部品があればそれを使う。Explore の 3 枚は TOP 等の既存画像で代用できるものは流用（設計メモの対応表に従う）
- **DX EXPERIENCE のカード**: カンプのレイヤー名が `video` でも、サンクスページでは静止画で出す（動画は読み込まない）。設計メモで動画の指定が明確なら要確認に回す
- **FV**: 設計メモのとおり CONTACT ページの FV と同一（写真・切り抜き・文字位置）→ `template-parts/contact/section-fv.php` / `.p-contactfv` を流用し文言だけ「THANK YOU / 送信完了」に差し替える。カンプで背面に隠れている `966:118` は使わない
- **Explore の 3 枚**: 既存画像を流用（WORKS `images/works/pc-fv-bg-821-116.jpg`、PLANS `images/plans/pc-fv-bg-718-242.jpg`、DX `images/top/dx_video-poster.jpg`）。カード全体を `/works/` `/plans/` `/dx/` へのリンクにする（支店モードは `exterior_exone_store_link()`）。新規に置くのは Next Step のアイコン 3 点と丸チェックだけ
- **カンプが指定している値はカンプに従う**（SP 左右余白 22、DX カードだけの 10% 暗幕、PLANS カードの影なしは他 2 枚に合わせる＝不備扱い）。SP の Next Step 03 の説明文は PC の文言を使う（SP は 02 のコピペ残り）
- カンプに無い状態（ホバー・アイコンの出現アニメーション等）は planner が要確認として列挙し、呼び出し側がユーザーに確認する

## 4. 要確認（仕様書 §6 から転記）

### ユーザーの回答（2026-10-15。すべて推奨どおり）

| 質問 | 回答 |
|---|---|
| Q1 Explore 3 枚のホバー | **TOP のカードと同じ**（少し浮いて影が深くなる）。タッチ端末では変化なし。Next Step カードは変化なし |
| Q2 丸チェックの出現 | **開いたとき 1 回、円 → ✓ の順に線を描く**（支店ページの線画と同じ速さ） |
| Q3 各ブロックのスクロール出現 | **他ページと同じ**下からのフェードイン（`--reveal-*`） |
| Q4 中間幅（1025〜1919px）の 3 列 | **3 列のまま比例縮小**（CONTACT と同じ作法。1024px 以下で縦 3 段。ブレイクポイントは増やさない） |
| Q8 PC 1920×1080 で開いたとき、一文と本文（`.p-thanksmsg__text`）が画面下端ぎりぎりでスクロール出現の条件を満たさず見えない（S1 evaluator の指摘） | **開いた時点で出す**。丸チェックの線に続けて、スクロールしなくても一文と本文がフェードインする（他のブロックはそのままスクロール出現）。2026-10-15 回答 |

### 呼び出し側の既定（実装上の判断。ユーザーには聞いていない）

| 項目 | 既定 |
|---|---|
| Q5 Next Step アイコンの形式 | 描画部分だけトリミングした WebP（SVG トレースはしない） |
| Q6 採用版の太字・改行位置 | お問い合わせ版と対応する位置（「追って採用担当よりご連絡いたします。」を太字） |
| Q7 送信後に「戻る」で戻ったときのフォーム | 入力前の初期状態（`pageshow` の復元時も入力ビューに戻す） |
| Q-A `thanks` の固定ページが無い・非公開の環境 | 遷移せず、現状の同ページ内の完了表示を出す（本番で固定ページを作る前にテーマだけ反映しても壊れない） |
| Q-B アイコンの置き場所 | `images/thanks/`（採用版でも使うため） |
| トークン | `style.css` の `:root` に追加する値は無し。新しい値は `css/thanks.css` の `:root` と PC / SP の比例ブロックに `--thanks-*` で置く（`contact.css` の作法） |
| 回帰の比較元 | 既存ベースラインは使わず、S1 の最初（テーマを触る前）に `docs/baseline-20261015-thanks/` を撮り直す |

### 進捗

- S1（2026-10-15）: `/contact/thanks/`（固定ページ ID 122・親 78）・`page-thanks.php`・`inc/thanks-data.php`・`template-parts/thanks/`・`css/thanks.css`・アイコン `images/thanks/*.webp`。ベースライン `docs/baseline-20261015-thanks/`。AC-01-19（Figma との見比べ）は evaluator 待ち
- S2（2026-10-15）: `/recruit/thanks/`（固定ページ ID 124・親 105・タイトル「送信完了（ご応募）」）。遷移先は `exterior_exone_thanks_url()`（`inc/thanks-data.php`）→ `data-contact-thanks-url`（`template-parts/contact/form.php`）/ `data-recruit-entry-thanks-url`（`page-recruit-entry.php`）。`js/contact-form.js`・`js/recruit-entry.js` が `wpcf7mailsent` で `location.assign`、`pageshow`（bfcache 復元）で入力前の状態へ。noindex は `wp_robots`、サイトマップ除外は `wp_sitemaps_posts_query_args`。SP の採用版 Next Step 03 は「流れを」の後でも改行（お問い合わせ版 03 と同じ扱い）
- Q8 対応済み（2026-10-15）: `template-parts/thanks/section-message.php` の `.p-thanksmsg__text` から `data-reveal` を外し、`css/thanks.css` のキーフレーム `p-thanksmsg-in`（`--reveal-duration` / `--reveal-ease` / `--reveal-shift` を流用、開始 `--thanks-text-delay` = `--diagram-draw-start` + `--thanks-check-gap` = 0.65s、fill `backwards`）で読み込み時にフェードイン。`(scripting: none)`・`prefers-reduced-motion: reduce` では `animation: none`。要素の top は変化なし
- S3（2026-10-15）: 回帰の証拠は `docs/baseline-20261015-thanks/s3/`（撮り直し・比較スクリプト・JSON）。`/contact/` 4 状態 × 375 / 1920 と `/recruit/entry/` は画素差分 0。許容差は 2 つ: (1) `/contact/` の body クラスに `page-parent`（子ページ `thanks` ができたため WP 本体が付ける。テーマは参照しない）、(2) `/recruit/` 1920 の FV 粒子 canvas 内の差（`recruit.js` の `Math.random`。撮り直し同士でも同程度の差が出て、canvas を除くと差分 0）。HTML の差は遷移先 URL の data 属性だけ。テーマの修正は無し。`CLAUDE.md` のフレーム対応表にサンクスの行を追加。AC-03-9・AC-01-19（Figma との見比べ）は evaluator 待ち。TOP・支店ページは AC-01-0 に画素ベースラインが無いため、読み込み範囲（`thanks.css`・`contact.css` が入らない）・コンソール 0・サンクス用の分岐が `exterior_exone_is_thanks_page()` で閉じていることで代えた。検証で Mailpit は 46 → 64 件（S3 の送信テスト 18 件）
- **S1〜S3 全合格**（2026-10-15、evaluator）。S3 の Figma 見比べ（AC-03-9 / AC-01-19）は MCP 切断のため S1 evaluator の比較画像と設計メモの数値で代替（PC 1920 は S1 で直接見比べ済み）。Mailpit は検証で 71 件まで増加。コミットは未実施

## 5. 公開前チェックリスト

- [ ] 本番でも `thanks` の固定ページ 2 つ（親 `contact` / `recruit`）を作成する（テーマだけでは URL ができない）
- [ ] `/contact/thanks/`・`/recruit/thanks/` が `noindex` で、`/wp-sitemap.xml` に出ないこと（本番 `blog_public=1`）
- [ ] 送信 → 遷移 → 戻るボタンで再送信にならないこと（AJAX 送信なので履歴は `/contact/` → `/contact/thanks/`）
- [ ] 支店モード（`/contact/?store=sendai` 等）で送信後も支店ヘッダーのままか

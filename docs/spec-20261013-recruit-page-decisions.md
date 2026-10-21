# 採用ページ `/recruit/`・応募フォーム `/recruit/entry/` — 決定事項

- 作成: 2026-10-13
- カンプは無い。**正は現サイト** https://exterior-exone.com/recruit/ と https://exterior-exone.com/recruit/entry/（ユーザー指示「URL・コードを元に忠実に再現。ヘッダー・フッターは現在の HP のまま」）
- 参照物 `docs/reference-20261013-recruit/`: `prod-recruit.html`（現サイトの出力全体）/ `prod-recruit-body.html`（`#page_recruit` 〜 FAQ セクションまでの本体 HTML。ユーザー貼り付けと同内容）/ `prod-recruit-entry.html`・`prod-entry-body.html`（応募フォーム。CF7 のレンダリング済み markup を含む）/ `recruit.css`（1423 行。現サイトの採用ページ CSS。貼り付けと同じ）/ `entry-form.css` / `recruit.js`（現サイトの JS。貼り付けと同じ）/ `images/`（現サイトから取得した画像 16 枚）
- 本ファイルは、ユーザーの指示・回答と、それを受けて呼び出し側が決めた既定をまとめたもの。仕様書（`docs/spec-20261013-recruit-page.md`）と食い違う場合は**本ファイルが正**

## 1. ユーザーの指示

- 「以下の URL、コードを元にリクルートページ、応募フォームページを忠実に再現して実装してください。ただし、ヘッダー、フッターは現在の HP のままで良い。」

## 2. ユーザーの回答（2026-10-13「推奨通りで問題ない」）

| # | 項目 | 決定 |
|---|---|---|
| 1 | 外部ライブラリ（Three.js / Splide + AutoScroll / Font Awesome） | **使わない**。粒子は Canvas 2D で自前実装（見た目は現サイトと同じ: 白地、6 色の小さな点が手前へ流れる。最初の 2 秒は速く、その後ゆっくり。色は `0x006e54 / 0x0075c2 / 0x009b9f / 0xfdd35c / 0xea5532 / 0x915da3`）。FV の写真スライダーと流れる大文字はテーマ既存の流し方（`js/plans-imglist.js` + CSS アニメ）で再現（写真のドラッグは無し）。アイコンは SVG を同梱（Font Awesome Free の Solid。CC BY 4.0 の表記をテーマ内のコメントに） |
| 2 | 配色・書体 | **現サイトのまま**（セージ #99aea4 / #608373、アース #4c3d15 / #ede3bf、グレー #3C3C3D、地 #f9f9f9、黄 #ffe622 など）。書体は新サイトの Noto Sans JP。値は `css/recruit.css` の `:root` に `--rc-*` トークンとして定義して使う（直値を散らさない） |
| 3 | ブレイクポイント | **1024px に統一**（現サイトの 960 の指定を 1024 に読み替える。768 の指定は SP 側の中間として残してよい） |
| 4 | 募集要項 | **6 職種とも畳んだ状態で始まり、見出しを押すと 1 つだけ開く**（現サイトどおり）。職種ナビは出さない。`/recruit/#sales` 等の URL で該当職種が開く機能は残す（スラッグ: construction / sales / manager / planner / office / executive） |
| 5 | 応募ボタンの固定表示 | **残す**（Training セクション以降で画面下中央に固定。FV では出さない） |
| 6 | モーダル | **新サイトの既存モーダルの方式**（`<dialog>` または既存の見積モーダルと同じ作り。見た目・動き（右から出る白いパネル・×ボタン・背景の暗幕・本文スクロール・背景スクロール禁止）は現サイトと同じ）。Popover API は使わない |
| 7 | フォームの送信設定 | **現サイトの CF7「採用フォーム」のメール設定どおり**（スクリーンショット 2026-10-13 17:05）: 送信先 `recruit@exone-exterior.jp` / 送信元 `株式会社EXone<info@exterior-exone.com>` / 題名 `HPからのお問い合わせ"[your-contact]"` / 追加ヘッダー `Reply-To: [your-email]` / 本文は下記 / **メール (2)（自動返信）は使わない** / HTML メールではない |
| 8 | フォーム完了後 | 同じページにメッセージ表示（お問い合わせページと同じ作り。確認画面は現サイトに無いので**付けない**） |

### CF7「採用フォーム」のメール本文（スクリーンショットどおり）

```
お名前: [your-name-last] [your-name-first]

希望職種：[your-job]

性別：[your-gender]

メールアドレス：[your-email]

電話番号：[your-tel]

題名：[your-subject]

住所：[your-postcode][your-address][your-extended-address]


メッセージ本文:
[your-contact]
```

- `[your-subject]` はフォームに無い項目（現サイトの設定のまま。空で出る）。そのまま再現する（報告で触れる）
- フォームの項目（現サイトのレンダリング結果 `prod-entry-body.html` から）: 希望職種 `your-job`（チェックボックス 6: 施工管理職 / 提案営業職 / 支店長候補 / エクステリアプランナー / 店舗事務スタッフ / 営業責任者(執行役員)）・性別 `your-gender`（チェックボックス: 男性 / 女性 / 無回答）・お名前 `your-name-last` + `your-name-first`（必須。placeholder「例）　山田」「例）　太郎」）・メールアドレス `your-email`（必須）・電話番号 `your-tel`（必須）・郵便番号 `your-postcode`（必須。「例）　105-0001」）・ご住所 `your-address`（必須）・以降のご住所 `your-extended-address`（必須）・応募先へのメッセージ `your-contact`（textarea 必須）。必須マーク「必須」（赤の札 `.is_req`）。送信ボタン「応募する」黄色（`.form-btn-flat`）
- 現サイトの CF7 の markup（`.page_form-layout` / `.page_form-item01` / `.p_form-labelarea` / `.is_label` / `.is_req` / `.p_form-namearea` …）はフォームタグの間に書かれた HTML なので、テーマが登録するフォームの雛形にも同じ HTML を入れる

## 3. 不要・要修正とした点（2026-10-13 ユーザー了承）

1. FV の写真スライダーの重複（`m-222079_photo1…640x480-1.jpg` が 2 回）→ **1 枚に減らす**（低解像度なので、残す 1 枚もそのまま）
2. 流れる大文字「Charenge Creative Let's Entry」→ **「Challenge Creative Let's Entry」に直す**
3. モーダル 2（営業）・3（施工管理）の Q6 がモーダル 1（支店長）の Q5 と同文 → **Q6 を削除**（2 は Q1〜Q5、3 は Q1〜Q5）
4. 営業責任者（執行役員）の注記 ※1（固定残業代）→ **※1 の 2 行を削除**（※2 と「お問い合わせは recruit@〜」は残す）
5. 現サイト末尾の Contact us / RECRUIT 共通パーツ → **置かない**（新サイトのフッター）
6. 粒子アニメは Three.js を使わず自前実装（§2-1）

## 4. 既定（呼び出し側が決めた。変えたい場合はユーザーに確認）

### URL・テンプレート
- 固定ページ `recruit`（テンプレート `page-recruit.php`）と、その子ページ `entry`（`/recruit/entry/`、テンプレート `page-recruit-entry.php`）。2026-10-13 に WP-CLI で作成済み（recruit = ID 105、entry = ID 106）
- ヘッダー・ドロワー・フッターの RECRUIT / 採用情報（`inc/menus.php` の `#`）と TOP の RECRUIT セクションの VIEW MORE（`template-parts/top/section-recruit.php`）を `/recruit/` に。支店モードでは `exterior_exone_store_link()`（採用はコーポレートの内容なので、支店モードでも同じページ）
- ヘッダーは他の FV ページと同じ重ね方（`exterior_exone_has_fv_header()` に追加）。FV は白地 + 粒子なので、半透明グラデのヘッダーの下に白地が来る（現サイトも同じ構図）。応募フォームは白地なので、コラムと同じ扱い（重ねて上余白を足す）
- CSS は `css/recruit.css`（採用）と `css/recruit-entry.css`（フォーム）。JS は `js/recruit.js`（粒子・モーダル・募集要項の開閉・応募ボタンの固定・FAQ）。流れる写真・文字は既存の `js/plans-imglist.js` の仕組みを使う。`is_page('recruit')` / `is_page('entry')`（親が recruit）のときだけ読む
- 画像は `images/recruit/` に jpg/webp（参照物 `docs/reference-20261013-recruit/images/` から。大きいものは 1920 幅程度に縮小。アイコン SVG は `images/recruit/icons/`）
- 値の持ち方: 現サイトの rem は html 10px 基準（`62.5%`）なので **1rem = 10px に読み替えて px / トークンに**。clamp() の vw 指定はそのまま使ってよい（1920 / 375 で現サイトと同じ大きさになる）。比例ブロック（`--*-vu`）は使わない（現サイトが clamp 設計のため）

### 採用ページの各部
- FV: 高さは中身なり（スライダー + コピー + ボタン + 下端のカーブ）。粒子は `<canvas>` を FV 全面に敷く（Canvas 2D。`prefers-reduced-motion` では静止）。写真スライダーは 7 枚（重複を除く）をループで右→左に流す（速さは現サイトの AutoScroll speed 1 ≒ 1px/フレーム ≒ 60px/秒）。コピーは `h1`（現サイトは h1 の中に p が入っている不正な構造 → h1 + p に直す）。「応募する」ボタン（黒丸ボタン、ホバーで白が広がり矢印が動く）。下端のカーブ（SVG clipPath。SP は浅く、PC は深く）
- Training: 写真 + 5 カテゴリ（アイコン SVG）。Work: 3 名のカード（写真の上に部署・役職・入社年、＋ボタン）→ モーダル（タイトル「働く人を知る」赤 #c80202、部署・入社年、写真、Q&A）。流れる大文字（#d9d9d984、装飾、クリックを通さない）。Requirements: 6 職種（セージの箱 #608373 + 白い表、開閉式）。Benefits: 写真バナー + コピー + 4 カード。Flow: Step 01〜04（PC 横並び・SP 縦積み、矢印）+ 注記。FAQ: 6 問の開閉
- 「応募する」ボタンの固定: Training の上端が画面に入ったら表示、`body` 直下に移す（現サイトと同じ）

### 応募フォーム
- 見出し「ENTRY / 応募フォーム」→ CF7 フォーム。CF7 のフォームはテーマのコードで登録（`inc/recruit-form.php`。`exterior_exone_recruit_install_form()`。お問い合わせの `inc/contact-forms.php` と同じ作法。既存を上書きしない）。送信は CF7 の AJAX。完了・エラーメッセージは CF7 の仕組み（見た目はテーマの CSS）
- 入力チェックは CF7（必須のみ。現サイトと同じ）。エラー表示はお問い合わせページと同じ見せ方（項目の下に赤文字）

### 対象外
- 粒子の WebGL 版、写真スライダーのドラッグ、職種ナビ、確認画面、自動返信、reCAPTCHA（公開前チェックリストに）

## 5. 仕様書のオープン論点への決定（2026-10-13、呼び出し側）

- **Q9: 送信ボタンの文言は現サイトどおり「送信する」**（§2 末尾の「応募する」は誤り。現サイトのレンダリング結果が正）
- **Q13: 完了時はお問い合わせページと同じく、フォームを隠して完了メッセージだけを出す**（文言は CF7 標準の日本語「ありがとうございます。メッセージは送信されました。」のまま）。入力エラーは項目の下の文言だけ（帯は出さない）
- Q5: 暗幕クリックでも閉じる（既定どおり。新サイトの他のモーダルと同じ）
- Q1〜Q4・Q6〜Q8・Q10〜Q12・Q14〜Q17 は仕様書の既定どおり（粒子は現サイトと同じ遠近で見た目合わせ・画面外では止める／流す速さは 60Hz 相当の px/秒／h1 は英字 + 和文の 2 段・各セクション見出しは h2／モーダル 0.8 秒フェードイン・閉じるは即時／モーダル・ドロワー中は固定ボタンを隠す／ヘッダー高 + 60 の上余白／雛形の閉じ忘れは直す／CJK 互換部首の文字は通常の漢字に／JS 無効時は全部開いた状態／データはテーマ側（`inc/recruit-data.php`）／準備中の案内文／ブレイクポイントは機械的に読み替え／weight 900 は同梱の 700 で表示／部署表記は現サイトのまま）
- 仕様書の注意: TOP の RECRUIT セクションが `.p-recruit*` を使っているので、採用ページのクラスは別の接頭辞（`.p-rc*` 等）。`inc/contact-forms.php` の `wpcf7_load_js` の条件に `/recruit/entry/` を足す

## 6. 進捗

（generator → evaluator の結果をここに追記）

- 2026-10-13 S1（F-01〜F-03）generator 実装済み・evaluator 未。AC-02-3（画素の変化量 2 倍）と AC-02-12 の 375（SP ヘッダーは全ページ共通で流れの中の黒帯なので FV の上端とヘッダーの上端は一致しない）は未チェック。粒子・スライダーの速さは仕様書 Q1・Q2 の既定どおり
- **S1（F-01〜F-03）合格**（2026-10-13、Evaluator）。27 件すべて合格、差し戻しなし。所見: 粒子は新サイトが真円（現サイトは canvas の引き伸ばしで横長）で手前の大きい点は一回り小さめ／PC の写真スライダーの上余白をヘッダー高 + 60 にしたため、1920×1080 の最初の画面で応募ボタンが見えない（現サイトは見える）→ ユーザーに確認／fv-02・fv-04 の alt が空（任意）。Evaluator は本番サイトにアクセスしない定義のため、現サイトとの比較は Generator が撮った `docs/baseline-20261013-recruit/shots/prod-*` を使った
- **S2（F-04〜F-07）実装済み**（2026-10-13、Generator。Evaluator 検証中）。呼び出し側の決定: **モーダルのパネル幅は現サイトの見た目どおり（80% + 左右 30px の padding = 1920 で 1340px、375 で 360px）**。AC-05-3 の「80%」はこの値で読み替える。Generator が決めた点: モーダルは `<dialog>`（`showModal`。dialog 自体が暗幕とスクロール領域、`body.is-rc-modal-open` で背景スクロール停止 → S3 の固定ボタンもこのクラスで隠す）／Work のカード写真は 1025〜1279 で 3 枚が収まる幅に縮小（現サイトは 3 枚目が切れていた）／モーダル 3 の Q2 の空行は仕様どおり「午前：」の前／モーダル 1〜3 の Q&A 上の余白を 50 に統一／アイコンは SVG を CSS mask で
- **S2（F-04〜F-07）合格**（2026-10-13、Evaluator）。31 件すべて合格（AC-06-4 の Requirements 側・AC-06-7 は S3 で）。差し戻しなし。所見: JS 無効時に FAQ の + アイコンが残る（回答は開いている）→ **S4 で + を隠す**／モーダルの写真の alt が空（任意）／Flow の箱幅は仕様の 200 ではなく現サイトと同じ 275（現サイトが正）
- **S3（F-08〜F-09）実装済み**（2026-10-13、Generator。Evaluator 未）。Generator が決めた点: 職種の見出しは `h3 > button`（Enter / Space はボタンの既定動作）／別の職種を開くとき・`hashchange` のときは他の職種を即時に閉じ、押した（対象の）見出しが動かないようスクロールを即時補正（現サイトは `hashchange` 時はアニメで閉じる）／箱の間隔は 20 で統一（現サイトの 3〜6 個目の間が 30 なのは HTML の全角スペースの行による崩れ）／店舗事務スタッフの仕事内容の 5・6 行目の前にある全角スペースの行（現サイトでは空の行として間が空く）は再現しない／固定ボタンは `a.p-rcbtn.p-rcapply[data-recruit-apply="fixed"]`（`js/recruit-apply.js` が body 直下へ移す。z-index はドロワー +100、`body.is-rc-modal-open` / `body.is-drawer-open` で隠す）／AC-08-12 の「項目名の right < 内容の left」は現サイトと同じく right = left（内容の左内余白 24 で文字が離れる）なので未チェック
- **S3（F-08・F-09）実装済み**（2026-10-13、Generator。Evaluator 検証中）。呼び出し側の決定: **AC-08-12 は項目名の右端 = 内容の左端（現サイトと同じ。文字は内側の余白 24 で離れる）で合格扱い**。Generator が決めた点: 別の職種を開くとき・hashchange のときは他を即時に閉じてスクロールを即時補正／固定ボタンの z-index は `--z-overlay + 100`／職種の箱に `scroll-margin-top`／箱の間隔 20 に統一（現サイトの 30 は全角スペース行の崩れ）／店舗事務の全角スペースだけの行は再現しない
- **S3（F-08・F-09 + AC-06-4/06-7）合格**（2026-10-13、Evaluator）。24 件すべて合格、差し戻しなし。所見: 営業責任者の仕事内容は原文の改行位置が空白で表示される（現サイトも同じ原文）／施工管理職の注記に ※2 が無いのは現サイトの原文どおり
- **S4（F-10〜F-12）実装済み**（2026-10-13、Generator。Evaluator 検証中）。CF7「採用フォーム」はローカル ID 107（post meta `_exterior_exone_recruit_form=entry` で探す。`exterior_exone_recruit_install_form()`）。呼び出し側の決定: **希望職種・性別は現サイトどおり 1 つだけ選べる（現サイトの markup が `wpcf7-exclusive-checkbox`）**／本文の「題名：」はフォームに無い `[your-subject]` を空にするフックで空欄に（現サイトは `[your-subject]` の文字のまま届いている可能性）。Generator が決めた点: 入力欄の書体は Noto Sans JP（現サイトはブラウザ既定）／登録関数は同名の既存フォームがあれば上書きせず識別子の meta だけ付ける／961〜1024 は SP の組み（Training の写真が全幅 3:4 で大きい = 現サイトの 960 未満と同じ）。要確認: 375 の `/recruit/` で Benefits → Flow の見出しが現サイトより約 80px、Flow の注記 → FAQ が約 90px 広い
- **S4（F-10〜F-12）合格**（2026-10-13、Evaluator）。全件合格、AC-12-7 の操作系再実行も合格。差し戻しなし
- **評価後に呼び出し側で対応（2026-10-13）**: (1) 375 の `/recruit/` の余白 3 か所（Training → Work・Benefits → Flow・Flow の注記 → FAQ）が現サイトより 80 広かった原因は、現サイトで `footer.css` の 959px 以下の `.recruit-content { padding: 0 30px }` が採用ページの箱にも当たっていたこと → **現サイトの実際の表示に合わせ、1024px 以下は `.p-rcbg__inner` の上下 padding を 0 に**（`css/recruit.css` 末尾）。3 か所とも 80 になった (2) **チェックボックスの文字を押しても選べるように** `use_label_element` を追加（`inc/recruit-form.php`。`install_form( true )` で再登録済み。並びは従来どおり、1 つだけ選べる動きも維持）
- 残っている確認事項: PC の FV でスライダーの上余白を「ヘッダー高 + 60」にしたため 1920×1080 の最初の画面に応募ボタンが入らない（現サイトは入る）→ ユーザー判断待ち／写真の alt が空（任意）／完了表示直後に「ENTRY」がヘッダーのグラデに重なって見える（完了文は見えている）
- **固定の「応募する」は FAQ セクションまで表示（2026-10-13 ユーザー指示）**: FAQ の下端が画面の下端より上に行ったら（フッターが見え始めたら）隠す（`js/recruit-apply.js`）。現サイトは最下部まで出していた

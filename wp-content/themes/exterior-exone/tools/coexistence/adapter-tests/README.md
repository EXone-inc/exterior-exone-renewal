# 新テーマ専用の表示アダプター

`docs/spec-20261016-theme-coexistence-implementation.md` S3 と関連するフォーム・非混入処理の初版。実装本体は `inc/coexistence.php`。このディレクトリは検証用で、配布テーマから除外する。

## 有効になる条件

`EXR_COEXISTENCE` が boolean `true` の場合だけ専用フックを登録する。今回、元 Local・本番の設定ファイルや DB にこの定数を追加していない。定数が無い通常の制作環境では従来の投稿タイプ・取得先・フォームが使われる。

取り込み先は `exr_page` / `exr_works` / `exr_news` / `exr_form`。分類は `exr_works_*`。すべて公開 URL・REST・検索対象として登録せず、保存状態は `exr_stored`。表示処理は `_exr_dataset=exterior-exone-renewal:v1`、`_exr_source_status=publish`、`_exr_import_state=complete`、公開日が現在以前の全条件を要求する。下書き・未完了・別データ群・将来日・同名の旧データへフォールバックしない。

## 処理する範囲

- `parse_request` で現在のリクエストだけを解決し、rewrite / home / siteurl / front 設定を保存しない。TOP は静的テンプレートと新用カード、施工事例は新用全件、お知らせは新用 10 件ずつ。
- 固定ページは `_exr_source_path` の完全パスで特定する。`/contact/thanks/` と `/recruit/thanks/` を分ける。同パス複数レコードは解決しない。既存の支店 5 URL を正規 URL とし、ローカルの短いパスから新テーマ内だけで 301。追加支店は元パスのまま。
- 投稿 ID・内部投稿タイプ・preview パラメーターによる新用/旧ページへの迂回を防ぐ。コラムの既存 ID・URL は別の `inc/coexistence-column.php` へ委譲する。
- 表示用の `WP_Post` を複製して既存テンプレートへ渡す。保存済みの投稿や共有オブジェクトキャッシュの型・状態は変更しない。
- 新テーマのサイト内検索は共有コラムと公開条件を満たす新用データに限定する。既定の RSS は共有コラムを取得し、新用保存 CPT は独自 feed を持たない。新添付の標準 REST ID 直接参照は匿名では 404。認証済みで対象 ID の `read_post` がある読取、さらに `edit_post` がある変更要求だけを core の通常認可処理へ渡す。nonce・編集・削除等の core の検査は省略しない。
- 標準 sitemap はコラム provider と専用 provider を使う。専用 URL は TOP・一覧・新用ページ/施工事例で、下書き・サンクスは除外。XML Sitemap は `sm_xml_sitemap_post_url` で旧投稿 URL を除き、`sm_buildmap` で新 URL を加える。共有設定・投稿タイプ選択キャッシュを上書きしない。プラグイン既存の index に空の旧タイプ sitemap が残る可能性は実プラグイン検証対象。
- 標準の users provider は新テーマ中だけ除外する。sitemap handler が応答を完了せず戻った場合は、最終の `template_redirect` で 404 にし、旧投稿の loop・queried object と feed 状態を除く。未知 provider、XML プラグイン未処理時に通常の HTML / RSS へ戻らず、旧 canonical への転送もしない。
- ローカルの CPT/分類登録と独自 activation flush、フォーム installer は共存時に実行しない。旧 CPT UI 定義・階層ありコラム分類をそのまま使う。

## フォームの重要な境界

CF7 は投稿状態を確認せず `wpcf7_contact_form` 型を受け入れるため、非公開状態だけでは旧テーマから新フォーム ID を直接送信できる。Importer と合意して **保存型を `exr_form` に変更した**。旧 CF7 の標準 ID 検索は型不一致で拒否する。

新テーマでは `_exr_source_key` と `_exr_form_role`（personal / corporate / entry）を確認し、複製した投稿を CF7 オブジェクトへ渡す。フォーム HTML はオブジェクトから生成し、元の hash やタイトルで検索し直さない。数値 ID の取り込み済み shortcode も専用型に限って解決する。

新用 ID の `feedback` / `feedback/schema` / `refill` は、標準 REST の認可・パラメーター検証後の `rest_dispatch_request` でのみ処理する。投稿型やキャッシュを一時的に書き換える方式は使わない。既存フォーム ID の処理には介入しない。応答の変換は CF7 6.1 系の形式に合わせ、入力検証は CF7 本体に委ねる。実送信は隔離環境の捕捉先で確認すること。非 JavaScript の標準 CF7 ID 送信経路では専用型が拒否される。

## 添付と管理画面

添付だけは WordPress 標準の `private` で保存する。独自状態 `exr_stored` は core `get_post_status()` によって `publish` と解釈されるため、添付の非公開化には使わない。画像ファイルの URL 配信と添付 HTML / REST の公開を区別する。

管理者など `read_private_posts` を持つ利用者の標準メディア一覧・グリッド・選択モーダルは `inherit,private` を取得するので、旧新テーマ双方で既存画像と追加画像を扱える。共有 MU plugin は追加しない。権限のない投稿者等に同じ一覧を保証するものではない。REST 一覧の既定値は `inherit` のみなので、private を取得する管理用 REST は認証して `status=inherit,private` を明示する。既存の役割・権限を変更しない。

`wp_get_attachment_image_src()` は private 画像でも元画像・派生画像 URL を生成できる。標準 `[gallery]` / `[playlist]` は既定で `inherit` を要求するため、今後これらを追加する場合は別途適合が必要。現 r3 の本文には両 shortcode は 0 件。

`inc/coexistence-admin.php` は、新テーマ有効時の外観配下に「新テーマ用コンテンツ」を追加する。`manage_options` を持つ利用者だけに、取り込み完了した新用の固定ページ・施工事例・お知らせ・フォーム・画像を分類、タイトル、元の公開状態、確認リンクで表示する。下書きや将来日の項目も確認できるが公開リンクは付けない。本文、メール設定、内部識別情報は表示せず、保存ボタン・変更処理もない。通常 Local では hook を登録しない。

これは取り込んだ内容を確認する画面であり、新用記事・フォームを編集・再同期する CMS の設計は含めない。画面内にも編集・保存できないことと、元の公開状態が一般公開準備の完了を意味しないことを明記する。メディアのリンク先は既存の管理画面であり、この確認画面から自動で編集しない。

## 実施済みの検証

専用 PHP **7.4.33** で変更対象 15 PHP ファイルの構文検査に合格。`run.php` は WordPress を起動せず、合成データだけを使い、DB/ファイル/ネットワークへ書き込まない。

```text
php -n tools/coexistence/adapter-tests/run.php
PASS 101 enabled-mode boundaries (no DB/bootstrap/network)

php -n tools/coexistence/adapter-tests/run.php --disabled
PASS 4 disabled-mode boundaries

php -n tools/coexistence/adapter-tests/sitemap-core.php
php -n tools/coexistence/adapter-tests/sitemap-core.php --known

php -n tools/coexistence/adapter-tests/media-core.php
PASS 10 exact WordPress 6.8.1 media function checks; SQL boundary stubbed, no bootstrap/DB/network.

php -n tools/coexistence/adapter-tests/admin.php
PASS 36 read-only admin checks; no DB/bootstrap/network writes.
php -n tools/coexistence/adapter-tests/admin.php --disabled
```

`sitemap-core.php` は WordPress 本体の `WP_Sitemaps` クラスを読み、合成 provider だけで実行する。未知 provider の戻りを 404 にすること、既知 provider は通常どおり応答を終了すること、配列値・未処理 plugin の feed fallback・著者 provider 除外を確認する。WordPress bootstrap・DB・HTTP は実行しない。

`media-core.php` は固定 lab に復元した **WordPress 6.8.1** の実関数を使い、添付状態、管理画面の取得条件、管理 REST の読取認可、画像 URL の生成を確認する。tokenizer を含む専用 PHP 7.4.33 runtime が必要。SQL 実行・ブラウザ描画は行わないため、管理 UI の実機確認とは区別する。

旧新同スラッグ、下書き・未完了・将来日、同名子ページ、曖昧パス、範囲外ページ、コラムに偽装した旧 ID、フォーム旧 ID/型境界・役割重複・タイトル不使用、送信 ID 不一致、CF7 の検証エラー形式、画像 REST、sitemap 除外、公開用コピーの不変性、installer/flush の書き込み禁止を確認した。

## 隔離環境で残る確認

実 DB の `WP_Query` とテンプレート、CF7 **本番同版 6.1.7** の表示/検証/捕捉送信/旧テーマでの直接 ID 拒否、XML Sitemap **本番同版 4.1.25** の全 sitemap、PHP 7.4.33 の画面、375/1920/768 の表示、キャッシュと旧→新→旧の保護対象比較は未実施。純 fixture の合格をこれらの合格と扱わない。本番アップロード・設定変更・有効化は行っていない。

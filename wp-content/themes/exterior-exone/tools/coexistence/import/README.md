# 隔離環境への取り込み（PHP 7.4.33）

`docs/spec-20261016-theme-coexistence-implementation.md` の S2 用。既存 PHP 8.2 ツールを読み込まず、WordPress を起動しない。旧ツールや元パッケージを変更しない。

## 現在の実行範囲

- `validate.php`: schema 1 の package を固定 private root 内で再検証し、件数だけの private report を保存する。DB・HTTP 接続なし。
- `tests/run.php`: PHP 7.4.33 の合成 fixture。private 内で入力・結果を作成し、型・参照変換・再利用判定・画像の排他的追加と失敗時の除去を確認する。実 DB を使わない。
- `cli.php`: `dry-run` / `apply` を分けた独立 SQL importer。接続には `guard.php` に固定した隔離環境契約と private sentinel の一致が必要。実 DB による検証は未完了。

例（`EXONE_IMPORT_PHP` は専用 PHP 7.4.33、`EXONE_IMPORT_PRIVATE` は固定 migration-private の絶対パス）:

```sh
"$EXONE_IMPORT_PHP" -n tools/coexistence/import/validate.php --package "$EXONE_IMPORT_PRIVATE/content-20261015-r3" --output "$EXONE_IMPORT_PRIVATE/import-validation-new"
"$EXONE_IMPORT_PHP" -n tools/coexistence/import/tests/run.php
"$EXONE_IMPORT_PHP" -n tools/coexistence/import/cli.php dry-run --package "$EXONE_IMPORT_PRIVATE/content-20261015-r3" --output "$EXONE_IMPORT_PRIVATE/coexistence-lab-20261016/import-runs/dry-run-new"
"$EXONE_IMPORT_PHP" -n tools/coexistence/import/cli.php apply --package "$EXONE_IMPORT_PRIVATE/content-20261015-r3" --output "$EXONE_IMPORT_PRIVATE/coexistence-lab-20261016/import-runs/apply-new" --confirm isolated-lab-only
```

出力先は未作成の private 子ディレクトリが必要。ディレクトリ 0700、ファイル 0600。CLI は本文・メール・認証情報を表示しない。

実取り込みには `mysqli` / `mysqlnd` を有効にした専用 PHP 7.4.33 が必要。既存の最小 PHP 7.4 CLI は package/fixture 専用。固定した環境は `coexistence-lab-20261016`、socket は `/private/tmp/exone-coexistence-20261016.sock`、DB は `exone_coexistence_lab_20261016`、URL は `http://127.0.0.1:18746` と `/retest`。元Localのsocketや任意hostへ切り替える引数はない。接続後にも DB・socket・TCP無効・datadir・home/siteurl を照合する。追加投稿の author は実在確認した隔離側 ID 1。

失敗試験は apply に `--fail-at after-terms` / `after-posts` / `after-media` / `after-references` / `before-commit` のいずれかを追加する。失敗時の `FAILED.json` に commit の有無と rollback 後の元行一致を残す。再試行は新しい report 出力先を使う。rollback による AUTO_INCREMENT の欠番は旧行の変更とは区別する。

隔離DB復元後、未取り込みの状態で親担当が以下を一度実行すると、5段階の失敗→元行一致→再試行可能性、120件の実取り込み、同じIDへの再実行をまとめて確認できる。実データを追加する試験なので、合成 fixture と混同しない。既存の新用データがあれば削除せず停止する。

```sh
"$EXONE_IMPORT_PHP" -n tools/coexistence/import/tests/integration.php --package "$EXONE_IMPORT_PRIVATE/content-20261015-r3" --confirm isolated-lab-only
```

## データ契約

- 原本は source key / record hash / manifest hash / COMPLETE / 全 media SHA-256 で照合。許可フィールドと型を厳格に限定し、JSON 重複キー、シリアライズオブジェクト・循環参照・末尾混入、symlink、パス逸脱を拒否する。本文から references と analysis を再構築して一致を確認する。
- 新用投稿タイプは `exr_page`, `exr_works`, `exr_news`, `exr_form`、保存状態は `exr_stored`。添付だけは `attachment` のまま別 ID、保存状態は WordPress 標準の `private` にする。WP は添付の未知状態を公開扱いにするため、添付へ `exr_stored` は使わない。comment / ping は closed、comment_count は 0。CF7 は status だけでは旧 REST 送信経路を隔離できないため、保存タイプも `exr_form` に分ける。パッケージの元 kind は `wpcf7_contact_form` のまま。
- 添付の source hash / fingerprint / 元状態は原本のまま保持し、変換後の destination hash だけが `private` を含む新しい値になる。inventory・新規割当・再利用の全値照合は同じ保存状態の契約を使う。画像ファイルの URL と実体は変更しない。
- 専用分類は `exr_works_category`, `exr_works_package`, `exr_works_tag`。term ID と term_taxonomy ID を別々に対応させる。
- `_exr_dataset=exterior-exone-renewal:v1`, `_exr_source_key`, `_exr_source_hash`, `_exr_source_fingerprint`, `_exr_import_state=complete` を新用データにだけ保存。元状態は `_exr_source_status`、親の出典は `_exr_source_parent_id` / `_exr_source_parent_type`、固定ページの元パスは `_exr_source_path`。
- CF7 は `_exr_form_role` の personal / corporate / entry と専用 source key で選ぶ。旧 role メタと `_hash` は保存しない。メール設定はデータとして保持し、実行・送信しない。
- 画像は `uploads/exone-renewal/<source_fingerprint>/<source_attachment_id>/<source_relative_path>` に追加。各添付の元画像・派生画像の相対関係を保つ。元添付の除外コラム親は destination parent 0。旧添付 ID / 旧パスとの推測照合はしない。
- 同 key / hash / package / type / role なら再利用候補。store で実際の投稿・メタ・分類が期待する変換後の全値と一致することも必要。異内容、複数候補、不完全 inventory、同役割別 source key、既存の未所有ファイルは停止する。
- dry-run は全件新規または全件再利用のみ検証完了にできる。新規と再利用が混在する場合、未割当の参照先 ID を推測して再利用内容を検証済みにせず、`dry-run-plan` 段階で停止する。全件再利用は全行の実内容を照合する。apply は実際に新 ID を確定してから全再利用行を検証する既存の手順を維持する。
- 共有 column、旧 page / works / news、旧 CF7、ユーザー、options、コメント、受信履歴は追加・更新しない。新用の参照だけを置換し、元のシリアライズ形式や画像順を保つ。
- review は項目別の台帳で保持・変換・未解決を分ける。未知メタの値は取り戻さない。非公開保存の成功と一般公開の合格は別。

## 失敗と検証の範囲

SQL store は InnoDB transaction を必須とし、INSERT と今回取得した新 ID だけの UPDATE を行う。旧行への UPDATE / DELETE は実装しない。元の core 11 テーブルの行を SHA-256 比較する。

画像は排他的に作成し、作成前の予定を private journal に残す。捕捉できる失敗は DB rollback と今回の画像だけの削除で戻す。既存ファイルの上書き再試行はしない。プロセス強制終了・電源断後の journal 復旧は、実行CLI統合時に別途検証が必要。

この段階で未確認: 固定環境 guard の実接続、実 DB 取り込みと再実行、保護行の前後一致、旧検索・REST・feed・sitemapへの非混入、フォームの実表示・隔離送信、テーマ切り替えと SEO。合成試験の合格だけで本番反映・公開可能とは判定しない。

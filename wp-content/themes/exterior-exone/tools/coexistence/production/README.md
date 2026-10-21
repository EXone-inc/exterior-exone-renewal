# 本番専用の非公開取り込み runner

このディレクトリは本番への転送・取り込みを親担当が準備するための CLI ソースです。ローカルの表示テーマや lab の接続 guard は変更しません。作成・テスト完了は、本番取り込み・テーマ有効化の承認を意味しません。実行担当は親担当だけです。

## 固定契約

- PHP CLI 7.4.33。実行例は `/opt/php-7.4.33/bin/php -d memory_limit=512M`。Web PHP の設定は変更しません。
- private root: `/home/xs357102/exone-renewal-migration-20261016`
- runner: 上記 root の `runner/`。このフォルダの実行用 PHP 8 ファイルのみを配置し、tests や資料は配布しません。
- package: 上記 root の `package/`。r3 fingerprint `9ebda8d9fb3a5479ddf173d30bb3e1852dae77bcf632e81a8e11c5df60e0c791`、120 レコード、195 ファイル、35,343,166 bytes に固定。
- WordPress root: `/home/xs357102/exterior-exone.com/public_html/retest`
- DB: `xs357102_w8mov` / prefix `wp_` / host `localhost` / socket `/var/lib/mysql/mysql.sock` / MariaDB 10.5。
- home: `https://exterior-exone.com`、siteurl: `https://exterior-exone.com/retest`、旧テーマ `affinger-child` / `affinger` を必須にします。
- uploads: `WP root/wp-content/uploads`。実測の空 upload 設定、または同じ実体・URL を表す固定値だけを受け入れます。
- private root、runner、package、runs、backup は 0700、private ファイルは 0600。新規公開画像だけはフォルダ 0755 / ファイル 0644 です。
- `environment.json` は `exterior_exone_production_contract()` の返却値とキー・型・値まで一致させます。接続先を CLI 引数で変更する機能はありません。

WordPress は bootstrap しません。固定 wp-config.php から DB の文字列リテラルと table prefix をトークン解析し、動的値・重複・独自 uploads 設定を拒否します。include/eval は使いません。認証情報、本文、メール設定値は標準出力・エラー出力に出しません。

## 今回の事前 dry-run は省略

ユーザー指示により、本番での事前 dry-run は実行しません。PC 上の固定 r3 だけから `offline-plan.json` を作り、source manifest SHA・種別内訳・追加 SQL 行数・195 画像を記録します。`production_dry_run_performed:false` と明示し、本番 inventory を確認したという証拠には使いません。

以下の dry-run 入口は、取り込み後に全件再利用の一致を読取確認する場合のために残しています。今回の apply の前提にはしません。

```sh
/opt/php-7.4.33/bin/php -d memory_limit=512M /home/xs357102/exone-renewal-migration-20261016/runner/cli.php dry-run
```

DB は READ ONLY transaction、公開 uploads への書込みは 0 です。private runs のみ、毎回新しいディレクトリへ plan / review-ledger / protected-before / result を保存します。全新規なら将来の ID は仮値であり、実 ID の承認資料には使いません。全件再利用なら実 ID と全対象本文・メタ・関連付け・全画像 hash を照合します。部分再利用、不明 namespace、既存ファイルの衝突は停止します。

別の読取 connection で全 11 core テーブルをストリーミング hash します。読取開始前と完了後で、既存行の変更・削除に加えて、他処理の新規行も検出します。差があれば成功 report を出しません。サイト全体の書込みロックは使いません。

## apply の証拠ファイル（未承認時は作成しない）

取り込みの明示承認と現行バックアップ確認後に、親担当が 0600 の `apply-authorization.json` と `backup-evidence.json` を固定 private root に用意します。日時は厳密な UTC `YYYY-MM-DDTHH:mm:ss+00:00`、未来でなく 24 時間以内です。許可キー以外は拒否します。

apply-authorization.json の必須値:

```json
{
  "schema_version": 1,
  "operation": "append-renewal-content-only",
  "approved_at": "実承認時の UTC",
  "source_fingerprint": "9ebda8d9fb3a5479ddf173d30bb3e1852dae77bcf632e81a8e11c5df60e0c791",
  "offline_plan_sha256": "承認した固定 offline-plan.json の SHA256",
  "database_inserts_approved": true,
  "media_append_approved": true,
  "theme_activation_approved": false,
  "production_dry_run_skipped_by_user": true,
  "backup_evidence_sha256": "backup-evidence.json の SHA256"
}
```

backup-evidence.json の必須値:

```json
{
  "schema_version": 1,
  "database": "xs357102_w8mov",
  "captured_at": "実バックアップ完了時の UTC",
  "sha256": "固定 backup/database.sql.gz の SHA256",
  "bytes": 1,
  "gzip_integrity_verified": true,
  "required_tables_verified": true,
  "restore_available": true,
  "server_web_recovery_available": true
}
```

`bytes:1` は書式例であり実ファイルサイズに置き換えます。バックアップ helper の `backups/current-db-.../database.sql.gz` を親担当が固定 `backup/database.sql.gz` へ排他的にコピーし、所有・0600・サイズ・SHA 一致を確かめます。runner は実ファイルを再 hash します。gzip/必要テーブル/復旧証拠の確認は別工程で行い、未確認を true と記録してはいけません。

固定 `offline-plan.json` の名前だけを渡します。任意の plan path、host、DB、SQL、失敗注入の引数はありません。offline plan の schema・source fingerprint・source manifest SHA・対象数を照合し、apply 内で実 inventory と r3 から再構築した予定 SQL 行数を承認表と比較します。独立した事前 dry-run の省略は、apply 自体の安全確認を解除するものではありません。

```sh
/opt/php-7.4.33/bin/php -d memory_limit=512M /home/xs357102/exone-renewal-migration-20261016/runner/cli.php apply --confirm approved-production-append --reviewed-plan offline-plan.json
```

## 書込み・失敗時の境界

apply は全新規 120 件に限り、terms → posts の ID 採番 → 画像 → 参照保存 → 全値照合を一つの READ COMMITTED transaction 内で行います。SQL は prepared statements、書込表は posts/postmeta/terms/termmeta/term_taxonomy/term_relationships の 6 表です。UPDATE は同じ実行で新規採番した post/term/TTID に限ります。DELETE、既存 ID の更新、options/users/comments 書込みは実装していません。全 11 core InnoDB、core trigger 0 と閲覧権限、core FK なしを事前確認します。

新ページ/施工例/お知らせは exr_* + exr_stored、添付は core の private、新フォームは exr_form + exr_stored です。コメントは closed / count 0。source key、fingerprint、専用 dataset を保存し、private の固定 APPLY-STARTED.json と run ごとの mapping/owned_rows 台帳で実行と追加 ID を結び付けます。

画像は固定 namespace `uploads/exone-renewal/<fingerprint>/` に新規作成のみ。既存 namespace がある新規 apply は拒否し、ファイルは exclusive create で上書きしません。全元画像・全追加画像のサイズ/hash、元画像署名、許可外の追加ファイルがないことを検証します。元 package は編集しません。

commit 前の確認済み rollback かつ保護表の差分なしが再確認できた場合に限り、その実行が作った inode・書込済み bytes/hash が一致する画像と空フォルダを回収します。同じ inode でも内容が変わった画像は残します。並行更新があれば未知の追加参照の可能性があるため画像を保持します。commit を試みた後、通信断、成否不明、commit 後の差分でも画像を削除せず inspection に止めます。post-commit 時は既知の追加行だけ除外して全保護表を再 hash し、追加行自体も再照合します。rollback により新規行を取り消した場合も、採番で使われた AUTO_INCREMENT の欠番は戻しません。

`APPLY-STARTED.json` は失敗・成功を問わず自動削除しません。強制終了や部分 namespace を盲目的に再試行できません。次の試行には private 台帳と DB/画像の読取照合が必要です。全件完成の場合は dry-run で全再利用の一致を確認できます。部分状態の自動回収・commit 後の削除取消は未実装です。既存行や後続の編集を巻き込まず、新データを非公開で保持し旧テーマを維持します。DB 全体復元は行いません。

通常更新との完全な同時点スナップショット保証はありません。別 connection の観測を commit 前後に行って差分を検出しますが、最後の検査以後の更新を防ぎません。結果は観測時点の一致です。テーマ有効化、実メール配送、公開 SEO 合格はこの runner の範囲外です。

## ローカル検証

実 PHP 7.4.33 で `tests/run.php` を実行します。純データ、私有 fixture ファイル、SQL double だけを使います。fake store/media は test 内 Reflection で作り、実行コードに test 接続用の抜け道を設けません。`tests/r3.php` は固定ローカル r3 を読み、lab reader と全 120 件の変換が URL を除いて同じであること、195 ファイルの hash を検証します。DB/ネットワークアクセスはしません。

MariaDB 10.5 上での実 dry-run/apply 統合は別の承認済み工程です。模擬テストの通過を本番実行成功と扱ってはいけません。

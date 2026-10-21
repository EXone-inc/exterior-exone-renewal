# 旧新テーマ併存：S3 準備ツール

WordPress を起動せず、ローカルの対象データを読み取って非公開パッケージに保存します。
実際の取り込み・本番接続・テーマ切替・メール送信機能はありません。
これは **S3 の準備部分**であり、親仕様 S3 全体や本番移行の完了を示しません。

## 実行環境と範囲

- PHP 8.2 CLI（Local 同梱）、mysqli / JSON / hash。追加依存なし。
- 接続先は Local サイト `GRbmaPCLC` の既知の UNIX socket / DB `local` に固定。
- `home` / `siteurl` が両方 `http://exterior-exone-renewal.local` の場合だけ抽出。
- TCP、任意ソケット、任意SQL、WP bootstrap、`wp-config.php` 評価、適用フラグはありません。
- 読取は InnoDB の REPEATABLE READ / CONSISTENT SNAPSHOT / READ ONLY。終了時に rollback。
- 読む表は posts / postmeta / terms / term_taxonomy / term_relationships、接続先照合用 options 2件、エンジン確認の information_schema のみ。
- comments、users、受信履歴、設定全体、ローカル column 本文・メタ・分類は抽出しません。
- 対象は works / news / page / attachment / wpcf7_contact_form と施工事例用3分類。
- 元の publish / draft / pending / future / private / inherit を保存。新用の予定値ではコメント・ping を closed、コメント数を0にします。
- 確認済みの旧件数を上限にせず、実行時点の対象を全件取得します。全添付を同梱し、column由来や未参照という理由で画像を省きません。
- 既存のテーマファイル、DB、uploads、プラグイン、本番には書き込みません。

## コマンド

PHP の絶対パス:

```text
/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php
```

`cli.php` のあるディレクトリから、上記PHPで以下を実行します。
`PRIVATE_ROOT` は説明用の表記で、実際には下記絶対パスへ置き換えてください。

```text
/Users/exone-se/.codex/visualizations/2026/10/15/01a13e27-de2a-7213-8884-dcd614a97848/migration-private
```

```text
php cli.php export-local --output PRIVATE_ROOT/package-NEW
php cli.php validate-package --package PRIVATE_ROOT/package-NEW
php cli.php plan-offline --package PRIVATE_ROOT/package-NEW --inventory /absolute/inventory.json --output PRIVATE_ROOT/plan-NEW
php cli.php plan-offline --package PRIVATE_ROOT/package-NEW --output PRIVATE_ROOT/plan-no-target-NEW
php tests/run.php
```

出力親は事前に0700で作成されている必要があります。出力はその配下の**未作成のディレクトリ**だけ。
上書きやディレクトリ外への書き出し、シンボリックリンクを拒否します。
実データをテーマ、docs、uploads、公開ルートへ保存しないでください。
ツールソース自体も、将来の公開テーマ配布物から `tools/coexistence/` 全体を除外してください。

終了コードは0が準備/検証終了、1が入力不正または操作失敗、2が模擬照合の衝突です。
`unknown_target` は検証できた範囲を保存するため0ですが、取り込み可能を意味しません。
標準出力は件数・状態・識別ハッシュのみ。本文や宛先を出しません。

## パッケージ契約（schema_version: 1）

分割ファイル間の参照ずれを避けるため、補助仕様の content/forms/media/references の責務は
**非公開 `manifest.json` 内へ統合**しています。フォーム・メール文面を含むため全体が機密データです。

| ファイル | 内容 |
|---|---|
| `manifest.json` | 型、版、取得時点、元ID/種別、対象・除外、内容、許可メタ、参照、件数、テーマ依存、確認事項 |
| `manifest.sha256` | manifest のバイト列SHA-256 |
| `media/<source-id>/<path-hash>/<filename>` | 添付の元画像・派生サイズ・original_image・編集前サイズの実体 |
| `summary.txt` | 本文/宛先を含まない件数・容量・未検証範囲 |
| `COMPLETE` | 全検証の後でのみ作成する完成マーカー。ないパッケージは再利用不可 |
| 別出力 `plan.json` | 模擬照合結果。入力パッケージは変更しない |

ディレクトリ0700、ファイル0600を検証します。
添付に記録されたファイルの欠損、uploads外、シンボリックリンク、コピー中の変更はエラーです。
取得後は元uploadsから独立して検証できます。テーマ依存ファイルはハッシュ一覧のみで、実体は別途コピーが必要です。
実データCLIは取得前後のテーマ依存ハッシュを比較します。既存PHPを実行しません。

`records` の各項目:

- `source_key`: `exterior-exone-renewal:v1:post:<type>:<id>`、分類は `...:term:<taxonomy>:<term_id>`。
- `kind`, `source_id`, `source_parent_id`: 出典。移行先IDではありません。
- `data`: 本文、タイトル、状態、日付、並び等。投稿者・パスワード・GUIDは含みません。
- `meta`: 種別ごとの許可キーと、元の保存文字列の配列。シリアライズは許可型で検査し、実行しません。
- `references`: 画像4枠、旧ギャラリー順、アイキャッチ、本文画像、フォームID、親、分類。
- `parent_policy`: root / remap / excluded-parent-destination-zero。除外元親への数値ID流用は禁止。
- `files`: 安全な相対パス、元パス、サイズ、SHA-256、画像役割。
- `content_sha256`: そのレコードの正規化した内容。元IDを同一内容の移行先IDとみなしません。

`source_fingerprint` は日時・run_idを除いた全レコードの正規化SHA-256。
manifest checksumは全体のバイト列、各file SHA-256は実メディアです。署名ではありません。
`analysis` に親をたどったページフルパス、フォーム3役割、画像利用状況、新用種別候補、未確認事項をまとめます。
同じthanksスラッグでも `/contact/thanks/` と `/recruit/thanks/` は別ページです。
`unknown_meta` はキー・元識別・件数だけ。値は保存しません。`_hash` / `_edit_lock` / `_edit_last` は除外します。
`_additional_settings` は設定文字列として保持しますが、Webhook等の実行や自動移植を許可しません。

参照検出は既存4画像メタ、ギャラリー、アイキャッチ、`wp-image-ID`、`attachment_ID`、gallery shortcode、
image/media-text/coverブロック、CF7数値ID shortcode、uploads URL/srcset、ローカル絶対リンクを扱います。
CF7 hash IDや未解決の明示ID/ローカルuploads URLはエラー。
外部画像のsrc/srcset、相対画像パス、独自メディア属性、CSS/picture/SVG等も保持・確認候補として記録します。
簡易解析で扱えないメディア記法を「参照なし」と扱いません。未知のブロックやshortcode、script、メール設定は `needs_review`。外部URLは取得しません。
公開対象レコードの状態と状態別件数を検証し、trash/auto-draftはpayloadに許可しません。除外状態の件数表は対象種別、trash/auto-draft、非負整数に限定します。
コラム本文を走査しないため、コラム元親の添付の用途は「元親は除外コラム、その他の利用は未調査」と明示します。
テーマ内の動的参照も別途検証が必要です。参照0を削除許可にしません。

## オフライン対象棚卸し

対象なし、または既存の `docs/production-url-inventory-20261015.json` は `unknown_target`。
既存資料は pages / columns / source の部分棚卸しとして読み、ターム・フォーム・メディア・名前空間等を空とみなしません。
クエリ付きプレビューURLは公開フルパス競合に算入しません。
本番の固定ページ・コラムは保護対象にし、`/works/`、`/column/`、旧支店URLはアダプター要件として報告します。

完全な**合成/隔離環境の棚卸し**だけがcreate/reuse候補を出せます。例:

```json
{
  "schema_version": 1,
  "inventory_type": "exone-coexistence-destination",
  "environment": "synthetic",
  "completeness": "complete",
  "namespaces": {},
  "records": [
    {"destination_id": 20, "kind": "page", "ownership": "protected"}
  ],
  "files": []
}
```

`namespaces` は使用中の名前→所有者(`protected`/`renewal`)の対応。完全棚卸しでは省略名は未使用を意味します。
対象は exr_works/exr_news/exr_page、exr_works_category/exr_works_package/exr_works_tag、
`_exr_dataset`/`_exr_source_key`/`_exr_form_role`。保護対象の既存使用はconflictです。

導入済み新用recordは `ownership=renewal`、`source_key`、`content_sha256` が必要です。
数値 `destination_id` は別途割り当てられたIDであること。タイトル・スラッグ・元数値IDは照合根拠にしません。
同じsource_keyが2個あればエラー。同じidentityでもkind/hashが違えばconflictで更新を提案しません。
フォームは任意の`form_role`が重複しないことも検査します。

filesは `{relative_path, sha256, ownership, source_key?}`。
新用メディア候補は `exone-renewal/<source-id>/<hash-prefix>-<basename>` に分離します。
元と同じパスの既存画像は確認事項、実際の候補パスが別の所有者/内容で埋まっていればconflictです。
同じバイト列だけで既存本番の添付へ自動統合しません。

模擬再実行テストは、1回目の候補を**メモリ上の合成棚卸し**へ反映し、2回目がreuseとなることを確認します。
実DBでの冪等性や本番への非混入が検証済みという意味ではありません。

## 検証と未実装範囲

`tests/run.php` は架空データだけを使い、DB接続・送信は0。合成成果は固定非公開rootの新規ディレクトリへ保存します。
実データの抽出、前後DB比較、本番URL資料との照合は親エージェントが担当します。
既存の別作業を戻さず、取得中の変更があれば新しいスナップショットを作ります。

未実装: インポーター、旧新データ隔離アダプター、URL振分け、コラム表示互換、SEO、検索/REST/RSS/サイトマップの実測、
PHP7.4本番互換、キャッシュ、フォーム実送信、本番切替と復帰。
public=false や exr_という名前だけで安全と判定しません。全出力に準備専用・本番未検証を明記します。

<?php
/**
 * 企業情報: ブランドストーリー。
 *
 * カンプ: PC 917:1424・917:1554-1559・917:1445-1448
 *         （左上の写真に右下のパネルが重なり、写真の下に VISION / MISSION）
 *         SP  917:914-917・917:950-951・917:965-968
 *
 * 見出しは PC / SP 共通で「Industry Challenges / チャレンジし続ける理由」
 * （PC カンプの「The Experience We Build」は誤りとの指示で SP 側に統一）。
 * VISION / MISSION は PC カンプのみ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-cstory" data-section="company-story">
	<div class="p-cstory__inner">
		<div class="p-cstory__head">
			<h2 class="c-company-eng p-cstory__eng">Industry Challenges</h2>
			<p class="c-company-jp p-cstory__jp">チャレンジし続ける理由</p>
		</div>

		<div class="p-cstory__body">
			<div class="p-cstory__col">
				<figure class="p-cstory__media">
					<img
						class="p-cstory__image"
						src="<?php echo esc_url( exterior_exone_company_image( 'profile.jpg' ) ); ?>"
						width="849"
						height="566"
						alt="株式会社EXone 代表取締役 中村 剣太"
						loading="lazy"
					>
					<figcaption class="p-cstory__sign">株式会社EXone<br>代表取締役　中村 剣太</figcaption>

					<?php // オフィス写真（SP カンプ 34:1079 のみ。PC カンプには無いので PC では出さない）。 ?>
					<img
						class="p-cstory__office"
						src="<?php echo esc_url( exterior_exone_company_image( 'profile-office.jpg' ) ); ?>"
						width="1000"
						height="603"
						alt=""
						loading="lazy"
					>
				</figure>

				<?php // 画面に入ったら VISION → MISSION の順に左からフェードイン（js/scroll-reveal.js）。 ?>
				<dl class="p-cstory__mv">
					<div class="p-cstory__mv-item" data-reveal="left">
						<dt class="c-display p-cstory__mv-eng">VISION</dt>
						<dd class="p-cstory__mv-desc">次世代の業界トレンドを創り、<br>社会に長期的なインパクトを与えるリーディングカンパニーへ。</dd>
					</div>
					<div class="p-cstory__mv-item" data-reveal="left" data-reveal-delay="120">
						<dt class="c-display p-cstory__mv-eng">MISSION</dt>
						<dd class="p-cstory__mv-desc">エクステリア業界の不透明さをなくし、<br>すべての人に見える顧客体験を提供する。</dd>
					</div>
				</dl>
			</div>

			<div class="p-cstory__panel">
				<?php
				// 画面に入ったら 1 文字ずつ出す。英字が出そろってから日本語が流れ出す
				// （待ち時間は css/company.css の --cstory-char-lag が持つ）。
				?>
				<p class="c-display p-cstory__panel-eng" data-reveal="chars"><?php echo exterior_exone_split_chars( 'Brand Story', 'p-cstory__char' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ヘルパー内でエスケープ済み。 ?></p>
				<p class="p-cstory__panel-jp" data-reveal="chars"><?php echo exterior_exone_split_chars( 'ブランドストーリー', 'p-cstory__char' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ヘルパー内でエスケープ済み。 ?></p>

				<div class="p-cstory__text">
					<p>外構をもっと透明に。もっと分かりやすく。もっとワクワクするものへ。</p>
					<p>住まいづくりの最後を彩る外構は、暮らしの価値を大きく左右する大切な存在です。しかし、その一方で、価格の分かりづらさや完成イメージの不透明さ、品質のばらつきなど、業界にはまだ多くの課題が残されています。</p>
					<p>私たちEXoneは、こうした「あたりまえ」を変えるために生まれました。</p>
					<p>デザイン、テクノロジー、そして仕組み化を融合し、見積もりからデザイン、施工管理、アフターサポートまで、すべてのプロセスを可視化することで、お客様が安心して外構づくりを進められる新しい顧客体験を提供しています。</p>
					<p>私たちが目指しているのは、単に外構を施工する会社ではありません。</p>
					<p>業界全体の品質と体験を向上させ、新しいスタンダードを創り続ける存在でありたいと考えています。</p>
					<p>これからも挑戦を止めることなく、テクノロジーと人の力を融合させながら、「外構をもっと透明に。」という想いを全国へ広げてまいります。</p>
				</div>
			</div>

		</div>
	</div>
</section>

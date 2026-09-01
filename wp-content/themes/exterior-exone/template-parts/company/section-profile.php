<?php
/**
 * 企業情報: 会社概要（Company Profile）。
 *
 * カンプ: PC 455:104 内 975:91-949:72（全幅の写真に見出し・コピー・ロゴを重ね、
 *         その下端へ 1200px の白カードで会社概要表を重ねる）
 *         SP  494:511 内 917:953-1046（見出し → 写真 → 表）
 *
 * コピー（949:81）とロゴ（949:72）は PC カンプにのみ存在する。
 * SP は写真を見出しと表の間に置くため、DOM は SP の順で並べて PC 側で組み替える。
 *
 * 写真は朝・夜の 2 枚を重ねてあり、画面に入った時点で朝 → 夜に切り替わる
 * （スクロール量には連動しない）。時間の内訳は css/company.css の --co-profile-* を参照。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_profile      = exterior_exone_company_profile();
$exterior_exone_profile_last = count( $exterior_exone_profile ) - 1;
?>
<?php
// 画面に入ったら、朝の写真を少し見せてから夜へふわっと切り替え、
// 続けてリード文とロゴが下から出る（きっかけは js/scroll-reveal.js の .is-inview）。
// data-reveal="stage" はセクション自体は動かさず、合図だけ受け取るための指定。
?>
<section class="p-cprofile" data-section="company-profile" data-reveal="stage">
	<div class="p-cprofile__media" aria-hidden="true">
		<?php // 朝。ここが枠の高さを決める。 ?>
		<img
			class="p-cprofile__day"
			src="<?php echo esc_url( exterior_exone_company_image( 'profile-bg-day.jpg' ) ); ?>"
			width="1920"
			height="951"
			alt=""
			loading="lazy"
		>

		<?php // 夜。朝の上に重ねてあり、遅れてフェードインしてくる。 ?>
		<img
			class="p-cprofile__night"
			src="<?php echo esc_url( exterior_exone_company_image( 'profile-bg.jpg' ) ); ?>"
			width="1920"
			height="951"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-cprofile__head">
		<h2 class="c-company-eng">Company Profile</h2>
		<p class="c-company-jp">会社概要</p>

		<?php // 2 文目の文言が PC（949:81）と SP（34:1316）で異なるため出し分ける。 ?>
		<p class="p-cprofile__lead p-cprofile__lead--pc">外構を、もっと自由に。もっと透明に。<br>デザインとテクノロジーの力で、外構づくりの新しいスタンダードをつくる。<br>住まいと暮らしの未来をデザインするEXTERIOR COMPANYです。</p>
		<p class="p-cprofile__lead p-cprofile__lead--sp">外構を、もっと自由に。もっと透明に。<br>全てのプロセスを可視化し、外構づくりの新しいスタンダードをつくる。<br>住まいと暮らしの未来をデザインするEXTERIOR COMPANYです。</p>

		<img
			class="p-cprofile__logo"
			src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
			width="201"
			height="31"
			alt="EXone"
			loading="lazy"
		>
	</div>

	<?php // カードは写真より下にあるため、上の流れとは切り離して単体で出す。 ?>
	<div class="p-cprofile__card" data-reveal>
		<dl class="p-cprofile__list">
			<?php foreach ( $exterior_exone_profile as $exterior_exone_index => $exterior_exone_row ) : ?>
				<div class="p-cprofile__row<?php echo $exterior_exone_index === $exterior_exone_profile_last ? ' p-cprofile__row--last' : ''; ?>">
					<dt class="p-cprofile__label"><?php echo esc_html( $exterior_exone_row['label'] ); ?></dt>
					<dd class="p-cprofile__value"><?php echo esc_html( $exterior_exone_row['value'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

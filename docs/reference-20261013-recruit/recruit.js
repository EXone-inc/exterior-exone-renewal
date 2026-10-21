// シーンの設定
// Three.js 初期化コード
let camera, scene, renderer, particles;
const colors = [
  new THREE.Color(0x006e54),
  new THREE.Color(0x0075c2),
  new THREE.Color(0x009b9f),
  new THREE.Color(0xfdd35c),
  new THREE.Color(0xea5532),
  new THREE.Color(0x915da3),
];

document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('mycanvas');
  const container = document.querySelector('.recruit-first--view__main');

  if (!canvas || !container) return;

  const width = container.clientWidth;
  const height = container.clientHeight;

  // Three.js 初期化
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
  camera.position.z = 3;

  renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
  renderer.setSize(width, height);
  renderer.setClearColor(0xffffff);

  // パーティクル設定
  particles = new THREE.Group();
  const particlesCount = 140;
  for (let i = 0; i < particlesCount; i++) {
    const x = (Math.random() * 2 - 1) * (width / height);
    const y = (Math.random() * 2 - 1) * (width / height);
    const z = -Math.random() * 10;

    const geometry = new THREE.SphereGeometry(0.015, 8, 8);
    const material = new THREE.MeshBasicMaterial({
      color: colors[Math.floor(Math.random() * colors.length)],
      transparent: true,
      opacity: 1
    });

    const sphere = new THREE.Mesh(geometry, material);
    sphere.position.set(x, y, z);
    particles.add(sphere);
  }

  scene.add(particles);

  // アニメーションパラメータ
  const startTime = Date.now();
  const initialDuration = 2000;
  const initialSpeed = 0.13;
  const finalSpeed = 0.005;

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  // アニメーションループ
  function animate() {
    requestAnimationFrame(animate);

    const elapsedTime = Date.now() - startTime;

    particles.children.forEach((sphere) => {
      let speed;
      if (elapsedTime < initialDuration) {
        const easedProgress = easeOutCubic(elapsedTime / initialDuration);
        speed = initialSpeed * (1 - easedProgress) + finalSpeed * easedProgress;
      } else {
        speed = finalSpeed;
      }

      sphere.position.z += speed;

      if (
        sphere.position.z > 3 ||
        sphere.position.x > 1.5 || sphere.position.x < -1.5 ||
        sphere.position.y > 1.5 || sphere.position.y < -1.5
      ) {
        sphere.position.set(
          (Math.random() * 2 - 1) * (width / height),
          Math.random() * 2 - 1,
          -Math.random() * 10
        );
        sphere.material.color.set(colors[Math.floor(Math.random() * colors.length)]);
      }
    });

    renderer.render(scene, camera);
  }

  animate();

  // リサイズ対応
  window.addEventListener('resize', () => {
    const newWidth = container.clientWidth;
    const newHeight = container.clientHeight;

    renderer.setSize(newWidth, newHeight);
    camera.aspect = newWidth / newHeight;
    camera.updateProjectionMatrix();
  });
});

const splide_re = new Splide( '.recruit_slide ', {
    mediaQuery: 'min',
    type: 'loop',
    gap: 40,
    fixedWidth: '180px',
    arrows: false,
    drag: 'free',
    flickPower: 300,
    pagination: false,
 
    autoScroll: {
      speed: 1,
      pauseOnHover: false,
      pauseOnFocus: false,
    },

    breakpoints: {
      960: {
        fixedWidth: '230px',
        gap: 35,
      },
      1920: {
        fixedWidth: '280px',
        gap: 50,
      },
    },
  });
  
  splide_re.mount(window.splide.Extensions);

  const splide_re__text = new Splide( '.recruit_text--slide ', {
    mediaQuery: 'min',
    type: 'loop',
    gap: 40,
    arrows: false,
    flickPower: 300,
    pagination: false,
 
    autoScroll: {
      speed: 1.4,
      pauseOnHover: false,
      pauseOnFocus: false,
    },

    breakpoints: {
      960: {
        gap: 10,

        autoScroll: {
            speed: 0.8,
            pauseOnHover: false,
            pauseOnFocus: false,
          },
      }
    },
  } );
  
  splide_re__text.mount(window.splide.Extensions);


// ------------------------------
// フェードイン処理
// ------------------------------
const FADE_IN_OBJECTS = document.querySelectorAll('.fade-in');

const FADE_IN_CB = function (entries, observer) {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
      observer.unobserve(entry.target); // 一度表示されたら監視終了
    }
  });
};

const FADE_IN_OPTIONS = {
  root: null,
  rootMargin: "0px",
  threshold: 0
};

const FADEIN_IO = new IntersectionObserver(FADE_IN_CB, FADE_IN_OPTIONS);

FADE_IN_OBJECTS.forEach(object => {
  FADEIN_IO.observe(object);
});

// ------------------------------
// モーダル close ボタンで fade-in をリセット
// ------------------------------
document.querySelectorAll('.modal_close').forEach(closeBtn => {
  closeBtn.addEventListener('click', () => {
    document.querySelectorAll('.fade-in.show').forEach(el => {
      el.classList.remove('show');
      FADEIN_IO.observe(el);
    });
  });
});

// ------------------------------
// ✅ 100vh対策（iOSなどで実高さを取得）
// ------------------------------
function setRealVh() {
  const vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--real-vh', `${vh}px`);
}

window.addEventListener('resize', setRealVh);
window.addEventListener('load', setRealVh);

// ------------------------------
// ✅ モーダル表示時にスクロール無効化
// ------------------------------
function disableScroll() {
  const scrollY = window.scrollY;
  document.body.style.position = 'fixed';
  document.body.style.top = `-${scrollY}px`;
  document.body.style.width = '100%';
  document.body.dataset.scrollY = scrollY;
  document.body.classList.add('modal-open');
}

function enableScroll() {
  const scrollY = document.body.dataset.scrollY;
  document.body.classList.remove('modal-open');
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.width = '';
  window.scrollTo(0, parseInt(scrollY || '0'));
}

// ------------------------------
// ✅ popover の open/close を検知してスクロール制御
// ------------------------------
document.addEventListener('toggle', (e) => {
  if (!e.target.matches('[popover]')) return;

  if (e.target.matches(':popover-open')) {
    disableScroll();
  } else {
    enableScroll();
  }
});


// ------------------------------
// 募集要項：職種ナビ ＋ 各職種アコーディオン
// 固定ページに <div class="req-nav" id="reqNav"></div> を置くと、
// 各職種（.req-list）を自動で読み取り、
//  ・通常表示の職種ナビを生成
//  ・各職種の募集要項を開閉式（アコーディオン）に変換
//  ・ナビで選ぶと URL に #職種ID が付き、その職種が開いてスクロール
//  ・共有された #職種ID 付きURLで開くと、その職種が自動で開く
// ------------------------------
(function () {
  const reqSection = document.getElementById('recruit-req');
  const navHost = document.getElementById('reqNav');
  if (!reqSection) return;

  // スクロール停止位置の上に空ける余白（px）。詰まって見える場合は数値を増やす。
  const SCROLL_OFFSET = 24;

  // 職種名 → アンカー用スラッグ（URLの #xxxx）の対応表。
  // 例: https://exterior-exone.com/recruit/#sales で「提案営業職」が開いた状態で遷移。
  // ※ キーは見出しテキスト（改行・空白は除去して照合）。職種名を変えたらここも更新。
  //   この表に無い職種は自動で req-job-連番 になります。
  const JOB_SLUGS = {
    '施工管理職': 'construction',
    '提案営業職': 'sales',
    '支店長候補': 'manager',
    'エクステリアプランナー': 'planner',
    '店舗事務スタッフ': 'office',
    '営業責任者（執行役員）': 'executive'
  };

  // 募集要項内の各職種（.req-list）をアコーディオン化して収集
  const jobs = [];
  Array.from(reqSection.querySelectorAll('.req-list')).forEach(function (list, i) {
    const heading = list.querySelector('.req-item--heading');
    const panel = list.querySelector('.req-outline--list');
    const titleEl = list.querySelector('.req-item--heading__text');
    if (!heading || !panel || !titleEl) return;

    const name = (titleEl.textContent || '').replace(/\s+/g, ' ').trim();
    if (!name) return;
    // アンカー用ID。HTML側で id 指定があれば優先、無ければスラッグ表、無ければ連番。
    if (!list.id) list.id = JOB_SLUGS[name] || ('req-job-' + i);

    // 開閉アニメーション用ラッパーでパネルを包む
    const body = document.createElement('div');
    body.className = 'req-list__body';
    body.id = list.id + '-body';
    panel.parentNode.insertBefore(body, panel);
    body.appendChild(panel);

    // 開閉アイコン（＋／−）を見出しに追加
    const icon = document.createElement('span');
    icon.className = 'req-acc__icon';
    icon.setAttribute('aria-hidden', 'true');
    heading.appendChild(icon);

    // 見出しをボタン化
    list.classList.add('req-list--accordion');
    heading.setAttribute('role', 'button');
    heading.setAttribute('tabindex', '0');
    heading.setAttribute('aria-expanded', 'false');
    heading.setAttribute('aria-controls', body.id);

    const job = { id: list.id, name: name, el: list, heading: heading, body: body };
    jobs.push(job);

    heading.addEventListener('click', function () { toggleJob(job); });
    heading.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleJob(job);
      }
    });
  });

  if (!jobs.length) return;

  function setActiveNav(id) {
    if (!navHost) return;
    navHost.querySelectorAll('.req-nav__link').forEach(function (a) {
      a.classList.toggle('is-current', a.dataset.target === id);
    });
  }

  function clearActiveNav(id) {
    if (!navHost) return;
    navHost.querySelectorAll('.req-nav__link').forEach(function (a) {
      if (a.dataset.target === id) a.classList.remove('is-current');
    });
  }

  function openJob(job, doScroll) {
    const wasOpen = job.el.classList.contains('is-open');

    // ── 同時に1つだけ開く ── 他の開いている項目を閉じる。
    //    対象より上にある項目を閉じるとレイアウトが詰まるので、その高さ(removeAbove)を控える。
    let removeAbove = 0;
    const targetTop = job.el.getBoundingClientRect().top;
    jobs.forEach(function (other) {
      if (other === job || !other.el.classList.contains('is-open')) return;
      if (other.el.getBoundingClientRect().top < targetTop) removeAbove += other.body.offsetHeight;
      // 見出しクリック時は即時、ナビ/スクロール時はアニメーションで閉じる
      closeJob(other, !doScroll);
    });

    // 見出しクリック時のみ：閉じた分だけ即補正し、押した見出しを画面内で動かさない。
    // （ナビ/スクロール時は補正せず、下の最終位置スクロールに任せる＝先頭に飛ばない）
    if (!doScroll && removeAbove) {
      window.scrollTo(0, Math.max(0, window.pageYOffset - removeAbove));
    }

    if (!wasOpen) {
      job.el.classList.add('is-open');
      job.heading.setAttribute('aria-expanded', 'true');
      job.body.style.maxHeight = job.body.scrollHeight + 'px';
      // アニメ完了後は高さ固定を解除（レスポンシブで折り返しても崩れない）
      job.body.addEventListener('transitionend', function te(e) {
        if (e.propertyName !== 'max-height') return;
        job.body.removeEventListener('transitionend', te);
        if (job.el.classList.contains('is-open')) job.body.style.maxHeight = 'none';
      });
      setActiveNav(job.id);
    }

    if (doScroll) {
      // ナビ/ディープリンク：上で閉じる項目(removeAbove)を差し引いた最終位置へ、
      // 現在地から一気にスムーズスクロール（先頭に戻らない）。
      const top = Math.max(0, job.el.getBoundingClientRect().top + window.pageYOffset - removeAbove - SCROLL_OFFSET);
      window.scrollTo({ top: top, behavior: 'smooth' });
    }
  }

  function closeJob(job, instant) {
    if (!job.el.classList.contains('is-open')) return;
    if (instant) {
      // アニメーションなしで即座に閉じる（他項目を閉じるとき用）
      const prev = job.body.style.transition;
      job.body.style.transition = 'none';
      job.el.classList.remove('is-open');
      job.heading.setAttribute('aria-expanded', 'false');
      job.body.style.maxHeight = '0px';
      void job.body.offsetHeight; // 反映を確定
      job.body.style.transition = prev || '';
      clearActiveNav(job.id);
      return;
    }
    // 通常（アニメーション）クローズ: none → 現在の高さ(px) に戻してから 0 へ
    job.body.style.maxHeight = job.body.scrollHeight + 'px';
    void job.body.offsetHeight; // 強制リフロー
    job.el.classList.remove('is-open');
    job.heading.setAttribute('aria-expanded', 'false');
    job.body.style.maxHeight = '0px';
    clearActiveNav(job.id);
  }

  function toggleJob(job) {
    if (job.el.classList.contains('is-open')) closeJob(job);
    else openJob(job, false);
  }

  // 職種ナビ（通常表示）を生成
  if (navHost) {
    const ul = document.createElement('ul');
    ul.className = 'req-nav__list';
    jobs.forEach(function (job) {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.className = 'req-nav__link';
      a.href = '#' + job.id;
      a.dataset.target = job.id;
      a.textContent = job.name;
      a.addEventListener('click', function (e) {
        e.preventDefault();
        history.pushState(null, '', '#' + job.id); // URLにアンカーを付与
        openJob(job, true);
      });
      li.appendChild(a);
      ul.appendChild(li);
    });
    navHost.appendChild(ul);
  }

  // 初回ロードのディープリンク用スクロール。
  // 上にある画像・フォント・スライダーの読み込みでレイアウトが下にずれても
  // 最終位置に合うよう、複数回＋load完了時に再スクロールして追従する。
  // ユーザーが手動でスクロール/操作したら、それ以降は補正しない。
  function deepLinkScroll(job) {
    let userInteracted = false;
    const markUser = function () { userInteracted = true; };
    window.addEventListener('wheel', markUser, { passive: true });
    window.addEventListener('touchmove', markUser, { passive: true });
    window.addEventListener('keydown', markUser);

    const go = function () {
      if (userInteracted) return;
      const top = Math.max(0, job.el.getBoundingClientRect().top + window.pageYOffset - SCROLL_OFFSET);
      window.scrollTo({ top: top, behavior: 'smooth' });
    };
    go();
    [250, 700, 1300].forEach(function (t) { setTimeout(go, t); });
    window.addEventListener('load', function () { setTimeout(go, 100); }, { once: true });
    setTimeout(function () {
      window.removeEventListener('wheel', markUser);
      window.removeEventListener('touchmove', markUser);
      window.removeEventListener('keydown', markUser);
    }, 2000);
  }

  // URLに #職種ID があれば、その職種を開いてスクロール
  function openFromHash(robust) {
    const id = (location.hash || '').replace('#', '');
    if (!id) return;
    const job = jobs.find(function (j) { return j.id === id; });
    if (!job) return;
    if (robust) {
      openJob(job, false);   // 開くだけ。スクロールは下の堅牢版に任せる
      deepLinkScroll(job);
    } else {
      openJob(job, true);    // 開く＋最終位置へスムーズスクロール（先頭に飛ばない）
    }
  }

  // 初回ロード時はブラウザの自動スクロール復元が干渉しないように
  if (location.hash && 'scrollRestoration' in history) {
    try { history.scrollRestoration = 'manual'; } catch (e) {}
  }
  openFromHash(true);                                           // 初回（レイアウト変動に強い）
  window.addEventListener('hashchange', function () { openFromHash(false); }); // 戻る/進む・手動変更

  // 「応募する」ボタンを最前面の固定CTAにする。
  // 祖先要素の重なり順（スタッキングコンテキスト）に影響されないよう body 直下へ移動。
  const applyBtn = reqSection.querySelector('.rec-rink_btn');
  if (applyBtn) {
    applyBtn.classList.add('is-fixed');
    document.body.appendChild(applyBtn);

    // FV（ファーストビュー）では非表示。「教育・研修体制」セクションに入ったら表示する。
    const eduSection = document.querySelector('.rsec--edu');
    if (eduSection) {
      const updateApplyVisible = function () {
        // 「教育・研修体制」セクションの上端が画面下端より上に来たら（＝画面内に入ったら）表示
        const show = eduSection.getBoundingClientRect().top <= window.innerHeight;
        applyBtn.classList.toggle('is-visible', show);
      };
      window.addEventListener('scroll', updateApplyVisible, { passive: true });
      window.addEventListener('resize', updateApplyVisible);
      updateApplyVisible();
    } else {
      // セクションが見つからない場合は常時表示（フォールバック）
      applyBtn.classList.add('is-visible');
    }
  }
})();
/* ============================================================
   採用ページ FAQ アコーディオン（2026-06 追加セクション）
   .faq-q クリックで .faq-item を開閉。max-height でスムーズ展開。
   ============================================================ */
(function () {
  var btns = document.querySelectorAll('.faq-q');
  if (!btns.length) return;
  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var panel = item.querySelector('.faq-a');
      if (item.classList.toggle('is-open')) {
        panel.style.maxHeight = panel.scrollHeight + 'px';
      } else {
        panel.style.maxHeight = null;
      }
    });
  });
  window.addEventListener('resize', function () {
    document.querySelectorAll('.faq-item.is-open .faq-a').forEach(function (p) {
      p.style.maxHeight = p.scrollHeight + 'px';
    });
  });
})();

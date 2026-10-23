# TOP mobile FV stable height (2026-10-22)

Scope: requested first-stage trial for iPhone Safari scrolling stutter. Change only style.css, at widths up to 1024px, for .is-front-page .l-pin .p-fv. Use calc(100svh - var(--header-height)) with @supports fallback.

Do not change the video or mask JavaScript. Preserve production theme metadata and PHP requirements.

Local verification: 390x844 uses svh, FV height 797.195px, thumbnails within the viewport, no horizontal overflow. Desktop 1440x900 retains 100dvh.

Actual Safari toolbar animation cannot be reproduced by a desktop viewport override. User should test scrolling into the philosophy section, the lower background edge and thumbnails as the toolbar retracts, and scrolling back up.

Production deployment completed: one CSS file, verified hash, temporary stage removed; no database writes, theme switches, or PHP changes. Public page computed style at 390x844 confirms the svh override.

## Follow-up: prevent exposed background below FV

User supplied a Safari screenshot with a pale strip below the video. The small viewport height used for the entire FV cannot cover the enlarged viewport after browser chrome retracts. Replaced the TOP/SP height override with stable 100lvh minus header height, and kept copy height and thumbnail bottom positioning relative to stable 100svh. No dynamic resizing, video or mask JS changes.

Controlled local fixture used large height 844px, small height 744px, header 48px. At visible viewport heights 744 and 844, background bottom stayed at 844, copy bottom at 744 and thumbnail bottom at 708.914px; uncovered pixels at expanded viewport: 0. Temporary fixture removed after verification. This tests geometry, not the actual iOS Safari rendering engine.

// Nguồn ESM — bundle ra main.js: trong packages/theme-vinahentai chạy npm install && npm run build:js
import {
  autoUpdate,
  computePosition,
  flip,
  offset,
  shift,
} from "@floating-ui/dom";

// Panel gắn trong header có overflow-hidden + ancestor transform → fixed bị cắt/sai tọa độ.
// Đưa panel ra body khi mở, trả về DOM cũ khi đóng.
const restorePanelDomIfNeeded = (panel) => {
  if (!panel._restoreParentNode || panel.parentNode !== document.body) {
    return;
  }
  const parent = panel._restoreParentNode;
  const next = panel._restoreNextSibling;
  if (next && next.parentNode === parent) {
    parent.insertBefore(panel, next);
  } else {
    parent.appendChild(panel);
  }
  panel._restoreParentNode = null;
  panel._restoreNextSibling = null;
};

const portalPanelToBody = (panel) => {
  if (panel.parentNode === document.body) {
    return;
  }
  panel._restoreParentNode = panel.parentNode;
  panel._restoreNextSibling = panel.nextSibling;
  document.body.appendChild(panel);
};

// Panel có data-nav-dropdown-tailwind-size: giữ w-* từ class Tailwind (vd. dropdown thông báo w-96).
const panelUsesTailwindWidth = (panel) =>
  panel?.dataset?.navDropdownTailwindSize === "true" ||
  panel?.getAttribute?.("data-nav-dropdown-tailwind-size") === "true";

// Menu user (góc phải): Floating UI bottom-end — tọa độ viewport (fixed) sau khi portal ra body.
const positionAlignEndPanel = (trigger, panel) =>
  computePosition(trigger, panel, {
    strategy: "fixed",
    placement: "bottom-end",
    middleware: [offset(12), flip(), shift({ padding: 8 })],
  }).then(({ x, y }) => {
    Object.assign(panel.style, {
      position: "fixed",
      left: `${x}px`,
      top: `${y}px`,
      right: "auto",
      transform: "",
    });
    if (!panelUsesTailwindWidth(panel)) {
      panel.style.width = "18rem";
      panel.style.minWidth = "min(18rem, calc(100vw - 1rem))";
      panel.style.maxWidth = "min(18rem, calc(100vw - 1rem))";
    }
  });

// Dưới 640px: full viewport width, căn trái như Radix. Từ sm trở lên: panel 640px căn giữa dưới nút.
const positionPanel = (trigger, panel) => {
  const alignEnd = trigger.closest('[data-nav-dropdown-align="end"]') !== null;
  if (alignEnd) {
    return positionAlignEndPanel(trigger, panel);
  }

  const r = trigger.getBoundingClientRect();
  const vw = window.innerWidth;
  const gap = 8;
  const isMobile = vw < 640;

  panel.style.position = "fixed";
  panel.style.transform = "";

  if (isMobile) {
    panel.style.left = "0px";
    panel.style.right = "0px";
    panel.style.width = "100vw";
    panel.style.maxWidth = "100vw";
    panel.style.top = r.bottom + gap + "px";
  } else {
    panel.style.right = "";
    const maxW = Math.min(640, vw * 0.95 - 16);
    panel.style.width = maxW + "px";
    panel.style.maxWidth = maxW + "px";
    const left = Math.max(
      8,
      Math.min(r.left + r.width / 2 - maxW / 2, vw - maxW - 8),
    );
    panel.style.left = `${left}px`;
    panel.style.top = r.bottom + gap + "px";
  }
  return Promise.resolve();
};

const closeAll = () => {
  document.querySelectorAll("[data-nav-dropdown-panel]").forEach((panel) => {
    restorePanelDomIfNeeded(panel);
    panel.classList.add("hidden");
    panel.dataset.state = "closed";
    panel.style.position = "";
    panel.style.left = "";
    panel.style.top = "";
    panel.style.right = "";
    panel.style.transform = "";
    panel.style.width = "";
    panel.style.minWidth = "";
    panel.style.maxWidth = "";
  });
  document.querySelectorAll("[data-nav-dropdown-trigger]").forEach((t) => {
    t.setAttribute("aria-expanded", "false");
    t.dataset.state = "closed";
  });
};

const openPanel = (trigger, panel) => {
  closeAll();
  const alignEnd = trigger.closest('[data-nav-dropdown-align="end"]') !== null;
  if (alignEnd) {
    portalPanelToBody(panel);
    // Fallback vị trí tức thì để user thấy menu ngay,
    // kể cả khi computePosition chạy chậm.
    const r = trigger.getBoundingClientRect();
    const gap = 12;
    panel.style.position = "fixed";
    panel.style.left = "auto";
    panel.style.right = `${Math.max(8, window.innerWidth - r.right)}px`;
    panel.style.top = `${r.bottom + gap}px`;
    panel.style.transform = "";
    if (!panelUsesTailwindWidth(panel)) {
      panel.style.width = "18rem";
      panel.style.minWidth = "min(18rem, calc(100vw - 1rem))";
      panel.style.maxWidth = "min(18rem, calc(100vw - 1rem))";
    }
  }
  panel.classList.remove("hidden");
  panel.dataset.state = "open";
  trigger.setAttribute("aria-expanded", "true");
  trigger.dataset.state = "open";
  return positionPanel(trigger, panel).then(() => {
    try {
      panel.focus({
        preventScroll: true,
      });
    } catch (_) {}
  });
};

document.querySelectorAll("[data-nav-dropdown]").forEach((root) => {
  const trigger = root.querySelector("[data-nav-dropdown-trigger]");
  const panel = root.querySelector("[data-nav-dropdown-panel]");
  if (!trigger || !panel) {
    return;
  }

  trigger.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    const isOpen = panel.dataset.state === "open";
    if (isOpen) {
      closeAll();
    } else {
      void openPanel(trigger, panel);
    }
  });

  panel.querySelectorAll("[data-nav-dropdown-close]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeAll();
    });
  });
});

document.querySelectorAll("[data-nav-dropdown-panel]").forEach((panel) => {
  panel.addEventListener("click", (e) => e.stopPropagation());
});

const inlineSearchRoot = document.querySelector("[data-mobile-inline-search]");
const mobileSearchOpenBtn = inlineSearchRoot?.querySelector(
  "[data-mobile-search-open]",
);
const mobileSearchPanel = inlineSearchRoot?.querySelector(
  "[data-mobile-search-panel]",
);
const mobileSearchCloseBtn = inlineSearchRoot?.querySelector(
  "[data-mobile-search-close]",
);
const mobileSearchInput = inlineSearchRoot?.querySelector(
  "[data-mobile-search-input]",
);

const closeMobileInlineSearch = () => {
  if (!mobileSearchOpenBtn || !mobileSearchPanel) {
    return;
  }
  mobileSearchOpenBtn.classList.remove("hidden");
  mobileSearchPanel.classList.add("hidden");
  mobileSearchOpenBtn.setAttribute("aria-expanded", "false");
};

const openMobileInlineSearch = () => {
  if (!mobileSearchOpenBtn || !mobileSearchPanel) {
    return;
  }
  closeAll();
  mobileSearchOpenBtn.classList.add("hidden");
  mobileSearchPanel.classList.remove("hidden");
  mobileSearchOpenBtn.setAttribute("aria-expanded", "true");
  requestAnimationFrame(() => {
    try {
      mobileSearchInput?.focus({
        preventScroll: true,
      });
    } catch (_) {}
  });
};

mobileSearchOpenBtn?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  openMobileInlineSearch();
});

mobileSearchCloseBtn?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  closeMobileInlineSearch();
});

mobileSearchPanel?.addEventListener("click", (e) => e.stopPropagation());

// Menu mobile: Floating UI (computePosition + autoUpdate) — tránh lệch/đè layer khi tự tính tay
const mobileMenuPopover = document.querySelector("[data-mobile-menu-popover]");
const mobileMenuTrigger = document.querySelector("[data-mobile-menu-trigger]");
const mobileMenuDialog = document.getElementById("mobile-menu-popover");

/** @type {(() => void) | null} */
let mobileMenuAutoUpdateCleanup = null;

const stopMobileMenuAutoUpdate = () => {
  if (typeof mobileMenuAutoUpdateCleanup === "function") {
    mobileMenuAutoUpdateCleanup();
    mobileMenuAutoUpdateCleanup = null;
  }
};

const startMobileMenuAutoUpdate = () => {
  if (!mobileMenuPopover || !mobileMenuTrigger) {
    return;
  }
  stopMobileMenuAutoUpdate();
  mobileMenuAutoUpdateCleanup = autoUpdate(
    mobileMenuTrigger,
    mobileMenuPopover,
    async () => {
      const { x, y } = await computePosition(
        mobileMenuTrigger,
        mobileMenuPopover,
        {
          strategy: "fixed",
          placement: "bottom-end",
          middleware: [offset(12), flip(), shift({ padding: 8 })],
        },
      );
      Object.assign(mobileMenuPopover.style, {
        position: "fixed",
        left: `${x}px`,
        top: `${y}px`,
        right: "auto",
        bottom: "auto",
        transform: "",
      });
    },
    { animationFrame: true },
  );
};

const closeMobileMenu = () => {
  stopMobileMenuAutoUpdate();
  if (!mobileMenuPopover || !mobileMenuTrigger || !mobileMenuDialog) {
    return;
  }
  mobileMenuPopover.classList.add("hidden");
  mobileMenuPopover.setAttribute("aria-hidden", "true");
  mobileMenuTrigger.setAttribute("aria-expanded", "false");
  mobileMenuTrigger.dataset.state = "closed";
  mobileMenuDialog.dataset.state = "closed";
};

const openMobileMenu = () => {
  if (!mobileMenuPopover || !mobileMenuTrigger || !mobileMenuDialog) {
    return;
  }
  closeAll();
  closeMobileInlineSearch();
  mobileMenuPopover.classList.remove("hidden");
  mobileMenuPopover.setAttribute("aria-hidden", "false");
  mobileMenuTrigger.setAttribute("aria-expanded", "true");
  mobileMenuTrigger.dataset.state = "open";
  mobileMenuDialog.dataset.state = "open";
  startMobileMenuAutoUpdate();
  try {
    mobileMenuDialog.focus({
      preventScroll: true,
    });
  } catch (_) {}
};

const toggleMobileMenu = () => {
  if (!mobileMenuPopover || !mobileMenuPopover.classList.contains("hidden")) {
    closeMobileMenu();
  } else {
    openMobileMenu();
  }
};

mobileMenuTrigger?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  toggleMobileMenu();
});

mobileMenuPopover?.addEventListener("click", (e) => e.stopPropagation());

// Trang profile: popover hướng dẫn Dâm Ngọc — hover (desktop) + click bật/tắt (mobile), Floating UI giống menu mobile
const pointsHelpPopover = document.querySelector(
  "[data-profile-points-help-popover]",
);
const pointsHelpTrigger = document.querySelector(
  "[data-profile-points-help-trigger]",
);
const pointsHelpDialog = document.getElementById("profile-points-help-dialog");

/** @type {(() => void) | null} */
let pointsHelpAutoUpdateCleanup = null;
/** @type {ReturnType<typeof setTimeout> | null} */
let pointsHelpCloseTimer = null;

const stopPointsHelpAutoUpdate = () => {
  if (typeof pointsHelpAutoUpdateCleanup === "function") {
    pointsHelpAutoUpdateCleanup();
    pointsHelpAutoUpdateCleanup = null;
  }
};

const startPointsHelpAutoUpdate = () => {
  if (!pointsHelpPopover || !pointsHelpTrigger) {
    return;
  }
  stopPointsHelpAutoUpdate();
  pointsHelpAutoUpdateCleanup = autoUpdate(
    pointsHelpTrigger,
    pointsHelpPopover,
    async () => {
      const { x, y } = await computePosition(
        pointsHelpTrigger,
        pointsHelpPopover,
        {
          strategy: "fixed",
          placement: "bottom",
          middleware: [offset(8), flip(), shift({ padding: 8 })],
        },
      );
      Object.assign(pointsHelpPopover.style, {
        position: "fixed",
        left: `${x}px`,
        top: `${y}px`,
        right: "auto",
        bottom: "auto",
        transform: "",
      });
    },
    { animationFrame: true },
  );
};

const closeProfilePointsHelp = () => {
  if (pointsHelpCloseTimer !== null) {
    clearTimeout(pointsHelpCloseTimer);
    pointsHelpCloseTimer = null;
  }
  stopPointsHelpAutoUpdate();
  if (!pointsHelpPopover || !pointsHelpTrigger || !pointsHelpDialog) {
    return;
  }
  pointsHelpPopover.classList.add("hidden");
  pointsHelpPopover.setAttribute("aria-hidden", "true");
  pointsHelpTrigger.setAttribute("aria-expanded", "false");
  pointsHelpTrigger.dataset.state = "closed";
  pointsHelpDialog.dataset.state = "closed";
};

const openProfilePointsHelp = () => {
  if (pointsHelpCloseTimer !== null) {
    clearTimeout(pointsHelpCloseTimer);
    pointsHelpCloseTimer = null;
  }
  if (!pointsHelpPopover || !pointsHelpTrigger || !pointsHelpDialog) {
    return;
  }
  pointsHelpPopover.classList.remove("hidden");
  pointsHelpPopover.setAttribute("aria-hidden", "false");
  pointsHelpTrigger.setAttribute("aria-expanded", "true");
  pointsHelpTrigger.dataset.state = "open";
  pointsHelpDialog.dataset.state = "open";
  startPointsHelpAutoUpdate();
};

const scheduleCloseProfilePointsHelp = () => {
  pointsHelpCloseTimer = window.setTimeout(() => {
    pointsHelpCloseTimer = null;
    closeProfilePointsHelp();
  }, 200);
};

const cancelCloseProfilePointsHelp = () => {
  if (pointsHelpCloseTimer !== null) {
    clearTimeout(pointsHelpCloseTimer);
    pointsHelpCloseTimer = null;
  }
};

if (pointsHelpPopover && pointsHelpTrigger && pointsHelpDialog) {
  pointsHelpTrigger.addEventListener("mouseenter", () => {
    openProfilePointsHelp();
  });
  pointsHelpTrigger.addEventListener("mouseleave", () => {
    scheduleCloseProfilePointsHelp();
  });
  pointsHelpPopover.addEventListener("mouseenter", () => {
    cancelCloseProfilePointsHelp();
  });
  pointsHelpPopover.addEventListener("mouseleave", () => {
    closeProfilePointsHelp();
  });

  pointsHelpTrigger.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    const isOpen = pointsHelpDialog.dataset.state === "open";
    if (isOpen) {
      closeProfilePointsHelp();
    } else {
      openProfilePointsHelp();
    }
  });

  pointsHelpPopover.addEventListener("click", (e) => e.stopPropagation());
}

// Trang Triệu Hồi Waifu: modal hướng dẫn + modal nhận thưởng
const waifuSummonGuideModal = document.querySelector(
  "[data-waifu-summon-guide-modal]",
);
const waifuSummonGuideTrigger = document.querySelector(
  "[data-waifu-summon-guide-trigger]",
);
const waifuSummonGuideDialog = document.getElementById(
  "waifu-summon-guide-dialog",
);
const waifuSummonGuideOverlay = document.querySelector(
  "[data-waifu-summon-guide-overlay]",
);

const waifuSummonRewardsModal = document.querySelector(
  "[data-waifu-summon-rewards-modal]",
);
const waifuSummonRewardsTrigger = document.querySelector(
  "[data-waifu-summon-rewards-trigger]",
);
const waifuSummonRewardsDialog = document.getElementById(
  "waifu-summon-rewards-dialog",
);
const waifuSummonRewardsOverlay = document.querySelector(
  "[data-waifu-summon-rewards-overlay]",
);

/** Phiên triệu hồi hợp lệ (sau POST thành công). */
let waifuSummonSessionActive = false;
/** @type {{ waifu_id?: number, name?: string, rarity?: number, exp: number, image_url: string }[] | null} */
let waifuSummonResults = null;
/** Escape trên lớp video — gán trong IIFE triệu hồi. */
let waifuSummonOnEscapeVideo = () => {
  dismissWaifuSummonCinematicIfAny();
};

const updateWaifuSummonBodyScrollLock = () => {
  const guideOpen = waifuSummonGuideModal?.dataset.state === "open";
  const rewardsOpen = waifuSummonRewardsModal?.dataset.state === "open";
  const videoLayer = document.querySelector("[data-waifu-summon-video-layer]");
  const cardLayer = document.querySelector("[data-waifu-summon-card-layer]");
  const cinematicOpen =
    Boolean(videoLayer && !videoLayer.classList.contains("hidden")) ||
    Boolean(cardLayer && !cardLayer.classList.contains("hidden"));
  document.documentElement.style.overflow =
    guideOpen || rewardsOpen || cinematicOpen ? "hidden" : "";
};

/** Timer tự đóng overlay chúc mừng 5★. */
let waifu5StarCelebrationHideTimer = null;

/** Ẩn overlay chúc mừng Waifu 5★ + dọn confetti / viền thẻ. */
const hideWaifu5StarCelebration = () => {
  if (waifu5StarCelebrationHideTimer !== null) {
    window.clearTimeout(waifu5StarCelebrationHideTimer);
    waifu5StarCelebrationHideTimer = null;
  }
  const el = document.querySelector("[data-waifu-summon-5star-celebration]");
  if (!el) {
    return;
  }
  el.classList.add("hidden");
  el.classList.add("pointer-events-none");
  el.classList.remove("pointer-events-auto", "waifu-5star-celebration--active");
  el.setAttribute("aria-hidden", "true");
  el.querySelector("[data-waifu-summon-5star-burst]")?.replaceChildren();
  document
    .querySelector("[data-waifu-summon-card-layer]")
    ?.classList.remove("waifu-5star-layer-shake");
  document
    .querySelectorAll(".waifu-summon-card-root--5star-glow")
    .forEach((node) =>
      node.classList.remove("waifu-summon-card-root--5star-glow"),
    );
};

/** Hiệu ứng chúc mừng khi lật ra Waifu 5★. */
const showWaifu5StarCelebration = () => {
  const el = document.querySelector("[data-waifu-summon-5star-celebration]");
  const burst = el?.querySelector("[data-waifu-summon-5star-burst]");
  if (!el || !burst) {
    return;
  }
  if (waifu5StarCelebrationHideTimer !== null) {
    window.clearTimeout(waifu5StarCelebrationHideTimer);
    waifu5StarCelebrationHideTimer = null;
  }
  burst.replaceChildren();
  const reducedMotion =
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  document
    .querySelector("[data-waifu-summon-card-layer]")
    ?.classList.toggle("waifu-5star-layer-shake", !reducedMotion);
  if (!reducedMotion) {
    const colors = [
      "#FFD700",
      "#FF69B4",
      "#DD94FF",
      "#FFF8DC",
      "#FFA500",
      "#E0B0FF",
      "#67e8f9",
      "#fef08a",
    ];
    const frag = document.createDocumentFragment();
    for (let i = 0; i < 110; i++) {
      const p = document.createElement("span");
      const roll = Math.random();
      if (roll < 0.22) {
        p.className = "waifu-5star-confetti waifu-5star-confetti--dot";
        p.style.setProperty("--ww", `${5 + Math.random() * 7}px`);
        p.style.setProperty("--wh", `${5 + Math.random() * 7}px`);
      } else if (roll < 0.4) {
        p.className = "waifu-5star-confetti waifu-5star-confetti--ribbon";
        p.style.setProperty("--ww", `${6 + Math.random() * 10}px`);
        p.style.setProperty("--wh", `${16 + Math.random() * 22}px`);
        p.style.setProperty("--wbr", "1px");
      } else {
        p.className = "waifu-5star-confetti";
        p.style.setProperty("--ww", `${8 + Math.random() * 12}px`);
        p.style.setProperty("--wh", `${10 + Math.random() * 18}px`);
      }
      p.style.setProperty("--wx", String(Math.random() * 100));
      p.style.setProperty("--wdelay", `${(Math.random() * 0.65).toFixed(3)}s`);
      p.style.setProperty(
        "--wdur",
        `${(2.4 + Math.random() * 1.8).toFixed(2)}s`,
      );
      p.style.setProperty("--wrot", `${Math.floor(Math.random() * 900)}deg`);
      p.style.setProperty(
        "--wx2",
        `${(Math.random() * 160 - 80).toFixed(1)}px`,
      );
      p.style.backgroundColor = colors[i % colors.length];
      frag.appendChild(p);
    }
    const nSpark = 42;
    for (let i = 0; i < nSpark; i++) {
      const ang = (Math.PI * 2 * i) / nSpark + Math.random() * 0.25;
      const dist = 55 + Math.random() * 140;
      const s = document.createElement("span");
      s.className = "waifu-5star-sparkle";
      s.style.backgroundColor = colors[i % colors.length];
      s.style.setProperty("--svx", `${(Math.cos(ang) * dist).toFixed(1)}px`);
      s.style.setProperty("--svy", `${(Math.sin(ang) * dist).toFixed(1)}px`);
      s.style.setProperty("--sdelay", `${(Math.random() * 0.35).toFixed(3)}s`);
      s.style.setProperty(
        "--sdur",
        `${(0.85 + Math.random() * 0.55).toFixed(2)}s`,
      );
      s.style.setProperty("--sw", `${4 + Math.random() * 6}px`);
      frag.appendChild(s);
    }
    burst.appendChild(frag);
  }
  el.classList.remove("hidden");
  el.classList.remove("pointer-events-none");
  el.classList.add("pointer-events-auto");
  el.setAttribute("aria-hidden", "false");
  void el.offsetWidth;
  el.classList.add("waifu-5star-celebration--active");
  const autoMs = reducedMotion ? 3000 : 8500;
  waifu5StarCelebrationHideTimer = window.setTimeout(() => {
    hideWaifu5StarCelebration();
    waifu5StarCelebrationHideTimer = null;
  }, autoMs);
};

document
  .querySelector("[data-waifu-summon-5star-celebration]")
  ?.addEventListener("click", () => {
    hideWaifu5StarCelebration();
  });

/** Đóng video + màn lật thẻ triệu hồi (dùng khi mở modal khác hoặc reset). */
const dismissWaifuSummonCinematicIfAny = (resetSummonSession = true) => {
  hideWaifu5StarCelebration();
  const videoLayer = document.querySelector("[data-waifu-summon-video-layer]");
  const cardLayer = document.querySelector("[data-waifu-summon-card-layer]");
  const video = document.querySelector("[data-waifu-summon-video]");
  const cardFooter = document.querySelector("[data-waifu-summon-card-footer]");
  document.querySelector("[data-waifu-summon-cards-grid]")?.replaceChildren();
  videoLayer?.classList.add("hidden");
  videoLayer?.setAttribute("aria-hidden", "true");
  cardLayer?.classList.add("hidden");
  cardLayer?.setAttribute("aria-hidden", "true");
  cardFooter?.classList.add("hidden");
  if (video) {
    try {
      video.pause();
    } catch (_) {}
    try {
      video.currentTime = 0;
    } catch (_) {}
  }
  if (resetSummonSession) {
    waifuSummonSessionActive = false;
    waifuSummonResults = null;
  }
  updateWaifuSummonBodyScrollLock();
};

const closeWaifuSummonRewards = () => {
  if (!waifuSummonRewardsModal) {
    return;
  }
  waifuSummonRewardsModal.classList.add("hidden");
  waifuSummonRewardsModal.setAttribute("aria-hidden", "true");
  waifuSummonRewardsModal.dataset.state = "closed";
  waifuSummonRewardsTrigger?.setAttribute("aria-expanded", "false");
  waifuSummonRewardsTrigger?.setAttribute("data-state", "closed");
  waifuSummonRewardsOverlay?.setAttribute("data-state", "closed");
  waifuSummonRewardsDialog?.setAttribute("data-state", "closed");
  updateWaifuSummonBodyScrollLock();
};

const getWaifuSummonPageRootEl = () =>
  document.querySelector("[data-waifu-summon-page]");

/** Lịch sử trong modal Nhận thưởng — GET JSON, phân trang bằng nút (không đổi URL trang). */
const loadWaifuSummonRewardsHistory = async (page = 1) => {
  const root = getWaifuSummonPageRootEl();
  const baseUrl = root?.dataset?.waifuSummonRewardsHistoryUrl?.trim();
  const tbody = document.querySelector("[data-waifu-summon-rewards-rows]");
  const pagEl = document.querySelector(
    "[data-waifu-summon-rewards-pagination]",
  );
  if (!baseUrl || !tbody) {
    return;
  }
  const esc = (s) => {
    const t = document.createElement("div");
    t.textContent = String(s ?? "");
    return t.innerHTML;
  };
  tbody.innerHTML =
    '<div class="border-bd-default grid grid-cols-3 border-b last:border-b-0"><div class="col-span-3 px-3 py-4 text-center font-sans text-sm font-medium text-txt-secondary">Đang tải…</div></div>';
  if (pagEl) {
    pagEl.innerHTML = "";
  }
  let url;
  try {
    const u = new URL(baseUrl, window.location.origin);
    u.searchParams.set("page", String(page));
    url = u.toString();
  } catch (_) {
    url = `${baseUrl}${baseUrl.includes("?") ? "&" : "?"}page=${page}`;
  }
  try {
    const res = await fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });
    const json = await res.json().catch(() => ({}));
    if (res.status === 401 || res.status === 419) {
      tbody.innerHTML =
        '<div class="border-bd-default grid grid-cols-3 border-b last:border-b-0"><div class="col-span-3 px-3 py-8 text-center font-sans text-sm font-medium text-txt-secondary">Đăng nhập để xem lịch sử.</div></div>';
      return;
    }
    if (!res.ok) {
      tbody.innerHTML =
        '<div class="border-bd-default grid grid-cols-3 border-b last:border-b-0"><div class="col-span-3 px-3 py-8 text-center font-sans text-sm font-medium text-txt-secondary">Không tải được lịch sử.</div></div>';
      return;
    }
    const rows = Array.isArray(json.data) ? json.data : [];
    if (rows.length === 0) {
      tbody.innerHTML =
        '<div class="px-3 py-8 text-center font-sans text-sm font-medium text-txt-secondary">Chưa có lịch sử triệu hồi.</div>';
    } else {
      tbody.innerHTML = rows
        .map(
          (r) =>
            `<div class="border-bd-default grid grid-cols-3 border-b last:border-b-0">
          <div class="border-bd-default flex flex-col gap-1 border-r px-3 py-2 sm:flex-row sm:items-center sm:gap-2">
            <div class="font-sans text-sm leading-6 font-medium text-txt-secondary sm:text-base">${esc(r.date)}</div>
            <div class="font-sans text-sm leading-6 font-medium text-txt-secondary sm:text-base">${esc(r.time)}</div>
          </div>
          <div class="border-bd-default border-r px-3 py-2">
            <div class="font-sans text-sm leading-6 font-medium text-txt-secondary sm:text-base">${esc(r.result)}</div>
          </div>
          <div class="px-3 py-2">
            <div class="font-sans text-sm leading-6 font-medium text-txt-secondary sm:text-base">${esc(r.rarity)}</div>
          </div>
        </div>`,
        )
        .join("");
    }
    if (pagEl) {
      pagEl.innerHTML =
        typeof json.pagination_html === "string" ? json.pagination_html : "";
    }
  } catch (_) {
    tbody.innerHTML =
      '<div class="border-bd-default grid grid-cols-3 border-b last:border-b-0"><div class="col-span-3 px-3 py-8 text-center font-sans text-sm font-medium text-txt-secondary">Lỗi mạng.</div></div>';
    if (pagEl) {
      pagEl.innerHTML = "";
    }
  }
};

const closeWaifuSummonGuide = () => {
  if (!waifuSummonGuideModal) {
    return;
  }
  waifuSummonGuideModal.classList.add("hidden");
  waifuSummonGuideModal.setAttribute("aria-hidden", "true");
  waifuSummonGuideModal.dataset.state = "closed";
  waifuSummonGuideTrigger?.setAttribute("aria-expanded", "false");
  waifuSummonGuideTrigger?.setAttribute("data-state", "closed");
  waifuSummonGuideOverlay?.setAttribute("data-state", "closed");
  waifuSummonGuideDialog?.setAttribute("data-state", "closed");
  updateWaifuSummonBodyScrollLock();
};

const openWaifuSummonRewards = () => {
  if (!waifuSummonRewardsModal || !waifuSummonRewardsDialog) {
    return;
  }
  dismissWaifuSummonCinematicIfAny();
  closeAll();
  closeMobileMenu();
  closeMobileInlineSearch();
  closeProfilePointsHelp();
  closeWaifuSummonGuide();
  waifuSummonRewardsModal.classList.remove("hidden");
  waifuSummonRewardsModal.setAttribute("aria-hidden", "false");
  waifuSummonRewardsModal.dataset.state = "open";
  waifuSummonRewardsTrigger?.setAttribute("aria-expanded", "true");
  waifuSummonRewardsTrigger?.setAttribute("data-state", "open");
  waifuSummonRewardsOverlay?.setAttribute("data-state", "open");
  waifuSummonRewardsDialog.setAttribute("data-state", "open");
  updateWaifuSummonBodyScrollLock();
  void loadWaifuSummonRewardsHistory(1);
  try {
    waifuSummonRewardsDialog.focus({
      preventScroll: true,
    });
  } catch (_) {}
};

/** Phân trang lịch sử modal — cùng data-pagination-* như components/pagination.blade.php */
const waifuRewardsPaginationHost = (el) =>
  el?.closest?.("[data-waifu-summon-rewards-pagination]");

/**
 * Click trong #waifu-summon-rewards-dialog bị chặn nổi bọt ở dialog (stopPropagation),
 * nên listener phải gắn trên dialog, không phải trên [data-waifu-summon-rewards-modal].
 */
const handleWaifuRewardsPaginationClick = (e) => {
  const dialog = waifuSummonRewardsDialog;
  if (!dialog) {
    return;
  }
  const pagHost = waifuRewardsPaginationHost(e.target);
  if (!pagHost || !dialog.contains(pagHost)) {
    return;
  }
  const pagFirst = e.target?.closest?.("[data-pagination-first]");
  if (pagFirst && pagHost.contains(pagFirst)) {
    e.preventDefault();
    e.stopPropagation();
    if (!pagFirst.disabled) {
      void loadWaifuSummonRewardsHistory(1);
    }
    return;
  }
  const pagLast = e.target?.closest?.("[data-pagination-last]");
  if (pagLast && pagHost.contains(pagLast)) {
    e.preventDefault();
    e.stopPropagation();
    if (!pagLast.disabled) {
      const max = Number(
        pagHost
          .querySelector("[data-pagination-jump-input]")
          ?.getAttribute("max") || 1,
      );
      void loadWaifuSummonRewardsHistory(Number.isFinite(max) ? max : 1);
    }
    return;
  }
  const pagPage = e.target?.closest?.("[data-pagination-page]");
  if (pagPage && pagHost.contains(pagPage)) {
    e.preventDefault();
    e.stopPropagation();
    if (pagPage.disabled) {
      return;
    }
    const p = Number(pagPage.getAttribute("data-pagination-page"));
    if (Number.isFinite(p) && p >= 1) {
      void loadWaifuSummonRewardsHistory(p);
    }
  }
};

waifuSummonRewardsDialog?.addEventListener(
  "click",
  handleWaifuRewardsPaginationClick,
);

waifuSummonRewardsDialog?.addEventListener("input", (e) => {
  const inp = e.target?.closest?.("[data-pagination-jump-input]");
  if (!inp || !waifuSummonRewardsDialog?.contains(inp)) {
    return;
  }
  const pagHost = waifuRewardsPaginationHost(inp);
  if (!pagHost || !waifuSummonRewardsDialog.contains(pagHost)) {
    return;
  }
  const form = inp.closest("form");
  const btn = form?.querySelector("[data-pagination-jump-submit]");
  if (!btn) {
    return;
  }
  const max = Number(inp.getAttribute("max") || 1);
  const min = Number(inp.getAttribute("min") || 1);
  const raw = String(inp.value || "").trim();
  if (raw === "") {
    btn.disabled = true;
    return;
  }
  const n = Number(raw);
  const ok = Number.isInteger(n) && n >= min && n <= max;
  btn.disabled = !ok;
});

waifuSummonRewardsDialog?.addEventListener("submit", (e) => {
  const form = e.target;
  if (!(form instanceof HTMLFormElement)) {
    return;
  }
  if (!form.matches("[data-pagination-jump-form]")) {
    return;
  }
  if (form.matches("[data-comments-pagination-jump]")) {
    return;
  }
  const pagHost = waifuRewardsPaginationHost(form);
  if (!pagHost || !waifuSummonRewardsDialog?.contains(pagHost)) {
    return;
  }
  e.preventDefault();
  e.stopPropagation();
  const input = form.querySelector("[data-pagination-jump-input]");
  const max = Number(input?.getAttribute("max") || 1);
  const n = Number(String(input?.value || "").trim());
  if (!Number.isInteger(n) || n < 1 || n > max) {
    return;
  }
  void loadWaifuSummonRewardsHistory(n);
});

const openWaifuSummonGuide = () => {
  if (!waifuSummonGuideModal || !waifuSummonGuideDialog) {
    return;
  }
  dismissWaifuSummonCinematicIfAny();
  closeAll();
  closeMobileMenu();
  closeMobileInlineSearch();
  closeProfilePointsHelp();
  closeWaifuSummonRewards();
  waifuSummonGuideModal.classList.remove("hidden");
  waifuSummonGuideModal.setAttribute("aria-hidden", "false");
  waifuSummonGuideModal.dataset.state = "open";
  waifuSummonGuideTrigger?.setAttribute("aria-expanded", "true");
  waifuSummonGuideTrigger?.setAttribute("data-state", "open");
  waifuSummonGuideOverlay?.setAttribute("data-state", "open");
  waifuSummonGuideDialog.setAttribute("data-state", "open");
  updateWaifuSummonBodyScrollLock();
  try {
    waifuSummonGuideDialog.focus({
      preventScroll: true,
    });
  } catch (_) {}
};

waifuSummonGuideTrigger?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  const isOpen = waifuSummonGuideModal?.dataset.state === "open";
  if (isOpen) {
    closeWaifuSummonGuide();
  } else {
    openWaifuSummonGuide();
  }
});

waifuSummonGuideOverlay?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  closeWaifuSummonGuide();
});

waifuSummonGuideDialog?.addEventListener("click", (e) => e.stopPropagation());

document.querySelectorAll("[data-waifu-summon-guide-close]").forEach((btn) => {
  btn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    closeWaifuSummonGuide();
  });
});

waifuSummonRewardsTrigger?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  const isOpen = waifuSummonRewardsModal?.dataset.state === "open";
  if (isOpen) {
    closeWaifuSummonRewards();
  } else {
    openWaifuSummonRewards();
  }
});

waifuSummonRewardsOverlay?.addEventListener("click", (e) => {
  e.preventDefault();
  e.stopPropagation();
  closeWaifuSummonRewards();
});

waifuSummonRewardsDialog?.addEventListener("click", (e) => {
  e.stopPropagation();
});

document
  .querySelectorAll("[data-waifu-summon-rewards-close]")
  .forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeWaifuSummonRewards();
    });
  });

/** Nhận quà mốc triệu hồi (Dâm Ngọc) — POST JSON (listener trên dialog vì dialog chặn bubble lên modal). */
(() => {
  const pageRoot = document.querySelector("[data-waifu-summon-page]");
  const claimUrl = pageRoot?.dataset?.waifuSummonMilestoneClaimUrl;
  const milestoneCsrf = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") ?? "";
  const milestonePointsEl = document.querySelector(
    "[data-waifu-summon-points]",
  );
  waifuSummonRewardsDialog?.addEventListener("click", async (e) => {
    const btn = e.target?.closest?.("[data-waifu-summon-milestone-claim]");
    if (!btn || !waifuSummonRewardsDialog?.contains(btn) || !claimUrl) {
      return;
    }
    e.preventDefault();
    e.stopPropagation();
    const milestone = Number(btn.getAttribute("data-milestone"));
    if (!Number.isFinite(milestone)) {
      return;
    }
    btn.disabled = true;
    try {
      const res = await fetch(claimUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": milestoneCsrf(),
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
        body: JSON.stringify({ milestone }),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        window.alert(
          json.message || json.errors?.milestone?.[0] || "Không nhận được quà.",
        );
        btn.disabled = false;
        return;
      }
      if (typeof json.points_remaining === "number" && milestonePointsEl) {
        milestonePointsEl.textContent = String(json.points_remaining);
      }
      btn.removeAttribute("data-waifu-summon-milestone-claim");
      const label = btn.querySelector("[data-waifu-summon-milestone-label]");
      if (label) {
        label.textContent = "Đã nhận";
      }
      btn.classList.add("cursor-not-allowed", "opacity-80");
      btn.disabled = true;
    } catch (_) {
      window.alert("Lỗi mạng.");
      btn.disabled = false;
    }
  });
})();

// Triệu hồi: POST → video → lưới N thẻ (x10 = 10 thẻ); không mở thẻ khi video lỗi nếu chưa có phiên hợp lệ
(() => {
  const pageRoot = document.querySelector("[data-waifu-summon-page]");
  const videoLayer = document.querySelector("[data-waifu-summon-video-layer]");
  const cardLayer = document.querySelector("[data-waifu-summon-card-layer]");
  const video = document.querySelector("[data-waifu-summon-video]");
  const cardStage = document.querySelector("[data-waifu-summon-card-stage]");
  const cardsScroll = document.querySelector(
    "[data-waifu-summon-cards-scroll]",
  );
  const cardsGrid = document.querySelector("[data-waifu-summon-cards-grid]");
  const cardTmpl = document.getElementById("waifu-summon-card-tmpl");
  const cardFooter = document.querySelector("[data-waifu-summon-card-footer]");
  const btnDoneCards = document.querySelector("[data-waifu-summon-done-cards]");
  const pointsEl = document.querySelector("[data-waifu-summon-points]");
  const summonBgm = document.querySelector("[data-waifu-summon-bgm]");

  /** Bật → sau triệu hồi vào thẻ luôn, không phát video (lưu localStorage). */
  const SKIP_VIDEO_PREF_KEY = "theme-vinahentai.waifuSummon.skipVideo.v1";

  const getWaifuSkipVideoPref = () => {
    try {
      return window.localStorage.getItem(SKIP_VIDEO_PREF_KEY) === "1";
    } catch (_) {
      return false;
    }
  };

  const setWaifuSkipVideoPref = (on) => {
    try {
      if (on) {
        window.localStorage.setItem(SKIP_VIDEO_PREF_KEY, "1");
      } else {
        window.localStorage.removeItem(SKIP_VIDEO_PREF_KEY);
      }
    } catch (_) {}
  };

  const skipVideoToggle = document.querySelector(
    "[data-waifu-summon-skip-video-toggle]",
  );

  const syncSkipVideoToggleUi = () => {
    if (!skipVideoToggle) {
      return;
    }
    skipVideoToggle.setAttribute(
      "aria-pressed",
      getWaifuSkipVideoPref() ? "true" : "false",
    );
  };

  syncSkipVideoToggleUi();
  skipVideoToggle?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    setWaifuSkipVideoPref(!getWaifuSkipVideoPref());
    syncSkipVideoToggleUi();
  });

  /** Nhạc nền: autoplay HTML bị chặn — phát sau tương tác (pointerdown), gọi lại mỗi lần đến khi play thành công. */
  const tryPlayWaifuSummonBgm = () => {
    if (!summonBgm || !summonBgm.paused) {
      return;
    }
    const vol = Number(summonBgm.dataset.volume ?? "0.4");
    if (Number.isFinite(vol) && vol >= 0 && vol <= 1) {
      summonBgm.volume = vol;
    }
    void summonBgm.play().catch(() => {});
  };

  if (!pageRoot || !videoLayer || !cardLayer || !video) {
    return;
  }

  if (summonBgm) {
    pageRoot.addEventListener("pointerdown", tryPlayWaifuSummonBgm, {
      passive: true,
    });
  }

  const csrf = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") ?? "";

  /**
   * Lật thẻ: mặt thưởng đang dùng ảnh lá bài (placeholder) → gán src = ảnh thưởng API (waifu / EXP) rồi xoay 3D.
   * @param {{ silent?: boolean }} opts silent=true: không bật overlay 5★ (dùng khi « Lật hết » — gọi một lần sau cùng).
   */
  const flipCardInner = async (inner, opts = {}) => {
    const silent = opts.silent === true;
    if (!inner || inner.dataset.flipped === "true") {
      return;
    }
    const reveal = inner.querySelector("[data-waifu-summon-result-img]");
    const rewardUrl = (
      reveal?.getAttribute("data-reward-url") ||
      reveal?.dataset?.rewardUrl ||
      ""
    ).trim();
    if (reveal && rewardUrl) {
      reveal.loading = "eager";
      reveal.src = rewardUrl;
      const altText = reveal.dataset.rewardAlt;
      if (altText) {
        reveal.alt = altText;
      }
      await new Promise((resolve) => {
        if (reveal.complete && reveal.naturalWidth > 0) {
          resolve();
          return;
        }
        reveal.addEventListener("load", () => resolve(), { once: true });
        reveal.addEventListener("error", () => resolve(), { once: true });
      });
      try {
        await reveal.decode();
      } catch (_) {
        /* Ảnh lỗi / trình duyệt không hỗ trợ decode — vẫn lật */
      }
    }
    inner.dataset.flipped = "true";
    inner.style.transform = "rotateY(180deg)";
    const root = inner.closest("[data-waifu-summon-card-root]");
    root?.classList.remove("animate-card-idle");
    if (root?.getAttribute("data-waifu-summon-rarity") === "5") {
      root.classList.add("waifu-summon-card-root--5star-glow");
      const reducedMotion =
        typeof window.matchMedia === "function" &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      const delay = reducedMotion ? 80 : 720;
      if (!silent) {
        window.setTimeout(() => {
          showWaifu5StarCelebration();
        }, delay);
      }
    }
  };

  const buildCardsFromResults = () => {
    if (!cardsGrid || !cardTmpl || !waifuSummonResults?.length) {
      return;
    }
    cardsGrid.replaceChildren();
    const n = waifuSummonResults.length;

    const cloneCard = (item, i) => {
      const frag = cardTmpl.content.cloneNode(true);
      const root = frag.querySelector("[data-waifu-summon-card-root]");
      const img = frag.querySelector("[data-waifu-summon-result-img]");
      if (root && item.rarity != null && item.rarity !== "") {
        root.setAttribute("data-waifu-summon-rarity", String(item.rarity));
      }
      if (img) {
        const rawUrl = item?.image_url ?? item?.imageUrl;
        const rewardUrl =
          typeof rawUrl === "string"
            ? rawUrl.trim()
            : String(rawUrl ?? "").trim();
        img.setAttribute("data-reward-url", rewardUrl);
        img.loading = "eager";
        if (rewardUrl) {
          const pre = new Image();
          pre.src = rewardUrl;
        }
        const name = item.name ? String(item.name) : "";
        const stars = item.rarity != null ? `${item.rarity}★` : "";
        img.dataset.rewardAlt = name
          ? `Kết quả ${i + 1}: ${name}${stars ? ` (${stars})` : ""}`
          : `Kết quả triệu hồi ${i + 1}`;
        img.alt = "";
      }
      return root;
    };

    if (n === 1) {
      if (cardsScroll) {
        cardsScroll.className =
          "flex w-full min-w-0 max-w-full justify-center overflow-x-hidden overflow-y-visible py-2 px-2 sm:max-w-7xl sm:px-2";
      }
      cardsGrid.className =
        "flex min-h-0 min-w-0 flex-row flex-nowrap items-center justify-center gap-x-2";
      const col = document.createElement("div");
      col.className = "relative z-10 flex flex-col items-center gap-4 sm:gap-5";
      const node = cloneCard(waifuSummonResults[0], 0);
      if (node) {
        col.appendChild(node);
      }
      cardsGrid.appendChild(col);
      return;
    }

    /* Nhiều thẻ: khối trong w-max để wrapper flex justify-center giữ căn giữa khi vừa màn; overflow cuộn trên wrapper. */
    if (cardsScroll) {
      cardsScroll.className =
        "flex w-full min-w-0 max-w-full justify-center overflow-x-auto overflow-y-visible overscroll-x-contain py-2 pl-2 pr-5 sm:max-w-7xl sm:px-2 sm:pr-5";
    }
    cardsGrid.className =
      "flex w-max max-w-none min-h-0 flex-row flex-nowrap items-center justify-center gap-x-2 sm:gap-x-3 md:gap-x-4";

    const pairs = [];
    for (let j = 0; j < n; j += 2) {
      pairs.push(waifuSummonResults.slice(j, j + 2));
    }

    pairs.forEach((pair, colIdx) => {
      const col = document.createElement("div");
      col.className =
        "relative z-10 flex min-h-0 shrink-0 flex-col items-center gap-4 sm:gap-5 sm:mr-2 last:mr-0 sm:last:mr-0";
      pair.forEach((item, rowIdx) => {
        const globalIndex = colIdx * 2 + rowIdx;
        const node = cloneCard(item, globalIndex);
        if (node) {
          col.appendChild(node);
        }
      });
      cardsGrid.appendChild(col);
    });
  };

  const showCardPhase = () => {
    if (!waifuSummonSessionActive || !waifuSummonResults?.length) {
      return;
    }
    videoLayer.classList.add("hidden");
    videoLayer.setAttribute("aria-hidden", "true");
    cardLayer.classList.remove("hidden");
    cardLayer.setAttribute("aria-hidden", "false");
    buildCardsFromResults();
    cardFooter?.classList.remove("hidden");
    try {
      video.pause();
    } catch (_) {}
    updateWaifuSummonBodyScrollLock();
  };

  const showVideoPhase = () => {
    dismissWaifuSummonCinematicIfAny(false);
    cardLayer.classList.add("hidden");
    cardLayer.setAttribute("aria-hidden", "true");
    videoLayer.classList.remove("hidden");
    videoLayer.setAttribute("aria-hidden", "false");
    cardFooter?.classList.add("hidden");
    try {
      video.currentTime = 0;
    } catch (_) {}
    void video.play().catch(() => {
      if (waifuSummonSessionActive) {
        showCardPhase();
      }
    });
    updateWaifuSummonBodyScrollLock();
  };

  waifuSummonOnEscapeVideo = () => {
    if (waifuSummonSessionActive) {
      showCardPhase();
    } else {
      dismissWaifuSummonCinematicIfAny();
    }
  };

  cardStage?.addEventListener("click", (e) => {
    e.stopPropagation();
    const root = e.target?.closest?.("[data-waifu-summon-card-root]");
    if (root && cardStage.contains(root)) {
      e.preventDefault();
      const inner = root.querySelector("[data-waifu-summon-card-inner]");
      void flipCardInner(inner);
    }
  });

  document.querySelectorAll("[data-waifu-summon-start]").forEach((btn) => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeWaifuSummonGuide();
      closeWaifuSummonRewards();
      const performUrl = pageRoot.dataset.waifuSummonPerformUrl;
      const loginUrl = pageRoot.dataset.waifuSummonLoginUrl || "/login";
      if (!performUrl) {
        window.location.href = loginUrl;
        return;
      }
      const type =
        btn.getAttribute("data-waifu-summon-type") === "ten" ? "ten" : "single";
      try {
        btn.disabled = true;
        const res = await fetch(performUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrf(),
            "X-Requested-With": "XMLHttpRequest",
          },
          credentials: "same-origin",
          body: JSON.stringify({ type }),
        });
        const json = await res.json().catch(() => ({}));
        if (res.status === 401 || res.status === 419) {
          window.location.href = loginUrl;
          return;
        }
        if (!res.ok) {
          const msg =
            json.message ||
            json.errors?.points?.[0] ||
            json.errors?.waifu?.[0] ||
            "Không triệu hồi được.";
          window.alert(msg);
          return;
        }
        if (!Array.isArray(json.results) || json.results.length === 0) {
          window.alert("Phản hồi không hợp lệ.");
          return;
        }
        waifuSummonResults = json.results;
        waifuSummonSessionActive = true;
        if (typeof json.points_remaining === "number" && pointsEl) {
          pointsEl.textContent = String(json.points_remaining);
        }
        tryPlayWaifuSummonBgm();
        if (getWaifuSkipVideoPref()) {
          showCardPhase();
        } else {
          showVideoPhase();
        }
      } catch (_) {
        window.alert("Lỗi mạng.");
      } finally {
        btn.disabled = false;
      }
    });
  });

  document
    .querySelector("[data-waifu-summon-video-skip]")
    ?.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (waifuSummonSessionActive) {
        showCardPhase();
      }
    });

  video.addEventListener("ended", () => {
    if (waifuSummonSessionActive) {
      showCardPhase();
    }
  });

  video.addEventListener("error", () => {
    if (waifuSummonSessionActive) {
      showCardPhase();
    }
  });

  document
    .querySelector("[data-waifu-summon-flip-all]")
    ?.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const inners = cardLayer.querySelectorAll(
        "[data-waifu-summon-card-inner]",
      );
      void (async () => {
        await Promise.all(
          [...inners].map((inner) => flipCardInner(inner, { silent: true })),
        );
        const has5 = [...inners].some(
          (inner) =>
            inner
              .closest("[data-waifu-summon-card-root]")
              ?.getAttribute("data-waifu-summon-rarity") === "5",
        );
        if (has5) {
          const reducedMotion =
            typeof window.matchMedia === "function" &&
            window.matchMedia("(prefers-reduced-motion: reduce)").matches;
          window.setTimeout(
            () => {
              showWaifu5StarCelebration();
            },
            reducedMotion ? 120 : 820,
          );
        }
      })();
    });

  document
    .querySelector("[data-waifu-summon-card-backdrop]")
    ?.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      dismissWaifuSummonCinematicIfAny();
    });

  btnDoneCards?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dismissWaifuSummonCinematicIfAny();
  });
})();

document.addEventListener("click", () => {
  closeAll();
  closeMobileMenu();
  if (pointsHelpDialog && pointsHelpDialog.dataset.state === "open") {
    closeProfilePointsHelp();
  }
  if (waifuSummonGuideModal && waifuSummonGuideModal.dataset.state === "open") {
    closeWaifuSummonGuide();
  }
  if (
    waifuSummonRewardsModal &&
    waifuSummonRewardsModal.dataset.state === "open"
  ) {
    closeWaifuSummonRewards();
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key !== "Escape") {
    return;
  }
  if (mobileSearchPanel && !mobileSearchPanel.classList.contains("hidden")) {
    closeMobileInlineSearch();
    e.preventDefault();
    return;
  }
  if (mobileMenuPopover && !mobileMenuPopover.classList.contains("hidden")) {
    closeMobileMenu();
    e.preventDefault();
    return;
  }
  if (pointsHelpDialog && pointsHelpDialog.dataset.state === "open") {
    closeProfilePointsHelp();
    e.preventDefault();
    return;
  }
  const waifuVid = document.querySelector("[data-waifu-summon-video-layer]");
  const waifuCard = document.querySelector("[data-waifu-summon-card-layer]");
  if (waifuVid && !waifuVid.classList.contains("hidden")) {
    waifuSummonOnEscapeVideo();
    e.preventDefault();
    return;
  }
  if (waifuCard && !waifuCard.classList.contains("hidden")) {
    dismissWaifuSummonCinematicIfAny();
    e.preventDefault();
    return;
  }
  if (
    waifuSummonRewardsModal &&
    waifuSummonRewardsModal.dataset.state === "open"
  ) {
    closeWaifuSummonRewards();
    e.preventDefault();
    return;
  }
  if (waifuSummonGuideModal && waifuSummonGuideModal.dataset.state === "open") {
    closeWaifuSummonGuide();
    e.preventDefault();
    return;
  }
  closeAll();
});

window.addEventListener("resize", () => {
  const open = document.querySelector(
    '[data-nav-dropdown-panel][data-state="open"]',
  );
  if (!open) {
    return;
  }
  const id = open.getAttribute("aria-labelledby");
  const trigger = id ? document.getElementById(id) : null;
  if (trigger) {
    void positionPanel(trigger, open);
  }
});

// Enter trên ô tìm truyện → redirect route search với ?q=
const headerForSearch = document.querySelector("header[data-site-search-url]");
const siteSearchBaseHref = headerForSearch?.dataset.siteSearchUrl || "/search";
const siteSearchQueryParam = headerForSearch?.dataset.siteSearchParam || "q";

const goToSiteSearchFromInput = (input) => {
  if (!input) {
    return;
  }
  const raw = input.value.trim();
  try {
    const u = new URL(siteSearchBaseHref, window.location.origin);
    if (raw) {
      u.searchParams.set(siteSearchQueryParam, raw);
    } else {
      u.searchParams.delete(siteSearchQueryParam);
    }
    window.location.assign(u.pathname + u.search + u.hash);
  } catch (_) {
    window.location.href = raw
      ? `${siteSearchBaseHref}?${encodeURIComponent(siteSearchQueryParam)}=${encodeURIComponent(raw)}`
      : siteSearchBaseHref;
  }
};

document.querySelectorAll("[data-site-search-input]").forEach((input) => {
  input.addEventListener("keydown", (e) => {
    if (e.key !== "Enter") {
      return;
    }
    e.preventDefault();
    goToSiteSearchFromInput(input);
  });
});

// Lọc thể loại trong mega menu (nhiều từ khóa, không phân biệt dấu đơn giản: chỉ lowercase)
document.querySelectorAll("[data-genre-mega-search]").forEach((input) => {
  input.addEventListener("input", () => {
    const root = input.closest("[data-nav-dropdown-panel]");
    if (!root) {
      return;
    }
    const q = input.value.trim().toLowerCase();
    const tokens = q.split(/\s+/).filter(Boolean);
    root.querySelectorAll("[data-genre-letter-group]").forEach((group) => {
      let visibleInGroup = false;
      group.querySelectorAll("[data-genre-search-text]").forEach((link) => {
        const text = link.getAttribute("data-genre-search-text") || "";
        const match =
          tokens.length === 0 || tokens.every((t) => text.includes(t));
        link.classList.toggle("hidden", !match);
        if (match) {
          visibleInGroup = true;
        }
      });
      group.classList.toggle("hidden", !visibleInGroup);
    });
  });
});

// Dropdown sắp xếp (tax + tìm kiếm nâng cao): portal fixed + đóng khi click ngoài / Escape.
document.querySelectorAll("[data-tax-sort-dropdown-root]").forEach((root) => {
  const trigger = root.querySelector("[data-tax-sort-dropdown-trigger]");
  const portalWrapper = root.querySelector("[data-tax-sort-dropdown-portal]");
  const listbox = portalWrapper?.querySelector("[data-tax-sort-dropdown-list]");
  const chevron = root.querySelector("[data-tax-sort-dropdown-chevron]");

  if (!trigger || !portalWrapper || !listbox) {
    return;
  }

  let isOpen = false;
  let restoreParent = null;
  let restoreNextSibling = null;

  const portalToBody = () => {
    if (portalWrapper.parentNode === document.body) {
      return;
    }
    restoreParent = portalWrapper.parentNode;
    restoreNextSibling = portalWrapper.nextSibling;
    document.body.appendChild(portalWrapper);
  };

  const restorePortal = () => {
    if (!restoreParent || portalWrapper.parentNode !== document.body) {
      return;
    }
    if (restoreNextSibling && restoreNextSibling.parentNode === restoreParent) {
      restoreParent.insertBefore(portalWrapper, restoreNextSibling);
    } else {
      restoreParent.appendChild(portalWrapper);
    }
    restoreParent = null;
    restoreNextSibling = null;
  };

  const close = () => {
    if (!isOpen) {
      return;
    }
    isOpen = false;
    portalWrapper.classList.add("hidden");
    trigger.setAttribute("aria-expanded", "false");
    chevron?.classList.remove("rotate-180");
    restorePortal();
  };

  const open = () => {
    if (isOpen) {
      return;
    }
    isOpen = true;

    const rect = trigger.getBoundingClientRect();
    const viewportWidth =
      window.innerWidth || document.documentElement.clientWidth || 0;
    const viewportHeight =
      window.innerHeight || document.documentElement.clientHeight || 0;
    const gap = 8;

    const desiredWidth = rect.width || 256;
    const width = Math.min(460, Math.max(220, desiredWidth));

    let left = rect.left;
    if (left + width > viewportWidth - 8) {
      left = Math.max(8, viewportWidth - width - 8);
    } else {
      left = Math.max(8, left);
    }

    portalToBody();
    portalWrapper.classList.remove("hidden");
    listbox.style.width = `${width}px`;
    listbox.style.left = `${left}px`;

    const spaceAbove = rect.top - gap;
    const spaceBelow = viewportHeight - rect.bottom - gap;
    const openUp = spaceAbove > spaceBelow;

    if (openUp) {
      listbox.style.top = `${Math.max(gap, rect.top - gap)}px`;
      listbox.style.transform = "translateY(-100%)";
      listbox.style.maxHeight = `${Math.max(140, Math.min(320, spaceAbove - gap))}px`;
    } else {
      listbox.style.top = `${rect.bottom + gap}px`;
      listbox.style.transform = "none";
      listbox.style.maxHeight = `${Math.max(140, Math.min(320, spaceBelow - gap))}px`;
    }

    trigger.setAttribute("aria-expanded", "true");
    chevron?.classList.add("rotate-180");
  };

  trigger.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isOpen) {
      close();
    } else {
      open();
    }
  });

  listbox.addEventListener("click", (e) => {
    const btn = e.target?.closest?.("[data-tax-sort-option]");
    if (!btn) {
      return;
    }
    const href = btn.getAttribute("data-tax-sort-href");
    if (href) {
      window.location.assign(href);
    }
  });

  document.addEventListener("click", (e) => {
    if (!isOpen) {
      return;
    }
    if (root.contains(e.target) || portalWrapper.contains(e.target)) {
      return;
    }
    close();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && isOpen) {
      close();
    }
  });
});

// Trang profile: tab cấp 1 + tab "Theo dõi" (con) — mỗi tab con gọi API một lần (cache).
(() => {
  const root = document.querySelector("[data-profile-tabs-root]");
  if (!root) {
    return;
  }

  const libRoot = root.querySelector("[data-profile-library]");
  const commentsRoot = root.querySelector("[data-profile-comments-root]");
  const waifuRoot = root.querySelector("[data-profile-waifu-root]");

  const subFromHash = () => {
    const h = (window.location.hash || "").replace(/^#/, "");
    const subMap = {
      "saved-stories": "following",
      "reading-history": "recent-read",
      "saved-translators": "translators",
      "saved-authors": "authors",
    };
    return subMap[h] || null;
  };

  let libraryListenersBound = false;

  const bindLibraryTabClicks = () => {
    if (!libRoot || libraryListenersBound) {
      return;
    }
    libraryListenersBound = true;
    libRoot.querySelectorAll("[data-profile-subtab]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const k = btn.getAttribute("data-profile-subtab");
        if (k) {
          showSubtab(k);
        }
      });
    });
  };

  /** Tab dịch giả / tác giả (API rỗng) chỉ tải một lần */
  /** @type {Set<string>} */
  const loadedStaticSubtabs = new Set();

  const esc = (s) =>
    String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");

  const attrHref = (u) => String(u || "#").replace(/"/g, "%22");

  const formatViews = (n) => {
    const x = Number(n) || 0;
    if (x >= 1_000_000) {
      return `${(x / 1_000_000).toFixed(1)}M`;
    }
    if (x >= 1000) {
      return `${(x / 1000).toFixed(1)}K`;
    }
    return String(x);
  };

  const renderMangaGrid = (items) => {
    if (!items.length) {
      return `<div class="py-8 text-center w-full"><p class="text-txt-secondary text-sm font-medium">Chưa có truyện đang theo dõi.</p></div>`;
    }
    const cards = items
      .map(
        (m) => `
      <a class="bg-bgc-layer1 border-bd-default group flex h-full flex-col overflow-hidden rounded-2xl border text-left transition hover:border-lav-500 lg:flex-row" href="${attrHref(m.url)}">
        <div class="aspect-[3/4] w-full overflow-hidden bg-bgc-layer2 lg:max-w-[180px]">
          <img class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" alt="${esc(m.title)}" loading="lazy" src="${esc(m.cover_image || "")}" />
        </div>
        <div class="flex flex-1 flex-col gap-3 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex flex-col gap-1">
              <h3 class="text-txt-primary line-clamp-2 text-base font-semibold">${esc(m.title)}</h3>
              <span class="text-xs font-semibold uppercase tracking-wide text-txt-focus">Chapter ${esc(m.latest_chapter_label)}</span>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-txt-secondary">
            <div class="flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-3.5 w-3.5" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>
              <span>${formatViews(m.total_views)}</span>
            </div>
          </div>
        </div>
      </a>`,
      )
      .join("");
    return `<div class="grid w-full grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-1">${cards}</div>`;
  };

  const renderHistoryGrid = (items) => {
    if (!items.length) {
      return `<div class="py-8 text-center w-full"><p class="text-txt-secondary text-sm font-medium">Chưa có lịch sử đọc.</p></div>`;
    }
    const cards = items
      .map((row) => {
        const m = row.manga;
        const c = row.chapter;
        if (!m) {
          return "";
        }
        const chapLabel = c?.number_label ?? "—";
        const chapUrl = c?.url || m.url;
        const curPage = Math.max(0, Number(row.last_read_page) || 0);
        const totalPages = Math.max(0, Number(c?.pages_count) || 0);
        const pageProgress =
          totalPages > 0 ? `${curPage}/${totalPages}` : `${curPage}/—`;
        return `
      <a class="bg-bgc-layer1 border-bd-default group flex h-full flex-col overflow-hidden rounded-2xl border text-left transition hover:border-lav-500 lg:flex-row" href="${attrHref(chapUrl)}">
        <div class="aspect-[3/4] w-full overflow-hidden bg-bgc-layer2 lg:max-w-[180px]">
          <img class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" alt="${esc(m.title)}" loading="lazy" src="${esc(m.cover_image || "")}" />
        </div>
        <div class="flex flex-1 flex-col gap-3 p-4">
          <div class="flex flex-col gap-1">
            <h3 class="text-txt-primary line-clamp-2 text-base font-semibold">${esc(m.title)}</h3>
            <span class="text-xs font-semibold uppercase tracking-wide text-txt-focus">Chapter ${esc(chapLabel)}</span>
          </div>
          <div class="grid grid-cols-2 gap-3 text-xs font-semibold text-txt-secondary sm:flex sm:flex-wrap sm:gap-4">
            <div class="flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
              <span>${formatViews(m.total_views)}</span>
            </div>
            <div class="flex items-center gap-1"><span class="tabular-nums">${esc(pageProgress)}</span></div>
          </div>
        </div>
      </a>`;
      })
      .filter(Boolean)
      .join("");
    return `<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-1">${cards}</div>`;
  };

  const renderEmptyMsg = (msg) =>
    `<div class="py-8 text-center w-full"><p class="text-txt-secondary text-sm font-medium">${esc(msg)}</p></div>`;

  const renderPager = (meta, subKey) => {
    if (!meta || meta.last_page <= 1) {
      return "";
    }
    const cp = Number(meta.current_page) || 1;
    const lp = Number(meta.last_page) || 1;
    return `<nav class="mt-4 flex flex-wrap items-center justify-center gap-3 sm:justify-between" aria-label="Phân trang">
      <button type="button" class="border-bd-default text-txt-secondary hover:border-lav-500 hover:text-txt-primary cursor-pointer rounded-xl border px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40" data-profile-lib-prev data-lib-sub="${esc(subKey)}" ${cp <= 1 ? "disabled" : ""}>Trước</button>
      <span class="text-txt-secondary text-sm font-medium tabular-nums">Trang ${cp} / ${lp}</span>
      <button type="button" class="border-bd-default text-txt-secondary hover:border-lav-500 hover:text-txt-primary cursor-pointer rounded-xl border px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40" data-profile-lib-next data-lib-sub="${esc(subKey)}" ${cp >= lp ? "disabled" : ""}>Sau</button>
    </nav>`;
  };

  /** Phân trang tab bình luận profile */
  const renderCommentsPager = (meta) => {
    if (!meta || meta.last_page <= 1) {
      return "";
    }
    const cp = Number(meta.current_page) || 1;
    const lp = Number(meta.last_page) || 1;
    return `<nav class="mt-4 flex w-full flex-wrap items-center justify-center gap-3 sm:justify-between" aria-label="Phân trang bình luận">
      <button type="button" class="border-bd-default text-txt-secondary hover:border-lav-500 hover:text-txt-primary cursor-pointer rounded-xl border px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40" data-profile-comments-prev ${cp <= 1 ? "disabled" : ""}>Trước</button>
      <span class="text-txt-secondary text-sm font-medium tabular-nums">Trang ${cp} / ${lp}</span>
      <button type="button" class="border-bd-default text-txt-secondary hover:border-lav-500 hover:text-txt-primary cursor-pointer rounded-xl border px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-40" data-profile-comments-next ${cp >= lp ? "disabled" : ""}>Sau</button>
    </nav>`;
  };

  const renderMyCommentsInner = (items, meta) => {
    const total = Number(meta?.total) || 0;
    const totalLine = `<div class="text-txt-secondary h-6 justify-center self-stretch font-sans text-sm leading-tight font-medium">Tổng cộng: ${total}</div>`;
    if (!items.length) {
      return `${totalLine}<div class="flex w-full flex-col items-center justify-center gap-4 self-stretch"><p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Chưa có bình luận nào.</p></div>${renderCommentsPager(meta)}`;
    }
    const rows = items
      .map((item) => {
        const m = item.manga || {};
        const title = m.title || "";
        const quote =
          item.content_preview != null &&
          String(item.content_preview).length > 0
            ? `\u201c${esc(item.content_preview)}\u201d`
            : "";
        const posted = esc(item.posted_label || "");
        return `<a class="bg-bgc-layer1 border-bd-default inline-flex items-start justify-between self-stretch rounded-xl border p-3" href="${attrHref(m.url)}">
      <div class="flex flex-1 items-start justify-start gap-3 sm:w-[522px]">
        <img class="h-8 w-8 flex-shrink-0 rounded object-cover" alt="${esc(title)} cover" loading="lazy" src="${esc(m.cover_image || "")}" />
        <div class="inline-flex min-w-0 flex-1 flex-col items-start justify-start gap-0.5">
          <div class="text-txt-primary line-clamp-1 justify-center self-stretch font-sans text-sm leading-tight font-medium">${esc(title)}</div>
          <div class="text-txt-secondary line-clamp-3 w-full justify-center font-sans text-xs leading-none font-medium sm:max-w-[484px]">${quote}</div>
        </div>
      </div>
      <div class="text-txt-secondary ml-3 flex-shrink-0 justify-center font-sans text-xs leading-normal font-medium sm:text-base">${posted}</div>
    </a>`;
      })
      .join("");
    return `${totalLine}<div class="flex flex-col items-center justify-center gap-4 self-stretch"><div class="flex w-full flex-col items-start justify-start gap-2">${rows}</div></div>${renderCommentsPager(meta)}`;
  };

  async function loadMyCommentsPage(page) {
    if (!commentsRoot) {
      return;
    }
    const pane = commentsRoot.querySelector("[data-profile-comments-pane]");
    const baseUrl = commentsRoot.getAttribute("data-url-my-comments");
    if (!pane || !baseUrl) {
      return;
    }
    const u = new URL(baseUrl, window.location.origin);
    u.searchParams.set("page", String(Math.max(1, page)));
    u.searchParams.set("per_page", "10");
    pane.innerHTML = `<p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>`;
    try {
      const res = await fetch(u.toString(), { credentials: "same-origin" });
      if (!res.ok) {
        throw new Error(String(res.status));
      }
      const json = await res.json();
      const meta = json.meta || null;
      pane.innerHTML = renderMyCommentsInner(json.data || [], meta);
      if (meta) {
        pane.dataset.commentsPage = String(meta.current_page ?? page);
        pane.dataset.commentsLastPage = String(meta.last_page ?? 1);
      }
    } catch (_) {
      pane.innerHTML = `<p class="text-error-error w-full py-6 text-center text-sm font-medium">Không tải được dữ liệu.</p>`;
    }
  }

  async function loadLibraryPage(subKey, page) {
    if (!libRoot) {
      return;
    }
    const pane = libRoot.querySelector(
      `[data-profile-library-pane="${subKey}"]`,
    );
    if (!pane) {
      return;
    }

    const urlMap = {
      following: libRoot.getAttribute("data-url-followed"),
      "recent-read": libRoot.getAttribute("data-url-history"),
    };
    const baseUrl = urlMap[subKey];
    if (!baseUrl) {
      return;
    }

    const u = new URL(baseUrl, window.location.origin);
    u.searchParams.set("page", String(Math.max(1, page)));
    u.searchParams.set("per_page", "10");

    pane.innerHTML = `<p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>`;
    try {
      const res = await fetch(u.toString(), { credentials: "same-origin" });
      if (!res.ok) {
        throw new Error(String(res.status));
      }
      const json = await res.json();
      const meta = json.meta || null;
      let listHtml = "";
      if (subKey === "following") {
        listHtml = renderMangaGrid(json.data || []);
      } else if (subKey === "recent-read") {
        listHtml = renderHistoryGrid(json.data || []);
      }
      pane.innerHTML = `${listHtml}${renderPager(meta, subKey)}`;
      if (meta) {
        pane.dataset.libPage = String(meta.current_page ?? page);
        pane.dataset.libLastPage = String(meta.last_page ?? 1);
      }
    } catch (_) {
      pane.innerHTML = `<p class="text-error-error w-full py-6 text-center text-sm font-medium">Không tải được dữ liệu.</p>`;
    }
  }

  if (commentsRoot) {
    commentsRoot.addEventListener("click", (e) => {
      const prev = e.target?.closest?.("[data-profile-comments-prev]");
      const next = e.target?.closest?.("[data-profile-comments-next]");
      const btn = prev || next;
      if (!btn || btn.disabled) {
        return;
      }
      const pane = commentsRoot.querySelector("[data-profile-comments-pane]");
      let p = parseInt(pane?.dataset.commentsPage || "1", 10);
      const last = parseInt(pane?.dataset.commentsLastPage || "1", 10);
      if (!Number.isFinite(p) || p < 1) {
        p = 1;
      }
      if (prev && p > 1) {
        e.preventDefault();
        void loadMyCommentsPage(p - 1);
      }
      if (next && p < last) {
        e.preventDefault();
        void loadMyCommentsPage(p + 1);
      }
    });
  }

  const csrfToken = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") ?? "";

  /** Cập nhật ô đồng hành + vùng điểm danh sau khi chọn waifu */
  const updateCompanionUi = (companion) => {
    if (!waifuRoot) {
      return;
    }
    const slot = waifuRoot.querySelector("[data-profile-companion-slot]");
    const blur = waifuRoot.querySelector("[data-profile-waifu-daily-blur]");
    const hint = waifuRoot.querySelector("[data-profile-waifu-daily-hint]");
    if (slot) {
      if (companion && companion.image) {
        slot.innerHTML = `<img class="h-full w-full object-cover" alt="${esc(companion.name)}" loading="lazy" src="${esc(companion.image)}" />`;
      } else if (companion && companion.name) {
        slot.innerHTML = `<span class="text-txt-tertiary px-1 text-center text-sm font-semibold">${esc(companion.name)}</span>`;
      } else {
        slot.innerHTML = `<span class="text-txt-tertiary text-lg font-semibold uppercase">Trống</span>`;
      }
    }
    const has = Boolean(companion && (companion.image || companion.name));
    if (blur) {
      if (has) {
        blur.classList.remove("blur-md");
      } else {
        blur.classList.add("blur-md");
      }
    }
    if (hint) {
      hint.textContent = has
        ? "Tính năng điểm danh sẽ bật trong bản cập nhật tới."
        : "Vui lòng chọn Waifu để nhận thưởng hàng ngày.";
    }
  };

  if (waifuRoot) {
    waifuRoot.addEventListener("click", async (e) => {
      const pick = e.target?.closest?.("[data-profile-waifu-pick]");
      if (pick) {
        const id = parseInt(
          pick.getAttribute("data-profile-waifu-pick") || "0",
          10,
        );
        if (!Number.isFinite(id) || id <= 0) {
          return;
        }
        const url = waifuRoot.getAttribute("data-url-companion");
        if (!url) {
          return;
        }
        try {
          const res = await fetch(url, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              "X-CSRF-TOKEN": csrfToken(),
              "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
            body: JSON.stringify({ waifu_id: id }),
          });
          const json = await res.json().catch(() => ({}));
          if (!res.ok) {
            const msg = json.message || "Không lưu được.";
            window.alert(msg);
            return;
          }
          if (json.companion) {
            updateCompanionUi(json.companion);
          }
        } catch (_) {
          window.alert("Lỗi mạng.");
        }
        return;
      }
      const tierBtn = e.target?.closest?.("[data-profile-waifu-tier-toggle]");
      if (!tierBtn) {
        return;
      }
      const key = tierBtn.getAttribute("data-profile-waifu-tier-toggle");
      const body = key
        ? waifuRoot.querySelector(`[data-profile-waifu-tier-body="${key}"]`)
        : null;
      const chevron = tierBtn.querySelector(
        "[data-profile-waifu-tier-chevron]",
      );
      if (!body) {
        return;
      }
      const expanded = tierBtn.getAttribute("aria-expanded") === "true";
      if (expanded) {
        body.classList.add("hidden");
        tierBtn.setAttribute("aria-expanded", "false");
        chevron?.classList.remove("rotate-180");
      } else {
        body.classList.remove("hidden");
        tierBtn.setAttribute("aria-expanded", "true");
        chevron?.classList.add("rotate-180");
      }
    });
  }

  if (libRoot) {
    libRoot.addEventListener("click", (e) => {
      const prev = e.target?.closest?.("[data-profile-lib-prev]");
      const next = e.target?.closest?.("[data-profile-lib-next]");
      const btn = prev || next;
      if (!btn || btn.disabled) {
        return;
      }
      const subKey = btn.getAttribute("data-lib-sub");
      if (!subKey) {
        return;
      }
      const pane = libRoot.querySelector(
        `[data-profile-library-pane="${subKey}"]`,
      );
      let p = parseInt(pane?.dataset.libPage || "1", 10);
      const last = parseInt(pane?.dataset.libLastPage || "1", 10);
      if (!Number.isFinite(p) || p < 1) {
        p = 1;
      }
      if (prev && p > 1) {
        e.preventDefault();
        void loadLibraryPage(subKey, p - 1);
      }
      if (next && p < last) {
        e.preventDefault();
        void loadLibraryPage(subKey, p + 1);
      }
    });
  }

  async function loadSubtab(key) {
    if (!libRoot) {
      return;
    }
    const pane = libRoot.querySelector(`[data-profile-library-pane="${key}"]`);
    if (!pane) {
      return;
    }

    if (key === "following" || key === "recent-read") {
      await loadLibraryPage(key, 1);
      return;
    }

    if (loadedStaticSubtabs.has(key)) {
      return;
    }

    const urlMap = {
      translators: libRoot.getAttribute("data-url-translators"),
      authors: libRoot.getAttribute("data-url-authors"),
    };
    const url = urlMap[key];
    if (!url) {
      return;
    }

    pane.innerHTML = `<p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>`;
    try {
      const res = await fetch(url, { credentials: "same-origin" });
      if (!res.ok) {
        throw new Error(String(res.status));
      }
      const json = await res.json();
      if (key === "translators") {
        const list = json.data || [];
        pane.innerHTML =
          list.length === 0
            ? renderEmptyMsg("Bạn chưa theo dõi nhóm dịch nào.")
            : renderEmptyMsg(
                "Danh sách dịch giả sẽ hiển thị khi tính năng hoàn tất.",
              );
      } else {
        const list = json.data || [];
        pane.innerHTML =
          list.length === 0
            ? renderEmptyMsg("Bạn chưa theo dõi tác giả nào.")
            : renderEmptyMsg(
                "Danh sách tác giả sẽ hiển thị khi tính năng hoàn tất.",
              );
      }
      loadedStaticSubtabs.add(key);
    } catch (_) {
      pane.innerHTML = `<p class="text-error-error w-full py-6 text-center text-sm font-medium">Không tải được dữ liệu.</p>`;
    }
  }

  function showSubtab(key) {
    if (!libRoot) {
      return;
    }
    libRoot.querySelectorAll("[data-profile-subtab]").forEach((btn) => {
      const on = btn.getAttribute("data-profile-subtab") === key;
      btn.setAttribute("aria-selected", on ? "true" : "false");
      btn.dataset.state = on ? "active" : "inactive";
    });
    libRoot.querySelectorAll("[data-profile-subpanel]").forEach((panel) => {
      const on = panel.getAttribute("data-profile-subpanel") === key;
      panel.dataset.state = on ? "active" : "inactive";
      if (on) {
        panel.removeAttribute("hidden");
      } else {
        panel.setAttribute("hidden", "");
      }
    });
    void loadSubtab(key);
  }

  function showPrimary(name) {
    root.querySelectorAll("[data-profile-primary-tab]").forEach((btn) => {
      const on = btn.getAttribute("data-profile-primary-tab") === name;
      btn.setAttribute("aria-selected", on ? "true" : "false");
      btn.dataset.state = on ? "active" : "inactive";
    });
    root.querySelectorAll("[data-profile-primary-panel]").forEach((panel) => {
      const on = panel.getAttribute("data-profile-primary-panel") === name;
      panel.dataset.state = on ? "active" : "inactive";
      if (on) {
        panel.removeAttribute("hidden");
      } else {
        panel.setAttribute("hidden", "");
      }
    });

    if (name === "comments") {
      void loadMyCommentsPage(1);
    } else if (name === "stories") {
      bindLibraryTabClicks();
      const sub = subFromHash() || "following";
      showSubtab(sub);
    }
  }

  root.querySelectorAll("[data-profile-primary-tab]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const name = btn.getAttribute("data-profile-primary-tab");
      if (name) {
        showPrimary(name);
      }
    });
  });

  const applyHash = () => {
    const h = (window.location.hash || "").replace(/^#/, "");
    const subMap = {
      "saved-stories": "following",
      "reading-history": "recent-read",
      "saved-translators": "translators",
      "saved-authors": "authors",
    };
    if (subMap[h]) {
      showPrimary("stories");
    }
  };

  window.addEventListener("hashchange", applyHash);
  applyHash();
})();

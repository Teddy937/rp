<template>
  <div>
    <div class="startbar d-print-none" id="startbar">
      <div class="brand">
        <LogoBox />
      </div>
      <div class="startbar-menu">
        <simplebar class="startbar-collapse" id="startbarCollapse" data-simplebar>
          <div class="d-flex align-items-start flex-column w-100">
            <SidebarSearch />
            <AppMenu :menu-items="getMenuItems()" />
          </div>
        </simplebar>
      </div>
    </div>
    <div class="startbar-overlay d-print-none"></div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import simplebar from "simplebar-vue";
import { getMenuItems } from "@/helpers/menu";
import LogoBox from "@/components/LogoBox.vue";
import SidebarSearch from "@/components/SidebarSearch/SidebarSearch.vue";

const SIDEBAR_SCROLL_STORAGE_KEY = "portal:startbar:scroll-top";
const SIDEBAR_SCROLL_WRAPPER_SELECTOR =
  "#startbarCollapse .simplebar-content-wrapper";

let scrollContainer: HTMLElement | null = null;
let bindRetryTimer: ReturnType<typeof setTimeout> | null = null;
const route = useRoute();

const clearBindRetryTimer = () => {
  if (!bindRetryTimer) {
    return;
  }

  clearTimeout(bindRetryTimer);
  bindRetryTimer = null;
};

const persistScrollPosition = () => {
  if (!scrollContainer) {
    return;
  }

  sessionStorage.setItem(
    SIDEBAR_SCROLL_STORAGE_KEY,
    String(scrollContainer.scrollTop),
  );
};

const clearScrollListener = () => {
  if (!scrollContainer) {
    return;
  }

  scrollContainer.removeEventListener("scroll", persistScrollPosition);
  scrollContainer = null;
};

const restoreScrollPosition = () => {
  if (!scrollContainer) {
    return;
  }

  const savedPosition = Number(
    sessionStorage.getItem(SIDEBAR_SCROLL_STORAGE_KEY),
  );

  if (!Number.isFinite(savedPosition) || savedPosition < 0) {
    return;
  }

  scrollContainer.scrollTop = savedPosition;
};

const bindScrollContainer = () => {
  const nextContainer = document.querySelector<HTMLElement>(
    SIDEBAR_SCROLL_WRAPPER_SELECTOR,
  );

  if (!nextContainer) {
    return false;
  }

  if (scrollContainer !== nextContainer) {
    clearScrollListener();

    scrollContainer = nextContainer;
    scrollContainer.addEventListener("scroll", persistScrollPosition, {
      passive: true,
    });
  }

  return true;
};

const initializeScrollPersistence = (attempt = 0) => {
  clearBindRetryTimer();

  const isBound = bindScrollContainer();

  if (isBound) {
    restoreScrollPosition();
    return;
  }

  if (attempt >= 6) {
    return;
  }

  bindRetryTimer = setTimeout(() => {
    initializeScrollPersistence(attempt + 1);
  }, 50);
};

onMounted(async () => {
  await nextTick();

  requestAnimationFrame(() => {
    initializeScrollPersistence();
  });
});

watch(
  () => route.fullPath,
  async () => {
    await nextTick();

    requestAnimationFrame(() => {
      initializeScrollPersistence();
    });
  },
);

onBeforeUnmount(() => {
  clearBindRetryTimer();

  persistScrollPosition();
  clearScrollListener();
});
</script>

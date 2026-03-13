<template>
  <div class="sidebar-search" ref="searchContainer">
    <div class="search-input-wrapper">
      <div class="search-icon">
        <i class="icofont-search"></i>
      </div>
      <input
        ref="searchInput"
        type="text"
        class="form-control search-input"
        :placeholder="placeholder"
        v-model="searchQuery"
        @input="handleInput"
        @focus="handleFocus"
        @keydown="handleKeydown"
        aria-label="Search modules"
        aria-autocomplete="list"
        :aria-expanded="showDropdown && filteredResults.length > 0"
        role="combobox"
      />
      <button
        v-if="searchQuery"
        class="clear-btn"
        @click="clearSearch"
        type="button"
        aria-label="Clear search"
      >
        <i class="icofont-close"></i>
      </button>
    </div>

    <!-- Search Results Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="showDropdown"
        class="search-results-dropdown"
        role="listbox"
        :aria-label="`${filteredResults.length} results found`"
      >
        <!-- Loading State -->
        <div v-if="isSearching" class="search-loading">
          <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Searching...</span>
          </div>
        </div>

        <!-- Results List -->
        <template v-else-if="filteredResults.length > 0">
          <div
            v-for="(result, index) in filteredResults"
            :key="result.key"
            class="search-result-item"
            :class="{ 'is-active': highlightedIndex === index }"
            @click="selectResult(result)"
            @mouseenter="highlightedIndex = index"
            role="option"
            :aria-selected="highlightedIndex === index"
          >
            <span class="result-icon" v-if="result.icon">
              <i :class="result.icon"></i>
            </span>
            <span class="result-content">
              <span
                class="result-label"
                v-html="highlightMatch(result.label, searchQuery)"
              ></span>
              <span class="result-path" v-if="result.pathLabels.length > 0">
                {{ result.pathLabels.join(" \u2192 ") }}
              </span>
            </span>
            <span class="result-arrow" v-if="result.children && result.children.length > 0">
              <i class="icofont-arrow-right"></i>
            </span>
          </div>
        </template>

        <!-- No Results -->
        <div v-else-if="searchQuery.length > 0" class="no-results">
          <div class="no-results-icon">
            <i class="icofont-search-2"></i>
          </div>
          <p class="no-results-text">No modules found</p>
          <p class="no-results-hint">Try searching with different keywords</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { MENU_ITEMS } from "@/assets/data/menu-items";
import type { MenuItemType } from "@/types/menu";
import { hasPermission } from "@/helpers/permissions";

interface FlattenedMenuItem extends MenuItemType {
  pathLabels: string[];
}

const props = withDefaults(
  defineProps<{
    placeholder?: string;
    maxResults?: number;
    debounceMs?: number;
  }>(),
  {
    placeholder: "Search modules...",
    maxResults: 10,
    debounceMs: 150,
  }
);

const emit = defineEmits<{
  (e: "search", query: string): void;
  (e: "select", item: MenuItemType): void;
}>();

const router = useRouter();
const searchContainer = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");
const showDropdown = ref(false);
const highlightedIndex = ref(-1);
const isSearching = ref(false);
const debounceTimer = ref<ReturnType<typeof setTimeout> | null>(null);

function isPermitted(item: MenuItemType): boolean {
  if (item.disabled) return false;
  if (!item.permission || item.permission.trim() === "") return true;
  return hasPermission(item.permission);
}

function isNavigable(item: MenuItemType): boolean {
  return !!(item.route || item.url);
}

function flattenMenuItems(items: MenuItemType[]): FlattenedMenuItem[] {
  const result: FlattenedMenuItem[] = [];
  let activeTitle: string | null = null;

  const visit = (item: MenuItemType, pathLabels: string[]) => {
    const nextPathLabels = item.label ? [...pathLabels, item.label] : [...pathLabels];

    if (isPermitted(item) && isNavigable(item) && item.label) {
      result.push({
        ...item,
        label: item.label,
        pathLabels,
      });
    }

    if (item.children && item.children.length > 0) {
      for (const child of item.children) {
        visit(child, nextPathLabels);
      }
    }
  };

  for (const item of items) {
    if (item.isTitle) {
      activeTitle = item.label || null;
      continue;
    }

    const pathLabels = activeTitle ? [activeTitle] : [];
    visit(item, pathLabels);
  }

  return result;
}

// Get all searchable items
const allMenuItems = computed(() => {
  return flattenMenuItems(MENU_ITEMS);
});

// Filter results based on search query
const filteredResults = computed(() => {
  if (!searchQuery.value || searchQuery.value.trim() === "") {
    return [];
  }

  const query = searchQuery.value.toLowerCase().trim();

  // Score and filter results
  const results = allMenuItems.value
    .map((item) => {
      let score = 0;
      const label = item.label?.toLowerCase() || "";
      const key = item.key?.toLowerCase() || "";
      const icon = item.icon?.toLowerCase() || "";

      // Exact match gets highest score
      if (label === query) score = 100;
      else if (label.startsWith(query)) score = 80;
      else if (label.includes(query)) score = 60;
      else if (key === query) score = 50;
      else if (key.includes(query)) score = 30;
      else if (icon.includes(query)) score = 10;

      // Check if any field matches
      const matches =
        label.includes(query) || key.includes(query) || icon.includes(query);

      return { item, score, matches };
    })
    .filter((r) => r.matches)
    .sort((a, b) => b.score - a.score)
    .slice(0, props.maxResults)
    .map((r) => r.item);

  return results;
});

// Check if we should show dropdown
const shouldShowDropdown = computed(() => {
  return searchQuery.value.trim().length > 0;
});

// Watch for query changes
watch(
  () => searchQuery.value,
  () => {
    if (debounceTimer.value) {
      clearTimeout(debounceTimer.value);
    }

    isSearching.value = true;

    debounceTimer.value = setTimeout(() => {
      isSearching.value = false;
      highlightedIndex.value = filteredResults.value.length > 0 ? 0 : -1;
      emit("search", searchQuery.value);
    }, props.debounceMs);
  }
);

// Watch for dropdown visibility
watch(
  () => shouldShowDropdown.value,
  (show) => {
    showDropdown.value = show;
    if (!show) {
      highlightedIndex.value = -1;
    }
  }
);

// Handle input event
const handleInput = () => {
  showDropdown.value = shouldShowDropdown.value;
};

// Handle focus
const handleFocus = () => {
  showDropdown.value = shouldShowDropdown.value;
};

// Handle click outside
const handleClickOutside = (event: MouseEvent) => {
  if (searchContainer.value && !searchContainer.value.contains(event.target as Node)) {
    showDropdown.value = false;
    highlightedIndex.value = -1;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Handle keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
  const resultsLength = filteredResults.value.length;

  switch (event.key) {
    case "ArrowDown":
      event.preventDefault();
      if (resultsLength > 0) {
        highlightedIndex.value = (highlightedIndex.value + 1) % resultsLength;
      }
      break;

    case "ArrowUp":
      event.preventDefault();
      if (resultsLength > 0) {
        highlightedIndex.value = highlightedIndex.value <= 0 ? resultsLength - 1 : highlightedIndex.value - 1;
      }
      break;

    case "Enter":
      event.preventDefault();
      if (highlightedIndex.value >= 0 && filteredResults.value[highlightedIndex.value]) {
        selectResult(filteredResults.value[highlightedIndex.value]);
      }
      break;

    case "Escape":
      event.preventDefault();
      showDropdown.value = false;
      highlightedIndex.value = -1;
      searchInput.value?.blur();
      break;
  }
};

// Highlight matching text
const highlightMatch = (text: string, query: string): string => {
  if (!query || !text) return text;

  const regex = new RegExp(`(${escapeRegExp(query)})`, "gi");
  return text.replace(regex, "<mark>$1</mark>");
};

// Escape special regex characters
const escapeRegExp = (str: string): string => {
  return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
};

// Select a result and navigate
const selectResult = (result: MenuItemType) => {
  emit("select", result);

  if (result.route) {
    router.push(result.route);
  } else if (result.url) {
    router.push(result.url);
  }

  // Reset search
  searchQuery.value = "";
  showDropdown.value = false;
  highlightedIndex.value = -1;

  // Focus back on search after navigation
  nextTick(() => {
    searchInput.value?.focus();
  });
};

// Clear search
const clearSearch = () => {
  searchQuery.value = "";
  showDropdown.value = false;
  highlightedIndex.value = -1;
  emit("search", "");
  searchInput.value?.focus();
};

// Expose method to focus search
const focus = () => {
  searchInput.value?.focus();
};

defineExpose({
  focus,
});
</script>

<style scoped lang="scss">
.sidebar-search {
  position: relative;
  padding: 12px 0;
  border-bottom: 1px solid var(--bs-startbar-e-border-color);
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: var(--bs-menu-icon-color);
  font-size: 14px;
  z-index: 1;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 36px 10px 36px;
  background: rgba(0, 0, 0, 0.04);
  border: 1px solid var(--bs-startbar-e-border-color);
  border-radius: 6px;
  color: var(--bs-menu-link-color);
  font-size: 13px;
  transition: all 0.2s ease;

  &::placeholder {
    color: rgba(6, 18, 55, 0.55);
  }

  &:focus {
    outline: none;
    background: rgba(0, 0, 0, 0.06);
    border-color: rgba(var(--bs-primary-rgb), 0.4);
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.15);
  }
}

.clear-btn {
  position: absolute;
  right: 8px;
  background: none;
  border: none;
  color: var(--bs-menu-icon-color);
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;

  &:hover {
    color: var(--bs-menu-link-color);
    background: rgba(0, 0, 0, 0.06);
  }

  i {
    font-size: 12px;
  }
}

.search-results-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: var(--bs-theme-white-color);
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
  overflow: hidden;
}

.search-loading {
  padding: 20px;
  display: flex;
  justify-content: center;
  color: var(--bs-text-muted);
}

.search-result-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.15s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);

  &:last-child {
    border-bottom: none;
  }

  &:hover,
  &.is-active {
    background: rgba(0, 0, 0, 0.03);

    .result-arrow {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .result-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.06);
    border-radius: 6px;
    margin-right: 12px;
    color: var(--bs-menu-link-color);
    font-size: 14px;
  }

  .result-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .result-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--bs-menu-link-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    :deep(mark) {
      background: #fff3cd;
      color: var(--bs-menu-link-color);
      padding: 0 2px;
      border-radius: 2px;
    }
  }

  .result-path {
    font-size: 12px;
    color: var(--bs-text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .result-arrow {
    color: var(--bs-text-muted);
    opacity: 0;
    transform: translateX(-4px);
    transition: all 0.15s ease;
    font-size: 12px;
  }
}

.no-results {
  padding: 32px 16px;
  text-align: center;

  .no-results-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.04);
    border-radius: 50%;
    color: var(--bs-text-muted);
    font-size: 20px;
  }

  .no-results-text {
    font-size: 14px;
    font-weight: 500;
    color: var(--bs-menu-link-color);
    margin: 0 0 4px;
  }

  .no-results-hint {
    font-size: 12px;
    color: var(--bs-text-muted);
    margin: 0;
  }
}

// Dark sidebar/search styling overrides
:global(html[data-startbar="dark"]) .sidebar-search,
:global(html[data-bs-theme="dark"]) .sidebar-search {
  .search-input {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.12);

    &::placeholder {
      color: rgba(255, 255, 255, 0.55);
    }

    &:focus {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(255, 255, 255, 0.2);
      box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.08);
    }
  }

  .clear-btn {
    &:hover {
      background: rgba(255, 255, 255, 0.1);
    }
  }

  .search-icon {
    color: rgba(255, 255, 255, 0.55);
  }

  .search-result-item {
    border-bottom-color: rgba(255, 255, 255, 0.08);

    &:hover,
    &.is-active {
      background: rgba(255, 255, 255, 0.06);
    }

    .result-icon {
      background: rgba(255, 255, 255, 0.06);
    }

    .result-label {
      :deep(mark) {
        background: rgba(255, 255, 255, 0.12);
      }
    }
  }

  .no-results {
    .no-results-icon {
      background: rgba(255, 255, 255, 0.08);
    }
  }
}

// Dropdown transition
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

// Custom scrollbar
.search-results-dropdown {
  &::-webkit-scrollbar {
    width: 6px;
  }

  &::-webkit-scrollbar-track {
    background: transparent;
  }

  &::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;

    &:hover {
      background: #adb5bd;
    }
  }
}
</style>

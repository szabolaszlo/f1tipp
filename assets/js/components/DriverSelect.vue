<template>
  <div ref="root" class="f1-select" :class="{'is-open': open, 'is-disabled': disabled, 'is-clearable': clearable}"
       @focusout="onFocusOut">
    <button ref="trigger" type="button" class="f1-select__trigger" :disabled="disabled"
            aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" :aria-label="ariaLabel"
            @click="toggle" @keydown="onTriggerKeydown">
      <template v-if="selected">
        <span class="f1-stripe" :style="{background: selected.teamColor}"></span>
        <span class="f1-name">{{ selected.familyName }}<small v-if="selected.givenName">{{ selected.givenName }}</small></span>
        <span class="f1-select__code">{{ selected.code }}</span>
      </template>
      <span v-else class="f1-select__placeholder">{{ placeholder }}</span>
      <span v-if="!disabled" class="f1-select__chevron" aria-hidden="true"></span>
    </button>
    <!-- kiválasztás törlése: így a pilóta újra választható máshová (pl. két pilóta cseréjéhez) -->
    <button v-if="clearable" type="button" class="f1-select__clear" :aria-label="clearLabel" :title="clearLabel"
            @click="clear">
      <span aria-hidden="true">&times;</span>
    </button>

    <div v-if="open" class="f1-select__panel" :class="{'f1-select__panel--up': openUp}">
      <input ref="search" v-model="query" class="f1-select__search" type="text" autocomplete="off"
             role="combobox" aria-autocomplete="list" aria-expanded="true"
             :aria-controls="listId" :aria-activedescendant="activeIndex >= 0 ? optionId(activeIndex) : null"
             :placeholder="searchPlaceholder" @keydown="onSearchKeydown">
      <ul :id="listId" ref="list" class="f1-select__list" role="listbox" @mousedown.prevent>
        <li v-for="(driver, index) in filtered" :id="optionId(index)" :key="driver.code"
            role="option" class="f1-select__option"
            :aria-selected="driver.code === modelValue ? 'true' : 'false'"
            :class="{'is-active': index === activeIndex, 'is-selected': driver.code === modelValue}"
            @mousemove="activeIndex = index" @click="choose(driver)">
          <span class="f1-stripe" :style="{background: driver.teamColor}"></span>
          <span class="f1-name">{{ driver.familyName }}<small v-if="driver.givenName">{{ driver.givenName }}</small></span>
          <span class="f1-select__code">{{ driver.code }}</span>
        </li>
        <li v-if="!filtered.length" class="f1-select__empty">{{ noResultText }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
let instanceCounter = 0;

const normalize = (text) => (text || '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase();

/**
 * TV-grafikás pilótaválasztó: csapatszín-csík, NAGYBETŰS családnév, kisebb keresztnév.
 * A más helyezésre már kiválasztott pilóták (takenBy) nem szerepelnek a listában, így az rövidebb;
 * a kiválasztás a × gombbal (vagy Delete/Backspace billentyűvel) törölhető, és a pilóta újra választható.
 */
export default {
  name: "DriverSelect",
  props: {
    modelValue: {type: String, default: ''},
    drivers: {type: Array, required: true},
    // pilótakód -> rövid címke ("P3") azokra, akiket másik helyre már kiválasztott: ezek kimaradnak a listából
    takenBy: {type: Object, default: () => ({})},
    disabled: {type: Boolean, default: false},
    placeholder: {type: String, default: ''},
    searchPlaceholder: {type: String, default: ''},
    noResultText: {type: String, default: ''},
    clearLabel: {type: String, default: ''},
    ariaLabel: {type: String, default: null}
  },
  emits: ['update:modelValue'],
  data() {
    return {
      open: false,
      openUp: false,
      query: '',
      activeIndex: -1,
      uid: ++instanceCounter
    }
  },
  computed: {
    listId() {
      return 'f1-select-list-' + this.uid;
    },
    clearable() {
      return !this.disabled && !!this.modelValue;
    },
    selected() {
      return this.drivers.find(driver => driver.code === this.modelValue) || null;
    },
    filtered() {
      const query = normalize(this.query.trim());
      const available = this.drivers.filter(driver => !this.isTaken(driver));
      if (!query) {
        return available;
      }
      return available.filter(driver =>
          normalize(driver.familyName + ' ' + driver.givenName + ' ' + driver.code).includes(query)
      );
    }
  },
  watch: {
    filtered() {
      this.activeIndex = this.filtered.length ? 0 : -1;
    }
  },
  beforeUnmount() {
    document.removeEventListener('pointerdown', this.onDocumentPointerDown, true);
  },
  methods: {
    optionId(index) {
      return this.listId + '-' + index;
    },
    isTaken(driver) {
      return Object.prototype.hasOwnProperty.call(this.takenBy, driver.code);
    },
    toggle() {
      this.open ? this.close(false) : this.openPanel();
    },
    openPanel(initialQuery = '') {
      if (this.disabled) {
        return;
      }
      const rect = this.$refs.trigger.getBoundingClientRect();
      const below = window.innerHeight - rect.bottom;
      this.openUp = below < 340 && rect.top > below;

      this.query = initialQuery;
      this.open = true;
      document.addEventListener('pointerdown', this.onDocumentPointerDown, true);
      this.$nextTick(() => {
        const selectedIndex = this.filtered.findIndex(driver => driver.code === this.modelValue);
        this.activeIndex = selectedIndex >= 0 ? selectedIndex : (this.filtered.length ? 0 : -1);
        // érintőképernyőn ne ugorjon fel a billentyűzet, ott görgetni kényelmesebb
        if (initialQuery || window.matchMedia('(pointer: fine)').matches) {
          this.$refs.search.focus();
        }
        this.scrollActiveIntoView();
      });
    },
    close(refocus = true) {
      this.open = false;
      this.query = '';
      document.removeEventListener('pointerdown', this.onDocumentPointerDown, true);
      if (refocus) {
        this.$nextTick(() => this.$refs.trigger && this.$refs.trigger.focus());
      }
    },
    choose(driver) {
      this.$emit('update:modelValue', driver.code);
      this.close();
    },
    clear() {
      this.$emit('update:modelValue', '');
      this.close();
    },
    move(step) {
      if (!this.filtered.length) {
        return;
      }
      const start = this.activeIndex < 0 ? (step > 0 ? 0 : this.filtered.length - 1) : this.activeIndex + step;
      this.activeIndex = Math.min(Math.max(start, 0), this.filtered.length - 1);
      this.scrollActiveIntoView();
    },
    scrollActiveIntoView() {
      this.$nextTick(() => {
        const list = this.$refs.list;
        const item = list && list.children[this.activeIndex];
        if (!item) {
          return;
        }
        if (item.offsetTop < list.scrollTop) {
          list.scrollTop = item.offsetTop;
        } else if (item.offsetTop + item.offsetHeight > list.scrollTop + list.clientHeight) {
          list.scrollTop = item.offsetTop + item.offsetHeight - list.clientHeight;
        }
      });
    },
    onTriggerKeydown(event) {
      if ((event.key === 'Delete' || event.key === 'Backspace') && this.clearable) {
        event.preventDefault();
        this.clear();
      } else if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        this.openPanel();
      } else if (event.key.length === 1 && /\S/.test(event.key) && !event.ctrlKey && !event.metaKey && !event.altKey) {
        // gépelésre azonnal nyílik és keres
        event.preventDefault();
        this.openPanel(event.key);
      }
    },
    onSearchKeydown(event) {
      switch (event.key) {
        case 'ArrowDown':
          event.preventDefault();
          this.move(1);
          break;
        case 'ArrowUp':
          event.preventDefault();
          this.move(-1);
          break;
        case 'Enter':
          event.preventDefault();
          if (this.activeIndex >= 0) {
            this.choose(this.filtered[this.activeIndex]);
          }
          break;
        case 'Escape':
          event.preventDefault();
          this.close();
          break;
      }
    },
    onDocumentPointerDown(event) {
      if (!this.$refs.root.contains(event.target)) {
        this.close(false);
      }
    },
    onFocusOut(event) {
      if (this.open && !this.$refs.root.contains(event.relatedTarget)) {
        this.close(false);
      }
    }
  }
}
</script>

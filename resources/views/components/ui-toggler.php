<?php

declare(strict_types=1);

?>

<div x-data="{ open: false }">
  <button
    type="button"
    class="offcanvas-toggler d-print-none"
    @click="open = true"
    aria-label="Abrir panel de interfaz"
    title="Opciones de interfaz">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>

  <template x-teleport="body">
    <div
      x-show="open"
      @keydown.escape.window="open = false"
      class="d-contents">
      <div
        class="offcanvas-backdrop"
        x-show="open"
        x-transition:enter="transition-backdrop"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-backdrop"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        aria-hidden="true"></div>

      <aside
        class="offcanvas"
        x-show="open"
        x-transition:enter="transition-transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.outside="open = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ui-offcanvas-title">
        <header class="offcanvas-header">
          <div>
            <span class="eyebrow">Personalización</span>
            <h3 id="ui-offcanvas-title" class="toolbar-title">Opciones de interfaz</h3>
          </div>
          <button
            type="button"
            class="offcanvas-close"
            @click="open = false"
            aria-label="Cerrar panel">
            &times;
          </button>
        </header>

        <section class="offcanvas-body">
          <template x-for="(option, key) in ui" :key="key">
            <label class="switch-label">
              <div>
                <strong x-text="option.name"></strong>
                <p class="muted-text" x-text="option.description"></p>
              </div>
              <input
                type="checkbox"
                class="switch-input"
                x-model="option.value">
            </label>
          </template>
        </section>
      </aside>
    </div>
  </template>
</div>

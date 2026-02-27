<!doctype html>
@props([
    'header' => true,
    'hero' => false,
    'breadcrumbs' => true,
    'main' => true,
    'sidebar' => false,
    'footer' => true,
])

<html @php(language_attributes()) class="h-full">

@include('partials.head')

<body @php(body_class('h-full'))>
@php(wp_body_open())

<div
  id="app"
  class="relative flex flex-col h-full"
  x-data="App()"
>
  <a class="sr-only focus:not-sr-only" href="#site-content">
    {{ __('Skip to content', 'sage') }}
  </a>

  {{-- Header Section --}}
  @if(isset($header) && !is_bool($header))
    <header {{ $header->attributes->class(['site-header']) }}>
      {{ $header }}
    </header>
  @else
    @includeWhen($header !== false, 'sections.header')
  @endif

  {{-- Main content Section --}}
  <main id="site-content" {{ $attributes->merge(['class' => 'site-content flex-1 flex flex-col']) }}>
    {{-- Hero --}}
    @if(isset($hero) && !is_bool($hero))
      <section {{ $hero->attributes->class(['hero']) }}>
        {{ $hero }}
      </section>
    @else
      @includeWhen($hero !== false, 'partials.hero')
    @endif

    {{-- Breadcrumbs --}}
    @if($breadcrumbs)
      <div class="container max-sm:hidden py-4 md:py-6">
        <x-breadcrumbs />
      </div>
    @endif

    <div
      class="z-[0] flex {{ $sidebar ? 'container flex-row justify-between gap-y-8 gap-x-12 max-lg:flex-col mx-auto px-4 lg:px-8' : 'flex-col' }}">
      {{-- Main --}}
      <article class="w-full {{ $sidebar ? 'lg:max-w-screen-md' : 'flex-grow' }}">
        @if(isset($main) && !is_bool($main))
          {{ $main }}
        @else
          {{ $slot }}
        @endif
      </article>

      {{-- Sidebar --}}
      @if(isset($sidebar) && is_string($sidebar))
        <aside class="lg:max-w-screen-sm">
          @include('sections.sidebar')
        </aside>
      @elseif($sidebar !== false)
        <aside {{ $sidebar->attributes->class(['lg:max-w-screen-sm']) }}>
          {{ $sidebar }}
        </aside>
      @endif
    </div>
  </main>

  {{-- Footer Section --}}
  @if(isset($footer) && !is_bool($footer))
    <footer {{ $footer->getAttributes() }}>
      {{ $footer }}
    </footer>
  @else
    @includeWhen($footer !== false, 'sections.footer')
  @endif

  {{-- Mini Cart Toast --}}
  <livewire:mini-cart />

  {{-- Backdrop --}}
  <div x-show="backdrop"
       x-transition:enter="transition-opacity ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-in duration-100"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       x-on:click="backdrop = false; searchActive = false"
       x-cloak
       class="fixed z-[1] inset-0 bg-gray-900/30"
  ></div>
</div>

@php(do_action('get_footer'))
@php(wp_footer())

{{-- Form Validator - must be defined before Livewire/Alpine initializes --}}
<script>
  function formValidator(config = {}) {
    return {
      form: config.form || {},
      rules: config.rules || {},
      messages: config.messages || {},
      errors: {},
      touched: {},

      validateField(el, includeTouched = false) {
        const fieldName = typeof el === 'string' ? el : el.name;
        const element = typeof el === 'string'
          ? document.getElementById(el) || document.querySelector(`[name="${el}"]`)
          : el;

        if (!this.touched[fieldName] && !includeTouched) {
          return true;
        }

        const rulesString = this.rules[fieldName];

        let value;
        if (element && element.type === 'checkbox') {
          value = element.checked;
        } else if (element && element.type === 'radio') {
          const checkedRadio = document.querySelector(`[name="${fieldName}"]:checked`);
          value = checkedRadio ? checkedRadio.value : '';
        } else {
          value = element ? element.value : this.form[fieldName];
        }

        let hasError = false;

        this.errors[fieldName] = '';

        if (!rulesString) return true;

        const rules = rulesString.split('|');

        for (let rule of rules) {
          let [ruleName, param] = rule.split(':');

          if (ruleName === 'required_if') {
            const [targetField, targetValue] = param.split(',');
            const left = String(this.form[targetField]).toLowerCase();
            const right = String(targetValue).toLowerCase();

            if (left === right) {
              if (value === null || value === undefined || value === '') {
                this.addError(fieldName, ruleName);
                hasError = true;
                break;
              }
            }
            continue;
          }

          if (this.runRule(ruleName, value, param) === false) {
            this.addError(fieldName, ruleName);
            hasError = true;
            break;
          }
        }

        return !hasError;
      },

      runRule(ruleName, value, param) {
        if (value === '' || value === null || value === false) {
          return ruleName !== 'required' && ruleName !== 'accepted';
        }

        switch (ruleName) {
          case 'required':
            return value !== undefined && value !== '';
          case 'min':
            if (typeof value === 'string') return value.length >= parseInt(param);
            if (Array.isArray(value)) return value.length >= parseInt(param);
            if (!isNaN(value)) return parseFloat(value) >= parseFloat(param);
            return false;
          case 'max':
            if (typeof value === 'string') return value.length <= parseInt(param);
            if (Array.isArray(value)) return value.length <= parseInt(param);
            if (!isNaN(value)) return parseFloat(value) <= parseFloat(param);
            return false;
          case 'alpha':
            return /^[a-zA-Z]+$/.test(value);
          case 'boolean':
            return /^(true|false|1|0)$/.test(String(value));
          case 'numeric':
            return /^\d+$/.test(value);
          case 'integer':
            return /^-?\d+$/.test(String(value));
          case 'string':
            return typeof value === 'string';
          case 'email':
            return /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(value);
          case 'accepted':
            return value === true || value === 'true' || value === 1 || value === '1' || value === 'yes' || value === 'on';
          case 'regex':
            return this.validateRegex(value, param);
          case 'nullable':
          case 'sometimes':
            return true;
          default:
            return true;
        }
      },

      validateRegex(value, param) {
        const invalidModifiers = ['x', 's', 'u', 'X', 'U', 'A'];
        let pattern = param;
        let modifiers = '';

        if (param.startsWith('/') && param.lastIndexOf('/') > 0) {
          const lastSlash = param.lastIndexOf('/');
          pattern = param.slice(1, lastSlash);
          modifiers = param.slice(lastSlash + 1);
          modifiers = [...modifiers].filter(m => !invalidModifiers.includes(m)).join('');
        }

        const jsRegex = new RegExp(pattern, modifiers);
        return jsRegex.test(value);
      },

      validateAll(formId = null) {
        let isValid = true;
        this.errors = {};

        for (const fieldName in this.rules) {
          let field = document.getElementById(fieldName);

          if (!field || !field.name) {
            field = document.querySelector(`[name="${fieldName}"]`);
          }

          if (field && field.offsetParent !== null) {
            if (!this.validateField(field, true)) {
              isValid = false;
            }
          }
        }

        if (!isValid) {
          this.focusFirstError(formId);
        }

        return isValid;
      },

      focusFirstError(formId = null) {
        const errorFields = Object.keys(this.errors).filter(
          key => this.errors[key] && this.errors[key].length > 0
        );

        const form = formId
          ? document.getElementById(formId)
          : document.querySelector('form');

        if (!form) return;

        const formElements = [...form.elements];
        const firstErrorEl = formElements.find(
          el => el.name && errorFields.includes(el.name) && el.offsetParent !== null
        );

        if (firstErrorEl) {
          firstErrorEl.focus();
        }
      },

      addError(fieldName, ruleName) {
        const messageKey = `${fieldName}.${ruleName}`;
        this.errors[fieldName] = this.messages[messageKey] || 'Dit veld is ongeldig';
      },

      markTouched(fieldName) {
        this.touched[fieldName] = true;
      },

      clearError(fieldName) {
        this.errors[fieldName] = '';
      },

      clearAllErrors() {
        this.errors = {};
      },

      hasError(fieldName) {
        return !!this.errors[fieldName];
      },

      getError(fieldName) {
        return this.errors[fieldName] || '';
      }
    };
  }
</script>
@livewireScripts
@vite('resources/js/app.js')
<script>
  function App() {
    return {
      backdrop: false,
      searchActive: false,
      productId: @js(is_product() ? get_the_ID() : ''),
      favorites: [],
      recentlyViewed: [],
      activeItem: null,
      activeSubItem: null,

      // Mega menu state
      megaMenuTimeout: null,
      scrolled: false,
      mobileMenuOpen: false,

      toggleItem(itemId) {
        this.activeSubItem = null;
        this.activeItem = this.activeItem === itemId ? null : itemId;
        this.backdrop = this.activeItem !== null;
      },

      toggleSubItem(subItemId) {
        this.activeSubItem = this.activeSubItem === subItemId ? null : subItemId;
      },

      resetMenu() {
        this.backdrop = false;
        this.toggleItem(null);
        this.activeItem = null;
      },

      // Mega menu hover-intent methods
      megaMenuEnter(itemId) {
        clearTimeout(this.megaMenuTimeout);
        this.megaMenuTimeout = setTimeout(() => {
          this.activeItem = itemId;
          this.backdrop = true;
        }, 100);
      },

      megaMenuLeave() {
        clearTimeout(this.megaMenuTimeout);
        this.megaMenuTimeout = setTimeout(() => {
          this.resetMenu();
        }, 150);
      },

      megaMenuStay() {
        clearTimeout(this.megaMenuTimeout);
      },

      // Mobile menu methods
      openMobileMenu() {
        this.mobileMenuOpen = true;
        document.body.style.overflow = 'hidden';
      },

      closeMobileMenu() {
        this.mobileMenuOpen = false;
        document.body.style.overflow = '';
      },

      init() {
        this.$nextTick( () => {
          if(this.productId) {
            this.trackProduct(this.productId);
          }
        });

        this.initFavorites();
        this.initRecentlyViewed();

        window.addEventListener('storage', () => {
          this.initFavorites();
          this.initRecentlyViewed();
        });

        // Scroll detection for sticky header
        window.addEventListener('scroll', () => {
          this.scrolled = window.scrollY > 50;
        }, { passive: true });

        // Close mega menu on escape
        window.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            this.resetMenu();
            this.closeMobileMenu();
          }
        });
      },

      initFavorites() {
        try {
          this.favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        } catch {
          this.favorites = [];
        }
      },

      initRecentlyViewed() {
        try {
          this.recentlyViewed = JSON.parse(sessionStorage.getItem('recentlyViewed') || '[]');
        } catch {
          this.recentlyViewed = [];
        }
      },

      isFavorite(productId) {
        return this.favorites.includes(Number(productId));
      },

      toggleFavorite(productId) {
        const id = Number(productId);
        if (this.isFavorite(id)) {
          this.favorites = this.favorites.filter(fav => fav !== id);
        } else {
          this.favorites.push(id);
        }
        localStorage.setItem('favorites', JSON.stringify(this.favorites));
      },

      trackProduct(productId) {
        const id = Number(productId);
        this.recentlyViewed = this.recentlyViewed.filter(p => p !== id);
        this.recentlyViewed.unshift(id);
        this.recentlyViewed = this.recentlyViewed.slice(0, 20);
        sessionStorage.setItem('recentlyViewed', JSON.stringify(this.recentlyViewed));
      },

      getRecentlyViewed(excludeId = null) {
        if (excludeId) {
          return this.recentlyViewed.filter(id => id !== Number(excludeId));
        }
        return this.recentlyViewed;
      },
    }
  }
</script>
@stack('scripts')
</body>
</html>

/**
 * Flyout menu with menu-aim logic.
 *
 * Tracks mouse movement direction. When the user moves diagonally
 * toward the submenu panel (rightward), category switching is deferred
 * so the panel doesn't flicker. Vertical movement switches immediately.
 */

function registerFlyoutMenu() {
  Alpine.data('flyoutMenu', (config = {}) => ({
    activeCategory: null,
    switchTimeout: null,

    // Mouse tracking – ring buffer of last positions
    positions: [],
    MAX_POSITIONS: 3,
    DELAY_MS: 400,
    GRACE_ANGLE: 55, // degrees from horizontal to consider "toward submenu"

    init() {
      const ids = config.categories || [];
      if (ids.length > 0) {
        this.activeCategory = ids[0];
      }
    },

    trackMouse(e) {
      this.positions.push({ x: e.clientX, y: e.clientY, t: Date.now() });
      if (this.positions.length > this.MAX_POSITIONS) {
        this.positions.shift();
      }
    },

    /**
     * Returns true if the mouse is moving rightward (toward the submenu).
     */
    isMovingTowardSubmenu() {
      if (this.positions.length < 2) return false;

      const prev = this.positions[this.positions.length - 2];
      const newest = this.positions[this.positions.length - 1];

      const dx = newest.x - prev.x;
      const dy = newest.y - prev.y;

      // Must be moving right
      if (dx <= 0) return false;

      // Angle from horizontal – if within grace angle, user is aiming at submenu
      const angle = Math.abs(Math.atan2(dy, dx)) * (180 / Math.PI);
      return angle < this.GRACE_ANGLE;
    },

    enterCategory(categoryId) {
      if (this.activeCategory === categoryId) return;

      clearTimeout(this.switchTimeout);

      if (this.isMovingTowardSubmenu()) {
        this.switchTimeout = setTimeout(() => {
          this.activeCategory = categoryId;
        }, this.DELAY_MS);
      } else {
        this.activeCategory = categoryId;
      }
    },

    enterSubmenu() {
      clearTimeout(this.switchTimeout);
    },
  }));
}

// Support both pre- and post-Alpine initialization
if (window.Alpine) {
  registerFlyoutMenu();
} else {
  document.addEventListener('alpine:init', registerFlyoutMenu);
}

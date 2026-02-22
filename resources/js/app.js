import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Import Swiper and make it globally available
import Swiper from './swiper.js';
window.Swiper = Swiper;

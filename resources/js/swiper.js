/**
 * Swiper configuration with required modules
 *
 * Import only the modules we need for better tree-shaking
 */
import Swiper from 'swiper';
import {Navigation, Pagination, Zoom, Autoplay, Thumbs, FreeMode, EffectFade, Scrollbar} from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/zoom';
import 'swiper/css/autoplay';
import 'swiper/css/thumbs';
import 'swiper/css/free-mode';
import 'swiper/css/effect-fade';
import 'swiper/css/scrollbar';

// Configure Swiper to use modules
Swiper.use([Navigation, Pagination, Zoom, Autoplay, Thumbs, FreeMode, EffectFade, Scrollbar]);

// Export configured Swiper
export default Swiper;

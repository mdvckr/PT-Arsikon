import './bootstrap';

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Flip } from 'gsap/Flip';
import { ArsikonAnimate } from './animations';
import { arsikonScroll } from './scroll';
import Alpine from 'alpinejs';
import { notificationCenter } from './notification-center';

gsap.registerPlugin(ScrollTrigger, Flip);

window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
window.Flip = Flip;
window.ArsikonAnimate = ArsikonAnimate;

window.Alpine = Alpine;
Alpine.data('notificationCenter', notificationCenter);

Alpine.start();

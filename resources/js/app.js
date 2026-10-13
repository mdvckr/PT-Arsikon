import './bootstrap';

import Alpine from 'alpinejs';
import { notificationCenter } from './notification-center';

window.Alpine = Alpine;
Alpine.data('notificationCenter', notificationCenter);

Alpine.start();


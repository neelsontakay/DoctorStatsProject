import './bootstrap';
import Alpine from 'alpinejs';
import * as DoctorStats from './doctorstats';
import { registerPages } from './pages';

window.DoctorStats = DoctorStats;
window.Alpine = Alpine;

registerPages(Alpine);

Alpine.start();

import './bootstrap';

import Alpine from 'alpinejs';
import { marked } from 'marked';

// Maps an SEO score (0-100) to a traffic-light colour, shared by the score ring.
window.scoreColor = (s) => (s >= 80 ? '#10b981' : s >= 50 ? '#f59e0b' : '#ef4444');

// Markdown -> HTML for the editor preview pane.
marked.setOptions({ breaks: true });
window.renderMarkdown = (md) => marked.parse(md || '');

window.Alpine = Alpine;

Alpine.start();

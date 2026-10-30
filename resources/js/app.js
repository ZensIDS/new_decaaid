import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

Alpine.plugin(intersect);
Alpine.plugin(collapse);

/*
 * x-focustrap: menjaga fokus keyboard (Tab / Shift+Tab) tetap di dalam dialog
 * yang sedang terbuka (menu mobile, pencarian). Tanpa dependensi tambahan.
 */
Alpine.directive('focustrap', (el, _directive, { cleanup }) => {
    const focusable =
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const onKeydown = (e) => {
        if (e.key !== 'Tab') return;

        const items = [...el.querySelectorAll(focusable)].filter((n) => n.getClientRects().length > 0);
        if (! items.length) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (! e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    };

    el.addEventListener('keydown', onKeydown);
    cleanup(() => el.removeEventListener('keydown', onKeydown));
});

window.Alpine = Alpine;
Alpine.start();
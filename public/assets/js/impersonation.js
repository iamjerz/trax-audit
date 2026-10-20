// "View as" — loaded globally (partials/script.blade.php) since the
// banner's "Return to my account" button (partials/bodyheader.blade.php)
// needs to work no matter which page an admin is currently viewing-as on.
// viewAsUser() is called from the /users Grid.js Actions column (user.js).
//
// CSRF: prefers the banner's own hidden input (always present while
// impersonating, even on pages that don't include the global
// meta[name="csrf-token"] tag — not every page does) and falls back to that
// meta tag otherwise.
function _impersonationCsrfToken() {
    const hidden = document.getElementById('impersonation-csrf-token');
    if (hidden) return hidden.value;
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function _impersonationPost(url) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': _impersonationCsrfToken(),
            'Accept': 'application/json',
        },
    }).then(async res => {
        // Rare edge case: EndStaleImpersonation (a global middleware) can
        // auto-end an already-stale session and redirect before this
        // request ever reaches the controller — its response is the
        // homepage HTML, not JSON. Treat that as success rather than
        // failing to parse it.
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return { success: true, redirect: res.url };
        }

        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    });
}

function viewAsUser(employeeid, name) {
    if (!confirm(`View the app as ${name}? You'll see exactly what they see until you return to your own account.`)) {
        return;
    }

    _impersonationPost(`/users/${encodeURIComponent(employeeid)}/view-as`)
        .then(data => { window.location.href = data.redirect; })
        .catch(err => notifyError(err.message || 'Could not start viewing as that user.'));
}

function stopViewingAs() {
    _impersonationPost('/stop-viewing-as')
        .then(data => { window.location.href = data.redirect; })
        .catch(err => notifyError(err.message || 'Could not return to your account.'));
}

// The theme's sidebar (.vertical-menu) and topbar (#page-topbar) are both
// position:fixed, pinned to the viewport regardless of where they sit in
// the DOM — so the banner above, which renders in normal document flow,
// was getting rendered but then covered up rather than pushing them down.
// Fix: measure the banner's own height, then nudge both fixed elements (and
// the content area's existing top margin) down by exactly that much. Reads
// each element's current computed value rather than hardcoding the
// theme's 75px topbar height, so this keeps working if that ever changes.
(function () {
    const banner = document.getElementById('impersonation-banner');
    if (!banner) return;

    const bannerHeight = banner.offsetHeight;

    banner.style.position = 'fixed';
    banner.style.top = '0';
    banner.style.left = '0';
    banner.style.right = '0';
    banner.style.zIndex = '2000';

    function pushDownBy(selector, prop) {
        const el = document.querySelector(selector);
        if (!el) return;
        const current = parseInt(getComputedStyle(el)[prop], 10) || 0;
        el.style[prop] = (current + bannerHeight) + 'px';
    }

    // Only .content (not .main-content itself, which has no top spacing of
    // its own) actually needs pushing — adjusting both would double it up.
    pushDownBy('#page-topbar', 'top');
    pushDownBy('.vertical-menu', 'top');
    pushDownBy('.main-content .content', 'marginTop');
    pushDownBy('.sidebar-menu-scroll', 'marginTop');
})();

/**
 * expchangeclass: the "Change content class" entry of the content tree and sub-items context menus
 * (popupmenu/classcontextmenu.tpl, popupmenu/classsubitemscontextmenu.tpl).
 *
 * The entries declare their form in data-menu-form, which the declared menus of ezpopupmenu.js handle (Exponential
 * 6.0.15 and later). For an older ezpopupmenu.js, without declared menus, this file does what the onclick and
 * onmouseover attributes did before: a click submits the entry's form (ezpopmenu_submitForm), pointing at it closes
 * an open submenu (ezpopmenu_mouseOver). Both templates print the file; it sets itself up once. No inline code, so the
 * menus work under a Content Security Policy without 'unsafe-inline'.
 */
(function (window, document) {
    if (window.expChangeClassMenuReady) { return; }
    window.expChangeClassMenuReady = true;
    var ENTRIES = { 'menu-class-change': 'ContextMenu', 'menu-class-change-sub': 'SubitemsContextMenu' };

    // ezpopupmenu.js with declared menus (data-menu-*) handles the entries itself
    function declared() { return typeof window._declaredClick === 'function'; }
    function entry(e) {
        var el = e.target && e.target.closest ? e.target.closest('#menu-class-change, #menu-class-change-sub') : null;
        return el && !declared() ? el : null;
    }

    document.addEventListener('click', function (e) {
        var link = entry(e);
        if (!link || e.defaultPrevented || typeof window.ezpopmenu_submitForm !== 'function') { return; }
        e.preventDefault();
        window.ezpopmenu_submitForm(link.getAttribute('data-menu-form'));
    });
    document.addEventListener('mouseover', function (e) {
        var link = entry(e);
        if (link && typeof window.ezpopmenu_mouseOver === 'function') { window.ezpopmenu_mouseOver(ENTRIES[link.id]); }
    });
}(window, document));

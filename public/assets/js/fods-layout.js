// Sidebar behaviour for templates/myheader.php:
//   desktop (>= 1200px): toggle button collapses to icons, remembered per browser
//   tablet/phone:        toggle button opens an off-canvas sidebar over an overlay
(function () {
	var root = document.documentElement;
	var KEY = 'fods.sidebar.collapsed';
	var DESKTOP = window.matchMedia('(min-width: 1200px)');

	function setOpen(open) {
		root.classList.toggle('fods-sidebar-open', open);
	}

	function toggle() {
		if (DESKTOP.matches) {
			var collapsed = root.classList.toggle('fods-collapsed');
			try { localStorage.setItem(KEY, collapsed ? '1' : '0'); } catch (e) {}
		} else {
			setOpen(!root.classList.contains('fods-sidebar-open'));
		}
	}

	var toggleBtn = document.getElementById('fodsSidebarToggle');
	var closeBtn = document.getElementById('fodsSidebarClose');
	var overlay = document.getElementById('fodsOverlay');

	if (toggleBtn) { toggleBtn.addEventListener('click', toggle); }
	if (closeBtn) { closeBtn.addEventListener('click', function () { setOpen(false); }); }
	if (overlay) { overlay.addEventListener('click', function () { setOpen(false); }); }

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { setOpen(false); }
	});

	// Leaving the mobile layout with the drawer open shouldn't leave the page locked
	DESKTOP.addEventListener('change', function () { setOpen(false); });

	// Keep the current page's menu item in view in a long sidebar
	var active = document.querySelector('.fods-nav-link.active');
	if (active && active.scrollIntoView) { active.scrollIntoView({ block: 'nearest' }); }
})();

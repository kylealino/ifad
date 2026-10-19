// FODS Assistant floating button + panel (lower right, every page; markup in
// templates/myfooter.php). The chat itself is fods-assistant.js, started the
// first time the panel opens. An open panel stays open while you move between
// pages in the same tab.
(function () {
	var fab = document.getElementById('fodsAiFab');
	var panel = document.getElementById('fodsAiPanel');
	if (!fab || !panel || !window.FodsAssistant) { return; }

	var OPEN_KEY = 'fods.assistant.open';
	var chat = null;

	function isOpen() { return panel.classList.contains('is-open'); }

	function setOpen(on, focus) {
		if (on) {
			if (!chat) {
				var opts = {};
				try { opts = JSON.parse(panel.getAttribute('data-opts') || '{}'); } catch (e) { /* bad options: defaults */ }
				chat = window.FodsAssistant.mount(document.getElementById('fodsAiChat'), opts);
			} else {
				chat.reload();   // the full page may have added to the chat
			}
		}
		panel.classList.toggle('is-open', on);
		fab.classList.toggle('is-open', on);
		fab.setAttribute('aria-expanded', on ? 'true' : 'false');
		panel.setAttribute('aria-hidden', on ? 'false' : 'true');
		fab.title = on ? 'Close the FODS Assistant' : 'Ask the FODS Assistant';
		try { sessionStorage.setItem(OPEN_KEY, on ? '1' : ''); } catch (e) { /* storage blocked */ }
		if (on && focus) { setTimeout(function () { chat.focus(); }, 160); }
	}

	fab.addEventListener('click', function () { setOpen(!isOpen(), true); });
	document.getElementById('fodsAiClose').addEventListener('click', function () { setOpen(false); fab.focus(); });
	document.getElementById('fodsAiNew').addEventListener('click', function () { if (chat) { chat.newChat(); } });
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen() && !document.querySelector('.modal.show')) { setOpen(false); fab.focus(); }
	});

	var wasOpen = false;
	try { wasOpen = sessionStorage.getItem(OPEN_KEY) === '1'; } catch (e) { /* storage blocked */ }
	if (wasOpen) { setOpen(true, false); }
})();

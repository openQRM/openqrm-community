function open_sshterm(url, title, left, top) {

	sshterm_window = window.open(url, title, "width=900,height=520,scrollbars=1,left=" + left + ",top=" + top);
	open_sshterm.focus();
}
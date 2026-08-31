(function () {
	'use strict';

	var config = window.YasyesShortlinkConfig || {};
	var listUrl = window.location.href.split('#')[0];

	var state = {
		search: '',
		page: 1,
		pages: 1,
		items: {},
		fingerprint: '',
		inflight: false,
		pendingDeleteId: 0
	};

	var els = {
		tbody: document.getElementById('link-tbody'),
		tableWrap: document.getElementById('table-wrap'),
		pagination: document.getElementById('pagination'),
		searchInput: document.getElementById('search-input'),
		btnCreate: document.getElementById('btn-create'),
		linkModal: document.getElementById('link-modal'),
		linkForm: document.getElementById('link-form'),
		formTitle: document.getElementById('form-title'),
		deleteModal: document.getElementById('delete-modal'),
		deleteCode: document.getElementById('delete-code'),
		deleteConfirm: document.getElementById('delete-confirm'),
		statActive: document.getElementById('stat-active'),
		statTotal: document.getElementById('stat-total'),
		statClicks: document.getElementById('stat-clicks'),
		liveDot: document.getElementById('live-dot'),
		toastEl: document.getElementById('ys-toast')
	};

	var isAppPage = Boolean(els.tbody && els.linkModal);

	if (!isAppPage) {
		initLogoutModal();
		initFeedback();
		return;
	}

	// ---- Util ----

	function escapeHtml(value) {
		var div = document.createElement('div');
		div.textContent = value == null ? '' : String(value);
		return div.innerHTML;
	}

	function formatNumber(value) {
		return new Intl.NumberFormat('id-ID').format(value);
	}

	function debounce(fn, wait) {
		var timer;
		return function () {
			clearTimeout(timer);
			timer = setTimeout(fn, wait);
		};
	}

	function toast(message) {
		if (!els.toastEl) return;
		els.toastEl.textContent = message;
		els.toastEl.classList.add('is-visible');
		clearTimeout(els.toastEl._timer);
		els.toastEl._timer = setTimeout(function () {
			els.toastEl.classList.remove('is-visible');
		}, 2200);
	}

	function post(action, data) {
		var body = new FormData();
		body.append('action', action);
		body.append('nonce', config.nonce);

		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});

		return fetch(config.ajaxUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin'
		}).then(function (response) {
			return response.json().catch(function () {
				throw new Error('Invalid response from server.');
			});
		}).then(function (json) {
			if (!json.success) {
				throw new Error(json.data && json.data.message ? json.data.message : 'An error occurred.');
			}
			return json.data;
		});
	}

	// ---- Modal umum ----

	function openOverlay(overlay) {
		overlay.hidden = false;
	}

	function closeOverlay(overlay) {
		if (overlay && !overlay.hidden) overlay.hidden = true;
	}

	function wireOverlay(overlay) {
		if (!overlay) return;

		overlay.addEventListener('mousedown', function (event) {
			if (event.target === overlay) overlay.hidden = true;
		});

		var closers = overlay.querySelectorAll('[data-modal-close]');
		Array.prototype.forEach.call(closers, function (button) {
			button.addEventListener('click', function () {
				overlay.hidden = true;
			});
		});
	}

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') return;

		closeOverlay(els.deleteModal);
		closeOverlay(els.linkModal);
		closeOverlay(document.getElementById('logout-modal'));
	});

	// ---- Render list ----

	function rowHtml(item) {
		var expiry = item.expiry_text ? escapeHtml(item.expiry_text) : '&mdash;';
		var badge = item.expired ? '<span class="badge badge-danger">expired</span>' : '';

		return '<tr data-id="' + item.id + '">' +
			'<td><a class="short-code" href="' + escapeHtml(item.short_url) + '" target="_blank" rel="noopener">/' +
				escapeHtml(item.short_code) + '</a>' + badge + '</td>' +
			'<td><span class="target-url" title="' + escapeHtml(item.original_url) + '">' +
				escapeHtml(item.original_url) + '</span></td>' +
			'<td class="col-num">' + formatNumber(item.clicks) + '</td>' +
			'<td>' + expiry + '</td>' +
			'<td class="col-actions">' +
				'<button type="button" class="btn-icon" data-copy="' + escapeHtml(item.short_url) + '" title="Copy link">' +
					'<span class="dashicons dashicons-admin-page"></span></button>' +
				'<button type="button" class="btn-icon" data-edit="' + item.id + '" title="Edit">' +
					'<span class="dashicons dashicons-edit"></span></button>' +
				'<button type="button" class="btn-icon btn-icon-danger" data-delete="' + item.id + '" title="Delete">' +
					'<span class="dashicons dashicons-trash"></span></button>' +
			'</td>' +
		'</tr>';
	}

	function fingerprintOf(data) {
		return JSON.stringify([
			data.total,
			data.pages,
			data.stats,
			data.items.map(function (item) {
				return [item.id, item.clicks, item.updated_at, item.short_code, item.original_url, item.expiry_raw];
			})
		]);
	}

	function renderList(data) {
		state.items = {};
		data.items.forEach(function (item) {
			state.items[item.id] = item;
		});

		els.tbody.innerHTML = data.items.length ? data.items.map(rowHtml).join('') : '';
		renderEmpty(data.items.length);
		renderPagination(data.page, data.pages);
		renderStats(data.stats);
	}

	function renderEmpty(count) {
		var existing = document.getElementById('empty-state');

		if (count) {
			if (existing) existing.remove();
			return;
		}
		if (existing) return;

		var empty = document.createElement('p');
		empty.className = 'muted empty';
		empty.id = 'empty-state';
		empty.textContent = state.search
			? 'No results found.'
			: 'No links yet. Click "Create link" to get started.';
		els.tableWrap.appendChild(empty);
	}

	function renderPagination(page, pages) {
		state.page = page;
		state.pages = pages;

		if (pages <= 1) {
			els.pagination.hidden = true;
			els.pagination.innerHTML = '';
			return;
		}

		els.pagination.hidden = false;

		var html = [];
		var range = 2;

		if (page > 1) html.push('<a href="#" data-page="' + (page - 1) + '">&lsaquo;</a>');

		for (var i = 1; i <= pages; i++) {
			if (1 === i || pages === i || Math.abs(i - page) <= range) {
				html.push(i === page
					? '<span class="is-current">' + i + '</span>'
					: '<a href="#" data-page="' + i + '">' + i + '</a>');
			} else if (Math.abs(i - page) === range + 1) {
				html.push('<span class="dots">&hellip;</span>');
			}
		}

		if (page < pages) html.push('<a href="#" data-page="' + (page + 1) + '">&rsaquo;</a>');
		els.pagination.innerHTML = html.join('');
	}

	function renderStats(stats) {
		els.statActive.textContent = formatNumber(stats.active);
		els.statTotal.textContent = formatNumber(stats.total_links);
		els.statClicks.textContent = formatNumber(stats.total_clicks);
	}

	// ---- Data ----

	function fetchList(options) {
		options = options || {};

		if (state.inflight) return Promise.resolve();
		state.inflight = true;

		if (!options.silent) document.body.classList.add('is-updating');

		return post('yasyes_shortlink_list', { s: state.search, paged: state.page })
			.then(function (data) {
				var fingerprint = fingerprintOf(data);

				if (options.silent && fingerprint === state.fingerprint) return;

				state.fingerprint = fingerprint;
				renderList(data);

				if (options.silent && els.liveDot) {
					els.liveDot.classList.add('is-blinking');
					setTimeout(function () {
						els.liveDot.classList.remove('is-blinking');
					}, 1200);
				}
			})
			.catch(function (error) {
				if (!options.silent) toast(error.message);
			})
			.finally(function () {
				state.inflight = false;
				document.body.classList.remove('is-updating');
			});
	}

	// ---- Modal buat/edit ----

	function openLinkModal(mode, id) {
		var isEdit = 'edit' === mode;
		var item = isEdit ? state.items[id] : null;

		if (isEdit && !item) return;

		state.pendingDeleteId = 0;
		els.formTitle.textContent = isEdit ? 'Edit link' : 'Create link';
		els.linkForm.elements.original_url.value = isEdit ? item.original_url : '';
		els.linkForm.elements.alias.value = isEdit ? item.short_code : '';
		els.linkForm.elements.expired_at.value = isEdit ? (item.expiry_raw || '') : '';
		els.linkForm.dataset.editId = isEdit ? id : '';

		openOverlay(els.linkModal);
		els.linkForm.elements.original_url.focus();
	}

	function saveFromForm(event) {
		event.preventDefault();

		var submitButton = els.linkForm.querySelector('[type="submit"]');
		submitButton.disabled = true;

		post('yasyes_shortlink_save', {
			link_id: els.linkForm.dataset.editId || 0,
			original_url: els.linkForm.elements.original_url.value,
			alias: els.linkForm.elements.alias.value,
			expired_at: els.linkForm.elements.expired_at.value
		}).then(function (data) {
			closeOverlay(els.linkModal);
			toast(data.message);
			return fetchList();
		}).catch(function (error) {
			toast(error.message);
		}).finally(function () {
			submitButton.disabled = false;
		});
	}

	// ---- Modal hapus ----

	function openDeleteModal(id) {
		var item = state.items[id];
		if (!item) return;

		state.pendingDeleteId = id;
		els.deleteCode.textContent = item.short_code;
		openOverlay(els.deleteModal);
	}

	function confirmDelete() {
		var id = state.pendingDeleteId;
		if (!id) return;

		els.deleteConfirm.disabled = true;

		post('yasyes_shortlink_delete', { link_id: id }).then(function (data) {
			closeOverlay(els.deleteModal);
			toast(data.message);

			if (Object.keys(state.items).length === 1 && state.page > 1) {
				state.page -= 1;
			}

			return fetchList();
		}).catch(function (error) {
			toast(error.message);
		}).finally(function () {
			els.deleteConfirm.disabled = false;
			state.pendingDeleteId = 0;
		});
	}

	// ---- Event wiring ----

	els.btnCreate.addEventListener('click', function () {
		openLinkModal('create');
	});

	wireOverlay(els.linkModal);
	wireOverlay(els.deleteModal);
	wireOverlay(document.getElementById('logout-modal'));

	initFeedback();

	els.linkForm.addEventListener('submit', saveFromForm);
	els.deleteConfirm.addEventListener('click', confirmDelete);

	document.getElementById('search-form').addEventListener('submit', function (event) {
		event.preventDefault();
		submitSearch();
	});

	els.searchInput.addEventListener('input', debounce(submitSearch, 350));

	function submitSearch() {
		var next = els.searchInput.value.trim();

		if (next === state.search) return;

		state.search = next;
		state.page = 1;
		fetchList();
	}

	els.pagination.addEventListener('click', function (event) {
		var link = event.target.closest('a[data-page]');
		if (!link) return;

		event.preventDefault();
		state.page = parseInt(link.getAttribute('data-page'), 10);
		fetchList();
	});

	els.tbody.addEventListener('click', function (event) {
		var copyButton = event.target.closest('[data-copy]');
		if (copyButton) {
			navigator.clipboard.writeText(copyButton.getAttribute('data-copy'))
				.then(function () { toast('Link copied'); });
			return;
		}

		var editButton = event.target.closest('[data-edit]');
		if (editButton) {
			openLinkModal('edit', parseInt(editButton.getAttribute('data-edit'), 10));
			return;
		}

		var deleteButton = event.target.closest('[data-delete]');
		if (deleteButton) {
			openDeleteModal(parseInt(deleteButton.getAttribute('data-delete'), 10));
		}
	});

	function initLogoutModal() {
		var link = document.getElementById('logout-link');
		var logoutModal = document.getElementById('logout-modal');
		if (!link || !logoutModal) return;

		var cancelButton = document.getElementById('logout-cancel');

		link.addEventListener('click', function (event) {
			event.preventDefault();
			logoutModal.hidden = false;
		});

		cancelButton.addEventListener('click', function () {
			logoutModal.hidden = true;
		});

		wireOverlay(logoutModal);
	}

	function initFeedback() {
		var btn = document.getElementById('btn-feedback');
		var modal = document.getElementById('feedback-modal');
		var form = document.getElementById('feedback-form');
		if (!btn || !modal || !form) return;

		btn.addEventListener('click', function () {
			modal.hidden = false;
		});

		wireOverlay(modal);

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var submitBtn = form.querySelector('[type="submit"]');
			var message = document.getElementById('feedback-message').value.trim();

			if (message.length < 10) {
				toast('Message must be at least 10 characters.');
				return;
			}

			submitBtn.disabled = true;

			post('yasyes_shortlink_feedback', { message: message })
				.then(function (data) {
					modal.hidden = true;
					form.reset();
					toast(data.message);
				})
				.catch(function (error) {
					toast(error.message);
				})
				.finally(function () {
					submitBtn.disabled = false;
				});
		});
	}

	// ---- Realtime polling ----

	setInterval(function () {
		if (document.hidden) return;
		fetchList({ silent: true });
	}, config.pollMs || 5000);

	fetchList();
})();

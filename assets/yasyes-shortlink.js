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
		pendingDeleteId: 0,
		qr: null,
		qrShortCode: ''
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
		qrModal: document.getElementById('qr-modal'),
		qrCode: document.getElementById('qr-code'),
		qrCanvasWrap: document.getElementById('qr-canvas-wrap'),
		qrDownload: document.getElementById('qr-download'),
		statActive: document.getElementById('stat-active'),
		statTotal: document.getElementById('stat-total'),
		statClicks: document.getElementById('stat-clicks'),
		liveDot: document.getElementById('live-dot'),
		toastHost: document.getElementById('ys-toasts')
	};

	var isAppPage = Boolean(els.tbody && els.linkModal);

	var TOAST_DURATION = { success: 2800, info: 2800, error: 5000 };
	var TOAST_LIMIT = 3;

	flushServerNotices();

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

	function toast(message, type) {
		if (!els.toastHost || !message) {
			return;
		}

		type = type === 'success' || type === 'error' ? type : 'info';

		var el = document.createElement('div');
		el.className = 'toast toast-' + type;
		el.setAttribute('role', type === 'error' ? 'alert' : 'status');
		el.textContent = message;
		els.toastHost.appendChild(el);

		while (els.toastHost.children.length > TOAST_LIMIT) {
			els.toastHost.removeChild(els.toastHost.firstElementChild);
		}

		requestAnimationFrame(function () {
			el.classList.add('is-visible');
		});

		setTimeout(function () {
			el.classList.remove('is-visible');
			setTimeout(function () {
				if (el.parentNode) {
					el.parentNode.removeChild(el);
				}
			}, 200);
		}, TOAST_DURATION[type]);
	}

	function flushServerNotices() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-ys-notice]'), function (node) {
			toast(node.textContent, node.getAttribute('data-ys-notice'));
			node.parentNode.removeChild(node);
		});
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
		closeOverlay(els.qrModal);
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
				'<button type="button" class="btn-icon" data-qr="' + item.id + '" title="QR Code">' +
					'<span class="dashicons dashicons-qrcode"></span></button>' +
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
				if (!options.silent) toast(error.message, 'error');
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
		els.linkForm.elements.action_type.value = isEdit ? 'update' : 'create';
		els.linkForm.elements.link_id.value = isEdit ? id : '';

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
			toast(data.message, 'success');
			return fetchList();
		}).catch(function (error) {
			toast(error.message, 'error');
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
			toast(data.message, 'success');

			if (Object.keys(state.items).length === 1 && state.page > 1) {
				state.page -= 1;
			}

			return fetchList();
		}).catch(function (error) {
			toast(error.message, 'error');
		}).finally(function () {
			els.deleteConfirm.disabled = false;
			state.pendingDeleteId = 0;
		});
	}

	// ---- Modal QR ----

	var qrAvailable = typeof qrcode === 'function';

	if (qrAvailable) {
		qrcode.stringToBytes = qrcode.stringToBytesFuncs['UTF-8'];
	}

	var QR_CELL_DISPLAY = 6;
	var QR_CELL_EXPORT = 12;

	function openQrModal(id) {
		if (!qrAvailable) {
			toast('QR generator failed to load.', 'error');
			return;
		}

		var item = state.items[id];
		if (!item) return;

		var qr = qrcode(0, 'M');
		qr.addData(item.short_url);
		qr.make();

		state.qr = qr;
		state.qrShortCode = item.short_code;
		els.qrCode.textContent = item.short_code;
		renderQrCanvas(qr, els.qrCanvasWrap, QR_CELL_DISPLAY);

		openOverlay(els.qrModal);
	}

	function renderQrCanvas(qr, target, cellSize) {
		var count = qr.getModuleCount();
		var canvas = document.createElement('canvas');
		canvas.width = count * cellSize;
		canvas.height = count * cellSize;

		var ctx = canvas.getContext('2d');
		ctx.fillStyle = '#fff';
		ctx.fillRect(0, 0, canvas.width, canvas.height);
		ctx.fillStyle = '#000';

		for (var row = 0; row < count; row++) {
			for (var col = 0; col < count; col++) {
				if (qr.isDark(row, col)) {
					ctx.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
				}
			}
		}

		target.innerHTML = '';
		target.appendChild(canvas);
	}

	function downloadQrPng() {
		if (!state.qr || !state.qrShortCode) return;

		var exportWrap = document.createElement('div');
		renderQrCanvas(state.qr, exportWrap, QR_CELL_EXPORT);
		var canvas = exportWrap.querySelector('canvas');

		if (!canvas) {
			toast('QR generator failed to render.', 'error');
			return;
		}

		try {
			if (typeof canvas.toBlob === 'function') {
				canvas.toBlob(function (blob) {
					if (!blob) {
						toast('Failed to generate QR image.', 'error');
						return;
					}
					var link = document.createElement('a');
					var url = URL.createObjectURL(blob);
					link.href = url;
					link.download = 'qr-' + state.qrShortCode + '.png';
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
					URL.revokeObjectURL(url);
				}, 'image/png');
				return;
			}

			var dataUrl = canvas.toDataURL('image/png');
			var link = document.createElement('a');
			link.href = dataUrl;
			link.download = 'qr-' + state.qrShortCode + '.png';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		} catch (e) {
			toast('Failed to download QR.', 'error');
		}
	}

	// ---- Event wiring ----

	els.btnCreate.addEventListener('click', function () {
		openLinkModal('create');
	});

	wireOverlay(els.linkModal);
	wireOverlay(els.deleteModal);
	wireOverlay(els.qrModal);

	initLogoutModal();
	initFeedback();

	els.linkForm.addEventListener('submit', saveFromForm);
	els.deleteConfirm.addEventListener('click', confirmDelete);
	els.qrDownload.addEventListener('click', downloadQrPng);

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
				.then(function () { toast('Link copied', 'success'); });
			return;
		}

		var qrButton = event.target.closest('[data-qr]');
		if (qrButton) {
			openQrModal(parseInt(qrButton.getAttribute('data-qr'), 10));
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
				toast('Message must be at least 10 characters.', 'error');
				return;
			}

			submitBtn.disabled = true;

			post('yasyes_shortlink_feedback', { message: message })
				.then(function (data) {
					modal.hidden = true;
					form.reset();
					toast(data.message, 'success');
				})
				.catch(function (error) {
					toast(error.message, 'error');
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

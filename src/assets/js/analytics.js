/**
 *
 * Analytics (JS)
 *
 * @author Takuto Yanagida
 * @version 2022-08-24
 *
 */


function wpinc_socio_analytics_initialize(args) {
	const LS_KEY = 'wpinc-socio-analytics';
	const CFM_OK = 'ok';
	const CFM_NO = 'no';

	const tagId    = args['tag_id']    ?? '';
	const limitDay = args['limit_day'] ?? 7;
	const idDialog = args['id_dialog'] ?? 'wpinc-socio-analytics-dialog';
	const idAccept = args['id_accept'] ?? 'wpinc-socio-analytics-accept';
	const idReject = args['id_reject'] ?? 'wpinc-socio-analytics-reject';

	initTag(tagId);

	onLoad(() => {
		const dlg = document.getElementById(idDialog) ?? createDialog(idDialog, idAccept, idReject);
		dlg.setAttribute('hidden', '');

		const cfm = getState(LS_KEY, tagId, limitDay);
		if (null !== cfm) {
			if (CFM_OK === cfm) {
				setEnabled();
			}
		} else {
			dlg.removeAttribute('hidden');
			const okBtn = document.getElementById(idAccept);
			const noBtn = document.getElementById(idReject);

			if (okBtn) {
				okBtn.addEventListener('click', () => {
					dlg.setAttribute('hidden', '');
					setState(LS_KEY, tagId, CFM_OK);
					setEnabled();
				});
			}
			if (noBtn) {
				noBtn.addEventListener('click', () => {
					dlg.setAttribute('hidden', '');
					setState(LS_KEY, tagId, CFM_NO);
					removeCookie();
				});
			}
		}
	});


	// -------------------------------------------------------------------------


	function onLoad(fn) {
		if ('loading' === document.readyState) {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			setTimeout(fn, 0);
		}
	}

	function createDialog(idDialog, idAccept, idReject) {
		const dlg = document.createElement('div');
		dlg.setAttribute('id', idDialog);
		dlg.setAttribute('hidden', '');
		dlg.setAttribute('style', `position:fixed;inset:auto 1rem 1rem 1rem;z-index:99999;padding:1rem;background-color:#fff;box-shadow:0 0.25rem 0.5rem #0009;border-radius:0.5rem;`)
		dlg.innerHTML =
			`<div>
				<p>We use cookies to improve our website for users' usability and experience.</p>
				<p>To accept cookies, click 'Accept'.</p>
			</div>
			<div style="margin-top:1rem;text-align:right;">
				<button id="${idAccept}">Accept</button>&nbsp;
				<button id="${idReject}">Reject</button>
			</div>`;
		document.body.appendChild(dlg);
		return dlg;
	}


	// -------------------------------------------------------------------------


	function getState(lsKey, tagId, limitDay) {
		const day = (1000 * 60 * 60 * 24);
		const it  = localStorage.getItem(lsKey);
		try {
			const jd = JSON.parse(it);
			if (jd) {
				const r   = jd[tagId] ?? {};
				const cfm = r['cfm'] ?? null;
				const utc = r['utc'] ?? null;
				if (cfm && utc && Date.now() - utc < limitDay * day) {
					return cfm;
				}
			}
		} catch (e) {
		}
		return null;
	}

	function setState(lsKey, tagId, cfm) {
		const it = localStorage.getItem(lsKey);

		let jd = null;
		try {
			jd = JSON.parse(it);
		} catch (e) {
		}
		if (!jd) jd = {};

		if (null === cfm) {
			delete jd[tagId];
			if (Object.keys(jd).length) {
				localStorage.setItem(lsKey, JSON.stringify(jd));
			} else {
				localStorage.removeItem(lsKey);
			}
		} else {
			jd[tagId] = { cfm, utc: Date.now() };
			localStorage.setItem(lsKey, JSON.stringify(jd));
		}
	}


	// -------------------------------------------------------------------------


	function initTag(tagId) {
		window.dataLayer = window.dataLayer || [];
		function gtag() { window.dataLayer.push(arguments); }
		gtag('consent', 'default', { analytics_storage: 'denied' });
		gtag('consent', 'default', { ad_storage: 'denied' });
		gtag('set', 'ads_data_redaction', true);
		gtag('js', new Date());
		gtag('config', tagId);
	}

	function setEnabled() {
		function gtag() { window.dataLayer.push(arguments); }
		gtag('consent', 'update', { analytics_storage: 'granted' });
	}

	function removeCookie() {
		let domain = location.hostname;
		while (true) {
			const orig = document.cookie;
			const cs   = orig.split(';')
			for (const c of cs) {
				const p = c.indexOf('=')
				const n = (-1 < p) ? c.substring(0, p).trim() : c.trim();
				if ('_ga' === n || '_gid' === n || n.startsWith('_ga_')) {
					document.cookie = `${n}=;max-age=0;domain=${domain}`;
				}
			}
			if (orig !== document.cookie) break;
			const p = domain.indexOf('.');
			if (-1 === p) break;
			domain = domain.substring(p + 1);
		}
	}
}

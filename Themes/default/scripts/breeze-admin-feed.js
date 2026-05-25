((() => {

	const feedURL = window.breezeFeedUrl ?? '';
	const errorMessage = window.breezeFeedError ?? '';
	const app = document.querySelector('#smfAnnouncements');

	if (!app || !feedURL) {
		return;
	}

	app.textContent = errorMessage;

	fetch(feedURL)
		.then((response) => response.json())
		.then((data) => {
			addReleases(data, app);
		})
		.catch(() => {
			app.textContent = errorMessage;
		});

	function addReleases(releases, container) {
		const dl = document.createElement('dl');
		container.innerHTML = '';

		Object.entries(releases).slice(0, 5).forEach(([, release]) => {
			const dt = document.createElement('dt');
			const dd = document.createElement('dd');
			const anchor = document.createElement('a');
			const date = new Date(release.published_at);

			anchor.textContent = release.name;
			anchor.href = release.html_url;
			dt.appendChild(anchor);
			dt.appendChild(document.createTextNode(' ' + date.toLocaleString('en-US')));
			dd.textContent = release.body;
			dl.appendChild(dt);
			dl.appendChild(dd);
		});

		container.appendChild(dl);
	}
})());

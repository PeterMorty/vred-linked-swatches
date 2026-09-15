(function () {
	'use strict';

	var sections = [
		{
			id: 'requirements',
			title: 'Requisitos',
			key: 'requirements',
			description: 'Entorno y dependencias del plugin.',
			wide: true
		},
		{
			id: 'settings',
			title: 'Configuración',
			key: 'settings',
			description: '',
			wide: true
		},
		{
			id: 'features',
			title: 'Funciones',
			key: 'features',
			description: 'Uso y presentación de los swatches.',
			wide: true
		},
		{
			id: 'integrations',
			title: 'Integraciones',
			key: 'integrations',
			description: 'Conexiones con otros plugins.',
			wide: true
		},
		{
			id: 'technical',
			title: 'Referencia técnica',
			key: 'technical',
			description: 'Metadatos, hooks y distribución de la documentación.',
			wide: true
		}
	];

	var state = {
		data: null,
		query: '',
		observer: null
	};

	var els = {
		description: document.getElementById('docs-description'),
		summary: document.getElementById('docs-summary'),
		status: document.getElementById('docs-status'),
		nav: document.getElementById('section-nav'),
		sections: document.getElementById('docs-sections'),
		search: document.getElementById('docs-search')
	};

	function setText(el, text) {
		el.textContent = text || '';
	}

	function showStatus(message) {
		setText(els.status, message);
		els.status.classList.toggle('is-visible', Boolean(message));
	}

	function normalize(value) {
		return String(value || '')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '');
	}

	function getItems(data, section) {
		if (section.key === 'settings') {
			return data.settings && Array.isArray(data.settings.tabs) ? data.settings.tabs : [];
		}

		return Array.isArray(data[section.key]) ? data[section.key] : [];
	}

	function compactList(value) {
		if (!Array.isArray(value)) {
			return '';
		}

		return value.filter(Boolean).slice(0, 5).join(', ');
	}

	function getDescription(item) {
		return item.shortDescription || item.description || item.answer || '';
	}

	function getTitle(item) {
		return item.title || item.question || item.id || 'Elemento';
	}

	function flattenValue(value, output) {
		if (value === null || typeof value === 'undefined') {
			return;
		}

		if (Array.isArray(value)) {
			value.forEach(function (item) {
				flattenValue(item, output);
			});
			return;
		}

		if (typeof value === 'object') {
			Object.keys(value).forEach(function (key) {
				output.push(key);
				flattenValue(value[key], output);
			});
			return;
		}

		output.push(String(value));
	}

	function flattenText(value) {
		var output = [];
		flattenValue(value, output);
		return output.join(' ');
	}

	function getSearchText(item) {
		return normalize([
			flattenText(item),
			getTitle(item),
			getDescription(item),
			item.group,
			item.type,
			compactList(item.requirements),
			compactList(item.whereToConfigure),
			compactList(item.options),
			compactList(item.mainOptions),
			compactList(item.items),
			flattenText(item.features),
			flattenText(item.useCases),
			flattenText(item.limitations),
			flattenText(item.tips),
			flattenText(item.technical)
		].join(' '));
	}

	function createEl(tag, className, text) {
		var el = document.createElement(tag);

		if (className) {
			el.className = className;
		}

		if (text) {
			el.textContent = text;
		}

		return el;
	}

	function createList(items) {
		var list = createEl('ul', 'list');

		items.filter(Boolean).forEach(function (item) {
			list.appendChild(createEl('li', '', item));
		});

		return list;
	}

	function createChips(items) {
		var chips = createEl('div', 'chips');

		items.filter(Boolean).forEach(function (item) {
			chips.appendChild(createEl('span', 'pill', item));
		});

		return chips;
	}

	function createDetail(title, children) {
		var detail = createEl('div', 'detail stack');
		var heading = createEl('h4', '', title);

		detail.appendChild(heading);
		children.forEach(function (child) {
			if (child) {
				detail.appendChild(child);
			}
		});

		return detail;
	}

	function addListDetail(stack, label, items, asChips) {
		if (!Array.isArray(items) || !items.filter(Boolean).length) {
			return;
		}

		stack.appendChild(createDetail(label, [
			asChips ? createChips(items) : createList(items)
		]));
	}

	function createFeature(feature) {
		var children = [];

		if (feature.description) {
			children.push(createEl('p', 'muted', feature.description));
		}

		if (Array.isArray(feature.options) && feature.options.length) {
			children.push(createChips(feature.options));
		}

		if (Array.isArray(feature.notes) && feature.notes.length) {
			children.push(createList(feature.notes));
		}

		return createDetail(feature.title || 'Función', children);
	}

	function addFeatures(stack, features) {
		if (!Array.isArray(features) || !features.length) {
			return;
		}

		var wrapper = createEl('div', 'stack');

		features.forEach(function (feature) {
			wrapper.appendChild(createFeature(feature));
		});

		stack.appendChild(createDetail('Funciones', [wrapper]));
	}

	function createTechnicalDetails(technical) {
		if (!technical || typeof technical !== 'object' || !Object.keys(technical).length) {
			return null;
		}

		var details = createEl('details', 'detail');
		var summary = createEl('summary', '', 'Datos técnicos');
		var pre = document.createElement('pre');

		pre.textContent = JSON.stringify(technical, null, 2);
		details.append(summary, pre);
		return details;
	}

	function createCard(item, section) {
		var article = createEl('article', 'card');
		var header = createEl('div', 'stack');
		var eyebrow = createEl('p', 'eyebrow', item.group || item.type || section.title);
		var title = createEl('h3', '', getTitle(item));
		var description = createEl('p', '', getDescription(item));
		var details = createEl('div', 'stack');
		var technical = createTechnicalDetails(item.technical);

		article.dataset.search = getSearchText(item);
		header.append(eyebrow, title);

		if (description) {
			description.className = 'muted';
			header.appendChild(description);
		}

		addFeatures(details, item.features);
		addListDetail(details, 'Configurar', item.whereToConfigure);
		addListDetail(details, 'Requisitos', item.requirements, true);
		addListDetail(details, 'Casos de uso', item.useCases);
		addListDetail(details, 'Notas de uso', item.tips);
		addListDetail(details, 'Limitaciones', item.limitations);
		addListDetail(details, 'Notas', item.notes);
		addListDetail(details, 'Opciones', item.options || item.mainOptions, true);
		addListDetail(details, 'Items', item.items);

		if (typeof item.optional === 'boolean') {
			header.appendChild(createEl('span', 'pill', item.optional ? 'Opcional' : 'Obligatorio'));
		}

		if (technical) {
			details.appendChild(technical);
		}

		article.appendChild(header);

		if (details.children.length) {
			article.appendChild(details);
		}

		return article;
	}

	function createSection(section, items) {
		var sectionEl = createEl('section', 'docs-section');
		var heading = createEl('div', 'section-heading');
		var text = document.createElement('div');
		var title = createEl('h2', '', section.title);
		var description = createEl('p', '', section.description);
		var count = createEl('span', 'pill', items.length + ' elementos');
		var grid = createEl('div', 'grid');

		sectionEl.id = section.id;
		sectionEl.dataset.section = section.id;
		if (section.wide) {
			sectionEl.classList.add('is-wide');
		}
		text.append(title, description);
		heading.append(text, count);
		sectionEl.appendChild(heading);

		items.forEach(function (item) {
			grid.appendChild(createCard(item, section));
		});

		sectionEl.appendChild(grid);
		return sectionEl;
	}

	function renderNav(data) {
		els.nav.replaceChildren();

		sections.forEach(function (section) {
			var items = getItems(data, section);
			var li = document.createElement('li');
			var link = document.createElement('a');
			var label = createEl('span', '', section.title);
			var count = createEl('span', 'nav-count', String(items.length));

			link.href = '#' + section.id;
			link.dataset.section = section.id;
			link.append(label, count);
			li.appendChild(link);
			els.nav.appendChild(li);
		});
	}

	function setActiveNav(sectionId) {
		els.nav.querySelectorAll('a').forEach(function (link) {
			link.classList.toggle('is-active', link.dataset.section === sectionId);
		});
	}

	function getActiveSectionId(sectionEls) {
		var marker = window.innerHeight * 0.28;
		var activeSection = sectionEls[0];
		var closestDistance = Infinity;

		sectionEls.forEach(function (sectionEl) {
			var rect = sectionEl.getBoundingClientRect();
			var distance = Math.abs(rect.top - marker);

			if (sectionEl.hidden) {
				return;
			}

			if (rect.top <= marker && rect.bottom >= marker) {
				activeSection = sectionEl;
				return;
			}

			if (distance < closestDistance && rect.bottom > 0) {
				closestDistance = distance;
				activeSection = sectionEl;
			}
		});

		return activeSection ? activeSection.id : '';
	}

	function setupSectionObserver() {
		var sectionEls = Array.prototype.slice.call(document.querySelectorAll('.docs-section'));

		if (state.observer) {
			state.observer.disconnect();
		}

		if (!sectionEls.length) {
			return;
		}

		setActiveNav(sectionEls[0].id);

		if (!('IntersectionObserver' in window)) {
			return;
		}

		state.observer = new IntersectionObserver(function (entries) {
			if (entries.some(function (entry) {
				return entry.isIntersecting;
			})) {
				setActiveNav(getActiveSectionId(sectionEls));
			}
		}, {
			rootMargin: '0px 0px -55% 0px',
			threshold: [0, 0.1, 0.25, 0.5]
		});

		sectionEls.forEach(function (sectionEl) {
			state.observer.observe(sectionEl);
		});
	}

	function renderSummary(data) {
		els.summary.replaceChildren();

		sections.forEach(function (section) {
			var items = getItems(data, section);
			els.summary.appendChild(createEl('span', 'pill', section.title + ': ' + items.length));
		});
	}

	function renderSections(data) {
		els.sections.replaceChildren();

		sections.forEach(function (section) {
			els.sections.appendChild(createSection(section, getItems(data, section)));
		});
	}

	function filterCards() {
		var query = normalize(state.query);
		var visibleCards = 0;

		document.querySelectorAll('.docs-section').forEach(function (sectionEl) {
			var visibleInSection = 0;

			sectionEl.querySelectorAll('.card').forEach(function (card) {
				var visible = !query || card.dataset.search.indexOf(query) !== -1;
				card.hidden = !visible;

				if (visible) {
					visibleCards += 1;
					visibleInSection += 1;
				}
			});

			sectionEl.hidden = visibleInSection === 0;
		});

		showStatus(query && visibleCards === 0 ? 'No se encontraron resultados para esa búsqueda.' : '');
	}

	function render(data) {
		state.data = data;
		if (data.meta && data.meta.name) {
			document.title = data.meta.name + ' Docs';
			setText(document.querySelector('h1'), data.meta.name);
			setText(document.getElementById('docs-title'), 'Documentación de uso de ' + data.meta.name);
		}
		sections[1].description = data.settings && data.settings.description || '';
		setText(els.description, data.meta && data.meta.description ? data.meta.description : 'Documentación pública de VRED Linked Swatches.');
		renderNav(data);
		renderSummary(data);
		renderSections(data);
		setupSectionObserver();
		filterCards();
	}

	function loadDocs() {
		var dataUrl = 'data/vred-linked-swatches.json?v=' + Date.now();

		fetch(dataUrl, { cache: 'no-store' })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('No se pudo cargar el JSON.');
				}

				return response.json();
			})
			.then(render)
			.catch(function () {
				showStatus('No se pudo cargar data/vred-linked-swatches.json. Abre esta página desde un servidor local para permitir fetch.');
				setText(els.description, 'La estructura de documentación está lista, pero el contenido no se pudo cargar.');
			});
	}

	els.search.addEventListener('input', function (event) {
		state.query = event.target.value;
		filterCards();
	});

	els.search.form.addEventListener('submit', function (event) {
		event.preventDefault();
	});

	loadDocs();
}());

(function () {
	var body = document.getElementById('rcs-fields-body');
	var template = document.getElementById('rcs-field-template');
	var addButton = document.getElementById('rcs-add-field');

	if (!body || !template || !addButton) {
		return;
	}

	function nextIndex() {
		var indexes = body.querySelectorAll('[name^="rcs_fields["]');
		var max = -1;

		indexes.forEach(function (element) {
			var match = element.getAttribute('name').match(/rcs_fields\[(\d+)\]/);

			if (match && parseInt(match[1], 10) > max) {
				max = parseInt(match[1], 10);
			}
		});

		return max + 1;
	}

	function syncOptionsVisibility(row) {
		var typeSelect = row.querySelector('.rcs-field-type');
		var optionsCell = row.querySelector('textarea[name$="[options]"]');

		if (!typeSelect || !optionsCell) {
			return;
		}

		var type = typeSelect.value;
		var usesOptions = type === 'select' || type === 'radio' || type === 'checkbox';

		optionsCell.disabled = !usesOptions;
		optionsCell.closest('td').style.opacity = usesOptions ? '1' : '0.4';
	}

	function refresh() {
		body.querySelectorAll('.rcs-field-row').forEach(syncOptionsVisibility);
	}

	addButton.addEventListener('click', function () {
		var index = nextIndex();
		var fragment = template.content.cloneNode(true);
		var row = fragment.querySelector('.rcs-field-row');

		row.querySelectorAll('[name*="__INDEX__"]').forEach(function (element) {
			element.setAttribute('name', element.getAttribute('name').replace(/__INDEX__/g, String(index)));
		});

		row.querySelectorAll('[value*="__INDEX__"]').forEach(function (element) {
			element.setAttribute('value', element.getAttribute('value').replace(/__INDEX__/g, String(index)));
		});

		body.appendChild(fragment);

		refresh();
	});

	body.addEventListener('click', function (event) {
		var button = event.target.closest('.rcs-remove-field');

		if (!button) {
			return;
		}

		event.preventDefault();

		var row = button.closest('.rcs-field-row');

		if (row) {
			row.parentNode.removeChild(row);
		}
	});

	body.addEventListener('change', function (event) {
		if (event.target.classList.contains('rcs-field-type')) {
			syncOptionsVisibility(event.target.closest('.rcs-field-row'));
		}
	});

	refresh();
})();

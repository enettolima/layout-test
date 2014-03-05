var newHire = {
	init: function() {
		var i = 1;
		$$('a[class=date_select]').each(function(e) {
			var dateSelects = {
				'date' : null,
				'month': null,
				'year': null };
				
			var j = 0;
			var selects = e.getParent().getElements('select');
			for(var key in dateSelects) {
				if (selects[j].id == '')
					selects[j].id = 'datesel_' + key + '_' + i;
					
				dateSelects[key] = selects[j++];
			}
				
			if (e.id == '')
				e.id = 'datesel_link_' + i;
			
			new Element('div', {'id': 'calendar_' + i }).injectAfter(e);
			new Calendar(
				'calendar_' + i, 
				e.id, {
					'idPrefix': 'cal' + i + '_',
					'startDate': new Date().fromString('year-100'),
					'endDate': new Date().fromString('month+1'),
					'allowSelection': true,
					'allowWeekendSelection': true,
					'inputField': dateSelects,
					'inputType': 'select'
				}
			);
				
			i++;
		});
	}
};

window.onDomReady(newHire.init.bind(newHire));
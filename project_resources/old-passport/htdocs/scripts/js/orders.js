var orders = {
	sliders: {},
	init: function() {
		var form = $('items_form');
		if ($defined(form)) {
			form.onsubmit = orders.itemsOnSubmit;
			
			$each(form.getElements('a.delete_button'), function (e) {
				e.onclick = orders.deleteOnClick;
			});
			
			$each(form.getElements('a.comment_button'), function (e) {
				e.onclick = orders.commentOnClick;
			});
		}
		
		$$('tr.comment_entry').each(function(e) {
			var item = e.getElement('input').getProperty('name').toInt();
			
			orders.sliders[item] = new Fx.Slide(e.getElement('div'));
			orders.sliders[item].hide();
		});
	}, 
	itemsOnSubmit: function() {
		var submitItemsAjax = new Ajax('/orders/update/order/' + orderId + '?format=html', {
			method: 'post',
			data: $('items_form'),
			update: $('order-data'),
			onComplete: function() {
				setLoading(false);
				orders.init();
			}
		} );
		
		setLoading(true);
		submitItemsAjax.request();
		return false;
	},
	deleteOnClick: function() {
		var item = orders.parseItem(this.href);
		var deleteItemAjax = new Ajax('/orders/delete-item/order/' + orderId + '/item/' + item + '/?format=html', {
			method: 'get',
			update: $('order-data'),
			onComplete: function() {
				setLoading(false);
				orders.init();
			}
		} );
		
		setLoading(true);
		deleteItemAjax.request();
		return false;
	},
	commentOnClick: function(e) {
		e = new Event(e);
		
		var item = orders.parseItem(this.href);
		var row = $(this).getParent().getParent();
		var nextRow = row.getNext();
		
		if (!$defined(nextRow) || !nextRow.hasClass('comment_entry')) {
			var div = new Element('div')
				.injectInside(new Element('td', { 'colspan': row.getChildren().length })
					.injectInside(new Element('tr', {'class': 'comment_entry' })
						.injectAfter(row)));
			
			div.adopt(new Element('label').appendText('Comment: '))
				.adopt(new Element('input', {
					'type': 'text',
					'name': item + '_comment' }));
					
			orders.sliders[item] = new Fx.Slide(div);
			orders.sliders[item].hide();
		}
		
		orders.sliders[item].toggle();
		e.stop();
	},
	parseItem: function(link) {
		var i = link.length - 1;
		for(; i >= 0 && link.charAt(i) != '/'; --i);
		
		return parseInt(link.substr(i + 1, link.length - i));
	}
};

window.onDomReady(orders.init.bind(orders));
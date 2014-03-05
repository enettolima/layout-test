var search = {
	init: function() {
		$$('div.order_menu').each(function(e) {
			e.getElement('form').onsubmit = search.onOrder;
		});
		
		// Handle navigation
		search.closeAll($('browse_box'));
		$('browse_box').getElements('h1').each(function(e) {
			var next = e.getNext();		
			e.onclick = search.openBrowse;
			
			if (next.tagName == 'UL') {
				next.getElements('h1').each(function(e) {
					e.onclick = search.openBrowse;
				});
			}
		});
	},
	closeAll: function(p) {
		p.getElements('h1').each(function(e) {
			var next = e.getNext();
			if (next.tagName == 'UL') {
				next.setStyle('display', 'none');
			}
		});
	},
	openBrowse: function() {
		var e = $(this);
		var next = e.getNext();
		search.closeAll(e.getParent().getParent());
		
		if (next.tagName == 'UL' && next.getStyle('display') == 'none') {
			next.setStyle('display', 'block');
			search.closeAll(next);
		}
	},
	onOrder: function() {
		var form = $(this);
		var action = form.getProperty('action');
		var orderAjax = new Ajax(action, {
			method: 'post',
			data: form,
			update: $('cart'),
			onComplete: function(request) {
				setLoading(false);
				cart.refreshEvents();
			}
		} );
		
		setLoading(true);
		orderAjax.request();
		return false;
	},
	orderComplete: function(request) {
		alert(request);
	}
};

window.onDomReady(search.init.bind(search));
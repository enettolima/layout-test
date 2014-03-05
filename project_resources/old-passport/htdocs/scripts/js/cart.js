var cart = {
	init: function() {
		cart.refreshEvents();
	},
	refreshEvents: function() {
		$$('a.cart_delete_button').each(function(el) {
			el.onclick = function() {
				var item = cart.parseItem(this.href);
				cart.deleteItem(item);
			
				return false;
			}
		});
		
		$$('a.cart_clear_button').each(function(el) {
			el.onclick = cart.clear;
		});
		
		var save_btn = $('cart_save_button');
		//var order_btn = $('cart_order_button');
		
		//if (order_btn != null)
		//	order_btn.onclick = cart.order;
			
		if (save_btn != null)
			save_btn.onclick = cart.save;
	},
	deleteItem: function(item_no) {
		var deleteAjax = new Ajax('/cart/delete/item/' + item_no, {
			method: 'get',
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		deleteAjax.request();
		return false;
	},
	addItem: function(item_no) {
		var addAjax = new Ajax('/cart/add/item/' + item_no, {
			method: 'get',
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		addAjax.request();
		return false;
	},
	clear: function() {
		var clearAjax = new Ajax('/cart/clear', {
			method: 'get',
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		clearAjax.request();
		return false;
	},
	save: function() {
		var form = $('cart').getElement('form');
		var orderAjax = new Ajax('/cart/save', {
			method: 'post',
			data: form,
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		orderAjax.request();
		return false;
	},
	order: function() {
		var form = $('cart').getElement('form');
		var orderAjax = new Ajax('/orders/add', {
			method: 'post',
			data: form,
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		orderAjax.request();
		return false;
	},
	refresh: function() {
		var refreshAjax = new Ajax('/cart', {
			method: 'get',
			update: $('cart'),
			onComplete: cart.finishLoading
		} );
		
		setLoading(true);
		refreshAjax.request();
		return false;
	},
	finishLoading: function() {
		setLoading(false);
		cart.refreshEvents();
	},
	parseItem: function(link) {
		var i = link.length - 1;
		for(; i >= 0 && link.charAt(i) != '/'; --i);
		
		return parseInt(link.substr(i + 1, link.length - i));
	}
};

window.onDomReady(cart.init.bind(cart));
var tabBox = {
    init: function() {
        $$('div.tabbed-box').each(function(tabRoot) {
            var headers = tabRoot.getElements('ul.tab-label li');
            
            $each(tabRoot.getElements('div.tab'), function (e, i) {
                headers[i].set( { 
                    'events': { 
                        'click': function() { 
                            tabBox.showTab(e);
                        } 
                    } 
                } );
                    
                if (i == 0)
                    tabBox.showTab(e);
            } );
        } );
    },
    showTab: function(tabElem) {
        var tabRoot = tabElem;
        
        while (!tabRoot.hasClass('tabbed-box'))
            if (!$defined(tabRoot = tabRoot.getParent()))
                return;
        
        var headers = tabRoot.getElements('ul.tab-label li');
        if (!headers)
            return;
            
        $each(tabElem.getParent().getElements('div.tab'), function(e, i) {
            if (e == tabElem) {
                e.addClass('selected');
                headers[i].addClass('selected');
            } else {
                e.removeClass('selected');
                headers[i].removeClass('selected');
            }
        } );
    }
};

window.onDomReady(tabBox.init.bind(tabBox));
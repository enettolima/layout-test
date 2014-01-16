stage.on('message:externalData', function(data) {

    var currentChildren = this.children();

    for (var i=0; i < currentChildren.length; i++) {
        currentChildren[i].remove();
    }

    if (data.nodeData.command === 'bar') {

        var count = parseInt(data.nodeData.count);

        var size = parseInt(data.nodeData.size);

        for (var l=0; l<count; l++) {
            new Rect(size*l, size*l, size, size).fill('random').addTo(stage);
        }
    }
});

stage.sendMessage('ready', {});

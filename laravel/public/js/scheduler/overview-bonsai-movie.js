stage.on('message:externalData', function(data) {

    if (data.nodeData.command === 'drawSchedule') {

        var currentChildren = this.children();

        for (var i=0; i < currentChildren.length; i++) {
            currentChildren[i].remove();
        }

        var days = data.nodeData.schedule;

        var dataToShow = false;

        for (var iDays=0; iDays < days.length; iDays++) {
            if (days[iDays].length > 0) {
                dataToShow = true;
            }
        }

        if (dataToShow) {

            var colors = [
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */
                // new color.RGBAColor(178  , 178  , 178 , alpha) , /* Blue */
                // new color.RGBAColor(122 , 122 , 122 , alpha) , /* Lt. Green */

                new color.RGBAColor(42  , 75  , 215 , alpha) , /* Blue */
                new color.RGBAColor(129 , 197 , 122 , alpha) , /* Lt. Green */
                new color.RGBAColor(173 , 35  , 35  , alpha) , /* Red */
                new color.RGBAColor(157 , 175 , 255 , alpha) , /* Lt. Blue */
                new color.RGBAColor(129 , 38  , 192 , alpha) , /* Purple */
                new color.RGBAColor(255 , 146 , 51  , alpha) , /* Orange */
                new color.RGBAColor(29  , 105 , 20  , alpha) , /* Green */
                new color.RGBAColor(255 , 238 , 51  , alpha) , /* Yellow */
                new color.RGBAColor(129 , 74  , 25  , alpha) , /* Brown */
                new color.RGBAColor(41  , 208 , 208 , alpha) , /* Cyan */

                // Repeat 'em so we don't run out... Todo: make this
                // smarter 
                new color.RGBAColor(42  , 75  , 215 , alpha) , /* Blue */
                new color.RGBAColor(129 , 197 , 122 , alpha) , /* Lt. Green */
                new color.RGBAColor(173 , 35  , 35  , alpha) , /* Red */
                new color.RGBAColor(157 , 175 , 255 , alpha) , /* Lt. Blue */
                new color.RGBAColor(129 , 38  , 192 , alpha) , /* Purple */
                new color.RGBAColor(255 , 146 , 51  , alpha) , /* Orange */
                new color.RGBAColor(29  , 105 , 20  , alpha) , /* Green */
                new color.RGBAColor(255 , 238 , 51  , alpha) , /* Yellow */
                new color.RGBAColor(129 , 74  , 25  , alpha) , /* Brown */
                new color.RGBAColor(41  , 208 , 208 , alpha) , /* Cyan */
            ];

            var startX = 0;
            var startY = 0;
            var blockWidth = 12;
            var blockHeight = 8;
            var alpha = 1;
            var dayWidth = blockWidth * 10;

            var myColor = new color.RGBAColor(42,75,215,alpha);

            for(var dayIterator=0; dayIterator < days.length; dayIterator++){

                var dayStartX = (dayWidth * dayIterator) + (dayIterator * blockWidth);

                var day = days[dayIterator];

                for (var empIterator=0; empIterator < days[dayIterator].length; empIterator++) {

                    var empStartX = dayStartX + (blockWidth * empIterator);

                    var emp = days[dayIterator][empIterator];

                    var inoutStartY = 0;

                    var dayStart = new Date(2000, 0, 1, 7, 0, 0);
                    var accumulatedPaddingBlocks = 0;

                    for(var inoutIterator=0; inoutIterator < emp.inouts.length; inoutIterator++) {
                        var inoutStartX = dayStartX + empStartX;
                        inout = emp.inouts[inoutIterator];
                        var clockInParts  = inout.in.match(/^(\d+):(\d+)$/);
                        var clockIn = new Date(2000, 0, 1, parseInt(clockInParts[1]), parseInt(clockInParts[2]), 0);

                        var clockOutParts = inout.out.match(/^(\d+):(\d+)$/);
                        var clockOut = new Date(2000, 0, 1, parseInt(clockOutParts[1]), parseInt(clockOutParts[2]), 0);

                        inOutBlockCount = ((clockOut - clockIn) / 1000 / 60 / 60) * 4;

                        var padBlockCount = ((clockIn - dayStart) / 1000 / 60 / 60) * 4;

                        inoutStartY = padBlockCount * blockHeight;

                        inoutStartY = inoutStartY + (accumulatedPaddingBlocks * blockHeight);

                        new Rect(
                            empStartX,
                            inoutStartY,
                            blockWidth,
                            inOutBlockCount*blockHeight
                        )
                        .addTo(stage)
                        .attr('fillColor', colors[empIterator])
                        // .attr('filters', new filter.Grayscale())
                        // .animate('1s', { filters: new filter.Grayscale()})
                        // .attr('filters', new filter.Sepia())
                        ;
                    }
                }
            }
        } else {
            new Text('No scheduling data available for the week starting ' + data.nodeData.strDate).addTo(stage).attr({
                fontFamily: 'Helvetica',
                fontSize: '16',
                textAlign: 'center',
                x: 10,
                y: 10
            });
        }
    }
});

stage.sendMessage('ready', {});

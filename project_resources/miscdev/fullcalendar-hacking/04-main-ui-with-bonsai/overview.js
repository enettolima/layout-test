
$(document).ready(function(){

    $('.day-button').click(function(){
        var dayOffset = $(this).attr('data-day-number');
        var weekOf = $("#rangeSelector").val();
        window.location.href = 'day.php?weekOf='+weekOf+'&dayOffset='+dayOffset;
    });

    $('#rangeSelector').change(function(){
        var selectedRange = $(this).val(); 
        var days = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

        console.log(selectedRange);

        var selectedRangeParts = selectedRange.split('-'); 
        var y = parseInt(selectedRangeParts[0], 10); 
        var m = parseInt(selectedRangeParts[1], 10); 
        var d = parseInt(selectedRangeParts[2], 10); 

        for (var i=0; i<7; i++) {
            var thisDateObj = new Date(y, m-1, d+i);
            var thisDate = thisDateObj.getDate();
            var thisMonth = thisDateObj.getMonth() + 1;

            $('.day-button').eq(i).html(days[thisDateObj.getDay()] + ' ' + thisMonth + '/' + thisDate);
        }

        console.log(selectedRange);
    });

});

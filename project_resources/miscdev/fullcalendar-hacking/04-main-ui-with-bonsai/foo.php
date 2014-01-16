<?php

$futureWeekCount = 10;
$ranges = array();

// Get the Sunday for this week
$lastSunday = strtotime('last sunday');

for ($i=0; $i<$futureWeekCount; $i++) {
    $ranges[] = array('start' => $lastSunday, 'end' => $lastSunday + (86400 * 6));
    $lastSunday = $lastSunday + (86400 * 7);
}


foreach ($ranges as $range) {
    var_dump(array(date('r', $range['start']), date('r', $range['end'])));
}

var_dump($ranges);

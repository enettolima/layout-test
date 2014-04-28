<?php

class EBTScheduler {

    public $inOut = array();

    public function setInOut($dayNum, $associateId, $in, $out)
    {
        if (! array_key_exists($dayNum, $this->inOut)) {
            $this->inOut[$dayNum] = array();
        }

        if (! array_key_exists($associateId, $this->inOut[$dayNum])) {
            $this->inOut[$dayNum][$associateId] = array();
        }

        $this->inOut[$dayNum][$associateId][] =
            array(
                'string' => date("g:ia", strtotime($in)) . ' - ' .  date("g:ia", strtotime($out)),
                'elapsed' => (strtotime($out) - strtotime($in)) / 3600)
        ;

    }

    public function getEmpHoursWeekSummaryArray()
    {
        $returnval = array();

        foreach ($this->inOut as $dayNum=>$staffMemberArray) {
            foreach ($staffMemberArray as $staffMemberId=>$inOutArray) {

                if (! array_key_exists($staffMemberId, $returnval)) {
                    $returnval[$staffMemberId] = array();
                    $returnval[$staffMemberId]['total'] = 0;
                    $returnval[$staffMemberId]['days'] = array();
                    $returnval[$staffMemberId]['days'][0] = array();
                    $returnval[$staffMemberId]['days'][1] = array();
                    $returnval[$staffMemberId]['days'][2] = array();
                    $returnval[$staffMemberId]['days'][3] = array();
                    $returnval[$staffMemberId]['days'][4] = array();
                    $returnval[$staffMemberId]['days'][5] = array();
                    $returnval[$staffMemberId]['days'][6] = array();
                }

                $totalHours = 0;

                foreach ($inOutArray as $inOutKey=>$inOutSet) {

                    $totalHours = $totalHours + $inOutSet['elapsed'];

                    if ($inOutKey == 0) {
                        $returnval[$staffMemberId]['days'][$dayNum]['string'] = $inOutSet['string'];
                    } else {
                        $returnval[$staffMemberId]['days'][$dayNum]['string'] .= $inOutSet['string'];
                    }

                    if (count($inOutArray) != $inOutKey + 1) {
                        $returnval[$staffMemberId]['days'][$dayNum]['string'] .= ', ';
                    } else {
                        $returnval[$staffMemberId]['days'][$dayNum]['string'] .= " [$totalHours Hours]";
                    }
                }

                $returnval[$staffMemberId]['total'] = $returnval[$staffMemberId]['total'] + $totalHours;
            }
        }

        return($returnval);
    } 

    public function getInOutStringsArray() 
    {
        $returnval = array();
        foreach ($this->inOut as $dayNum=>$staffMemberArray) {

            foreach ($staffMemberArray as $staffMemberId=>$inOutArray) {

                $totalHours = 0;

                foreach ($inOutArray as $inOutKey=>$inOutSet) {

                    $totalHours = $totalHours + $inOutSet['elapsed'];

                    if ($inOutKey == 0) {
                        $returnval[$dayNum][$staffMemberId] = $inOutSet['string'];
                    } else {
                        $returnval[$dayNum][$staffMemberId] .= $inOutSet['string'];
                    }

                    if (count($inOutArray) != $inOutKey+1) {
                        // This isn't the last set of inouts, append a comma and space
                        $returnval[$dayNum][$staffMemberId] .= ', ';
                    } else {
                        // This was the last set of inouts, append the total hours
                        $returnval[$dayNum][$staffMemberId] .= ' ['.$totalHours.' Hours]';
                    } 
                }
            }
        }

        for ($i=0; $i<=6; $i++) {
            if (! array_key_exists($i, $returnval)) {
                $returnval[$i] = array();
            }
        }

        ksort($returnval);

        return($returnval);
    }
}

<?php

class LSvcController extends BaseController
{

    public function getIndex()
    {
        // Log::info('asdf', array('username', Auth::check()));
    }

    public function postIndex()
    {

        // Log::info('asdf', array('username', Auth::check()));
    }

    public function getSchedulerStoreDaySchedule()
    {
        $storeNumber = Request::segment(3);

        $sundayDate = Request::segment(4);

        $date = date('Y-m-d', strtotime($sundayDate));

        $returnval = array();

        $scheduleSQL = "
            SELECT
                s.`id`,
                s.`associate_id`,
                s.`store_id`, 
                s.`date_in`, 
                s.`date_out` 
            FROM 
                scheduled_inout s 
            WHERE
                s.`store_id` = $storeNumber AND
                DATE(date_in) = '$date';
        ";

        $scheduleRES = DB::connection('mysql')->select($scheduleSQL);

        // Metadata currently resides at the week point...

        $ts = strtotime($date);
        $sundayTimestamp = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
        $sundayDate = date('Y-m-d', $sundayTimestamp);

        $metaSQL = "
            SELECT
                data
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$sundayDate'
        ";

        $metaRES = DB::connection('mysql')->select($metaSQL);

        if (! isset($metaRES[0])) {
            $metaRES[0] = (object) '';
            $metaRES[0]->{'data'} = null;
        } 

        $metaArray = json_decode($metaRES[0]->{'data'}, true);

        $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
        $scheduleHalfHourLookupRES  = DB::connection('mysql')->select($scheduleHalfHourLookupSQL);

        return Response::json(array(
            'meta' => $metaArray,
            'schedule' => $scheduleRES,
            'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
        ));
    }

    public function getSchedulerStoreWeekSchedule()
    {
        $storeNumber = Request::segment(3);
        $sundayDate = Request::segment(4);

        // Normalize supplied 'sundayDate' to YYYY-MM-DD
        $sundayDate = date('Y-m-d', strtotime($sundayDate));

        $returnval = array();

        $metaSQL = "
            SELECT
                data
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$sundayDate'
        ";

        $metaRES = DB::connection('mysql')->select($metaSQL);

        if (! isset($metaRES[0])) {
            $metaRES[0] = (object) '';
            $metaRES[0]->{'data'} = null;
        } 

        $metaArray = json_decode($metaRES[0]->{'data'}, true);

        // So we get 7 total days starting from sunday...
        for ($i=0; $i <=6; $i++) {

            $onDate = date('Y-m-d', strtotime($sundayDate) + ($i * 86400));

            /*
             * Following is a copy from /storeDaySchedule below, which
             * is not the proper way to do this, but Slim's scope is messing with 
             * me. TODO: Refactor this to avoid obvious DRY breakage
             */ 

            // $querySchedule = "
            $scheduleSQL = "
                SELECT
                    s.`id`,
                    s.`associate_id`,
                    s.`store_id`, 
                    s.`date_in`, 
                    s.`date_out` 
                FROM 
                    scheduled_inout s 
                WHERE
                    s.`store_id` = $storeNumber AND
                    DATE(date_in) = '$onDate'
            ";

            $scheduleRES = DB::connection('mysql')->select($scheduleSQL);

            $returnval[$onDate] = array('schedule' => $scheduleRES);
        }

        $nr = array();

        $summary = array();

        $e = new EBTScheduler;

        // This: is getting super messy
        foreach ($returnval as $day => $val) {

            Log::info('val', $val);

            if (! isset($dayNum)) {
                $dayNum = 0;
            }
            $dayArray = array();
            $empArray = array();

            $summary['hoursByDate'][$day] = 0;

            $summary['hoursByDayNum'][$dayNum] = 0; 


            foreach ($val['schedule'] as $inoutVal) {

                $empArray[$inoutVal->{'associate_id'}][] = array (
                    'in' => date("H:i", strtotime($inoutVal->{'date_in'})),
                    'out' => date("H:i", strtotime($inoutVal->{'date_out'}))
                );

                $e->setInOut($dayNum, $inoutVal->{'associate_id'}, $inoutVal->{'date_in'}, $inoutVal->{'date_out'});

                $totFoo = strtotime($inoutVal->{'date_out'}) - strtotime($inoutVal->{'date_in'});

                $summary['hoursByDate'][$day] = $summary['hoursByDate'][$day] + ($totFoo / 3600);

                $summary['hoursByDayNum'][$dayNum] = $summary['hoursByDayNum'][$dayNum] + ($totFoo / 3600);

            }

            // $summary['empHoursByDayNum'][$dayNum] = $empSchedArray;

            foreach ($empArray as $empKey=>$empVal) {
                $dayArray[] = array('eid' => $empKey, 'inouts' => $empVal);
            }

            // Ill-advised sorting method START
            // TODO: This probably has the potential to break a lot of things
            // The point of this is to re-sort the employees in the schedule based on their 
            // sequence in the meta array
            if (isset($metaArray) && array_key_exists('sequence', $metaArray)) {
                $sorted = array();
                foreach ($metaArray['sequence'] as $sortKey) {
                    foreach ($dayArray as $empOuts) {
                        if ($sortKey == $empOuts['eid']) {
                            $sorted[] = $empOuts;
                        }
                    }
                }
                $dayArray = $sorted;
            }
            // Ill-advised sorting method STOP

            $nr[] = $dayArray;
            $dayNum++;
        }

        $summary['empHoursByDayNum'] = $e->getInOutStringsArray();
        $summary['empHoursByEmp'] = $e->getEmpHoursWeekSummaryArray();

        $returnArray = array('meta' => $metaArray, 'schedule' => $nr, 'summary' => $summary);

        return Response::json($returnArray);
    }

    public function getSchedulerTargets()
    {
        // /scheduler-targets/301/2014-01-01

        $store = Request::segment(3);

        $weekOf = Request::segment(4);

        $from = date("m/d/Y", strtotime($weekOf)); // Sunday, or "Week Of"

        $to = date("m/d/Y", strtotime($weekOf) + (86400 * 6)); // Sunday, or "Week Of"

        $targetsSQL = "
            SELECT
                Store,
                DailyBudget,
                BDWeekday,
                HR_PROFILE,
                PROF_HOUR_NEW, 
                PROF_PER, 
                HR_BUDGET,
                Date,
                HR_OPEN_MIL,
                HR_CLOSE_MIL
              FROM
                SCHED_BUDGET_PER_HOURS_FINAL_TABLE
              WHERE
                Store = '$store' and
                Date >= convert(datetime, '$from', 101) and
                Date <= convert(datetime, '$to', 101)
              ORDER BY
                Store,
                Date,
                PROF_HOUR_NEW
        ";

        $targetsRES = DB::connection('sqlsrv')->select($targetsSQL);

        $returnval = array();

        foreach ($targetsRES as $result) {
            $returnval[$result->BDWeekday]['target'] = $result->DailyBudget;
            $returnval[$result->BDWeekday]['profile'] = $result->HR_PROFILE;
            $returnval[$result->BDWeekday]['open'] = $result->HR_OPEN_MIL;
            $returnval[$result->BDWeekday]['close'] = $result->HR_CLOSE_MIL;
            $returnval[$result->BDWeekday]['hours'][$result->PROF_HOUR_NEW]['budget'] = $result->HR_BUDGET;
            $returnval[$result->BDWeekday]['hours'][$result->PROF_HOUR_NEW]['percent'] = $result->PROF_PER;
        }

        return Response::json($returnval);
    }

    /*
     * Currently a "stub" function which will probably be hooked into Oracle
     */
    public function getEmployees()
    {
        // To Protect
        $returnval = array();
        $returnval[] = array("userId" => "397CL" , "firstName" => "Courtney", "lastName" => "Lopez");
        $returnval[] = array("userId" => "368JD" , "firstName" => "Jonathan", "lastName" => "Douglas");
        $returnval[] = array("userId" => "402PL" , "firstName" => "Peyre", "lastName" => "Lumpkin");
        $returnval[] = array("userId" => "314JN" , "firstName" => "JoAnn", "lastName" => "Nichols");
        $returnval[] = array("userId" => "329SS" , "firstName" => "Steven", "lastName" => "Shinn");
        $returnval[] = array("userId" => "337KB" , "firstName" => "Kimberly", "lastName" => "Brown");
        $returnval[] = array("userId" => "343NB" , "firstName" => "Natalie", "lastName" => "Boovia");
        $returnval[] = array("userId" => "365TJ" , "firstName" => "Tyler", "lastName" => "Jack");
        $returnval[] = array("userId" => "383BT" , "firstName" => "Brenda", "lastName" => "Thompson");
        $returnval[] = array("userId" => "384PM" , "firstName" => "Patrick", "lastName" => "Merrell");
        $returnval[] = array("userId" => "390CS" , "firstName" => "Courtney", "lastName" => "Sutherland");
        $returnval[] = array("userId" => "401KB" , "firstName" => "Kyle", "lastName" => "Boone");
        $returnval[] = array("userId" => "401MB" , "firstName" => "Marie", "lastName" => "Burrows");
        $returnval[] = array("userId" => "350RV" , "firstName" => "Rachel", "lastName" => "Vargas");
        $returnval[] = array("userId" => "392AB" , "firstName" => "Alexander", "lastName" => "Barton");
        $returnval[] = array("userId" => "410AB" , "firstName" => "Alma", "lastName" => "Barrera");
        $returnval[] = array("userId" => "356JW" , "firstName" => "Juanita", "lastName" => "Workman");
        $returnval[] = array("userId" => "372SR" , "firstName" => "STephanie", "lastName" => "Russo");
        $returnval[] = array("userId" => "384LG" , "firstName" => "Lauren", "lastName" => "Gelpi");
        $returnval[] = array("userId" => "395CB" , "firstName" => "Cristoforo", "lastName" => "Bruno");
        $returnval[] = array("userId" => "366JJ" , "firstName" => "Jesse", "lastName" => "Johnson");
        $returnval[] = array("userId" => "373DM" , "firstName" => "Daniel", "lastName" => "Martini");
        $returnval[] = array("userId" => "382TS" , "firstName" => "Timothy", "lastName" => "Sims");
        $returnval[] = array("userId" => "399KR" , "firstName" => "Kaitlyn", "lastName" => "Ramsey");
        $returnval[] = array("userId" => "399MA" , "firstName" => "Mamie", "lastName" => "Armstrong");
        $returnval[] = array("userId" => "406SF" , "firstName" => "Sable", "lastName" => "Furrh");
        $returnval[] = array("userId" => "353CB" , "firstName" => "Charlene", "lastName" => "Brucker");
        $returnval[] = array("userId" => "363AJ" , "firstName" => "Adam", "lastName" => "Johnson");
        $returnval[] = array("userId" => "363AK" , "firstName" => "Abeer", "lastName" => "Khalid");
        $returnval[] = array("userId" => "389CK" , "firstName" => "Clinton", "lastName" => "Kyles");
        $returnval[] = array("userId" => "391CB" , "firstName" => "Cody", "lastName" => "Bottoms");
        $returnval[] = array("userId" => "398AB" , "firstName" => "Alysha", "lastName" => "Baxley");
        $returnval[] = array("userId" => "314AW" , "firstName" => "Andrea", "lastName" => "Westbrook");
        $returnval[] = array("userId" => "344AS" , "firstName" => "Amanda", "lastName" => "Serafin");
        $returnval[] = array("userId" => "344HS" , "firstName" => "Heather", "lastName" => "Spurgeon");
        $returnval[] = array("userId" => "354SF" , "firstName" => "Staci", "lastName" => "Farmer");
        $returnval[] = array("userId" => "395NM" , "firstName" => "Nate", "lastName" => "Maxey");
        $returnval[] = array("userId" => "330DC" , "firstName" => "Danica", "lastName" => "Carolina");
        $returnval[] = array("userId" => "388GN" , "firstName" => "Grace", "lastName" => "Nielsen");
        $returnval[] = array("userId" => "201KA" , "firstName" => "Kyle", "lastName" => "Ayers");
        $returnval[] = array("userId" => "206IM" , "firstName" => "Ian", "lastName" => "Millard");
        $returnval[] = array("userId" => "382LS" , "firstName" => "Laurie", "lastName" => "Scott");
        $returnval[] = array("userId" => "400AD" , "firstName" => "Azaleah", "lastName" => "Dorr");
        $returnval[] = array("userId" => "314EH" , "firstName" => "Eric", "lastName" => "Hanson");
        $returnval[] = array("userId" => "305TM" , "firstName" => "Taylor", "lastName" => "McNally");
        $returnval[] = array("userId" => "317JW" , "firstName" => "Joseph", "lastName" => "Webb");
        $returnval[] = array("userId" => "332JH" , "firstName" => "Jackie", "lastName" => "Hewitt");
        $returnval[] = array("userId" => "342RC" , "firstName" => "Rachel", "lastName" => "Crice");
        $returnval[] = array("userId" => "353AG" , "firstName" => "Alexandria", "lastName" => "Griffith");
        $returnval[] = array("userId" => "378CA" , "firstName" => "Carina", "lastName" => "Samble");
        $returnval[] = array("userId" => "378KF" , "firstName" => "Kara", "lastName" => "Forest");
        $returnval[] = array("userId" => "378ZW" , "firstName" => "Zachary", "lastName" => "Webster");
        $returnval[] = array("userId" => "394WC" , "firstName" => "Whitley", "lastName" => "Connell");
        $returnval[] = array("userId" => "352RS" , "firstName" => "Ramsey", "lastName" => "Sample");
        $returnval[] = array("userId" => "301LS" , "firstName" => "Lauren", "lastName" => "Stephan");
        $returnval[] = array("userId" => "306KG" , "firstName" => "Kameron", "lastName" => "Gillespie");
        $returnval[] = array("userId" => "344RB" , "firstName" => "Robert", "lastName" => "Bates");
        $returnval[] = array("userId" => "355SG" , "firstName" => "Stephen", "lastName" => "Gillespie");
        $returnval[] = array("userId" => "384TS" , "firstName" => "Terry", "lastName" => "Sanderson");
        $returnval[] = array("userId" => "201CC" , "firstName" => "Cassie", "lastName" => "Collett");
        $returnval[] = array("userId" => "341AR" , "firstName" => "Alyssa", "lastName" => "Cruz");
        $returnval[] = array("userId" => "382CV" , "firstName" => "Cassie", "lastName" => "Vestal");
        $returnval[] = array("userId" => "393RC" , "firstName" => "Rochelle", "lastName" => "Cousins");
        $returnval[] = array("userId" => "312DB" , "firstName" => "Dayton", "lastName" => "Binyon");
        $returnval[] = array("userId" => "333CB" , "firstName" => "Christy", "lastName" => "Bashaw");
        $returnval[] = array("userId" => "334DR" , "firstName" => "David", "lastName" => "Randall");
        $returnval[] = array("userId" => "387JK" , "firstName" => "Joshua", "lastName" => "Kroontje");
        $returnval[] = array("userId" => "394SW" , "firstName" => "Scarlet", "lastName" => "Westby");
        $returnval[] = array("userId" => "395BW" , "firstName" => "Breanna", "lastName" => "Williams");
        $returnval[] = array("userId" => "395CP" , "firstName" => "Christopher", "lastName" => "Preston");
        $returnval[] = array("userId" => "395MM" , "firstName" => "Megan", "lastName" => "Moisant");
        $returnval[] = array("userId" => "402AH" , "firstName" => "Amelia", "lastName" => "Hall");
        $returnval[] = array("userId" => "402AJ" , "firstName" => "Adrianna", "lastName" => "Jesuino");
        $returnval[] = array("userId" => "402BC" , "firstName" => "Brandon", "lastName" => "Chapman");
        $returnval[] = array("userId" => "402FB" , "firstName" => "Francis", "lastName" => "Bustard");
        $returnval[] = array("userId" => "402GS" , "firstName" => "Ginny", "lastName" => "Summerford");
        $returnval[] = array("userId" => "402JH" , "firstName" => "Jess", "lastName" => "Hooper");
        $returnval[] = array("userId" => "402KM" , "firstName" => "Karen", "lastName" => "Miles");
        $returnval[] = array("userId" => "402KW" , "firstName" => "Keith", "lastName" => "Watkins");
        $returnval[] = array("userId" => "402LM" , "firstName" => "Lorenzo", "lastName" => "McFarland");
        $returnval[] = array("userId" => "204JT" , "firstName" => "Jessica", "lastName" => "Treas");
        $returnval[] = array("userId" => "339KM" , "firstName" => "Kristin", "lastName" => "Mattix");
        $returnval[] = array("userId" => "342KJ" , "firstName" => "Kayleigh", "lastName" => "Jamerson");
        $returnval[] = array("userId" => "366IB" , "firstName" => "Illora", "lastName" => "Brown");
        $returnval[] = array("userId" => "367AR" , "firstName" => "Annastasia", "lastName" => "Riedel");
        $returnval[] = array("userId" => "383JP" , "firstName" => "Jennifer", "lastName" => "Poile");
        $returnval[] = array("userId" => "384BJ" , "firstName" => "Briana", "lastName" => "Jordan");
        $returnval[] = array("userId" => "384CG" , "firstName" => "Cody", "lastName" => "Gobert");
        $returnval[] = array("userId" => "398MA" , "firstName" => "Madison", "lastName" => "McMahan");
        $returnval[] = array("userId" => "399DM" , "firstName" => "Douglas", "lastName" => "Mineer");
        $returnval[] = array("userId" => "305AC" , "firstName" => "Ariana", "lastName" => "Castaneda");
        $returnval[] = array("userId" => "353AC" , "firstName" => "Alexandria", "lastName" => "C Griffith");
        $returnval[] = array("userId" => "380MF" , "firstName" => "Matthew", "lastName" => "Farrell");
        $returnval[] = array("userId" => "393ML" , "firstName" => "Monica", "lastName" => "Logan");
        $returnval[] = array("userId" => "398KM" , "firstName" => "Katie", "lastName" => "M Wardlow");
        $returnval[] = array("userId" => "399RB" , "firstName" => "Rachel", "lastName" => "Blais");
        $returnval[] = array("userId" => "313JR" , "firstName" => "Joseph", "lastName" => "Ruiz");
        $returnval[] = array("userId" => "361FP" , "firstName" => "Fernanda", "lastName" => "Perez");
        $returnval[] = array("userId" => "376JM" , "firstName" => "Justin", "lastName" => "Maxwell");
        $returnval[] = array("userId" => "397EJ" , "firstName" => "Endia", "lastName" => "Jones");
        $returnval[] = array("userId" => "201PT" , "firstName" => "Patricia", "lastName" => "Tennison");
        $returnval[] = array("userId" => "395EF" , "firstName" => "Elizabeth", "lastName" => "Fyall");
        $returnval[] = array("userId" => "334NG" , "firstName" => "Nickie", "lastName" => "Glenn");
        $returnval[] = array("userId" => "371LH" , "firstName" => "Leah", "lastName" => "Hailey");
        $returnval[] = array("userId" => "371MB" , "firstName" => "Matt", "lastName" => "Boren");
        $returnval[] = array("userId" => "376JV" , "firstName" => "Jessica", "lastName" => "Vallery");
        $returnval[] = array("userId" => "401JU" , "firstName" => "Joseph", "lastName" => "Underfinger");
        $returnval[] = array("userId" => "331CF" , "firstName" => "Cody", "lastName" => "Furlow");
        $returnval[] = array("userId" => "391TH" , "firstName" => "Troy", "lastName" => "Hogue");
        $returnval[] = array("userId" => "392KF" , "firstName" => "Kristen", "lastName" => "Fails");
        $returnval[] = array("userId" => "399JB" , "firstName" => "Joshua", "lastName" => "Birmingham");
        $returnval[] = array("userId" => "406KB" , "firstName" => "Kaylee", "lastName" => "Burton");
        $returnval[] = array("userId" => "406KK" , "firstName" => "Karin", "lastName" => "Konieczki");
        $returnval[] = array("userId" => "321AN" , "firstName" => "Anchestonique", "lastName" => "Norris");
        $returnval[] = array("userId" => "388KT" , "firstName" => "Kayleigh ", "lastName" => "Trammell");
        $returnval[] = array("userId" => "371LM" , "firstName" => "LaShawn", "lastName" => "McGlothin");
        $returnval[] = array("userId" => "304SG" , "firstName" => "Sarah", "lastName" => "Gilbert");
        $returnval[] = array("userId" => "355BA" , "firstName" => "Brianna", "lastName" => "K. Allen");
        $returnval[] = array("userId" => "403SS" , "firstName" => "Summar", "lastName" => "Suasillo");
        $returnval[] = array("userId" => "367DB" , "firstName" => "Daniel", "lastName" => "Babcock");
        $returnval[] = array("userId" => "399CC" , "firstName" => "Colton", "lastName" => "Cross");
        $returnval[] = array("userId" => "399MC" , "firstName" => "Mandy", "lastName" => "Chapman");
        $returnval[] = array("userId" => "403AM" , "firstName" => "Adam", "lastName" => "Moseley");
        $returnval[] = array("userId" => "342WY" , "firstName" => "William", "lastName" => "Young");
        $returnval[] = array("userId" => "372AV" , "firstName" => "Angela", "lastName" => "Vidal");
        $returnval[] = array("userId" => "376TK" , "firstName" => "Tana", "lastName" => "Knight");
        $returnval[] = array("userId" => "398JL" , "firstName" => "Josh", "lastName" => "Livaudais");
        $returnval[] = array("userId" => "350SC" , "firstName" => "Sara", "lastName" => "Cooper");
        $returnval[] = array("userId" => "406TC" , "firstName" => "Torey", "lastName" => "Cook");
        $returnval[] = array("userId" => "307BT" , "firstName" => "Brenna", "lastName" => "Tarply");
        $returnval[] = array("userId" => "311SS" , "firstName" => "Sarah", "lastName" => "Schwartz");
        $returnval[] = array("userId" => "314TM" , "firstName" => "Trifa", "lastName" => "Mahmood");
        $returnval[] = array("userId" => "314RD" , "firstName" => "Rhiannon", "lastName" => "Garcia");
        $returnval[] = array("userId" => "356KB" , "firstName" => "Katherine", "lastName" => "Buchheit");
        $returnval[] = array("userId" => "390AH" , "firstName" => "Adam", "lastName" => "Harper");
        $returnval[] = array("userId" => "361AR" , "firstName" => "Ariel", "lastName" => "Rios");
        $returnval[] = array("userId" => "376CC" , "firstName" => "Christopher", "lastName" => "Collier");
        $returnval[] = array("userId" => "376EL" , "firstName" => "Elizabeth", "lastName" => "Leitch");
        $returnval[] = array("userId" => "312RF" , "firstName" => "Ruel", "lastName" => "Flandes");
        $returnval[] = array("userId" => "397RM" , "firstName" => "Robyn", "lastName" => "Metivier");
        $returnval[] = array("userId" => "397TT" , "firstName" => "Tanin", "lastName" => "Taylor");
        $returnval[] = array("userId" => "389LT" , "firstName" => "Leah", "lastName" => "Tullis");
        $returnval[] = array("userId" => "395KH" , "firstName" => "Kelly", "lastName" => "Hovelsrud");
        $returnval[] = array("userId" => "325HM" , "firstName" => "Hailey", "lastName" => "Molina");
        $returnval[] = array("userId" => "333AM" , "firstName" => "Ashely", "lastName" => "Madar");
        $returnval[] = array("userId" => "333PO" , "firstName" => "Philip", "lastName" => "Oje");
        $returnval[] = array("userId" => "407AR" , "firstName" => "Amber", "lastName" => "Rhoades");
        $returnval[] = array("userId" => "331KN" , "firstName" => "Kaplan", "lastName" => "Nuckols");
        $returnval[] = array("userId" => "337KJ" , "firstName" => "Khristina", "lastName" => "Jimenez");
        $returnval[] = array("userId" => "343BT" , "firstName" => "Belen", "lastName" => "Tole");
        $returnval[] = array("userId" => "396PE" , "firstName" => "Phylisha", "lastName" => "Elliot");
        $returnval[] = array("userId" => "398TK" , "firstName" => "Tanya", "lastName" => "Kemker");
        $returnval[] = array("userId" => "404LM" , "firstName" => "Lucas", "lastName" => "Montaigne");
        $returnval[] = array("userId" => "404MC" , "firstName" => "Morenike", "lastName" => "Coker");
        $returnval[] = array("userId" => "406BK" , "firstName" => "Blake", "lastName" => "Kelley");
        $returnval[] = array("userId" => "301PM" , "firstName" => "Peter", "lastName" => "Maraccini");
        $returnval[] = array("userId" => "361SW" , "firstName" => "Steve", "lastName" => "West");
        $returnval[] = array("userId" => "384CF" , "firstName" => "Cassie", "lastName" => "F. Garrick");
        $returnval[] = array("userId" => "396JE" , "firstName" => "Jessica", "lastName" => "Escobar");
        $returnval[] = array("userId" => "371KE" , "firstName" => "Kenny", "lastName" => "Emerson");
        $returnval[] = array("userId" => "371RH" , "firstName" => "Rachel", "lastName" => "Harford");
        $returnval[] = array("userId" => "396JK" , "firstName" => "John", "lastName" => "Kolster");
        $returnval[] = array("userId" => "328JT" , "firstName" => "Jonathan", "lastName" => "Toler");
        $returnval[] = array("userId" => "373AK" , "firstName" => "Avery", "lastName" => "Kea");
        $returnval[] = array("userId" => "393DV" , "firstName" => "Daniel", "lastName" => "Vassef");
        $returnval[] = array("userId" => "406AW" , "firstName" => "Andrew", "lastName" => "Wilson");
        $returnval[] = array("userId" => "407HW" , "firstName" => "Holly", "lastName" => "Warren");
        $returnval[] = array("userId" => "313JA" , "firstName" => "Jolie", "lastName" => "Byrd");
        $returnval[] = array("userId" => "344ZK" , "firstName" => "Ziva", "lastName" => "Kennedy");
        $returnval[] = array("userId" => "363KM" , "firstName" => "Kayanne", "lastName" => "Matthews");
        $returnval[] = array("userId" => "383RS" , "firstName" => "Roilene", "lastName" => "Sullivan");
        $returnval[] = array("userId" => "398TC" , "firstName" => "Tiffany", "lastName" => "Camacho");
        $returnval[] = array("userId" => "383LR" , "firstName" => "Lisa", "lastName" => "Rosa");
        $returnval[] = array("userId" => "313SG" , "firstName" => "Sharla", "lastName" => "Gardner");
        $returnval[] = array("userId" => "397DT" , "firstName" => "Desmond", "lastName" => "Thompson-Rivera");
        $returnval[] = array("userId" => "397JJ" , "firstName" => "Johnny", "lastName" => "Jones");
        $returnval[] = array("userId" => "204KC" , "firstName" => "Kegan", "lastName" => "Cronin");
        $returnval[] = array("userId" => "329RJ" , "firstName" => "Richard", "lastName" => "Jenne");
        $returnval[] = array("userId" => "359CS" , "firstName" => "Courtney", "lastName" => "Stafford");
        $returnval[] = array("userId" => "333BS" , "firstName" => "Bobbie", "lastName" => "Sinklair");
        $returnval[] = array("userId" => "398HN" , "firstName" => "Hayley", "lastName" => "Naylor");
        $returnval[] = array("userId" => "406GM" , "firstName" => "Garland", "lastName" => "Moore");
        $returnval[] = array("userId" => "339KW" , "firstName" => "Katelyn", "lastName" => "Wilbanks");
        $returnval[] = array("userId" => "341AA" , "firstName" => "Ashley", "lastName" => "Arthur");
        $returnval[] = array("userId" => "350RB" , "firstName" => "Rhonda", "lastName" => "Brown");
        $returnval[] = array("userId" => "391JC" , "firstName" => "Jessica", "lastName" => "Cunningham");
        $returnval[] = array("userId" => "407JP" , "firstName" => "Jeramy", "lastName" => "POindexter");
        $returnval[] = array("userId" => "358DC" , "firstName" => "Daniel", "lastName" => "Carde");
        $returnval[] = array("userId" => "396BW" , "firstName" => "Britnee", "lastName" => "Weinkauf");
        $returnval[] = array("userId" => "304AW" , "firstName" => "Andrew", "lastName" => "Wikle");
        $returnval[] = array("userId" => "317MS" , "firstName" => "Mary", "lastName" => "Simmons");
        $returnval[] = array("userId" => "397LC" , "firstName" => "Coutney", "lastName" => "Lopez");
        $returnval[] = array("userId" => "201DN" , "firstName" => "Douglas", "lastName" => "Nanni");
        $returnval[] = array("userId" => "326CW" , "firstName" => "Charles", "lastName" => "Whitten");
        $returnval[] = array("userId" => "326MN" , "firstName" => "Melissa", "lastName" => "Nunn");
        $returnval[] = array("userId" => "332DB" , "firstName" => "Dorinda", "lastName" => "Bruce");
        $returnval[] = array("userId" => "332MZ" , "firstName" => "Mark", "lastName" => "Zebley");
        $returnval[] = array("userId" => "335BB" , "firstName" => "Brandi", "lastName" => "Branes");
        $returnval[] = array("userId" => "335MK" , "firstName" => "Matthew", "lastName" => "King");
        $returnval[] = array("userId" => "350CC" , "firstName" => "Casie", "lastName" => "Cox");
        $returnval[] = array("userId" => "354RR" , "firstName" => "Raymond", "lastName" => "Reaves Jr.");
        $returnval[] = array("userId" => "356LW" , "firstName" => "Laura", "lastName" => "Michelle Wedeking");
        $returnval[] = array("userId" => "371EG" , "firstName" => "Emma", "lastName" => "George");
        $returnval[] = array("userId" => "373DD" , "firstName" => "Darci", "lastName" => "McMackin");
        $returnval[] = array("userId" => "393JW" , "firstName" => "Jabari", "lastName" => "Warfield");
        $returnval[] = array("userId" => "397AV" , "firstName" => "Angela", "lastName" => "Vidal");
        $returnval[] = array("userId" => "398CR" , "firstName" => "Christopher", "lastName" => "Rinck");
        $returnval[] = array("userId" => "400AS" , "firstName" => "Amy", "lastName" => "Schenck");
        $returnval[] = array("userId" => "403ME" , "firstName" => "Mitchell", "lastName" => "Ellis");
        $returnval[] = array("userId" => "312NV" , "firstName" => "Nicolas", "lastName" => "Villegas");
        $returnval[] = array("userId" => "370LP" , "firstName" => "Lee", "lastName" => "Pruett");
        $returnval[] = array("userId" => "402MF" , "firstName" => "Margaret", "lastName" => "Fannin-Buckner");
        $returnval[] = array("userId" => "201TM" , "firstName" => "Taylor", "lastName" => "Mcdonald");
        $returnval[] = array("userId" => "339LP" , "firstName" => "Lisa", "lastName" => "Parks");
        $returnval[] = array("userId" => "365JP" , "firstName" => "Julia", "lastName" => "Porter");
        $returnval[] = array("userId" => "354JB" , "firstName" => "Jonathon", "lastName" => "Barclay");
        $returnval[] = array("userId" => "384RF" , "firstName" => "Rachael", "lastName" => "Falardeaux");
        $returnval[] = array("userId" => "402CS" , "firstName" => "Clare", "lastName" => "Smith");
        $returnval[] = array("userId" => "314MP" , "firstName" => "Mickennon", "lastName" => "Piil");
        $returnval[] = array("userId" => "326MS" , "firstName" => "Maria", "lastName" => "Sanchez");
        $returnval[] = array("userId" => "373NM" , "firstName" => "Nathan", "lastName" => "Maxey");
        $returnval[] = array("userId" => "398EJ" , "firstName" => "Erinn", "lastName" => "Jordan");
        $returnval[] = array("userId" => "346MK" , "firstName" => "Michael", "lastName" => "Krohn");
        $returnval[] = array("userId" => "346TM" , "firstName" => "Tyler", "lastName" => "Miller");
        $returnval[] = array("userId" => "397VG" , "firstName" => "Victoria", "lastName" => "Garcia");
        $returnval[] = array("userId" => "344DD" , "firstName" => "Desiree", "lastName" => "Deshazo");
        $returnval[] = array("userId" => "396DS" , "firstName" => "David", "lastName" => "M. Shrauner");
        $returnval[] = array("userId" => "407AC" , "firstName" => "Amanda", "lastName" => "M Canizales");
        $returnval[] = array("userId" => "204JV" , "firstName" => "Jason", "lastName" => "Vaughan");
        $returnval[] = array("userId" => "313AD" , "firstName" => "Amanda", "lastName" => "Dittmar");
        $returnval[] = array("userId" => "325CG" , "firstName" => "Crystal", "lastName" => "Goudeaux");
        $returnval[] = array("userId" => "353AJ" , "firstName" => "Ashley", "lastName" => "J Summers");
        $returnval[] = array("userId" => "373LG" , "firstName" => "Lucas", "lastName" => "Gregorie");
        $returnval[] = array("userId" => "395AL" , "firstName" => "Andrea", "lastName" => "Laferriere");
        $returnval[] = array("userId" => "383SE" , "firstName" => "Stefanie", "lastName" => "Escobar");
        $returnval[] = array("userId" => "387CS" , "firstName" => "Cameron", "lastName" => "Shoemaker");
        $returnval[] = array("userId" => "307DF" , "firstName" => "Danielle", "lastName" => "Frantz");
        $returnval[] = array("userId" => "395SS" , "firstName" => "Sherri", "lastName" => "Sierra");
        $returnval[] = array("userId" => "317BA" , "firstName" => "Bryan", "lastName" => "Arney");
        $returnval[] = array("userId" => "347LP" , "firstName" => "Louie", "lastName" => "Ponce");
        $returnval[] = array("userId" => "372CM" , "firstName" => "Cory", "lastName" => "Michael Hannon");
        $returnval[] = array("userId" => "201RN" , "firstName" => "Ryan", "lastName" => "Nace");
        $returnval[] = array("userId" => "312MP" , "firstName" => "Mary", "lastName" => "Luna-Perez");
        $returnval[] = array("userId" => "358FV" , "firstName" => "Francisco", "lastName" => "Velazquez");
        $returnval[] = array("userId" => "388PN" , "firstName" => "Pierre", "lastName" => "Nelson");
        $returnval[] = array("userId" => "204LH" , "firstName" => "Logan", "lastName" => "Hayes");
        $returnval[] = array("userId" => "314NS" , "firstName" => "Nikkala", "lastName" => "Svenry");
        $returnval[] = array("userId" => "334RK" , "firstName" => "Rebecca", "lastName" => "Kimble");
        $returnval[] = array("userId" => "378AA" , "firstName" => "Amber", "lastName" => "Carter");
        $returnval[] = array("userId" => "391CN" , "firstName" => "Catherine", "lastName" => "Neige");
        $returnval[] = array("userId" => "323NS" , "firstName" => "Nicole", "lastName" => "Semik");
        $returnval[] = array("userId" => "371SL" , "firstName" => "Staci", "lastName" => "Lee");
        $returnval[] = array("userId" => "201KL" , "firstName" => "Kristi", "lastName" => "Murray");
        $returnval[] = array("userId" => "408AJ" , "firstName" => "Aswey", "lastName" => "Jeter");
        $returnval[] = array("userId" => "408GP" , "firstName" => "Gabrielle", "lastName" => "Parker");
        $returnval[] = array("userId" => "408JP" , "firstName" => "Jessica", "lastName" => "Peterson");
        $returnval[] = array("userId" => "408KH" , "firstName" => "Kori", "lastName" => "Hedgemon");
        $returnval[] = array("userId" => "408MW" , "firstName" => "Marcia", "lastName" => "Williams");
        $returnval[] = array("userId" => "408SL" , "firstName" => "Stacy", "lastName" => "Lewis");
        $returnval[] = array("userId" => "408TD" , "firstName" => "Tyler", "lastName" => "Dillard");
        $returnval[] = array("userId" => "389SN" , "firstName" => "Stephanie", "lastName" => "Nuss");
        $returnval[] = array("userId" => "409BJ" , "firstName" => "Bryan", "lastName" => "Janak");
        $returnval[] = array("userId" => "409JH" , "firstName" => "Jasmine", "lastName" => "Holmes");
        $returnval[] = array("userId" => "409JL" , "firstName" => "Jessica", "lastName" => "Lewis");
        $returnval[] = array("userId" => "409MW" , "firstName" => "Michelle", "lastName" => "Williams");
        $returnval[] = array("userId" => "359AU" , "firstName" => "Alex", "lastName" => "Udovich");
        $returnval[] = array("userId" => "409BH" , "firstName" => "Brittney", "lastName" => "Holden");
        $returnval[] = array("userId" => "348JL" , "firstName" => "Jacob", "lastName" => "Lorenz");
        $returnval[] = array("userId" => "371NJ" , "firstName" => "Nigel", "lastName" => "James");
        $returnval[] = array("userId" => "408SH" , "firstName" => "Shannon", "lastName" => "Howell");
        $returnval[] = array("userId" => "317AT" , "firstName" => "Alexis", "lastName" => "Taylor");
        $returnval[] = array("userId" => "335DW" , "firstName" => "David", "lastName" => "Wright");
        $returnval[] = array("userId" => "377DR" , "firstName" => "Daniel", "lastName" => "Richard");
        $returnval[] = array("userId" => "377TN" , "firstName" => "Thane", "lastName" => "Neffendorf");
        $returnval[] = array("userId" => "335CM" , "firstName" => "Charlotte", "lastName" => "McKenzie");
        $returnval[] = array("userId" => "353KK" , "firstName" => "Kadene", "lastName" => "M. Kromka");
        $returnval[] = array("userId" => "383BF" , "firstName" => "Bobby", "lastName" => "Flores");
        $returnval[] = array("userId" => "332JP" , "firstName" => "Jessica", "lastName" => "Penn");
        $returnval[] = array("userId" => "341JK" , "firstName" => "Judith", "lastName" => "K Maddox");
        $returnval[] = array("userId" => "363BL" , "firstName" => "Brandon", "lastName" => "Lisk");
        $returnval[] = array("userId" => "377RZ" , "firstName" => "Rhonda", "lastName" => "Zamora");
        $returnval[] = array("userId" => "341LS" , "firstName" => "Lacy", "lastName" => "Smith");
        $returnval[] = array("userId" => "362BC" , "firstName" => "Brandy", "lastName" => "Curl");
        $returnval[] = array("userId" => "374KP" , "firstName" => "Kyle", "lastName" => "Pursley");
        $returnval[] = array("userId" => "379DD" , "firstName" => "Denisia", "lastName" => "Downs");
        $returnval[] = array("userId" => "343SK" , "firstName" => "Sara", "lastName" => "Knox");
        $returnval[] = array("userId" => "397SP" , "firstName" => "Shane", "lastName" => "Pool");

        return Response::json($returnval);
    }

    public function getSchedulerSetCurrentWeekOf($string)
    {

        if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', Request::segment(3))) {

            Session::set('schedulerCurrentWeekOf', Request::segment(3));

            /*
            if (Session::has('schedulerCurrentWeekOf')) {
                echo "I has currentWeekOf and it is " . Session::get('schedulerCurrentWeekOf');
            }
            */
        }
    }

    public function postCheckStoreAuth()
    {
        $storeNumber = Input::get('storeNumber');

        if (! preg_match('/^\d\d\d$/', $storeNumber)) {
            App::abort(403, "'$storeNumber' not a properly-formatted storeNumber.");
        }

        if (! $username = Input::get('username')) {
            if (! Auth::check()) {
                App::abort(403, "No username passed and no currently logged in user.");
            } else {
                $username = Auth::user()->username;
            }
        }

        if (! preg_match('/^[A-Z]+$/i', $username)) {
            App::abort(403, "'$username' not a properly-formatted username");
        }

        $returnval = array();

        if (! Entrust::hasRole('Store' . $storeNumber)) {
            $returnval['status'] = false; 
        } else {
            $returnval['status'] = true; 
            Session::set('storeContext', $storeNumber);
        }

        return Response::json($returnval);
    }
}

<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronRefreshEmployees extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:name';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try {

            $employees = json_decode(file_get_contents($_ENV['ebt_api_address'] . "/rproemployees/active", true));

            $activeEmployeesLookup = array();
            $employeesChanged = array();
            $newEmployees = array();
            $employeesRemoved = array();

            foreach ($employees as $key=>$employee) {

                $activeEmployeesLookup[] = $employee->empl_id;

                if ($el = EmployeesLookup::where('empl_id', $employee->empl_id)->first()) {

                    $elPrev = clone $el; // Stash a copy of the original for the report

                    $updateFlag = false;

                    if ($el->active != $employee->active) {
                        $el->active = $employee->active;
                        $updateFlag = true;
                    }

                    if ($el->description != $employee->description) {
                        $el->description = $employee->description;
                        $updateFlag = true;
                    }

                    if ($el->empl_name != $employee->empl_name) {
                        $el->empl_name = $employee->empl_name;
                        $updateFlag = true;
                    }

                    if ($el->empl_no1 != $employee->empl_no1) {
                        $el->empl_no1 = $employee->empl_no1;
                        $updateFlag = true;
                    }

                    if ($el->empl_no2 != $employee->empl_no2) {
                        $el->empl_no2 = $employee->empl_no2;
                        $updateFlag = true;
                    }

                    if ($el->rpro_full_name != $employee->rpro_full_name) {
                        $el->rpro_full_name = $employee->rpro_full_name;
                        $updateFlag = true;
                    }

                    if ($updateFlag) {
                        $el->save();
                        $employeesChanged[] = array('before' => $elPrev, 'after' => $el);
                    }

                } else {
                    $el = new EmployeesLookup;
                    $el->active = $employee->active;
                    $el->description = $employee->description;
                    $el->empl_id = $employee->empl_id;
                    $el->empl_name = $employee->empl_name;
                    $el->empl_no1 = $employee->empl_no1;
                    $el->empl_no2 = $employee->empl_no2;
                    $el->rpro_full_name = $employee->rpro_full_name;
                    $el->save();
                    $newEmployees[] = $el;
                }
            }

            if (count(EmployeesLookup::all()) > count($employees)) {
                // We have more employees in our lookup table than Oracle 
                // has. We should delete an employee.
                foreach (EmployeesLookup::all() as $ela) {
                    if (! in_array($ela->empl_id, $activeEmployeesLookup)) {
                        $employeesRemoved[] = $ela;
                        $ela->delete();
                    }
                }
            }

            echo "New Employees: " . count($newEmployees) . "\n";
            foreach ($newEmployees as $newEmployee) {
                echo $newEmployee->active . "\n\n";
                echo $newEmployee->description . "\n\n";
                echo $newEmployee->empl_id . "\n\n";
                echo $newEmployee->empl_name . "\n\n";
                echo $newEmployee->empl_no1 . "\n\n";
                echo $newEmployee->empl_no2 . "\n\n";
                echo $newEmployee->rpro_full_name . "\n\n";
            }

            echo "Employees Changed: " . count($employeesChanged) . "\n";
            foreach ($employeesChanged as $changedEmployee) {
                echo "\n";
                echo $changedEmployee['before']->empl_id . " Before:\n";
                echo $changedEmployee['before']->active . "\n";
                echo $changedEmployee['before']->description . "\n";
                echo $changedEmployee['before']->empl_name. "\n";
                echo $changedEmployee['before']->empl_no1 . "\n";
                echo $changedEmployee['before']->empl_no2 . "\n";
                echo $changedEmployee['before']->rpro_full_name . "\n\n";

                echo $changedEmployee['after']->empl_id . " After:\n";
                echo $changedEmployee['after']->active . "\n";
                echo $changedEmployee['after']->description . "\n";
                echo $changedEmployee['after']->empl_name. "\n";
                echo $changedEmployee['after']->empl_no1 . "\n";
                echo $changedEmployee['after']->empl_no2 . "\n";
                echo $changedEmployee['after']->rpro_full_name . "\n\n";

            }

            echo "Employees Removed: " . count($employeesRemoved) . "\n";
            foreach ($employeesRemoved as $removedEmployee) {
                echo $removedEmployee->empl_id . "\n";
                echo $removedEmployee->active . "\n";
                echo $removedEmployee->description . "\n";
                echo $removedEmployee->empl_name . "\n";
                echo $removedEmployee->empl_no1 . "\n";
                echo $removedEmployee->empl_no2 . "\n";
                echo $removedEmployee->rpro_full_name . "\n\n";
            }

        } catch(Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}

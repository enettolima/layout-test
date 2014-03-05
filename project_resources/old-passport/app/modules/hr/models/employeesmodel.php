<?php
class EmployeesModel extends OracleModel {
	private $validCols;
	
	public function __construct() {
		$this->validCols = array(
			'empl_type', 
			'first_name', 
			'mi', 
			'last_name', 
			'address', 
			'address2', 
			'hire_date', 
			'marital_status', 
			'pay_type', 
			'pay_rate', 
			'job_id', 
			'state', 
			'city', 
			'sex', 
			'zip', 
			'birth_date', 
			'ssn_secure', 
			'ssn_last4',
			'store_code',
			'chk_inpaychex', 
			'chk_taxform');
	}
	
	public function createEmployee($employeeData) {
		$employeeData['ssn_secure'] = EmployeesModel::getSecureSSN($employeeData['ssn']);
		$employeeData['ssn_last4'] = substr((string)$employeeData['ssn'], -4);
		
		foreach(array_diff(array_keys($employeeData), $this->validCols) as $key)
			unset($employeeData[$key]);
		
		foreach (array('hire_date', 'birth_date') as $key)
			$employeeData[$key] = date('j-M-Y', strtotime($employeeData[$key]));	
			
		foreach ($employeeData as &$value)
			$value = $this->getDB()->quote($value);
		
		$insertEmployeeSql =
			"INSERT INTO employees(" . implode(', ', array_keys($employeeData)) . ") VALUES (" . 
			implode(', ', array_values($employeeData)) . ")";

		$insertStmt = new Zend_Db_Statement_Oracle($this->getDB(), $insertEmployeeSql);

		try {
			$insertStmt->execute();
			return $this->getDB()->lastInsertId('GUID');
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function getSecureSSN($ssn) {
		$key = base64_decode(file_get_contents(APP_ROOT . '/etc/ssn.key'));
		$before = "$ssn";
		$after = bin2hex(mcrypt_ecb(MCRYPT_3DES, $key, $before, MCRYPT_ENCRYPT));

		return $after;
	}

	public function getAvailableEmployees($userId) {
		$getEmployeesSql = 
			"SELECT 
			  s.store_code, 
			  s.store_name, 
			  e.empl_id, 
			  e.first_name, 
			  e.last_name, 
			  e.hire_date, 
			  e.chk_inpaychex, 
			  e.chk_taxform, 
			  e.rpro_id
			FROM
			  employees e
			  INNER JOIN stores s ON (s.STORE_CODE = e.store_code)
			  INNER JOIN adminStoreAccess sa ON (sa.store_code = e.store_code)
			WHERE
			  sa.user_id = :userId AND e.active = 1";
		
		$getEmplStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getEmployeesSql);
        $getEmplStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        
        $getEmplStmt->execute(array('userId' => $userId));
        
        $employees = array();
        while ($row = $getEmplStmt->fetch()) {
        	$storeString = $row->STORE_NAME;
        	if (!isset($employees[$storeString]))
        		$employees[$storeString] = array();
        	
        	$employees[$storeString][] = $row;
        }
        
        return $employees;
	}
	
	public function updateEmployee($emplId, $employeeData) {
		if (isset($employeeData['ssn'])) {
			$employeeData['ssn_secure'] = EmployeesModel::getSecureSSN($employeeData['ssn']);
			$employeeData['ssn_last4'] = substr((string)$employeeData['ssn'], -4);
		}
		
		foreach(array('chk_inpaychex', 'chk_taxform') as $key) {
			if (isset($employeeData[$key])) 
				$employeeData[$key] = (intval($employeeData[$key]) ? 'Y' : 'N');
		}
		
		foreach (array('hire_date', 'birth_date') as $key) {
			if (isset($employeeData[$key]))
				$employeeData[$key] = date('j-M-Y', strtotime($employeeData[$key]));	
		}
			
		$sets = array();
		foreach ($employeeData as $key => $val) {
			if (!in_array($key, $this->validCols))
				continue;
				
			$sets[] = $key . ' = ' . $this->getDB()->quote($val);
		}
			
		$updateEmployeeSql =
			"UPDATE employees SET " . join(', ', $sets) . " WHERE empl_id = :emplId";

		PassportLog::getInstance()->log($updateEmployeeSql, Zend_Log::INFO);
		$updateStmt = new Zend_Db_Statement_Oracle($this->getDB(), $updateEmployeeSql);
		
		try {
			$updateStmt->execute(array('emplId' => $emplId));
			return $this->getDB()->lastInsertId('GUID');
		} catch (Exception $e) {
			return false;
		}
	}
	
	public function getEmployee($emplId) {
		$getEmplSql =
			"SELECT
			  e.empl_id, 
			  e.active, 
			  e.sex, 
			  e.first_name, 
			  e.mi,
			  e.last_name,
			  e.address,
			  e.address2,
			  e.city,
			  e.zip,
			  e.state,
			  e.marital_status,
			  e.ssn_last4,
			  e.birth_date,
			  e.hire_date,
			  e.pay_type,
			  decode(e.chk_inpaychex, 'Y', 1, 0) chk_inpaychex,
			  decode(e.chk_taxform, 'Y', 1, 0) chk_taxform,
			  e.rpro_id,
			  e.job_id,
			  e.empl_type,
			  e.pay_rate,
			  j.description job_description,
			  e.store_code,
			  s.store_name
			FROM
			  employees e 
			  INNER JOIN jobs j ON (e.job_id = j.job_id)
			  INNER JOIN stores s ON (e.store_code = s.store_code)
			WHERE
			  e.empl_id = :emplId";
		
		$getContactSql = 
			"SELECT
			  empl_id, 
			  contact_method, 
			  contact_type, 
			  value,
			  note
			FROM
			  employeeContact
			WHERE
			  empl_id = :emplId";
		 
		$getEmplStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getEmplSql);
        $getEmplStmt->execute(array('emplId' => $emplId));
        
        $employee = $getEmplStmt->fetchObject();
        
        $getContactStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getContactSql);
        $getContactStmt->execute(array('emplId' => $emplId));
        
        $employee->contact = array();
        while ($row = $getContactStmt->fetch())
        	$employee->contact[] = (object)$row;
        
        return $employee;
	}

	function getJobs() {
		$getJobsSql = 
			"SELECT * FROM jobs";
		
		$getJobsStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getJobsSql);
        $getJobsStmt->execute();
        
        return $getJobsStmt->fetchAll();
	}
	
	/* Session Methods */
	function saveEmployee($employeeData) {
		try {
			$session = new Zend_Session_Namespace('Employees');
			$session->newHire = $employeeData;
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function restoreEmployee() {
		try {
			$session = new Zend_Session_Namespace('Employees');
			return $session->newHire;
		} catch (Exception $e) {
			return false;
		}
	}

	public function clearEmployee() {
		try {
			$session = new Zend_Session_Namespace('Employees');
			unset($session->newHire);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
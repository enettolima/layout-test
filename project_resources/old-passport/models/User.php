<?php 
require_once('config_doctrine.php');

class User extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('users');
		
		$this->hasColumn('user_id as id', 'integer', null, array('primary' => true, 'sequence' =>  'guid'));
		$this->hasColumn('role_id', 'integer', null);
		$this->hasColumn('username', 'string', 64);
		$this->hasColumn('first_name', 'string', 32);
		$this->hasColumn('last_name', 'string', 32);
		$this->hasColumn('passhash', 'string', 40);
		$this->hasColumn('expires_on', 'timestamp');
	}
	
	public function setUp() {
		$this->hasMany('StoreAccessRule', array('local' => 'user_id', 'foreign' => 'user_id'));
		$this->hasMany('StageAccessRule', array('local' => 'user_id', 'foreign' => 'user_id'));
		
		$this->hasOne('Role', array('local' => 'role_id', 'foreign' => 'role_id'));
	}
	
	public static function findAll() {
		$q = new Doctrine_Query;
		$q->from('User u');
		
		return $q->execute();
	}
	
	public static function findByPage($page, $perPage, &$pages = null, $sort = 'u.username', $order = 'asc') {
		$q = Doctrine_Query::create()
			->from('User u')
			->innerJoin('u.Role r')
			->orderBy("$sort $order");
		
		$p = new Doctrine_Pager($q, $page, $perPage);
		$results = $p->execute();
		$pages = ceil($p->getNumResults() / $perPage);
		
		return $results;
	}
	
	public static function findByUsername($username) {
		$q = Doctrine_Query::create()
			->from('User u')
			->innerJoin('u.Role r')
	  		->leftJoin('u.StageAccessRule sa')
	  		->leftJoin('u.StoreAccessRule sa2')
	  		->where('u.username = ?', $username);
		  
		$results = $q->execute();
		return isset($results[0]) ? $results[0] : false;
	}
	
	public static function find($user_id) {
		$q = new Doctrine_Query;
		$q->from('User u')
		  ->leftJoin('u.StageAccessRule sa')
		  ->leftJoin('u.StoreAccessRule sa2')
		  ->where('u.id = ?', $user_id);
		  
		$results = $q->execute();
		return isset($results[0]) ? $results[0] : false;
	}
	
	public function __toString() {
		$str = "User username: {$this->username}, first name: {$this->first_name}";
		if (!empty($this->last_name))
			$str .= ", last name: {$this->last_name}";
			
		return $str;
	}
	
	public function delete(Doctrine_Connection $conn = null) {
		if ($conn === null) {
			$conn = Doctrine_Manager::connection();
		}
	
		if (empty($this->id)) {
			return false;
		}
	
		try {
			$conn->beginTransaction();
		
			// Delete StoreAccessRule
			Doctrine_Query::create($conn)
				->delete()
				->from('StoreAccessRule sar')
				->where('sar.user_id = ?', $this->id)
				->execute();
			
			// Delete StageAccessRule
			Doctrine_Query::create($conn)
				->delete()
				->from('StageAccessRule sar')
				->where('sar.user_id = ?', $this->id)
				->execute();
			
			// Delete user
			$rows = Doctrine_Query::create($conn)
				->delete()
				->from('User u')
				->where('u.user_id = ?', $this->id)
				->execute();
			
			if (empty($rows)) {
				throw new Exception("Unable to delete User {$this->id}.");
			}
			
			// Commit changes
			$conn->commit();
		} catch (Exception $e) {
			$conn->rollback();
			return false;
		}
		
		return true;
	}
}
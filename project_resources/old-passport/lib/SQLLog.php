<?php
require_once('Doctrine.php');

class SQLLog extends Doctrine_EventListener {
	public function preExec(Doctrine_Event $event) {
		file_put_contents(APP_ROOT . '/logs/doctrine_sql.log', date('d/m/Y g:i:sa') . '   Query: ' . $event->getQuery() . "\r\n\r\n", FILE_APPEND);
	}
	
	public function preStmtExecute(Doctrine_Event $event) {
		$data = 
			date('d/m/Y g:i:sa') . '   Query: ' . $event->getQuery() . "\r\n" .
			date('d/m/Y g:i:sa') . '   Parameters: ' . implode(', ', $event->getParams()) . "\r\n";
		
		file_put_contents(APP_ROOT . '/logs/doctrine_sql.log', $data, FILE_APPEND);
	}
}

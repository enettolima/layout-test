<?php
class NewsModel extends OracleModel {
	function __construct()
	{
		parent::__construct();
	}
	
	function createArticle($user_id)
	{
		$insert = array(
			'ARTICLE_ID' => 0,
			'USER_ID' => $user_id,
			'POSTED_ON' => date('d-M-Y')
		);
		
		try {
			$this->getDB()->insert('NEWSARTICLES', $insert);
			return $this->getDB()->lastInsertId('GUID');
		} catch (Exception $e) {
			return false;
		}
	}
	
	function updateArticle($article_id, $update)
	{
		$n = $this->getDB()->update(
			'NEWSARTICLES', 
			$update, 
			$this->getDB()->quoteInto('ARTICLE_ID = ?', $article_id));
			
		return $n > 0;
	}
	
	function deleteArticle($article_id)
	{
		$n = $this->getDB()->delete(
			'NEWSARTICLES', 
			$this->getDB()->quoteInto('ARTICLE_ID = ?', $article_id));
			
		return $n > 0;
	}
	
	function getArticlesCount()
	{
		try {
			$row = $this->getDB()->fetchRow('SELECT COUNT(*) ARTICLE_COUNT FROM NewsArticles');
			
			return $row->ARTICLE_COUNT;
		}catch(Exception $e) {
			return false;
		}
	}
	
	function getArticle($article_id)
	{
		$sql = $this->getDB()->quoteInto(
		"SELECT 
		    n.ARTICLE_ID, 
		    n.POSTED_ON, 
		    n.TYPE, 
		    n.SUBJECT, 
		    n.BODY, 
		    u.FIRST_NAME, 
		    u.LAST_NAME, 
		    u.USER_ID
		  FROM 
		    NewsArticles n LEFT JOIN 
		    Users u ON (n.USER_ID = u.USER_ID)
		  WHERE
		    n.ARTICLE_ID = ?", array($article_id));
		
		return $this->getDB()->fetchRow($sql);
	}
	
	function getArticlesByPage($page, $page_size, $order_col, $order)
	{
		$start = $page * $page_size;
		$stop = ($page + 1) * $page_size;
		
		$sql = 
			"SELECT * FROM (
			  SELECT 
			    n.ARTICLE_ID, 
			    n.POSTED_ON, 
			    n.TYPE, 
			    n.SUBJECT, 
			    n.BODY, 
			    u.FIRST_NAME, 
			    u.LAST_NAME, 
			    u.USER_ID, 
			    ROW_NUMBER() OVER (ORDER BY $order_col $order) RNO 
			  FROM 
			    NewsArticles n LEFT JOIN 
			    Users u ON (n.USER_ID = u.USER_ID)
			) WHERE
			  RNO >= $start AND RNO < $stop ORDER BY RNO";
		
		return $this->getDB()->fetchAll($sql);
	}
}
?>
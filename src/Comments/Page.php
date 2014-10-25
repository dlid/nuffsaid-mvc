<?php

namespace Anax\Comments;
 
/**
 * Model for Comments.
 *
 */
class Page extends \Anax\MVC\CDatabaseModel
{

	public function getByUrl($url) {

		$this->db->select()
	           ->from($this->getSource())
	           ->where('url = ?');

	  $a = $this->db->execute([$url]);
	  return $this->db->fetchInto($this);
	} 


	public function ensurePage($url) {

	  $page = $this->pages->getByUrl( $url );

	  if( $page  ) {
	      $prop = $page->getProperties();
	      return $prop['id'];
	  } else {
	      $this->pages->add($this->request->getPost('page'));
	      return $this->pages->id;
	  }

	}


	public function add($url) {
		$now = date('Y-m-d H:i:s');

		$this->create([
			'url' => $url,
			'created' => $now
		]);

		return $this->id;

	}
    

}
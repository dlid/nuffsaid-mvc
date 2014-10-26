<?php

namespace Anax\Users;
 
/**
 * Model for Users.
 *
 */
class User extends \Anax\MVC\CDatabaseModel
{

	/**
	* Find and return specific.
	*
	* @return this
	*/
	public function findByAcronym($acronym)
	{
	  $this->db->select()
	           ->from($this->getSource())
	           ->where("acronym = ?");

	  $this->db->execute([$acronym]);
	  return $this->db->fetchInto($this);
	}	

	public function findByLogin($username, $password) {
		#$this->db->setVerbose(true);
		$col = "acronym";
		if( strpos($username, "@") !== false ) {
			$col = "email";
		}

		 $this->db->select()
           ->from($this->getSource())
           ->where("{$col} = ?");
           $this->db->execute([$username]);
      $u = $this->db->fetchOne($this);

      if( $u ) {
				if (password_verify($password, $u->password)) {
				    return $u;
				}
			}

		  
		  return false;
	}

	/**
	* Find and return specific.
	*
	* @return this
	*/
	public function findByEmail($email)
	{
	  $this->db->select()
	           ->from($this->getSource())
	           ->where("email = ?");

	  $this->db->execute([$email]);
	  return $this->db->fetchInto($this);
	}	

}
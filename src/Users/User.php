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
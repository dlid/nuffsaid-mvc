<?php

namespace Anax\UserContext;
 
/**
 * Model for UserContext.
 *
 */
class UserContext extends \Anax\MVC\CDatabaseModel
{

	const SESSION_CONTEXTID = 'UserContextId';


	public function loginUserById($id) {

		// First. Clear expired sessions.
		$this->vacuum();

		// Set existing sessions for this user to expired
		$now = date('Y-m-d H:i:s');
		$this->db->update(
		    $this->getSource(),
		    array('expired'),
		    "id = ?"
		);
		$this->db->execute([$now, $id]);

		$token = guid();
		$this->create(array(
			'user_id' => $id,
			'token' => $token,
			'created' => $now,
			'seen' => $now
		));

		if( $this->id ) {
			$this->session->set(self::SESSION_CONTEXTID, array($id, $token));
			return true;
		} else {
			return false;
		}
	}

	private function vacuum() {
		#$this->db->setVerbose(true);

		// Expire active sessions where the user has been inactive for 15 minutes or more
		$expires = new \DateTime();
		date_add($expires,date_interval_create_from_date_string("-15 minutes"));
		$now = date('Y-m-d H:i:s');
		
		$this->db->update(
	      $this->getSource(),
	      array('expired'),
	      "expired IS NULL AND seen <=?"
	  );
	  
	  $this->db->execute([$now, $expires->format('Y-m-d H:i:s')]);
}

	private function findActiveByToken($token) {
		#$this->db->setVerbose(true);
		$this->db->select('ns_usercontext.id,user_id,acronym,name,is_admin,ns_usercontext.created,seen')
	           ->from($this->getSource())
	           ->join('user', ' user_id = ns_user.id')
	           ->where("token = ?")
	           ->andWhere("expired IS NULL");

	  $this->db->execute(array($token));
	  return $this->db->fetchInto($this);
	}

	private function updateSeen() {
		if($this->id) {
			$now = date('Y-m-d H:i:s');
			$this->update(array('seen' => $now));
		}
	}

	public function getUserDisplayName() {
		if($this->name) {
			return $this->name;
		}
		return $this->acronym;
	}

	public function getUserId() {
		if($this->user_id) {
			return $this->user_id;
		}
		return null;
	}

	

	public function logout() {

		if($this->isLoggedIn() ) {
			$this->session->set(self::SESSION_CONTEXTID, null);
			if(isset($this->id)) {
				$now = date('Y-m-d H:i:s');
				$this->update(array('seen' => $now, 'expired' => $now));
				unset($this->id);
			}
		}
	}

	public function isLoggedIn() {

		$this->vacuum();

		if(isset($this->id)) 
			return true;

		if( $this->session->get(self::SESSION_CONTEXTID) ) {
			$data = $this->session->get(self::SESSION_CONTEXTID);
			if(is_array($data)) {
				$this->findActiveByToken($data[1]);
				if( isset($this->id) ) {
					$this->updateSeen();
					return true;
				}
			}
			$this->session->set(self::SESSION_CONTEXTID, null);
		} else {
			return false;
		}
		return false;
	}

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
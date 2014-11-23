<?php

namespace Anax\UserContext;
 
/**
 * Model for UserContext.
 *
 */
class UserContext extends \Anax\MVC\CDatabaseModel
{

	const SESSION_CONTEXTID = 'UserContextId';

	public function getFormDigest() {
		if( $this->isLoggedIn() ) {
			$data = $this->session->get(self::SESSION_CONTEXTID);
			return md5(implode(',', $data));
		}
	}


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
		#
		$columns = array('c.id',
			'user_id',
			'email',
			'acronym',
			'name',
			'is_admin',
			'c.created',
			'seen',
			'(SELECT SUM(reputation_score) FROM ns_useractivity a WHERE user_id = c.user_id AND deleted IS NULL) as reputation',
			'(SELECT SUM(activity_score) FROM ns_useractivity a WHERE user_id = c.user_id AND deleted IS NULL) as activity_score',
			);
		
		$this->db->select(implode(',', $columns) )
	           ->from($this->getSource(), 'c')
	           ->join('user', 'c.user_id = ns_user.id')
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
		if( $this->isLoggedIn() ){
			if($this->name) {
				return $this->name;
			}
			return $this->acronym;
		}
	}

	public function getUserEmail() {
		if( $this->isLoggedIn() ){
			if($this->email) {
				return $this->email;
			}
		}
	}
	public function getUserName() {
		if( $this->isLoggedIn() ){
			if($this->name) {
				return $this->name;
			}
		}
	}

	public function getUserAcronym() {
		if( $this->isLoggedIn() ){
			if($this->acronym) {
				return $this->acronym;
			}
		}
	}


	public function getIsAdmin() {
		if($this->isLoggedIn()) {
			if($this->is_admin) {
				return true;
			}
		}
		return false;
	}

	public function getUserId() {
		if($this->isLoggedIn()) {
			if($this->user_id) {
				return $this->user_id;
			}
		}
		return null;
	}

	public function getUserReputation() {
		if($this->isLoggedIn()) {
			if($this->reputation) {
				return $this->reputation ? $this->reputation : 0;
			}
		}
		return 0;
	}

	public function getUserActivityScore() {
		if($this->isLoggedIn()) {
			if($this->activity_score) {
				return $this->activity_score ? $this->activity_score : 0;
			}
		}
		return 0;
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

	public function login($username, $password) {

		$users = new \Anax\Users\User();
		$users->setDI($this->di);
		$user = $users->findByLogin($username, $password);
		if( $user ) {
			return $this->loginUserById($user->id);
		}
		return false;
	}

	public function isLoggedIn($vacuum = false) {

		if( $vacuum ) {
			$this->vacuum();
		}

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
<?php

namespace Anax\Contributions;
 
/**
 * Model for Users.
 *
 */
class Contribution extends \Anax\MVC\CDatabaseModel
{

	public function findTags($query) {

		$cols = '*, (SELECT COUNT(*) FROM ns_contribution_tag ct WHERE c.id = ct.tag_id) as count,(SELECT MAX(created) FROM ns_contribution_tag ct WHERE c.id = ct.tag_id) as last_use';

		if($query) {
			$this->db->select($cols)
				->from('tag c')
				->where('name LIKE ?')
				->orderBy('count DESC');
				$this->db->execute(['%' . $query . '%']);
		} else {
			$this->db->select($cols)
				->from('tag c')
				->orderBy('count DESC');
				$this->db->execute();
		}
		return $this->db->fetchAll();
	}

	public function findUserByAcronym($acronym) {
			$columns = array(
			'id as user_id',
			'email',
			'created',
			'(SELECT MAX(seen) FROM ns_usercontext WHERE user_id = c.id) as last_seen',
			'acronym',
			'name',
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.id AND deleted IS NULL) ) as userActivity"
		);

		$this->db->select( implode(',', $columns) )	
			->from('user', 'c')
			->where('acronym = ?')
			->orderBy('userReputation DESC');

		$this->db->execute([$acronym]);

		
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);

		return count($rows) == 1 ? $rows[0] : null;
	}

	public function findUsers($query) {

		$columns = array(
			'id as user_id',
			'email',
			'created',
			'(SELECT MAX(seen) FROM ns_usercontext WHERE user_id = c.id) as last_seen',
			'acronym',
			'name',
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.id AND deleted IS NULL) ) as userActivity"
		);

		if($query) {
			$query = "%{$query}%";
			$this->db->select( implode(',', $columns) )	
				->from('user', 'c')
				->where('name LIKE ? OR acronym LIKE ?')
				->orderBy('userReputation DESC');
			$this->db->execute([$query,$query]);
		} else {
			$this->db->select( implode(',', $columns) )	
				->from('user', 'c')
				->orderBy('userReputation DESC');
			$this->db->execute();
		}
		
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);

		return $rows;
	}

	public function setTags($idList) {
		#dump($idList);
		#$this->db->setVerbose(true);
		foreach( $idList as $tag_id) {
			$this->db->insert('contribution_tag', 
				array('contribution_id', 'tag_id', 'created'), 
				array($this->id, $tag_id, date('Y-m-d H:i:s'))
			);
			$this->db->execute();
		}
	}

	public function findQuestionByChild($id) {

		do {
			#$this->db->setVerbose(true);
			$this->db->select( 'id, title, parent_id, user_id' )	
			->from($this->getSource())
			->where('id = ?');

			$this->db->execute([$id]);
			$id = 0;

			$item = $this->db->fetchOne();

			if($item) {
				if( $item->parent_id ) {
					$id = $item->parent_id;
				}
			}
		
		} while($id !== 0);



		if( $item ) {
			$this->attachSingleUrl($item);
			return $item;
		}
		return null;
	}

	public function findAnswersTo($id) {
		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'type',
			'email',
			'acronym',
			'user_id',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id) as calcAnswerCount',
			"(SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON a.id=x.activity_id AND a.key IN('UPVOTE') WHERE x.contribution_id = c.id AND x.deleted IS NULL ) as calcUpvoteCount",
			"(SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON a.id=x.activity_id AND a.key IN('DOWNVOTE') WHERE x.contribution_id = c.id AND x.deleted IS NULL ) as calcDownvoteCount",
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"

		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->andWhere('parent_id = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.created DESC');
		
		$this->db->execute(['ANSWR', $id]);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows, 48);
		$this->attachVoteStatusForCurrentUser($rows);
		$this->attachUrl($rows);
		return $rows;
	}

	public function getUserActivity($keys,$user_id, $contribution_id ) {

		$this->db->select()
			->from('activity')
			->where('key IN (' . implode(',', array_fill(0, count($keys), '?')) . ')');

		$this->db->execute($keys);
		$activity = $this->db->fetchAll();

		if(!$activity) {
			throw new \Exception("No such activity {$key}");
		}
		$activity_ids = array();
		foreach($activity as $a)
			$activity_ids[] = $a->id;

			// Attempt to get any existing item
		$this->db->select()
			->from('useractivity')
			->where('user_id=?')
			->andWhere('activity_id IN(' . implode(',', array_fill(0, count($activity_ids), '?')) . ')')
			->andWhere('contribution_id=?')
			->andWhere('deleted IS NULL');
		
			$params = array($user_id);
			foreach( $activity_ids as $i )
				$params[] = $i;
			$params[] = $contribution_id;

		#$this->db->setVerbose(true);
		$this->db->execute($params);
		$existing = $this->db->fetchOne();
		return $existing;
	}

	public function deleteUserActivity($key, $user_id, $contribution_id) {

		$this->db->select()
			->from('activity')
			->where('key = ?');

		$this->db->execute(array($key));
		$activity = $this->db->fetchOne();

		if(!$activity) {
			throw new \Exception("No such activity {$key}");
		}
		#$this->db->setVerbose(true);
		$this->db->update('useractivity', 
			array('deleted'),
			array(date('Y-m-d H:i:s')),
			'activity_id = ? AND contribution_id = ? AND user_id = ? AND deleted IS NULL');

		$this->db->execute(array($activity->id, $contribution_id, $user_id));

	}

	public function logUserActivity($key, $user_id, $contribution_id, $related_activity = null) {

		$this->db->select()
			->from('activity')
			->where('key=?');

		$this->db->execute([strtoupper($key)]);
		$activity = $this->db->fetchOne();

		if(!$activity) {
			throw new \Exception("No such activity {$key}");
		}

		$columns = array('created','reputation_score',
			'activity_score', 'user_id', 'contribution_id',
			'activity_id', 'deleted', 'related_activity');

		$values = array(
			date('Y-m-d H:i:s'),
			$activity->reputation_score,
			$activity->activity_score,
			$user_id,
			$contribution_id,
			$activity->id,
			null,
			$related_activity
		);

		#$this->db->setVerbose(true);
		$this->db->insert('useractivity', 
			$columns, 
			$values
		);
		$this->db->execute();

		return $this->db->lastInsertId();

	}

	public function findQuestionsWithoutCorrectAnswer() {

		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'c.type',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount',
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->andWhere('accepted_answer IS NULL')
			->andWhere('closed IS NULL')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.created ASC')
			->limit(8);
		
		$this->db->execute(['ANSWR','QUERY']);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows, 48);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}

	public function findCommentsTo($id, $applyFilters = true) {
		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'user_id',
			'c.thread_updated',
			'type',
			'email',
			'acronym',
			'name',
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity",
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->andWhere('parent_id = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.created ASC');
		
		$this->db->execute(['COMNT', $id]);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows, 16);
		$this->attachUrl($rows);
		if($applyFilters) {
			$this->attachFilters($rows);
		}
		return $rows;
	}

	public function findRecentUserActivity() {
		$tagMgr = new \Anax\Tags\Tag();
		$this->db->execute(<<<EOD
			SELECT 
			 acronym, 
			 name,
			 email,
			 title,
			 user_id,
			 MAX(c.created) as active_time, 
			 c.type, 
			 c.id as contribution_id
			FROM ns_contribution c
			JOIN ns_user u ON c.user_id = u.id
			GROUP BY user_id
			ORDER BY active_time DESC
EOD
);
		$rows = $this->db->fetchAll();

		$verbs = array(
			'COMNT' => 'kommenterade ',
			'ANSWR' => 'skrev ett svar till ',
			'QUERY' => 'ställde frågan'
		);
		$icons = array(
			'COMNT' => 'fa-comment-o',
			'ANSWR' => 'fa-comments-o',
			'QUERY' => 'fa-question green'
		);

		$newrows = array();
		foreach( $rows as $row) {
			$title = $row->title;
			$query_id = $row->contribution_id;
			$childHash = "";

			if( $row->type != 'QUERY') {
				$query = $this->findQuestionByChild($row->contribution_id);
				if($query) {
					$query_id = $query->id;
					$title = $query->title;
				}
				$childHash = "#c{$row->contribution_id}";
			}

			$slug = $tagMgr->createSlug($title);


			$newrows[] = (object)[
				'icon' => $icons[$row->type],
				'contribution_url' => $this->url->create("questions/view/{$query_id}/{$slug}{$childHash}"),
				'user_url' => $this->url->create("users/view/{$row->acronym}"),
				'avatar' => 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=16',
				'title' => $title,
				'user' => $row->name ? $row->name : $row->acronym,
				'verb' => $verbs[$row->type],
				'active_time' => $row->active_time
			];

		}



		return $newrows;
	}

	public function findUserAnswers($user_id) {
	
		$this->db->execute(<<<EOD

SELECT 
(SELECT title FROM ns_contribution ca WHERE ca.id = c.parent_id) as title,
(SELECT id FROM ns_contribution ca WHERE ca.id = c.parent_id) as id,
(SELECT thread_updated FROM ns_contribution ca WHERE ca.id = c.parent_id) as thread_updated,
c.id as answer_id,
acronym,
email,
name,
ua.created
FROM 
 ns_useractivity ua
 JOIN ns_activity a ON a.id = ua.activity_id 
 JOIN ns_contribution c ON ua.contribution_id = c.id
 JOIN ns_user u ON ua.user_id = u.id
WHERE a.key = 'ANSWERED' AND ua.deleted IS NULL AND ua.user_id = ?


EOD
, array($user_id));

		$rows = $this->db->fetchAll();
		$this->attachUrl($rows);
		return $rows;
	}

	public function findUserQuestions($user_id) {
		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'c.type',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount'
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->andWhere('user_id =? ')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.thread_updated DESC')
			->limit(10);
		
		$this->db->execute(['ANSWR','QUERY', $user_id]);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows, 16);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}

	public function findMyOpenQuestions() {

		if(!$this->userContext->isLoggedIn()) return null;


		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'c.type',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount'
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->andWhere('accepted_answer IS NULL')
			->andWhere('closed IS NULL')
			->andWhere('user_id =? ')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.thread_updated DESC')
			->limit(10);
		
		$this->db->execute(['ANSWR','QUERY', $this->userContext->getUserId()]);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows, 16);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}

	public function findUserActivities($user = null) {
		$params = array();
		$userStatement = "";
		if($user) {
			$userStatement = " AND ua.user_id=?";
			$params = array($user);
		}

		$this->db->execute(<<<EOD
			SELECT 
 name,
 acronym, 
 ua.activity_score, 
 ua.reputation_score, 
 description, 
 c.type, 
 c.id as contribution_id,
 key,
 CASE  
  WHEN c.title IS NULL THEN (SELECT title FROM ns_contribution x WHERE x.[id] = c.parent_id) 
  ELSE c.title  
 END AS title
FROM 
 ns_useractivity ua 
JOIN ns_activity a ON ua.[activity_id] = a.id
JOIN ns_user u ON ua.user_id = u.id
JOIN ns_contribution c ON ua.contribution_id = c.id
 WHERE 
  ua.deleted IS NULL $userStatement
 ORDER BY ua.created DESC, ua.related_activity DESC
 LIMIT 10
EOD
,$params);	

		$rows = $this->db->fetchAll();

		foreach($rows as &$row) {
			$row->icon = "";
			$row->usernameSufix = "";

			if( $row->key == "ACCEPT") {
				$row->icon = "fa-check";
			} else if( $row->key == "ACCEPTED") {
				$row->icon = "fa-check-square-o green";
				$row->usernameSufix = ":s";
			} else if( $row->key == "UPVOTE") {
				$row->icon = "fa-thumbs-up";
			} else if( $row->key == "UPVOTED") {
				$row->icon = "fa-thumbs-o-up green";
			} else if( $row->key == "DOWNVOTE") {
				$row->icon = "fa-thumbs-down";
			} else if( $row->key == "DOWNVOTED") {
				$row->icon = "fa-thumbs-o-down red";
			} else if( $row->key == "CLOSED") {
				$row->icon = "fa-close";
			} else if( $row->key == "ANSWERED") {
				$row->icon = "fa-mail-reply";
			} else if( $row->key == "COMMENTED") {
				$row->icon = "fa-comment-o";
			} else if( $row->key == "ASKED") {
				$row->icon = "fa-question";
			}

			$row->username = $row->name ? $row->name : $row->acronym;

			$thread = $this->findQuestionByChild($row->contribution_id);
			$row->link = $this->url->create('questions/view/' . $thread->id);

			if( $thread->id != $row->contribution_id) {
				$row->link.="#c" . $row->contribution_id;
			}

		}


		return $rows;
	}


	public function findMostUsedTags($limit = 20) {

		$fontMin = 11;
		$fontMax = 38;
		$fontRange = $fontMax - $fontMin;

		$columns = array('name',
 			'COUNT(*) as casd ', 
 			'3 as m');

		$this->db->execute(<<<EOD
			SELECT 
			 name,
			 slug,
			 tag_id, 
			 COUNT(*) as count, 
			 MAX(t.created) as latest
			FROM ns_contribution_tag 
			JOIN ns_tag t ON t.id = tag_id
			GROUP BY `tag_id`
			ORDER BY name
			LIMIT {$limit}
EOD
);

		$rows = $this->db->fetchAll();

		$totalTagged = 0;
		foreach( $rows as $row) { 
			if( $totalTagged < $row->count) 
				$totalTagged=$row->count; 
		}		


		foreach( $rows as &$row) { 
			$row->heat = round($fontMin + (($row->count/$totalTagged) * $fontRange), 2);
		}		



		return $rows;
	}

	

	public function setAcceptedAnswer($contribution_id, $answer_id) {

		$this->db->update($this->getSource(), 
			array('accepted_answer'),
			'id=?');
		$this->db->execute([$answer_id, $contribution_id]);
	}

	public function setClosed($contribution_id) {

		$this->db->update($this->getSource(), 
			array('closed'),
			'id=?');
		$this->db->execute([date('Y-m-d H:i:s'), $contribution_id]);
	}

	public function removeClosed($contribution_id) {
		$this->db->execute("UPDATE ns_contribution SET closed = null WHERE id=?", array($contribution_id));
	}

	public function removeAccepted($contribution_id) {
		$this->db->execute("UPDATE ns_contribution SET accepted_answer = null WHERE id=?", array($contribution_id));
	}


	public function setThreadUpdated($contribution_id) {

		// Make sure the contribution is the question
		// you can therefore update the thread-updated by passing a child's ID
		$c = $this->findQuestionByChild($contribution_id);
		$contribution_id = $c->id;
		$this->db->update($this->getSource(), 
			array('thread_updated'),
			array(date('Y-m-d H:i:s')),
			'id=?');
		$this->db->execute([$contribution_id]);
	}

	public function filter($text) {
		return $this->filter->doFilter($text, 'markdown,striphtml');
	}

	public function findRecent($limit = 50, $start = 0, $order = null, $options = array()) {

		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.accepted_answer',
			'c.closed',
			'c.thread_updated',
			'c.type',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount',
			"((SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON x.activity_id = a.id WHERE x.contribution_id = c.id AND a.key = 'UPVOTE' AND deleted IS NULL) - (SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON x.activity_id = a.id WHERE x.contribution_id = c.id AND a.key = 'DOWNVOTE' AND deleted IS NULL)) as rating",
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"
		);

		if( $order ) {
			switch($order) {
				case 'activity': $order = 'c.thread_updated DESC';break;
				case 'answers': $order = 'calcAnswerCount DESC';break;
				case 'rating': $order = 'rating DESC';break;
				default:
					$order = 'c.created DESC';
				break;
			}
		} else {
			$order = 'c.created DESC';
		}

		if(isset($options['tag'])) {

			$this->db->select()
				->from('tag')
				->where('slug=?');

			$this->db->execute([$options['tag']]);

			$tag = $this->db->fetchOne();
			if(!$tag) {
				throw new \Exception("Unknown tag");
			}

			$this->db->select( implode(',', $columns) )	
				->from($this->getSource(), 'c')
				->where('type = ?')
				->andWhere('c.id IN (SELECT contribution_id FROM ns_contribution_tag ct WHERE ct.tag_id = ?)')
				->join('user', 'user_id=ns_user.id')
				->orderBy($order)
				->limit($limit);
				$this->db->execute(['ANSWR','QUERY', $tag->id]);
		} else {
			$this->db->select( implode(',', $columns) )	
				->from($this->getSource(), 'c')
				->where('type = ?')
				->join('user', 'user_id=ns_user.id')
				->orderBy($order)
				->limit($limit);
				$this->db->execute(['ANSWR','QUERY']);
		}
		
		
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}

	public function findRecentlyActive($limit = 50, $start = 0) {

		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'c.type',
			'user_id',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount',
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('type = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.thread_updated DESC')
			->limit($limit);
		
		$this->db->execute(['ANSWR','QUERY']);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}


	public function attachVoteStatusForCurrentUser(&$rows) {
		
		$ids = array();
		$user_id = $this->userContext->getUserId();


		foreach($rows as &$row) {
			$ids[] = $row->id;
			$row->upvoted = false;
			$row->downvoted = false;
		}

		if(!$user_id || count($ids) == 0) {
			return null;
		}

		$this->db->select()
			->from('useractivity ua')
			->join('activity a', 'activity_id = a.id')
			->where('contribution_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')')
			->andWhere('user_id=?')
			->andWhere('deleted IS NULL')
			->andWhere('key IN (?,?)');

		$ids[] = $user_id;
		$ids[] = 'UPVOTE';
		$ids[] = 'DOWNVOTE';

		#$this->db->setVerbose(true);
		$this->db->execute($ids);
		$votes = $this->db->fetchAll();

		$upvotes = array();
		$downvotes = array();
		foreach($votes as $vote) {
			if( $vote->key == 'UPVOTE') {
				$upvotes[$vote->contribution_id] = true;
			} else if( $vote->key == 'DOWNVOTE') {
				$downvotes[$vote->contribution_id] = true;
			}
		}

		foreach($rows as &$row) {
			$row->upvoted = isset($upvotes[$row->id]) ? true : false;
			$row->downvoted = isset($downvotes[$row->id]) ? true : false;
			$row->upvote = true;
			$row->downvote = true;

			if( ($row->upvoted || $row->downvoted) || $row->user_id == $this->userContext->getUserId() ) {
				$row->upvote = false;
				$row->downvote = false;
			}

		}



	}

	public function find($id) {

		$columns = array(
			'c.id',
			'c.created',
			'c.thread_updated',
			'c.title',
			'c.body',
			'c.thread_updated',
			'c.accepted_answer',
			'c.closed',
			'c.parent_id',
			'c.type',
			'user_id',
			'email',
			'acronym',
			'name',
			'(SELECT COUNT(*) FROM `ns_contribution` x WHERE x.parent_id = c.id AND x.`type` = ?) as calcAnswerCount',
			"(SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON a.id=x.activity_id AND a.key IN('UPVOTE') WHERE x.contribution_id = c.id AND x.deleted IS NULL ) as calcUpvoteCount",
			"(SELECT COUNT(*) FROM `ns_useractivity` x JOIN ns_activity a ON a.id=x.activity_id AND a.key IN('DOWNVOTE') WHERE x.contribution_id = c.id AND x.deleted IS NULL ) as calcDownvoteCount",
			"((SELECT SUM(reputation_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userReputation",
			"((SELECT SUM(activity_score) FROM `ns_useractivity` x WHERE x.user_id = c.user_id AND deleted IS NULL) ) as userActivity"
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource(), 'c')
			->where('c.id = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('c.created DESC');
		
		$this->db->execute(['ANSWR',$id]);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);
		$this->attachTags($rows);
		$this->attachUrl($rows);
		$this->attachVoteStatusForCurrentUser($rows);

		return count($rows) > 0 ? $rows[0] : null;
	}

	public function attachUrl(&$rows) {
		$tagMgr = new \Anax\Tags\Tag();
		foreach( $rows as &$row ){
			$this->attachSingleUrl($row);
		}
	}

	public function attachFilters(&$rows) {
		foreach( $rows as &$row ){
			$row->body = $this->filter($row->body);
		}
	}

	public function attachSingleUrl(&$item) {
		$tagMgr = new \Anax\Tags\Tag();
		$item->slug = $tagMgr->createSlug($item->title);
		$item->calculatedUrl = $this->url->create('questions/view/' . $item->id . '/' . $item->slug);
	}

	public function attachAvatar(&$rows, $size = 32) {
		$tagMgr = new \Anax\Tags\Tag();
		foreach( $rows as &$row ){
			$row->avatar = 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=' . $size;
			$row->avatar16 = 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=16';
			$row->avatar32 = 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=32';
			$row->avatar42 = 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=42';
			$row->user_url = $this->url->create('users/view/' . $row->acronym);
			$row->userReputationBadge = "";
			$row->userActivityBadge = isset($row->userActivity) ? $row->userActivity : null;
			if(isset($row->userReputation)) {
				if($row->userReputation < 0) {
					$row->userReputationBadge = "<small class='info orange' title='Indikerar användarens rykte'><span class='fa fa-star'></span>{$row->userReputation}</small>";
				} else {
					$row->userReputationBadge = "<small class='info green' title='Indikerar användarens rykte'><span class='fa fa-star'></span>{$row->userReputation}</small>";
				}
			}
			if($row->userActivityBadge)
			$row->userActivityBadge = "<small class='info' title='Indikerar hur aktiv användaren är'><span class='fa fa-tachometer'></span>{$row->userActivity}</small>";
			

		}
	}

	

	public function attachTags(&$rows) {
		$ids = array();

		foreach( $rows as &$row ){
			$ids[] = $row->id;
		}

		if( count($ids) == 0) return;
		
		$macros = implode(',', array_fill(0, count($ids), '?'));

		// Get all unique tags for the posts
		$this->db->select('*')
			->from('contribution_tag')
			->where("contribution_id IN ({$macros})");
		$this->db->execute($ids);
		$row_tags = $this->db->fetchAll();

		$tag_ids = array_unique(array_map(function($o) {
			return $o->tag_id;
		}, $row_tags));

		// Get the tags and add them to the corresponding posts
		$tagMgr = new \Anax\Tags\Tag();
		$tagMgr->setDi($this->di);
		
		$tags = $tagMgr->findTagsByIds($tag_ids);

		foreach( $rows as &$row ) {
			$row->tags = array();
			foreach( $row_tags as $row_tag) {
				if( $row->id == $row_tag->contribution_id) {
					foreach( $tags as $tag) {
						if( $tag->id == $row_tag->tag_id) {
							$row->tags[] = $tag;
							break;
						}
					}
				}
			}
		}

		// Sort tags by name
		foreach( $rows as &$row) {
			usort($row->tags, function($a,$b) {
				if ($a->name == $b->name) {
        return 0;
		    }
		    return ($a->name < $b->name) ? -1 : 1;
			});
		}



	}


}
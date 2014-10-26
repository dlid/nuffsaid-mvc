<?php

namespace Anax\Contributions;
 
/**
 * Model for Users.
 *
 */
class Contribution extends \Anax\MVC\CDatabaseModel
{


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



	public function findRecent() {

		$columns = array(
			'ns_contribution.id',
			'ns_contribution.created',
			'ns_contribution.thread_updated',
			'ns_contribution.title',
			'ns_contribution.thread_updated',
			'email',
			'acronym',
			'name'
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource())
			->where('type = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('ns_contribution.created DESC');
		
		$this->db->execute(['QUERY']);
		$rows = $this->db->fetchAll();
		$this->attachAvatar($rows);
		$this->attachUrl($rows);
		$this->attachTags($rows);
		return $rows;
	}

	public function find($id) {

		$columns = array(
			'ns_contribution.id',
			'ns_contribution.created',
			'ns_contribution.thread_updated',
			'ns_contribution.title',
			'ns_contribution.body',
			'ns_contribution.thread_updated',
			'email',
			'acronym',
			'name'
		);

		$this->db->select( implode(',', $columns) )	
			->from($this->getSource())
			->where('ns_contribution.id = ?')
			->join('user', 'user_id=ns_user.id')
			->orderBy('ns_contribution.created DESC');
		
		$this->db->execute([$id]);
		$rows = $this->db->fetchAll();
//		$this->attachUrl($rows);
		$this->attachTags($rows);

		return count($rows) > 0 ? $rows[0] : null;
	}

	public function attachUrl(&$rows) {
		$tagMgr = new \Anax\Tags\Tag();
		foreach( $rows as &$row ){
			$row->calculatedUrl = $this->url->create('questions/view/' . $row->id . '/' . $tagMgr->createSlug($row->title));
		}
	}

	public function attachAvatar(&$rows) {
		$tagMgr = new \Anax\Tags\Tag();
		foreach( $rows as &$row ){
			$row->avatar = 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=32';
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



	}


}
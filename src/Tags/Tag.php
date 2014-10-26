<?php

namespace Anax\Tags;
 
/**
 * Model for UserContext.
 *
 */
class Tag extends \Anax\MVC\CDatabaseModel
{

	public function findTagsByIds($ids) {

		if(count($ids) == 0) {
			return [];
		}
		$macros = implode(',', array_fill(0, count($ids), '?'));

		$this->db->select()
			->from($this->getSource())
			->where("id IN ($macros)");

		$this->db->execute($ids);
		$ret = $this->db->fetchAll();
		$this->addUrlToTag($ret);
		return $ret;
	}

	public function searchTag($contains) {
		$contains = "%" . $contains . "%";
		#$this->db->setVerbose(true);
		
		$this->db->select()
			->from($this->getSource())
			->where("name LIKE ?")
			->limit(20);

		$this->db->execute(array($contains));
		return $this->db->fetchAll();
	}

	public function ensureTag($tags) {

		if(!is_numeric($tags['id'])) {
			$this->create(array(
				'name' => $tags['text'],
				'slug' => $tags['slug'],
				'created' => date('Y-m-d H:i:s')
			));
			return $this->id;
		} else {
			return $tags['id'];
		}

		dump($tags);
		exit;
	}

	public function fillExistingTags(&$listOfTags) {
		$ids = [];
		foreach( $listOfTags as $t) {
			if( is_numeric($t['id']) ) {
			 $ids[] = $t['id'];
			}
		}
		
		$items = $this->findTagsByIds($ids);

		foreach( $items as $row ) {
			foreach($listOfTags as &$t) {
				if( $t['id'] == $row->id) {
					$t['text'] = $row->name;
					$t['slug'] = $row->slug;
					break;
				}
			}
		}

	}

	public function addUrlToTag(&$tags) {
		foreach( $tags as &$tag) {
			$tag->url = $this->url->create('questions/tagged/' . $tag->slug);
		}
	}

	public function markBannedTags(&$listOfTags) {

		$words = [];
		foreach( $listOfTags as $t) {
			if( isset($t['text']) ) {
			 $words[] = mb_strtolower($t['text'], 'UTF-8');
			}
		}

		if( count($words) == 0 )
			return;

		$macros = implode(',', array_fill(0, count($words), '?'));

		$this->db->select()
			->from('wordlist')
			->where("word IN ($macros)");

		$this->db->execute($words);
		$x = $this->db->fetchAll();

		foreach($x as $row) {
			foreach($listOfTags as &$tag) {
				if( strtolower($tag['text']) == strtolower($row->word)) {
					$tag['banned'] = 1;
					$tag['banned_reason'] = $row->banned_reason;
				}
			}
		}
	}

	public function createSlug($text) {
		$slug = mb_strtolower($text, 'UTF-8');

		$slug = str_replace('å', 'a', $slug);
		$slug = str_replace('ä', 'a', $slug);
		$slug = str_replace('ö', 'o', $slug);
		$slug = str_replace('.', 'dot', $slug);
		$slug = str_replace(' ', '-', $slug);
		$slug = str_replace('$', 'dollar', $slug);
		$slug = str_replace('€', 'euro', $slug);
		$slug = str_replace('!', 'exclaim', $slug);
		$slug = preg_replace("/[^a-z0-9-]/", '-', $slug);
		$slug = preg_replace("/-{2, }/", '-', $slug);
		$slug = trim($slug, '-');

		return $slug;

	}

	public function findTags($slugs) {

		if( count($slugs) == 0) return array();
		
		$macros = implode(',', array_fill(0, count($slugs), '?'));
		$this->db->select()
			->from($this->getSource())
			->where("slug IN ($macros)");
		$this->db->execute($slugs);
		$existing = $this->db->fetchAll();
		return $existing;
	}

	public function parseString($string) {

		$ret = array('string' => $string, 'items' => array());
		$newItems = [];

		if( strlen($string) > 0 ) {

			$items = explode(',', utf8_decode($string));
			foreach( $items as $item) {
				if( is_numeric($item)) {
					$ret['items'][] = array('id' => $item);
				} else {
						$slug  = $this->createSlug(urldecode(substr($item, 1)));
						$newItems[] = $slug;
						$ret['items'][] = array(
							'id' => $item, 
							'text' => urldecode(substr($item, 1)),
							'slug' => $this->createSlug(urldecode(substr($item, 1)))
						);
				}
			}

			// Get the name and slug for existing ones
			$this->fillExistingTags($ret['items']);

			// If a new tag was added we need to check if it already exists
			// so we use the id of the existing tag indead of fetching a new one
			$newThatDoExist = $this->findTags($newItems);
			foreach( $newThatDoExist as $row ){
				foreach( $ret['items'] as &$sitem ) {
					if( !is_numeric($sitem['slug']) && $sitem['slug'] == $row->slug ) {
						$sitem['id'] = $row->id;
					}
				}
			}

			// Build new string
			$new = array();
			foreach($ret['items'] as $s) {
				$new[] = $s['id'];
			}
			$ret['string'] = implode(',',$new);
			$this->markBannedTags($ret['items']);
		}


		return (object)$ret;

	}

}
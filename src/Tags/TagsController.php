<?php

namespace Anax\Tags;
 
/**
 * A controller for users and admin related events.
 *
 */
class TagsController implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;

	public function indexAction() {
		$this->theme->setTitle("Tags");

		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$tags = $ctb->findTags($this->request->getGet('q'));

		$this->views->add('tags/browse', [
			'tags' => $tags,
			'query' => htmlentities($this->request->getGet('q'), null, 'utf-8')
		]);
	}

	public function getByIdsAction() {

		header("Content-Type: application/json");

		// Only get the first 'five
		$idList = array_slice(func_get_args(), 0, 5);

		$tagMgr = new \Anax\Tags\Tag();
		$tagMgr->setDi($this->di);
		$items = array();


		$items = array_map(function($o) {
			unset($o->created);
			unset($o->description);
			unset($o->slug);
			return $o;
		}, $tagMgr->findTagsByIds($idList));

		echo json_encode(array('items' => $items));

		exit;
	}

	public function editorSearchAction() {

		$tagMgr = new \Anax\Tags\Tag();
		$tagMgr->setDi($this->di);

		$result = array_map(function($o) {
			return (object)array(
				'id' => $o->id,
				'text' => $o->name
			);
		}, $tagMgr->searchTag($this->request->getGet('q')));


		header("Content-Type: application/json");

		echo json_encode( $result ) ;
		exit;

	}

}
/*

 */
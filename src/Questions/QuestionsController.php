<?php

namespace Anax\Questions;
 
/**
 * A controller for users and admin related events.
 *
 */
class QuestionsController implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;

	public function indexAction() {
		$this->theme->setTitle("");
	}

	public function searchAction() {

		header("Content-Type: application/json");

		echo json_encode( array( 
			array('id' => 0, 'text' => 'Desiny'),
			array('id' => 1, 'text' => 'Mariokart 8'),
			array('id' => 2, 'text' => 'Skyrim'),
			array('id' => 3, 'text' => 'Batman Arkham Asylum'),
			array('id' => 4, 'text' => 'Playstation 4'),
			array('id' => 5, 'text' => 'XBox 360'),
			array('id' => 6, 'text' => 'PC'),
			 )) ;
		exit;

	}

	public function askAction() {
		$this->theme->setTitle("Stll en fråga");

		$this->theme->addJavaScript('js/markitup/jquery.markitup.js');
		$this->theme->addJavaScript('js/markitup/sets/markdown/set.js');
		$this->theme->addStylesheet('js/markitup/skins/markitup/style.css');
		$this->theme->addStylesheet('js/markitup/sets/markdown/style.css');

		$this->theme->addJavaScript('js/select2/select2.min.js');
		$this->theme->addJavaScript('js/select2/select2_locale_sv.js');
		$this->theme->addStylesheet('js/select2/select2.css');



		$this->views->add('editor/cheatsheet', [], 'sidebar');
		$this->views->add('editor/markdown', [], 'main');


	}

}
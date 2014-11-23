<?php

namespace Anax\About;
 
/**
 * A controller for users and admin related events.
 *
 */
class AboutController implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;

	public function indexAction() {
		$this->theme->setTitle("Om");

	  $content = $this->fileContent->get('about.md');
	  $content = $this->textFilter->doFilter($content, 'shortcode, markdown');

	  $this->views->add('about/sections', [
	  		'asking' => $this->url->create('about/asking'),
	  		'formatting' => $this->url->create('about/formatting'),
	  		'tags' => $this->url->create('about/tags')
	  	], 'main');

	  $this->views->add('shared/page', [
	      'content' => $content,
	      'sidebar' => ""
	  ], 'main');

	  $this->views->addString('','sidebar');
	}

	public function reportAction() {
		$this->theme->setTitle("Redovisning");

	  $content = $this->fileContent->get('redovisning.md');
	  $content = $this->textFilter->doFilter($content, 'shortcode, markdown');

	  $this->views->add('shared/page', [
	      'content' => $content,
	      'sidebar' => ""
	  ], 'main');

	  $this->views->addString('','sidebar');
	}


	public function askingAction() {
		$this->theme->setTitle("Att ställa en fråga");
		$this->views->addString('<h1>Att ställa en fråga</h1>','main');
	}

	public function formattingAction() {
		$this->theme->setTitle("Markdown");
		$this->views->addString('<h1>Markdown</h1>','main');
	}

	public function tagsAction() {
		$this->theme->setTitle("Taggar");
		$this->views->addString('<h1>Taggar</h1>','main');
	}


}

<?php

namespace Nuffsaid\Contributions;

class CContributionForm implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable; 
	var $options = array();

	private static $assetsIncluded = false;
	
	public function __construct($options) {

		$defaults = array(
			'id' => 0,
			'reply_to' => 0,
			'is_comment' => false,
			'digest' => null,
			'reply_to_slug' => null,
			'form_action' => '?'
		);
 
		$this->options = array_merge($defaults, $options);

		$this->options['reply_to'] = intval($this->options['reply_to']);
		$this->options['id'] = intval($this->options['id']);

		if($this->options['id'] != 0) {
			$this->options['type'] == null; # get from the post if we're editing
		} else {
			if( $this->options['reply_to'] != 0 ) {
				if($this->options['is_comment']) {
					$this->options['type'] = 'COMNT';
				} else {
					$this->options['type'] = 'ANSWR';
				}
			} else {
				$this->options['type'] = 'QUERY';
			}
		}


		$this->options['form'] = md5($this->options['type'] . $this->options['reply_to']);


	}

	private function includeAssets() {

		if( self::$assetsIncluded ) {
			return;
		}

		$this->theme->addJavaScript('js/markitup/jquery.markitup.js');
		$this->theme->addJavaScript('js/markitup/sets/markdown/set.js');
		$this->theme->addStylesheet('js/markitup/skins/markitup/style.css');
		$this->theme->addStylesheet('js/markitup/sets/markdown/style.css');

		$this->theme->addJavaScript('js/select2/select2.min.js');
		$this->theme->addJavaScript('js/select2/select2_locale_sv.js');
		$this->theme->addStylesheet('js/select2/select2.css');
		self::$assetsIncluded = true;
	}

	/**
	 * Initialize the form for asking a new question or edit an existing one
	 * @param  integer $id The ID of the item to edit
	 * @return [type]      [description]
	 */
	public function addForm($zone = 'main') {
		$this->includeAssets();

		$viewData = $this->createViewData($zone);

		if(isset($viewData['info']) || isset($viewData['warning']) || isset($viewData['danger'])) {
			$this->views->add('shared/alert', $viewData, $zone);
		}
		$this->views->add('editor/markdown', $viewData, $zone);
	}

	public function getHtml() {
		$this->includeAssets();
		$viewData = $this->createViewData();
		$renderedView = $this->views->renderAsString('editor/comment', $viewData);
		return $renderedView;
	}

	/**
	 * Create the configuration data for the view
	 * @return array An array of settings for the view
	 */
	function createViewData($zone = null) {

		$data = array(
			'formDigest' => '<input type="hidden" name="ns-digest" value="' . $this->options['digest'] .  '" />',
			'id' => $this->options['id'],
			'reply_to' => $this->options['reply_to'],
			'tags' => '',#htmlentities($tags, null, 'utf-8'),
			'title' => '',#htmlentities($title, null, 'utf-8'),
			'text' => '',#,htmlentities($text, null, 'utf-8'),
			'type' => $this->options['type'],
			'form_action' => $this->options['form_action'],
			'reply_to_slug' => $this->options['reply_to_slug'],
			'form' => $this->options['form'] // Unique ID of the form so the right class process the right form
		);

		$data['form_id'] = $this->options['type'];

		if($this->options['reply_to'] != 0) {
					$data['form_id'].="to".$this->options['reply_to'];
					#$data['form_action'] = "#" . $data['form_id'];
				}



		if($this->request->getPost('preview') || $this->request->getPost('submit')) {

			// Make sure we're receiving the correct form since there may be multiple instances
			if($this->request->getPost('form') == $this->options['form']) {
				$data['title'] = $this->request->getPost('title');
				$data['tags'] = $this->request->getPost('tags');
				$data['text'] = $this->request->getPost('text');

				$required_fields = array('text' => '<span class="fa fa-warning"></span> Du måste skriva något');

				if( $this->options['type'] == 'QUERY') {
					$required_fields['title'] = '<span class="fa fa-warning"></span> Du måste ange en rubrik';
					$required_fields['tags'] = '<span class="fa fa-warning"></span> Du måste ange minst en tag';
				}

				if( $this->notEmpty($data, $required_fields)) 
				{
					$isValid = false;
					$tagData = null;
					if( $this->options['type'] == 'QUERY') {
						// We only require tags when a new question is added
						$tagMgr = new \Anax\Tags\Tag();
						$tagMgr->setDi($this->di);
						$tagData = $tagMgr->parseString($data['tags']);
						$data['tags'] = $tagData->string;
						if( $this->notBanned($data, $tagData) ) {
							$isValid = true;
						}
					} else {
						$isValid = true;
					}

					if($this->request->getPost('preview') && $zone) {
						$this->addPreview($data, $tagData, $zone);
					} else {
						$this->save($data, $tagData);
					}
				}
			}
		}

		return $data;
	}

	private function save($data, $tagData) {
		if( $this->request->getPost('ns-digest') == $this->userContext->getFormDigest() ){
			

			$tagMgr = new \Anax\Tags\Tag();
			$tagMgr->setDi($this->di);
			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);

			$now = date('Y-m-d H:i:s');

			$insertData = array(
				'user_id' => $this->userContext->getUserId(),
				'created' => $now,
				'type' => $this->options['type'],
				'updated' => $now,
				'accepted_answer' => null,
				'body' => $data['text']
			);

			if(isset($data['title']) && !empty($data['title'])) {
				$insertData['title'] = $data['title'];
			}

			if($data['reply_to'] !== 0) {
				$parent = $ctb->findQuestionByChild($data['reply_to']);
				$insertData['parent_id'] = $data['reply_to'];
				$reply_to_slug = $parent->slug;
			}

			// Begin transaction
			$this->db->begin();

			$ctb->create($insertData);

			$tag_ids = [];
			if( $tagData ) {
				foreach($tagData->items as $tag) {
					$tag_ids[] = $tagMgr->ensureTag((object)$tag);
				}
			}

			$ctb->setTags($tag_ids);
			if(isset($parent)) {
				$ctb->setThreadUpdated($parent->id);
			}

			switch($this->options['type']) {
				case 'QUERY': $ctb->logUserActivity('ASKED', $this->userContext->getUserId(), $ctb->id); break;
				case 'COMNT': $ctb->logUserActivity('COMMENTED', $this->userContext->getUserId(), $ctb->id); break;
				case 'ANSWR': $ctb->logUserActivity('ANSWERED', $this->userContext->getUserId(), $ctb->id); break;
			}

			$this->db->commit();

			if($data['reply_to']!=0) {
				header('Location: ' . $this->di->url->create('questions/view/' . $parent->id . '/' . $reply_to_slug . '#c' . $ctb->id ));
			} else {
				header('Location: ' . $this->di->url->create('questions/view/' . $ctb->id . '/' . $tagMgr->createSlug($data['title']) ));
			}
			exit;
		} else {
			$this->dispatcher->forward([
	        'controller' => 'error',
	        'action' => 'statusCode',
	        'params' => [
	            'code' => 403,
	            'message' => "Valideringsfel för formuläret. Du är ej behörig",
	        ],
	    ]);
	    return;
		}
	}

	private function addPreview($data, $tagData, $zone) {
		$previewTagData = array();
		if($tagData) {
			foreach( $tagData->items as $td) {
				$previewTagData[] = (object)$td;
			}
		}

		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$data['text'] = $this->filter->doFilter($ctb->filter($data['text']), 'markdown');
		$data['title'] = htmlentities($data['title'], null, 'utf-8');
		$data['username'] = htmlentities($this->di->userContext->getUserDisplayName(), null, 'utf-8');
	  $data['avatar'] = 'http://www.gravatar.com/avatar/' . md5($this->di->userContext->getUserEmail()) . '.jpg?s=32';
	  $data['userlink'] = $this->di->url->create('users/view/' . $this->di->userContext->getUserAcronym());
	  $data['item'] = (object)array(
	  	'acronym' => $this->di->userContext->getUserAcronym(),
	  	'user_url' => $data['userlink'],
	  	'avatar32' => 'http://www.gravatar.com/avatar/' . md5($this->di->userContext->getUserEmail()) . '.jpg?s=32',
	  	'userActivityBadge' => '',
	  	'userReputationBadge' => '',
	  	'created' => date('Y-m-d H:i:s')
		);
	  $data['preview'] = 1;
	  $data['accepted'] = false;
	  $data['upvoted'] = false;
	  $data['downvoted'] = false;
	  $data['rating'] = 0;
	  $data['upvote'] = false;
	  $data['downvote'] = false;
	  
	  $data['tags'] = $previewTagData;
	  $data['created'] = date('Y-m-d H:i:s');
	  $this->views->add('shared/alert', ['info' => 'Förhandsvisning'] , $zone);
		$this->views->add('question/display-single', $data, $zone);
		#$this->views->addString('<hr><br />', $zone);
	}

	private function notEmpty(&$data, $fields) {
		$errors = 0;
		foreach( $fields as $name => $message) {

			// We won't require title or tags if this is an answer
			if($this->options['reply_to'] != 0) {
				if( $name == 'title' || $name == 'tags') {
					continue;
				}
			}

			if(isset($data[$name])) {
				if(strlen(trim($data[$name])) > 0) {
					continue;
				}
			}
			$errors++;
			$data['danger'] = $message;
			break;
		}

		return $errors == 0 ;
 
	}

	private function notBanned(&$data, $tagData) {

		$bannedTags = array();
		foreach( $tagData->items as $t) {
			if( isset($t['banned'])) {
				$bannedTags[] = "<span class='banned-tag'>" . htmlentities($t['name'], null, 'utf-8') . "</span>";
			}
		}
		$bannedCount = count($bannedTags);
		if( $bannedCount > 0) {
			if( $bannedCount> 1) {
				$last = array_pop($bannedTags);
				$tagList	 = implode(' ', $bannedTags);
				$tagList .= " och " . $last;
			} else {
				$tagList = $bannedTags[0];
			}
			$data['warning'] = $tagList  ." är flagga". ($bannedCount === 1 ? 'd' : 'de'). 
			" som olämplig" .($bannedCount != 1 ? 'a' : '')." att använda som tag".($bannedCount != 1 ? 'gar' : '').
			"<ul class='fa-ul'><li> Välj en annan tag eller <span class='fa fa-envelope-o'></span> <a href='" . $this->url->create('contact') . "'>hör av dig</a> om du tycker detta är felaktigt.</li></ul>";
			return false;
		}
		return true;
	}
}

/*


		#$form->InitializeComment();
		#$form->InitializeAnswer();
		#$form->getHTML();


		$alertInfo = null;
		$alertWarning = null;
		$alertDanger = null;

		$id = 0;
		$reply_to = 0;
		$type = 'QUERY';  // QUERY, ANSWR or COMNT
		$tags = '';
		$title = '';
		$text = '';
		$tagmessage = '';
		$preview = false;

		$tagMgr = new \Anax\Tags\Tag();
		$tagMgr->setDi($this->di);

		$isValid = true;
		if($this->request->getPost('preview') || $this->request->getPost('submit')) {
			$title = $this->request->getPost('title');
			$tags = $this->request->getPost('tags');
			$text = $this->request->getPost('text');

			if( trim(strlen($title)) == 0 ) {
					$isValid = false;
					$alertWarning = "Du måste ange en rubrik";
			} else if( strlen(trim($tags)) == 0 ) {
					$isValid = false;
					$alertWarning = "Du måste ange minst en tag";
			} else if( strlen(trim($text)) == 0 ) {
					$isValid = false;
					$alertWarning = "Du måste skriva en fråga också";
			}

			if( $isValid ) {
				$tagData = $tagMgr->parseString($tags);
				$tags = $tagData->string;

				if($tagData) {
					$bannedTags = array();
					foreach( $tagData->items as $t) {
						if( isset($t['banned'])) {
							$bannedTags[] = "<span class='banned-tag'>" . htmlentities($t['text'], null, 'utf-8') . "</span>";
						}
					}
					if( count($bannedTags) > 0) {
						if( count($bannedTags) > 1) {
							$last = array_pop($bannedTags);
							$tagList	 = implode(' ', $bannedTags);
							$tagList .= " och " . $last;
						} else {
							$tagList = $bannedTags[0];
						}
						$alertWarning = $tagList  ." är flagga".(count($bannedTags) > 1 ? 'de' : 't')." som olämpligt att använda som tag".(count($bannedTags) > 1 ? 'gar' : '')."<ul class='fa-ul'><li> Välj en annan <span class='fa fa-tag'></span> tag </li><li>eller <span class='fa fa-envelope-o'></span> <a href='" . $this->url->create('contact') . "'>hör av dig</a> om du tycker detta är felaktigt.</li></ul>";
						$isValid = false;
					}
				} else {
					$alertWarning = "Du måste ha minst en tagg";
				}

				if($this->request->getPost('submit') && $isValid) {
					
					if( $this->request->getPost('ns-digest') == $this->userContext->getFormDigest() ){
						// Begin transaction
						$this->db->begin();

						$ctb = new \Anax\Contributions\Contribution();
						$ctb->setDi($this->di);

						$now = date('Y-m-d H:i:s');
						$ctb->create(array(
							'user_id' => $this->userContext->getUserId(),
							'created' => $now,
							'type' => $type,
							'updated' => $now,
							'title' => $title,
							'body' => $text,
							'thread_updated' => $now
						));

						$tag_ids = [];
						foreach($tagData->items as $tag) {
							$tag_ids[] = $tagMgr->ensureTag((object)$tag);
						}

						$ctb->setTags($tag_ids);

						$this->db->commit();
						header('Location: ' . $this->url->create('questions/view/' . $ctb->id . '/' . $tagMgr->createSlug($title) ));
						exit;
					} else {
						$this->dispatcher->forward([
			          'controller' => 'error',
			          'action' => 'statusCode',
			          'params' => [
			              'code' => 403,
			              'message' => "Valideringsfel för formuläret. Du är ej behörig",
			          ],
			      ]);
			      return;
					}


				}
			}

		}

		if($this->request->getPost('preview') && $isValid) {
			if(!$alertWarning && !$alertDanger && !$alertInfo)  {
				$preview = true;
				$alertInfo = "<span class='fa fa-exclamation-sign'></span> Det här är en förhandsgranskning av ditt inlägg. Klicka på <strong>Skicka fråga</strong> för att skicka frågan.";
			}
		
		}

		$this->views->add('shared/alert', [
			'info' => $alertInfo,
			'warning' => $alertWarning,
			'danger' => $alertDanger
		], 'main');

		if( $preview ) {

			$previewTagData = array();
			foreach( $tagData->items as $td) {
					$previewTagData[] = (object)$td;
			}

			$this->views->add('question/display-single', array(
					'text' => $this->filter->doFilter($text, 'markdown'),
					'title' => htmlentities($title, null, 'utf-8'),
					'username' => htmlentities($this->di->userContext->getUserDisplayName(), null, 'utf-8'),
					'avatar' => 'http://www.gravatar.com/avatar/' . md5($this->di->userContext->getUserEmail()) . '.jpg?s=32',
					'preview' => 1,
					'tags' => $previewTagData,
					'created' => date('Y-m-d H:i:s')
				), 'main');
				$this->views->addString('<hr><br />', 'main');
		}


		$this->views->add('editor/markdown', [
			'formDigest' => '<input type="hidden" name="ns-digest" value="' . $this->userContext->getFormDigest() .  '" />',
			'id' => 0,
			'reply_to' => 0,
			'tags' => htmlentities($tags, null, 'utf-8'),
			'title' => htmlentities($title, null, 'utf-8'),
			'text' => htmlentities($text, null, 'utf-8'),
			'type' => $type
			], 'main');



 */
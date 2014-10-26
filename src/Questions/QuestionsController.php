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
		$this->theme->setTitle("Frågor");


		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$recent = $ctb->findRecent();


		$this->views->add('question/list', [
			'items' => $recent ], 'main');

		$this->views->addString(' ', 'sidebar');

	}

	public function viewAction($id, $slug = null) {
		
		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$row = $ctb->find($id);

		$this->theme->setTitle(htmlentities($row->title, null, 'utf-8'));
		$this->views->addString('
<div id="current-post-info">
	meh
</div>
			<div id="help-commenting" class="helpbox hidden">
<h2>Kommentarer</h2>
<p class="pre">När du behöver mer information</p>
<hr />
<p>I kommentarer kan du diskutera och fråga om mer detaljer. Använd t.ex.
kommentarer när du beöver mer information för att kunna ge ett svar.</p>
</div>','sidebar');
		$this->views->add('question/display-single', [
			'title' => htmlentities($row->title, null, 'utf-8'),
			'text' => $this->filter->doFilter($row->body, 'markdown'),
			'avatar' => 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=32',
			'created' => $row->created,
			'username' => $row->acronym,
			'tags' => $row->tags,
			'currentuser_avatar' => 'http://www.gravatar.com/avatar/' . md5($this->userContext->getUserEmail()) . '.jpg?s=24',
		], 'main');


	}

	public function askAction() {

		if( !$this->userContext->isLoggedIn() ){
			$this->dispatcher->forward([
          'controller' => 'error',
          'action' => 'statusCode',
          'params' => [
              'code' => 403,
              'message' => "Du saknar behörighet för den här sidan",
          ],
      ]);
			return;
		}

		$this->theme->setTitle("Stll en fråga");

		$this->theme->addJavaScript('js/markitup/jquery.markitup.js');
		$this->theme->addJavaScript('js/markitup/sets/markdown/set.js');
		$this->theme->addStylesheet('js/markitup/skins/markitup/style.css');
		$this->theme->addStylesheet('js/markitup/sets/markdown/style.css');

		$this->theme->addJavaScript('js/select2/select2.min.js');
		$this->theme->addJavaScript('js/select2/select2_locale_sv.js');
		$this->theme->addStylesheet('js/select2/select2.css');

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
		$this->views->add('editor/cheatsheet', [], 'sidebar');


	}

}
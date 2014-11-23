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
		$this->listQuestions([
			'tag' => null
		]);
	}

	private function listQuestions($options = array()) {

		$page = 1;
		$pageSize = 50;

		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$recent = $ctb->findRecent( $pageSize, 0, $this->request->getGet('order'), $options );

		$recent = array_map(function($item) {
			$filter = new \Anax\Content\CTextFilter();
			$text = $filter->doFilter($item->body, 'markdown');
			$item->excerpt = \Nuffsaid\Utility\CTextUtil::clipText($text, 0, 150);
			$item->excerpt = $filter->doFilter($item->excerpt, 'striphtml');
			return $item;	
		},$recent);

		

		$this->views->add('question/list', [
			'baseUrl' => $this->request->getBaseUrl() . "/" . $this->request->getRoute(),
			'tag' => isset($options['tag']) ? $options['tag'] : null,
			'items' => $recent ], 'main');

		$this->views->addString(' ', 'sidebar');
	}

	public function acceptAction($id, $undo = null) {



		if($this->userContext->isLoggedIn()) {
			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);
			

			$item = $ctb->find($id);
			if( $item ) { 

				if( $item->user_id != $this->userContext->getUserId()) {

					$thread = $ctb->findQuestionByChild($id);

					$existing = $ctb->getUserActivity(['ACCEPT'], $this->userContext->getUserId(), $thread->id);
					if( !$existing ) {
						$ctb->db->begin();

						// If closed, simply remove closed status
						$ctb->deleteUserActivity('CLOSED', $this->userContext->getUserId(), $thread->id);
						$ctb->removeClosed($thread->id);

						// Add "Accept" activity to the current user
						$voteActivityId = $ctb->logUserActivity('ACCEPT', $this->userContext->getUserId(), $thread->id);

						// Add "Accepted" activity to the author of the answer
						$ctb->logUserActivity('ACCEPTED', $item->user_id, $id, $voteActivityId);

						$ctb->setAcceptedAnswer($thread->id, $item->id);
						$ctb->setThreadUpdated($thread->id);

						$ctb->db->commit();

						header('Location: ' . $_SERVER['HTTP_REFERER']);
						exit;
					} else {
						if( $undo ) {
							$ctb->db->begin();
							$ctb->removeAccepted($thread->id);
							$ctb->deleteUserActivity('ACCEPT', $thread->user_id, $thread->id);
							$ctb->deleteUserActivity('ACCEPTED', $item->user_id, $item->id);
							$ctb->setThreadUpdated($thread->id);
							$ctb->db->commit();
							header('Location: ' . $_SERVER['HTTP_REFERER']);
						} else {
							throw new \Exception("Can not accept more than one answer at a time");
						}
					}
				} else {
					throw new \Exception("Can not accept own contributions");
				}
			}
			exit;
		} else {
			throw new \Exception("Not logged in");
		}
	}

	public function closeAction($id, $undo = null) {

	
		if($this->userContext->isLoggedIn()) {
			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);
			
			$item = $ctb->find($id);
			if( $item ) { 

				if( $item->parent_id) {
					throw new \Exception("Only questions can be closed");
				}

				if( $item->user_id == $this->userContext->getUserId()) {

					$existing = $ctb->getUserActivity(['CLOSED','ACCEPT'], $this->userContext->getUserId(), $item->id);
					if( !$existing ) {

						$ctb->db->begin();

						// Add "CLOSED" activity to the current user
						$voteActivityId = $ctb->logUserActivity('CLOSED', $this->userContext->getUserId(), $item->id);

						$ctb->setClosed($id);
						$ctb->setThreadUpdated($id);

						$ctb->db->commit();

						header('Location: ' . $_SERVER['HTTP_REFERER']);
						exit;
					} else {

						if( $undo ) {
							$ctb->db->begin();
							$ctb->removeClosed($id);
							$ctb->deleteUserActivity('CLOSED', $this->userContext->getUserId(), $item->id);
							$ctb->setThreadUpdated($id);
							$ctb->db->commit();
							header('Location: ' . $_SERVER['HTTP_REFERER']);
							exit;
						} else {
							throw new \Exception("Contribution is already closed or has an accepted answer");
						}
					}
				} else {
					throw new \Exception("Can only close own contributions");
				}
			}
			exit;
		} else {
			throw new \Exception("Not logged in");
		}
	}

	public function upvoteAction($id, $undo = null) {
		if($this->userContext->isLoggedIn()) {
			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);
			

			$item = $ctb->find($id);
			if( $item ) { 

				if( $item->user_id != $this->userContext->getUserId()) {

					$existing = $ctb->getUserActivity(['DOWNVOTE','UPVOTE'], $this->userContext->getUserId(), $id);
					if( !$existing ) {
						$ctb->db->begin();
						// Add "Upvote" activity to the current user
						$voteActivityId = $ctb->logUserActivity('UPVOTE', $this->userContext->getUserId(), $id);

						// Add "Upvoted" activity to the user upvoted
						$ctb->logUserActivity('UPVOTED', $item->user_id, $id, $voteActivityId);
						$ctb->setThreadUpdated($id);
						$ctb->db->commit();

						header('Location: ' . $_SERVER['HTTP_REFERER']);
						exit;
					} else {
						if( $undo ) {
							$ctb->db->begin();
							$ctb->deleteUserActivity('UPVOTE', $this->userContext->getUserId(), $item->id);
							$ctb->deleteUserActivity('UPVOTED', $item->user_id, $item->id);
							$ctb->setThreadUpdated($item->id);
							$ctb->db->commit();
							header('Location: ' . $_SERVER['HTTP_REFERER']);
						} else {
							throw new \Exception("Can not vote on an item more than once");
						}
					}
				} else {
					throw new \Exception("Can not upvote own contributions");
				}
			}
			exit;
		} else {
			throw new \Exception("Not logged in");
		}
	}

	public function downvoteAction($id, $undo = null) {
		if($this->userContext->isLoggedIn()) {
			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);
			

			$item = $ctb->find($id);
			if( $item ) { 

				if( $item->user_id != $this->userContext->getUserId()) {

					$existing = $ctb->getUserActivity(['DOWNVOTE','UPVOTE'], $this->userContext->getUserId(), $id);
					if( !$existing ) {
						$ctb->db->begin();
						// Add "Upvote" activity to the current user
						$voteActivityId = $ctb->logUserActivity('DOWNVOTE', $this->userContext->getUserId(), $id);

						// Add "Downvotes" activity to the user upvoted
						$ctb->logUserActivity('DOWNVOTED', $item->user_id, $id, $voteActivityId);
						$ctb->setThreadUpdated($id);
						$ctb->db->commit();

						header('Location: ' . $_SERVER['HTTP_REFERER']);
						exit;
					} else {
						if( $undo ) {
							$ctb->db->begin();
							$ctb->deleteUserActivity('DOWNVOTE', $this->userContext->getUserId(), $item->id);
							$ctb->deleteUserActivity('DOWNVOTED', $item->user_id, $item->id);
							$ctb->setThreadUpdated($id);
							$ctb->db->commit();
							header('Location: ' . $_SERVER['HTTP_REFERER']);
						} else {
							throw new \Exception("Can not vote on an item more than once");
						}
					}
				} else {
					throw new \Exception("Can not downvote own contributions");
				}
			}
			exit;
		} else {
			throw new \Exception("Not logged in");
		}
	}


	public function taggedAction($tagSlug) {
		$this->theme->setTitle("Tags");
		
		$this->theme->setTitle("Frågor");
		$this->listQuestions([
			'tag' => $tagSlug
		]);

	}


	public function viewAction($id, $slug = null) {
		
		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$row = $ctb->find($id);

		if($row->type != 'QUERY') {
			echo "no";
			exit;
		}
		$thread_owner = $row->user_id;
		$thread_answer_id = $row->accepted_answer;



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

		$slug = $row->slug;
		$commentformHtml = "";

		if( $this->userContext->isLoggedIn() ) {
			$form = new \Nuffsaid\Contributions\CContributionForm(array(
				'digest' => $this->userContext->getFormDigest(),
				'reply_to' => $id, // Which post to reply to
				'is_comment' => true,
				'reply_to_slug' => $row->slug
			));
			$form->setDi($this->di);
			$commentformHtml = $form->getHtml();
		}

		// List comments
		$comments = $ctb->findCommentsTo($id);


		$this->views->add('question/display-single', [
			'item' => $row,
			'title' => htmlentities($row->title, null, 'utf-8'),
			'text' => $ctb->filter($row->body),
			'avatar' => 'http://www.gravatar.com/avatar/' . md5($row->email) . '.jpg?s=48',
			'created' => $row->created,
			'username' => $row->acronym,
			'userlink' => $this->url->create('users/view/' . urlencode($row->acronym)),
			'tags' => $row->tags,
			'type' => $row->type,
			'user_id' => $row->user_id,
			'upvote' => isset($row->upvote) ? $row->upvote : false,
			'downvote' => isset($row->downvote) ? $row->downvote : false,
			'upvoted' => $row->upvoted,
			'accepted' => false,
			'downvoted' => $row->downvoted,
			'rating' => $row->calcUpvoteCount - $row->calcDownvoteCount,
			'close' => ($row->user_id == $this->userContext->getUserId() && !$row->accepted_answer),
			'closed' => $row->closed,
			'id' => $row->id,
			'commentform' => $commentformHtml,
			'comments' => $comments,
			'currentuser_avatar' => 'http://www.gravatar.com/avatar/' . md5($this->userContext->getUserEmail()) . '.jpg?s=24',
		], 'main');	

/*
<img width="24" height="24" alt="avatar" style="vertical-align:top; margin-top: 2px;" src="<?= $currentuser_avatar ?>">
				<input type="text" placeholder="Skriv din kommentar här" style="width: 80%; color:#333; padding:4px;" />
				<p style="margin:4px 0; line-height: 1.5em; font-size: .85em">Använd kommentarer om du behöver mer information för att kunna ge ett svar.<br />
				Du kan använda Markdown - men håll det kortfattat!</p>
 */


		$this->views->add('question/comments-navigation', [
			'count' => $row->calcAnswerCount
			]);

		// List answers
		$rows = $ctb->findAnswersTo($id);

		foreach($rows as $row) {
			$commentformHtml = "";


			if( $this->userContext->isLoggedIn() ) {
				$commentform = new \Nuffsaid\Contributions\CContributionForm(array(
					'digest' => $this->userContext->getFormDigest(),
					'reply_to' => $row->id, // Which post to reply to
					'is_comment' => true
				));
				$commentform->setDi($this->di);
				$commentformHtml = $commentform->getHtml();
			}

			// List comments
			$comments = $ctb->findCommentsTo($row->id);

			$this->views->add('question/display-single', [
				'item' => $row,
				'id' => $row->id,
				'title' => htmlentities($row->title, null, 'utf-8'),
				'text' => $this->filter->doFilter($row->body, 'markdown'),
				'avatar' => $row->avatar,
				'created' => $row->created,
				'userlink' => $this->url->create('users/view/' . urlencode($row->acronym)),
				'username' => $row->acronym,
				'user_id' => $row->user_id,
				'type' => $row->type,
				'upvote' => isset($row->upvote) ? $row->upvote : false,
				'downvote' => isset($row->downvote) ? $row->downvote : false,
				'downvoted' => $row->downvoted,
				'upvoted' => $row->upvoted,
				'rating' => $row->calcUpvoteCount - $row->calcDownvoteCount,
				'accept' => $thread_owner == $this->userContext->getUserId() && $row->user_id != $thread_owner,
				'accepted' => $row->id == $thread_answer_id,
				'commentform' => $commentformHtml,
				'comments' => $comments,
				'currentuser_avatar' => 'http://www.gravatar.com/avatar/' . md5($this->userContext->getUserEmail()) . '.jpg?s=24',
			], 'main');

		}

		if( $this->userContext->isLoggedIn() ) {
			$form = new \Nuffsaid\Contributions\CContributionForm(array(
				'digest' => $this->userContext->getFormDigest(),
				'reply_to' => $id, // Which post to reply to
				'form_action' => '#answers'
			));
			$form->setDi($this->di);
			$form->addForm('main');
		} else {
			$this->views->add('question/notloggedin', [], 'main');
		}

	}

	public function askAction() {

		$this->theme->setTitle("Ställ en fråga");
		if( !$this->userContext->isLoggedIn() ){
			$this->views->add('shared/join', [], 'main');
			return;
		}

		$form = new \Nuffsaid\Contributions\CContributionForm(array(
			'digest' => $this->userContext->getFormDigest()
			#'reply_to' => 0, // Which post to reply to
			#'id' => // if editing an existing post
		));
		$form->setDi($this->di);
		$form->addForm('main');
		$this->views->add('editor/cheatsheet', [], 'sidebar');




	}

}
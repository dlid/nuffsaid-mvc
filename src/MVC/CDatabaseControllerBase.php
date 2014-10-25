<?php

namespace Anax\MVC;
 
/**
 * A controller for users and admin related events.
 *
 */
class CDatabaseControllerBase implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;

	protected $model;

	public function getSource() {
		return strtolower(str_replace("Controller", "", implode('', array_slice(explode('\\', get_class($this)), -1))));
	}
 	
	/**
	* Setup the controller.
	*
	* @return void
	*/
	public function SetupController($options)
	{

		$this->options = array_merge_recursive(
			[
				'singular' => 'Item',
				'plural' => 'Items',
				'model' => null,

				// Array of column names for add-action
				'add' => [],

				// Optional column with created date
				'created' => 'created',

				// Optional column with updated date
				'updated' => 'updated',

				// Optional column with soft delete date
				'softDelete' => 'deleted'
			],
			 $options);

		if(!isset($this->options['model'])) {
			die("Model not defined");
		}

		$this->model = new \Anax\Comments\Comment();
		$this->model->setDI($this->di);
	}

  /**
	 * List all users.
	 *
	 * @return void
	 */
	public function listAction()
	{
	    $all = $this->model->findAll();

	    $this->theme->setTitle("List all comments");
	    $this->views->add('comment/list-all', [
	        'items' => $all,
	        'title' => "View all comments",
	    ]);
	}

	/**
	* List user with id.
	*
	* @param int $id of user to display
	*
	* @return void
	*/
	public function idAction($id = null)
	{

			if(!$id) {
				header("Location: " .  $this->url->create('users/list'));
				exit;
			}

	    $user = $this->model->find($id);
	 
	    $this->theme->setTitle("View comment with id");
	    $this->views->add('comment/view', [
	    		'title' => "Visa användare #{$id}",
	        'item' => $user
	    ]);
	}


	/**
	* Add new user.
	*
	* @param string $acronym of user to add.
	*
	* @return void
	*/
	public function addAction()
	{
	  $now = date('Y-m-d H:i:s');

	  // Insert based on the parameters set in the "add" option
	  $parameters = [];
	  $args = func_get_args();

	  for($i=0; $i < count($this->options['add']); $i++) {
	  	$key = $this->options['add'][$i];

	  	if(!isset($args[$i])) {
	  		die("Bad args. Parameters: " . implode(', ', $this->options['add']) );
	  	}
  		$parameters[$key] = $args[$i];
	  }

	  if(isset($this->options['created']) && !empty($this->options['created'])) {
	  	$parameters[$this->options['created']] = $now;
	  }

	  if(isset($this->options['updated']) && !empty($this->options['updated'])) {
	  	$parameters[$this->options['updated']] = $now;
	  }

	  $this->model->create($parameters);

	  $url = $this->url->create( $this->getSource() . '/id/' . $this->model->id);
	  $this->response->redirect($url);
	}



	/**
	* Delete user.
	*
	* @param integer $id of user to delete.
	*
	* @return void
	*/
	public function deleteAction($id = null)
	{
		if (!isset($id)) {
		    die("Missing id");
		}

		$res = $this->model->delete($id);

		$url = $this->url->create($this->getSource() . '/list');
		$this->response->redirect($url);
	}

	/**
 * Delete (soft) user.
 *
 * @param integer $id of user to delete.
 *
 * @return void
 */
	public function softDeleteAction($id = null)
	{
	  if (!isset($id)) {
	      die("Missing id");
	  }


	  if(!isset($this->options['softDelete']) || empty($this->options['softDelete'])) {
	  	die("Soft delete not applicable");
	  }

	  $now = date('Y-m-d H:i:s');

	  $user = $this->model->find($id);

	  $user->deleted = $now;
	  $user->save();

	  $url = $this->url->create($this->getSource() . '/id/' . $id);
	  $this->response->redirect($url);
	}



	/**
	* List all active and not deleted users.
	*
	* @return void
	*/
	/*public function activeAction()
	{
	  $all = $this->users->query()
	      ->where('active IS NOT NULL')
	      ->andWhere('deleted is NULL')
	      ->execute();

	  $this->theme->setTitle("Users that are active");
	  $this->views->add('users/list-all', [
	      'users' => $all,
	      'title' => "Users that are active",
	  ]);
	}

	/**
	* List all inactive and not deleted users.
	*
	* @return void
	* /
	public function inactiveAction()
	{
		$all = $this->users->query()
		    ->where('active IS NULL')
		    ->andWhere('deleted is NULL')
		    ->execute();

		$this->theme->setTitle("Users that are inactive");
		$this->views->add('users/list-all', [
		    'users' => $all,
		    'title' => "Users that are inactive",
		]);
	}*/

	/**
	* List all deleted users.
	*
	* @return void
	*/
	public function deletedAction()
	{
		$all = $this->users->query()
		    ->where('deleted is NOT NULL')
		    ->execute();

		$this->theme->setTitle("Users that are deleted");
		$this->views->add('users/list-all', [
		    'users' => $all,
		    'title' => "Users that are deleted",
		]);
	}


}
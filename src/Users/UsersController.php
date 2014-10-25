<?php

namespace Anax\Users;
 
/**
 * A controller for users and admin related events.
 *
 */
class UsersController implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;


	/**
	* Initialize the controller.
	*
	* @return void
	*/
	public function initialize()
	{
		$this->users = new \Anax\Users\User();
		$this->users->setDI($this->di);
	}

	 /**
	 * Startpage
	 *
	 * @return void
	 */
	public function indexAction()
	{
	    $all = $this->users->findAll();

	    $this->theme->setTitle("List all users");
	    $this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
	    $this->views->addString( '<h1>Användare</h1><p>Här hittar du alla tester för Användare för kursmoment 04.</p><p>Navigera genom menyn till höger.</p>','main');
	}

  /**
	 * List all users.
	 *
	 * @return void
	 */
	public function listAction()
	{
	    $all = $this->users->findAll();

	    $this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');	
	    $this->theme->setTitle("Visa alla användare");
	    $this->views->add('users/list-all', [
	        'users' => $all,
	        'title' => "Alla användare",
	    ], 'main');
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

	    $user = $this->users->find($id);
	 		
	 		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');	
	    $this->theme->setTitle("View user with id");
	    $this->views->add('users/view', [
	    		'title' => "Visa användare #{$id}",
	        'user' => $user
	    ]);
	}

	/**
	 * Validation to avoid two users with same alias
	 * @param  [type]  $value [description]
	 * @return boolean        [description]
	 */
	public function isDuplicateAcronym($value) {
		$user = $this->users->findByAcronym($value);
		return empty($user);
	}

	/**
	 * Validation to avoid two users with same email
	 * @param  [type]  $value [description]
	 * @return boolean        [description]
	 */
	public function isDuplicateEmail($value) {
		$user= $this->users->findByEmail($value);
		return empty($user);
	}


	/**
	* Add new user.
	*
	* @return void
	*/
	public function addAction()
	{
		
		$di = $this->di;

		$form = new \Mos\HTMLForm\CForm([], [
			  'name' => [
			    'type'  => 'text',
			    'label' => 'Namn',
			    'validation'  => ['not_empty']
			  ],
			  'acronym' => [
			    'type'  => 'text',
			    'label' => 'Alias',
			    'validation'  => [
			    	'not_empty',
			    	'custom_test' => [
			    		'message' => 'Det finns redan en användare med samma alias',
			    		'test' => array($this, 'isDuplicateAcronym')
			    	]
					]
			  ],
			  'email' => [
			    'type'  => 'email',
			    'label' => 'E-post',
			    'validation'  => ['not_empty', 'email_adress',
			    'custom_test' => [
			    		'message' => 'Det finns redan en användare med samma e-post',
			    		'test' => array($this, 'isDuplicateEmail')
			    	]
			    ]
			  ],
			  'password' => [
			    'type'  => 'password',
			    'label' => 'Välj lösenord',
			    'validation'  => [
			    'custom_test' => [
			    		'message' => 'Lösenordet måste vara minst 4 tecken långt och innehålla minst en siffra',
			    		'test' => function($value) {
			    			if (strlen($value) < 4) return false;
			    			if (!preg_match("/\d/", $value)) return false;
			    			return true;
			    		}
			    	]]
			  ],
			  'submit' => [
			    'type'      => 'submit',
			    'callback'  => function($form) {
			      $form->saveInSession = true;
			      return true;
			    }
			  ],
			  'output-write' => function($output, $errors) use ($di) {
			      if ($errors) { 
			          $di->views->addString($output, 'flash-warning');
			      } else {
			          $di->views->addString($output, 'flash-success');
			      }
			  }
			]
		);
		
		// Check the status of the form
    $status = $form->check();

    if ($status === true) {
    	$now = date('Y-m-d H:i:s');

			$this->users->create([
				'acronym' => $form->value('acronym'),
				'email' => $form->value('email'),
				'name' => $form->value('name'),
				'password' => password_hash($form->value('password'), PASSWORD_DEFAULT),
				'created' => $now,
				'active' => $now
			]);


			$url = $this->url->create('users/id/' . $this->users->id);
	  	$this->response->redirect($url);

    } else if ($status === false) {
        $form->AddOutput("<h2>Hoppsan!</h2><p>Ett fel uppstod. Kontrollera att du fyllt i formuläret på rätt sätt.</p>", 'gw');
        header("Location: " . $di->request->getCurrentUrl());
    }
    $this->theme->setTitle('Lägg till användare');
		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
		$this->views->addString("<h1>Lägg till användare</h1>" . $form->getHTML(['novalidate' => true]), 'main');

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
		
			$di = $this->di;

			$allUsers = [ 0 => ''];

		$all = $this->users->query()
		    ->where('deleted is NULL')
		    ->execute();


			foreach( $all as $user){
				$allUsers[$user->id] = "#{$user->id} {$user->acronym} ({$user->email})";
			}

			$form = new \Mos\HTMLForm\CForm([], [
				  'user' => [
				    'type'  => 'select',
				    'label' => 'Användare',
				    'options' => $allUsers,
				    'value' => $id,
				    'validation'  => [
				    	'custom_test' => [
				    		'message' => 'Ingen användare vald',
				    		'test' => function($value) {
				    		return $value !== '0';
				    	}]
				    ]
				  ],
				  'soft' => [
				  	'type' => 'checkbox',
				  	'label' => 'Soft delete'
				  ],
				  'submit' => [
				    'type'      => 'submit',
				    'value' => 'Delete',
				    'callback'  => function($form) {
				      $form->saveInSession = true;
				      return true;
				    }
				  ],
				  'output-write' => function($output, $errors) use ($di) {
				      if ($errors) { 
				          $di->views->addString($output, 'flash-warning');
				      } else {
				          $di->views->addString($output, 'flash-success');
				      }
				  }
				]
			);
		
			// Check the status of the form
	    $status = $form->check();

	    if ($status === true) {
				$form->AddOutput("<h2>Great success!</h2><p>Användaren är borttagen</p>");

				if( $form->checked('soft') ) {
					$user = $this->users->find($form->value('user'));
					$user->deleted = date('Y-m-d H:i:s');
		    	$user->save();
				} else {
	    		$res = $this->users->delete($form->value('user'));
	    	}
				$url = $this->url->create('users/delete');
		  	$this->response->redirect($url);
	    } else if ($status === false) {
	        $form->AddOutput("<h2>Hoppsan!</h2><p>Ett fel uppstod. Kontrollera att du fyllt i formuläret på rätt sätt.</p>", 'gw');
	        header("Location: " . $di->request->getCurrentUrl());
	    }
	    $this->theme->setTitle('Ta bort användare');
			$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
			$this->views->addString("<h1>Ta bort användare</h1>" .  (count($allUsers) > 1 ? $form->getHTML(['novalidate' => true]) : '<p>Det finns inga användare att ta bort</p>'), 'main');

	}

	/**
	* Delete user.
	*
	* @param integer $id of user to delete.
	*
	* @return void
	*/
	public function deactivateAction($id = null)
	{
		
			$di = $this->di;

			$all = $this->users->query()
		    ->where('active IS NOT NULL')
		    ->andWhere('deleted is NULL')
		    ->execute();

			$allUsers = [ 0 => ''];
			foreach( $all as $user){
				$allUsers[$user->id] = "#{$user->id} {$user->acronym} ({$user->email})";
			}

			$form = new \Mos\HTMLForm\CForm([], [
				  'user' => [
				    'type'  => 'select',
				    'label' => 'Användare',
				    'options' => $allUsers,
				    'value' => $id,
				    'validation'  => [
				    	'custom_test' => [
				    		'message' => 'Ingen användare vald',
				    		'test' => function($value) {
				    		return $value !== '0';
				    	}]
				    ]
				  ],
				  'submit' => [
				    'type'      => 'submit',
				    'value' => 'Inaktivera',
				    'callback'  => function($form) {
				      $form->saveInSession = true;
				      return true;
				    }
				  ],
				  'output-write' => function($output, $errors) use ($di) {
				      if ($errors) { 
				          $di->views->addString($output, 'flash-warning');
				      } else {
				          $di->views->addString($output, 'flash-success');
				      }
				  }
				]
			);
		
			// Check the status of the form
	    $status = $form->check();

	    if ($status === true) {
				$form->AddOutput("<h2>Great success!</h2><p>Användaren är inaktiverad</p>");

				$user = $this->users->find($form->value('user'));
				$user->active = null;
	    	$user->save();
				$url = $this->url->create('users/deactivate');
		  	$this->response->redirect($url);
	    } else if ($status === false) {
	        $form->AddOutput("<h2>Hoppsan!</h2><p>Ett fel uppstod. Kontrollera att du fyllt i formuläret på rätt sätt.</p>", 'gw');
	        header("Location: " . $di->request->getCurrentUrl());
	    }
	    $this->theme->setTitle('Inaktivera användare');
			$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
			$this->views->addString("<h1>Inaktivera användare</h1>" .  (count($allUsers) > 1 ? $form->getHTML(['novalidate' => true]) : '<p>Det finns inga användare att inaktivera</p>'), 'main');

	}


	/**
	* Activate user.
	*
	* @param integer $id of user to delete.
	*
	* @return void
	*/
	public function activateAction($id = null)
	{
		
			$di = $this->di;

			$all = $this->users->query()
		    ->where('active IS NULL')
		    ->andWhere('deleted is NULL')
		    ->execute();

			$allUsers = [ 0 => ''];
			foreach( $all as $user){
				$allUsers[$user->id] = "#{$user->id} {$user->acronym} ({$user->email})";
			}

			$form = new \Mos\HTMLForm\CForm([], [
				  'user' => [
				    'type'  => 'select',
				    'label' => 'Användare',
				    'options' => $allUsers,
				    'value' => $id,
				    'validation'  => [
				    	'custom_test' => [
				    		'message' => 'Ingen användare vald',
				    		'test' => function($value) {
				    		return $value !== '0';
				    	}]
				    ]
				  ],
				  'submit' => [
				    'type'      => 'submit',
				    'value' => 'Aktivera',
				    'callback'  => function($form) {
				      $form->saveInSession = true;
				      return true;
				    }
				  ],
				  'output-write' => function($output, $errors) use ($di) {
				      if ($errors) { 
				          $di->views->addString($output, 'flash-warning');
				      } else {
				          $di->views->addString($output, 'flash-success');
				      }
				  }
				]
			);
		
			// Check the status of the form
	    $status = $form->check();

	    if ($status === true) {
				$form->AddOutput("<h2>Great success!</h2><p>Användaren är aktiverad</p>");

				$user = $this->users->find($form->value('user'));
				$user->active = date('Y-m-d H:i:s');
	    	$user->save();
				$url = $this->url->create('users/activate');
		  	$this->response->redirect($url);
	    } else if ($status === false) {
	        $form->AddOutput("<h2>Hoppsan!</h2><p>Ett fel uppstod. Kontrollera att du fyllt i formuläret på rätt sätt.</p>", 'gw');
	        header("Location: " . $di->request->getCurrentUrl());
	    }
	    $this->theme->setTitle('Aktivera användare');
			$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
			$this->views->addString("<h1>Aktivera användare</h1>" .  (count($allUsers) > 1 ? $form->getHTML(['novalidate' => true]) : '<p>Det finns inga användare att aktivera</p>'), 'main');

	}


/**
	* Activate user.
	*
	* @param integer $id of user to delete.
	*
	* @return void
	*/
	public function restoreAction($id = null)
	{
		
			$di = $this->di;

			$all = $this->users->query()
		    ->where('deleted is NOT NULL')
		    ->execute();

			$allUsers = [ 0 => ''];
			foreach( $all as $user){
				$allUsers[$user->id] = "#{$user->id} {$user->acronym} ({$user->email})";
			}

			$form = new \Mos\HTMLForm\CForm([], [
				  'user' => [
				    'type'  => 'select',
				    'label' => 'Användare',
				    'options' => $allUsers,
				    'value' => $id,
				    'validation'  => [
				    	'custom_test' => [
				    		'message' => 'Ingen användare vald',
				    		'test' => function($value) {
				    		return $value !== '0';
				    	}]
				    ]
				  ],
				  'submit' => [
				    'type'      => 'submit',
				    'value' => 'Återställ',
				    'callback'  => function($form) {
				      $form->saveInSession = true;
				      return true;
				    }
				  ],
				  'output-write' => function($output, $errors) use ($di) {
				      if ($errors) { 
				          $di->views->addString($output, 'flash-warning');
				      } else {
				          $di->views->addString($output, 'flash-success');
				      }
				  }
				]
			);
		
			// Check the status of the form
	    $status = $form->check();

	    if ($status === true) {
				$form->AddOutput("<h2>Great success!</h2><p>Användaren är återställd</p>");

				$user = $this->users->find($form->value('user'));
				$user->deleted = null;
	    	$user->save();
				$url = $this->url->create('users/restore');
		  	$this->response->redirect($url);
	    } else if ($status === false) {
	        $form->AddOutput("<h2>Hoppsan!</h2><p>Ett fel uppstod. Kontrollera att du fyllt i formuläret på rätt sätt.</p>", 'gw');
	        header("Location: " . $di->request->getCurrentUrl());
	    }
	    $this->theme->setTitle('Åteställ användare');
			$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
			$this->views->addString("<h1>Åteställ användare</h1><p>Här kan du återställa användare som tagits bort (fungerar endast vid 'soft' delete)</p>" .  (count($allUsers) > 1 ? $form->getHTML(['novalidate' => true]) : '<p>Det finns inga radera användare att återställa</p>'), 'main');

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

	  $now = date(DATE_RFC2822);

	  $user = $this->users->find($id);

	  $user->deleted = $now;
	  $user->save();

	  $url = $this->url->create('users/id/' . $id);
	  $this->response->redirect($url);
	}



	/**
	* List all active and not deleted users.
	*
	* @return void
	*/
	public function activeAction()
	{
	  $all = $this->users->query()
	      ->where('active IS NOT NULL')
	      ->andWhere('deleted is NULL')
	      ->execute();

		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');	
		$this->theme->setTitle("Visa aktiva användare");
		$this->views->add('users/list-all', [
		    'users' => $all,
		    'title' => "Aktiva användare",
		], 'main');

	}

	/**
	* List all inactive and not deleted users.
	*
	* @return void
	*/
	public function inactiveAction()
	{
		$all = $this->users->query()
		    ->where('active IS NULL')
		    ->andWhere('deleted is NULL')
		    ->execute();

		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');	
		$this->theme->setTitle("Visa inaktiva användare");
		$this->views->add('users/list-all', [
		    'users' => $all,
		    'title' => "Inaktiva användare",
		], 'main');
	}

	/**
	* List all deleted users.
	*
	* @return void
	*/
	public function trashcanAction()
	{
		$all = $this->users->query()
		    ->where('deleted is NOT NULL')
		    ->execute();

		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');	
		$this->theme->setTitle("Borttagna användare");
		$this->views->add('users/list-all', [
		    'users' => $all,
		    'title' => "Borttagna användare",
		]);
	}



	public function setupAction() {

		ob_start();

    $this->db->setVerbose();

    $this->db->dropTableIfExists('user')->execute();
 
    $this->db->createTable(
        'user',
        [
            'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
            'acronym' => ['varchar(20)', 'unique', 'not null'],
            'email' => ['varchar(80)'],
            'name' => ['varchar(80)'],
            'password' => ['varchar(255)'],
            'created' => ['datetime'],
            'updated' => ['datetime'],
            'deleted' => ['datetime'],
            'active' => ['datetime'],
        ]
    )->execute();

   $this->db->insert(
        'user',
        ['acronym', 'email', 'name', 'password', 'created', 'active']
    );
 
    $now = date('Y-m-d H:i:s');
 
    $this->db->execute([
        'admin',
        'admin@dbwebb.se',
        'Administrator',
        password_hash('admin', PASSWORD_DEFAULT),
        $now,
        $now
    ]);
 
    $this->db->execute([
        'doe',
        'doe@dbwebb.se',
        'John/Jane Doe',
        password_hash('doe', PASSWORD_DEFAULT),
        $now,
        $now
    ]);

    $content = ob_get_clean();
    $this->theme->setTitle('Återställ databas');

		$this->views->addString( $content ,'main');
		$this->views->addString( $this->di->navbar->getSubmenu() ,'sidebar');
		$this->views->addString( "<h1>Databas återställd</h1>" ,'flash-success');


	}


}
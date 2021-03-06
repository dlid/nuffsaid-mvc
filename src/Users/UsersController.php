<?php

namespace Anax\Users;
 
/**
 * A controller for users and admin related events.
 *
 */
class UsersController implements \Anax\DI\IInjectionAware
{
	use \Anax\DI\TInjectable;


	public function logoutAction() {
		$this->userContext->logout();
		$this->views->add('users/loggedout', [], 'main');
	}

	public function welcomeAction() {
		$this->theme->setTitle("Välkommen");
		if( $this->userContext->isLoggedIn() ){
			$this->views->add('users/welcome', [
				'name' =>  htmlentities($this->userContext->getUserDisplayName(), null, 'UTF-8')
				], 'main');
		} else {
			$this->dispatcher->forward([
            'controller' => 'error',
            'action' => 'statusCode',
            'params' => [
                'code' => 403,
                'message' => "Du saknar behörighet för den här sidan",
            ],
        ]);
		}
	}

	public function signupAction() {
		$this->theme->setTitle("Bli medlem");
		$form = $this->getSignupForm();

		$status = $form->check();

		$formOrMessage = $form->getHTML(array('novalidate' => true));

    if ($status === true) {
			header("Location: " . $this->url->create('users/welcome'));
    } 

		$this->views->add('users/signup',[
			'form' => $formOrMessage,
			], 'main');
	}

	public function updateAction() {
		$this->theme->setTitle("Uppdatera profil");
		$form = $this->getUpdateForm($this->userContext->getUserId());

		$status = $form->check();

		$formOrMessage = $form->getHTML(array('novalidate' => true));

    if ($status === true) {
			header("Location: " . $this->url->create('users/view/' . $this->userContext->getUserAcronym()));
    } 

		$this->views->add('users/update',[
			'form' => $formOrMessage,
			], 'main');
	}


	public function lookatmeAction() {
		header("Content-Type: application/json");
		$ret = array();

		// Update last seen for the user, and vacuum expired sessions as well
		if( $this->userContext->isLoggedIn(true) ){
			$ret['saw-you'] = $this->userContext->seen;
		}
		echo json_encode($ret);
		exit;
	}

	public function loginAction() {
		$this->theme->setTitle("Logga in");
		$form = $this->getLoginForm();

		$status = $form->check();

		$formOrMessage = $form->getHTML(array('novalidate' => true));

    if ($status === true) {
			header("Location: " . $this->url->create(''));
    }else if( $status === false ){
    	header('Location: ' . $this->url->create('users/login'));
    }

		$this->views->add('users/login',[
			'form' => $formOrMessage,
			], 'main');
	}

	public function testForUserWithUsername($value) {
		if( $value != "") {
			$item = $this->users->findByAcronym($value);
			if($item) {
				return false;
			}
			return true;
		}
	}

	public function testForUserWithEmail($value) {
		if( $value != "") {
			$item = $this->users->findByEmail($value, $this->userContext->getUserId());
			if($item) {
				return false;
			}
			return true;
		}
	}

	public function testPassword($value) {
		if( $value !="") {
			if( strlen($value) >= 6) {
				if( preg_match("/\d/", $value) && preg_match("/[a-z]/i", $value)) {
					return true;
				}
			}
			return false;
		}
	}

	private function getLoginForm() {
		$di = $this;
		$form = $this->form->create([], [
        'usernameoremail' => [
            'type'        => 'text',
            'label'       => 'Användarnamn eller e-post',
            'required'    => true, 
            'maxlength'   => 255,
            'validation'  => array(
            	'not_empty'
            )
        ],
        'password' => [
        	'label'       => 'Lösenord',
            'type'        => 'password',
            'required'    => true,
            'validation'  => array(
            	'not_empty'
	           ),
        ],
        'submit' => [
        		'value' 		=> 'Logga in',
            'type'      => 'submit',
            'callback'  => function ($form) use ($di) {
            		if( $di->userContext->login($form->Value('usernameoremail'), $form->Value('password'))) {
            			return true;
            		}

            		$form->AddOutput('Felaktigt användarnamn eller lösenord');

                return false;
            }
        ]
    ]);
		return $form;
	}

	private function getSignupForm() {
		$di = $this;
		$form = $this->form->create([], [
        'arconym' => [
            'type'        => 'text',
            'label'       => 'Användarnamn',
            'required'    => true, 
            'maxlength'   => 20,
            'validation'  => array(
            	'not_empty',
            	'alphanumeric',
	            'custom_test' => array(
	            		'message' => 'Användarnamnet används redan', 
	            		'test' => array($this, 'testForUserWithUsername')
	            )
            )
        ],
        'email' => [
        	'label'       => 'E-post',
            'type'        => 'text',
            'required'    => true,
            'validation'  => array(
            	'not_empty', 'email_adress',
	            'custom_test' => array(
	            		'message' => 'En användare med denna e-post finns redan', 
	            		'test' => array($this, 'testForUserWithEmail')
	            )
	           ),
        ],
        'name' => [
            'type'        => 'text',
            'label'       => 'Namn',
            'maxlength'   => 80,
            'required'    => false,
            'validation'  => ['not_empty'],
        ],
        'password' => [
            'type'        => 'password',
            'required'    => true,
            'maxlength'   => 60,
            'validation'  => array('not_empty',
	            'custom_test' => array(
	            		'message' => 'Lösenordet måste vara minst 6 tecken långt och innehålla minst en siffra och en bokstav', 
	            		'test' => array($this, 'testPassword')
	            )),
        ],
        'submit' => [
        		'value' 		=> 'Bli medlem',
            'type'      => 'submit',
            'callback'  => function ($form) use ($di) {
//
            		$now = date('Y-m-d H:i:s');
					    	$di->users->create(array(
					    		'acronym' => $form->Value('arconym'),
					    		'email' => $form->Value('email'),
					    		'name' => $form->Value('name'),
					    		'password' => password_hash($form->value('password'), PASSWORD_DEFAULT),
					    		'created' => $now,
					    		'updated' => $now,
					    		'active' => $now,
					    		'is_admin' => 0
					    	));

					    	if( $di->users->id ) {
					    		if( !$di->userContext->loginUserById($di->users->id)) {
					    			$form->AddOutput('Du kunde inte loggas in. Försök igen senare.');
					    			return false;
					    		}
					    	} else {
					    		$form->AddOutput('Användaren kunde inte skapas. Försök igen senare.');
					    		return false;
					    	}
                return true;
            }
        ]
    ]);
		return $form;
	}

	private function getUpdateForm() {
		$di = $this;
		$form = $this->form->create([], [
        'email' => [
        	'label'       => 'E-post',
            'type'        => 'text',
            'required'    => true,
            'value' => $this->userContext->getUserEmail(),
            'validation'  => array(
            	'not_empty', 'email_adress',
	            'custom_test' => array(
	            		'message' => 'En användare med denna e-post finns redan', 
	            		'test' => array($this, 'testForUserWithEmail')
	            )
	           ),
        ],
        'name' => [
            'type'        => 'text',
            'label'       => 'Namn',
            'value' => $this->userContext->getUserName(),
            'maxlength'   => 80,
            'required'    => false,
            'validation'  => ['not_empty'],
        ],
        'submit' => [
        		'value' 		=> 'Spara',
            'type'      => 'submit',
            'callback'  => function ($form) use ($di) {

            		$now = date('Y-m-d H:i:s');

            		$this->db->update('user',
            			array('name','email', 'updated'),
            			array($form->Value('name'),$form->Value('email'), $now),
            			'id=?');

            		$this->db->execute([$this->userContext->getUserId()]);

                return true;
            }
        ]
    ]);
		return $form;
	}

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

			$ctb = new \Anax\Contributions\Contribution();
			$ctb->setDi($this->di);

			$users = $ctb->findUsers($this->request->getGet('q'));

	    $this->theme->setTitle("List all users");
	    $this->views->add('users/list', [
	    	'query' => $this->request->getGet('q'),
	    	'users' => $users
	    	]);

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

	public function viewAction($acronym) {

		$ctb = new \Anax\Contributions\Contribution();
		$ctb->setDi($this->di);

		$user = $ctb->findUserByAcronym($acronym);
		

		if( $user ) {
			$activities = $ctb->findUserActivities($user->user_id);
			$this->theme->setTitle(htmlentities($user->acronym, null, 'utf-8'));
			$this->views->add('users/view', [
				'item' => $user
			]);
				$this->views->add('users/activities', [
				'userActivities' => $activities,
				'title' => 'Aktiviteter'
			]);
			$this->views->add('users/questions', [
				'yourOpenQuestions' => $ctb->findUserQuestions($user->user_id),
				'skipavatar' => true,
				'title' => 'Senaste frågorna'
			]);
			$this->views->add('users/answers', [
				'recentlyAnswered' => $ctb->findUserAnswers($user->user_id),
				'skipavatar' => true,
				'title' => 'Senaste svaren'
			]);
		} else {
			throw new \Exception("User not found");
		}


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
<?php

namespace App\FormTest;

/**
 * Anax base class for wrapping sessions.
 *
 */
class FormTestController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectionaware;

    // Adapted from Java code at http://www.merriampark.com/anatomycc.htm
    // by Andy Frey, onesandzeros.biz
    // Checks for valid credit card number using Luhn algorithm
    // Source from: http://onesandzeros.biz/notebook/ccvalidation.php
    // 
    // Try the following numbers, they should be valid according to the check:
    // 4408 0412 3456 7893
    // 4417 1234 5678 9113
    //
    function isValidCCNumber( $ccNum ) {

        $digitsOnly = "";
        // Filter out non-digit characters
        for( $i = 0; $i < strlen( $ccNum ); $i++ ) {
            if( is_numeric( substr( $ccNum, $i, 1 ) ) ) {
                $digitsOnly .= substr( $ccNum, $i, 1 );
            }
        }
        // Perform Luhn check
        $sum = 0;
        $digit = 0;
        $addend = 0;
        $timesTwo = false;
        for( $i = strlen( $digitsOnly ) - 1; $i >= 0; $i-- ) {
            $digit = substr( $digitsOnly, $i, 1 );
            if( $timesTwo ) {
                $addend = $digit * 2;
                if( $addend > 9 ) {
                    $addend -= 9;
                }
            } else {
                $addend = $digit;
            }
            $sum += $addend;
            $timesTwo = !$timesTwo;
        }
        return $sum % 10 == 0;
    }


    public function creditcardAction() {
            
        $di = $this->di;
        $currentYear = date('Y');
        $elements = array(
          'payment' => array(
            'type' => 'hidden',
            'value' => 10
          ),
          'name' => array(
            'type' => 'text',
            'label' => 'Name on credit card:',
            'required' => true,
            'autofocus' => true,
            'validation' => array('not_empty')
          ),
          'address' => array(
            'type' => 'text',
            'required' => true,
            'validation' => array('not_empty')
          ),
          'zip' => array(
            'type' => 'text',
            'required' => true,
            'validation' => array('not_empty')
          ),
          'city' => array(
            'type' => 'text',
            'required' => true,
            'validation' => array('not_empty')
          ),
          'country' => array(
            'type' => 'select',
            'options' => array(
              'default' => 'Select a country...',
              'no' => 'Norway',
              'se' => 'Sweden',
            ),
            'validation' => array('not_empty', 'not_equal' => 'default')
          ),
          'cctype' => array(
            'type' => 'select',
            'label' => 'Credit card type:',
            'options' => array(
              'default' => 'Select a credit card type...',
              'visa' => 'VISA',
              'mastercard' => 'Mastercard',
              'eurocard' => 'Eurocard',
              'amex' => 'American Express',
            ),
            'validation' => array('not_empty', 'not_equal' => 'default')
          ),
          'ccnumber' => array(
            'type' => 'text',
            'label' => 'Credit card number:',
            'validation' => array('not_empty', 'custom_test' => array('message' => 'Credit card number is not valid, try using 4408 0412 3456 7893 or 4417 1234 5678 9113 :-).', 'test' => array($this, 'isValidCCNumber'))),
          ),
          'expmonth' => array(
            'type' => 'select',
            'label' => 'Expiration month:',
            'options' => array(
              'default' => 'Select credit card expiration month...',
              '01' => 'January',
              '02' => 'February',
              '03' => 'March',
              '04' => 'April',
              '05' => 'May',
              '06' => 'June',
              '07' => 'July',
              '08' => 'August',
              '09' => 'September',
              '10' => 'October',
              '11' => 'November',
              '12' => 'December',
            ),
            'validation' => array('not_empty', 'not_equal' => 'default')
          ),
          'expyear' => array(
            'type' => 'select',
            'label' => 'Expiration year:',
            'options' => array(
              'default' => 'Select credit card expiration year...',
              $currentYear    => $currentYear,
              ++$currentYear  => $currentYear,
              ++$currentYear  => $currentYear,
              ++$currentYear  => $currentYear,
              ++$currentYear  => $currentYear,
              ++$currentYear  => $currentYear,
            ),
            'validation' => array('not_empty', 'not_equal' => 'default')
          ),
          'cvc' => array(
            'type' => 'text',
            'label' => 'CVC:',
            'required' => true,
            'validation' => array('not_empty', 'numeric')
          ),
          'doPay' => array(
            'type' => 'submit',
            'value' => 'Perform payment',
            'callback' => function($form) {
              // Taking some money from the creditcard.
                $form->AddOutput("<pre>" . print_r($_POST, 1) . "</pre>");
                $form->saveInSession = true;
              return true;
            }
          ),
            'submit-fail' => [
               'type'      => 'submit',
               'callback'  => function ($form) use ($di) {
                    $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
                    return false;
                }
            ],
            'output-write' => function($output, $errors) use ($di) {
                  if ($errors) { 
                    $di->views->addString($output, 'flash-danger');
                } else {
                    $di->views->addString($output, 'flash-success');
                }
            }
        );


        $form = new \Mos\HTMLForm\CForm(array(), $elements);

        // Check the status of the form
        $status = $form->check();

        if ($status === true) {
                // What to do if the form was submitted?
            $form->AddOutput("<p><i>Form was submitted and the callback method returned true.</i></p>");
            header("Location: " . $di->request->getCurrentUrl());

        } else if ($status === false) {
            $di->views->addString('flash-danger', $form->getValidationErrors());

                // What to do when form could not be processed?
            $form->AddOutput("<h2>Check() == false</h2>Form was submitted and the Check() method returned false.");
            header("Location: " . $di->request->getCurrentUrl());
        }


        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $this->di->theme->setTitle("CForm Test");
        $this->di->views->add('default/page', [
            'title' => "TBA",
            'content' => $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function multicheckboxAction() {
        

        $form = new \Mos\HTMLForm\CForm(array(), array(
            'items' => array(
              'type'        => 'checkbox-multiple',
              'values'      => array('tomato', 'potato', 'apple', 'pear', 'banana'),
              'checked'     => array('potato', 'pear'),
            ),
            'submit' => array(
              'type'      => 'submit',
              'callback'  => function($form) {
                $form->AddOutput("<p><i>DoSubmit(): Form was submitted. Do stuff (save to database) and return true (success) or false (failed processing form)</i></p>");
                $form->AddOutput("<pre>" . print_r($_POST, 1) . "</pre>");
                $form->saveInSession = true;
                return true;
              }
            ),
            'submit-fail' => array(
              'type'      => 'submit',
              'callback'  => function($form) {
                $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
                return false;
              }
            ),
          )
        );

        // Check the status of the form
        $status = $form->check();

        if ($status === true) {
                // What to do if the form was submitted?
            $form->AddOutput("<p><i>Form was submitted and the callback method returned true.</i></p>");
            header("Location: " . $this->di->request->getCurrentUrl());

        } else if ($status === false) {
            $this->di->views->addString('flash-danger', $form->getValidationErrors());

                // What to do when form could not be processed?
            $form->AddOutput("<h2>Check() == false</h2>Form was submitted and the Check() method returned false.");
            header("Location: " . $this->di->request->getCurrentUrl());
        }


        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $this->di->theme->setTitle("CForm Test");
        $this->di->views->add('default/page', [
            'title' => "Multiple checkboxes",
            'content' => $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test1Action() {
        $form = new CFormContact1();
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $this->di->theme->setTitle("CForm Test");
        $this->di->views->add('default/page', [
            'title' => "Test 1",
            'content' => '<p>Testar att använda klassen FormContact1</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test2Action() {
        $form = new CFormContact2();
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $form->Check();

        $this->di->theme->setTitle("CForm Test");
        $this->di->views->add('default/page', [
            'title' => "Test 2",
            'content' =>'<p>Testar att använda klassen FormContact2</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test3Action() {
        $form = new CFormContact3();
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $status = $form->Check();


        // What to do if the form was submitted?
        if($status === true) {
          $this->di->views->addString("<p><i>Form was submitted and the callback method returned true. I should redirect to a page to avoid issues with reloading posted form.</i></p>", 'flash-success');
        }

        // What to do when form could not be processed?
        else if($status === false){
            $this->di->views->addString("<p><i>Form was submitted and the callback method returned false. I should redirect to a page to avoid issues with reloading posted form.</i></p>", 'flash-success');
        }


        $this->di->theme->setTitle("CForm Test 3");
        $this->di->views->add('default/page', [
            'title' => "Test 3",
            'content' =>'<p>Testar att använda klassen FormContact3</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test4Action() {
        $form = new CFormContact4();
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $status = $form->Check();


        // What to do if the form was submitted?
        if($status === true) {
          $this->di->views->addString("<p><i>Form was submitted and the callback method returned true. I should redirect to a page to avoid issues with reloading posted form.</i></p>", 'flash-success');
        }

        // What to do when form could not be processed?
        else if($status === false){
            $this->di->views->addString("<p><i>Form was submitted and the callback method returned false. I should redirect to a page to avoid issues with reloading posted form.</i></p>", 'flash-danger');
        }


        $this->di->theme->setTitle("CForm Test 4");
        $this->di->views->add('default/page', [
            'title' => "Test 4",
            'content' =>'<p>Testar att använda klassen FormContact4, nu med validering</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test5Action() {
       $form = new CFormContact5();
       $form->setDI($this->di);
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );
        
        // Check the status of the form
        $status = $form->Check();

        // What to do if the form was submitted?
        if($status === true) {
          $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        // What to do when form could not be processed?
        else if($status === false){
          $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        $this->di->theme->setTitle("CForm Test 5");
        $this->di->views->add('default/page', [
            'title' => "Test 5",
            'content' =>'<p>Testar att använda klassen FormContact5</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function test6Action() {
         $form = new CFormContact6();
       $form->setDI($this->di);
        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );
        
        // Check the status of the form
        $status = $form->Check();

        // What to do if the form was submitted?
        if($status === true) {
          $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        // What to do when form could not be processed?
        else if($status === false){
          $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        $this->di->theme->setTitle("CForm Test 6");
        $this->di->views->add('default/page', [
            'title' => "Test 6",
            'content' =>'<p>Testar att använda klassen FormContact6</p>' . $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function validationAction() {
        
$di = $this->di;
        $rules = array('not_empty', 'numeric', 'email_adress');
        $validation = array();
        if(!empty($_POST['tests'])) {
          foreach($_POST['tests'] as $val) {
            if(in_array($val, $rules)) {
              $validation[] = $val;
            }
          }
        }

        $form = new \Mos\HTMLForm\CForm(array(), array(
            'enter-a-value' => array(
              'type'        => 'text',
            ),        
            'tests' => array(
              'type'        => 'checkbox-multiple',
              'description' => 'Choose the validation rules to use.',
              'values'      => $rules,
            ),
            'submit' => array(
              'type'      => 'submit',
              'callback'  => function($form) {
                $form->AddOutput("<p><i>DoSubmit(): Nothing to do.</i></p>");
                //$form->AddOutput("<pre>" . print_r($_POST, 1) . "</pre>");
                $form->saveInSession = true;
                return true;
              }
            ),
            'submit-fail' => array(
              'type'      => 'submit',
              'callback'  => function($form) {
                $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
                return false;
              }
            ),
            'output-write' => function($output, $errors) use ($di) {
                  if ($errors) { 
                    $di->views->addString($output, 'flash-danger');
                } else {
                    $di->views->addString($output, 'flash-success');
                }
            }
          )
        );

        // Set the active validation rules
        $form->SetValidation('enter-a-value', $validation);

        $status =$form->Check();

          // What to do if the form was submitted?
        if($status === true) {
          $form->AddOUtput("<p><i>Form was submitted and the callback method returned true.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        // What to do when form could not be processed?
        else if($status === false){
          $form->AddOutput("<p><i>Form was submitted and the Check() method returned false.</i></p>");
          header("Location: " . $_SERVER['PHP_SELF']);
        }

        $this->di->views->addString(  $this->di->navbar->getSubmenu(), 'sidebar' );

        $this->di->theme->setTitle("CForm Test");
        $this->di->views->add('default/page', [
            'title' => "TBA",
            'content' => $form->getHTML(['novalidate' => true])
        ]); 
    }

    public function checkboxAction() {

       $di = $this->di;
       $form = $di->form->Create([], [

           'accept_mail' => array(
              'type'        => 'checkbox',
              'label'       => 'It´s great if you send me product information by mail.',
              'checked'     => false,
              ),        
           'accept_phone' => array(
              'type'        => 'checkbox',
              'label'       => 'You may call me to try and sell stuff.',
              'checked'     => true,
              ),        
           'accept_agreement' => array(
              'type'        => 'checkbox',
              'label'       => 'You must accept the <a href=http://opensource.org/licenses/GPL-3.0>license agreement</a>.',
              'required'    => true,
              'validation'  => array('must_accept'),
              ),        
           'submit' => array(
              'type'      => 'submit',
              'callback'  => function($form) {
                $form->AddOutput("<p><i>DoSubmit(): Form was submitted. Do stuff (save to database) and return true (success) or false (failed processing form)</i></p>");
                $form->AddOutput("<pre>" . print_r($_POST, 1) . "</pre>");
                $form->saveInSession = true;
                return true;
            }
            ),
           'submit-fail' => [
               'type'      => 'submit',
               'callback'  => function ($form) use ($di) {
                    $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
                    return false;
                }
            ],
            'output-write' => function($output, $errors) use ($di) {
                  if ($errors) { 
                    $di->views->addString($output, 'flash-danger');
                } else {
                    $di->views->addString($output, 'flash-success');
                }
            }
        ]);


        // Check the status of the form
        $status = $form->check();

        if ($status === true) {
                // What to do if the form was submitted?
            $form->AddOutput("<p><i>Form was submitted and the callback method returned true.</i></p>");
            header("Location: " . $di->request->getCurrentUrl());

        } else if ($status === false) {
            $di->views->addString('flash-danger', $form->getValidationErrors());

                // What to do when form could not be processed?
            $form->AddOutput("<h2>Check() == false</h2>Form was submitted and the Check() method returned false.");
            header("Location: " . $di->request->getCurrentUrl());
        }

        $this->di->views->addString(  $di->navbar->getSubmenu(), 'sidebar' );

        $this->di->theme->setTitle("Welcome to Anax");
        $this->di->views->add('default/page', [
            'title' => "Checkbox",
            'content' => $form->getHTML(['novalidate' => true])
        ]); 

    }



public function arrayAction()
{
 $di = $this->di;

 $form = $this->di->form->Create([], [

    'name' => [
    'type'        => 'text',
    'label'       => 'Name of contact person:',
    'required'    => true,
    'validation'  => ['not_empty'],
    ],
    'email' => [
    'type'        => 'text',
    'required'    => true,
    'validation'  => ['not_empty', 'email_adress'],
    ],
    'phone' => [
    'type'        => 'text',
    'required'    => true,
    'validation'  => ['not_empty', 'numeric'],
    ],
    'submit' => [
    'type'      => 'submit',
    'callback'  => function ($form) {
        $form->AddOutput("<p><i>DoSubmit(): Form was submitted. Do stuff (save to database) and return true (success) or false (failed processing form)</i></p>");
        $form->AddOutput("<p><b>Name: " . $form->Value('name') . "</b></p>");
        $form->AddOutput("<p><b>Email: " . $form->Value('email') . "</b></p>");
        $form->AddOutput("<p><b>Phone: " . $form->Value('phone') . "</b></p>");
        $form->saveInSession = true;
        return true;
    }
    ],
    'submit-fail' => [
    'type'      => 'submit',
    'callback'  => function ($form) use ($di) {
        $form->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
        return false;
    }
    ],
    'output-write' => function($output, $errors) use ($di) {
      if ($errors) { 
        $di->views->addString($output, 'flash-danger');
    } else {
        $di->views->addString($output, 'flash-success');
    }
}
]);


  // Check the status of the form
$status = $form->check();

if ($status === true) {
      // What to do if the form was submitted?
  $form->AddOutput("<p><i>Form was submitted and the callback method returned true.</i></p>");
  header("Location: " . $di->request->getCurrentUrl());

} else if ($status === false) {
    $di->views->addString('flash-danger', $form->getValidationErrors());

        // What to do when form could not be processed?
    $form->AddOutput("<h2>Check() == false</h2>Form was submitted and the Check() method returned false.");
    header("Location: " . $di->request->getCurrentUrl());
}

$this->di->views->addString(  $di->navbar->getSubmenu(), 'sidebar' );

$this->di->theme->setTitle("Welcome to Anax");
$this->di->views->add('default/page', [
    'title' => "Array",
    'content' => $form->getHTML(['novalidate' => true])
    ]); 
}

}

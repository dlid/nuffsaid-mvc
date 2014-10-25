<?php

namespace App\FormTest;

/**
 * Create a class for a contact-form with name, email and phonenumber.
 */
class CFormContact6 extends \Mos\HTMLForm\CForm implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectionaware;


  /** Create all form elements and validation rules in the constructor.
   *
   */
  public function __construct() {
    parent::__construct();

    $self = $this;
    
    $this->AddElement(new \Mos\HTMLForm\CFormElementText('name', array('label'=>'Name of contact person:', 'required'=>true)))
         ->AddElement(new \Mos\HTMLForm\CFormElementText('email', array('required'=>true)))
         ->AddElement(new \Mos\HTMLForm\CFormElementText('phone', array('required'=>true)))
         ->AddElement(new \Mos\HTMLForm\CFormElementSubmit('submit', array('callback'=>array($this, 'DoSubmit'))))
         ->AddElement(new \Mos\HTMLForm\CFormElementSubmit('submit-fail', array('callback'=>array($this, 'DoSubmitFail'))));

    $this->SetValidation('name', array('not_empty'))
         ->SetValidation('email', array('not_empty', 'email_adress'))
         ->SetValidation('phone', array('not_empty', 'numeric'))
         ->setOutputCallback(function($output, $errors) use ($self) {
            
            $target = empty($errors) ? 'flash-success' : 'flash-danger';
            if( empty($errors)) {
            $this->di->views->addString($output, $target);
            }

         });
  }

    /**
   * Callback for submitted forms, will always fail
   */
  protected function DoSubmitFail() {
    $this->AddOutput("<p><i>DoSubmitFail(): Form was submitted but I failed to process/save/validate it</i></p>");
    return false;
  }


  /**
   * Callback for submitted forms
   */
  protected function DoSubmit() {
    $this->AddOutput("<p><i>DoSubmit(): Form was submitted. Do stuff (save to database) and return true (success) or false (failed processing form)</i></p>");
    $this->AddOutput("<p><b>Name: " . $this->Value('name') . "</b></p>");
    $this->AddOutput("<p><b>Email: " . $this->Value('email') . "</b></p>");
    $this->AddOutput("<p><b>Phone: " . $this->Value('phone') . "</b></p>");
    $this->saveInSession = true;
    return true;
  }


}
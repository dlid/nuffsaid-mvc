<?php

namespace App\FormTest;

/**
 * Create a class for a contact-form with name, email and phonenumber.
 */
class CFormContact4 extends \Mos\HTMLForm\CForm {


  /** Create all form elements and validation rules in the constructor.
   *
   */
  public function __construct() {
    parent::__construct();
    
    $this->AddElement(new \Mos\HTMLForm\CFormElementText('name'))
         ->AddElement(new \Mos\HTMLForm\CFormElementText('email'))
         ->AddElement(new \Mos\HTMLForm\CFormElementText('phone'))
         ->AddElement(new \Mos\HTMLForm\CFormElementSubmit('submit', array('callback'=>array($this, 'DoSubmit'))));

           $this->SetValidation('name', array('not_empty'))
         ->SetValidation('email', array('not_empty', 'email_adress'))
         ->SetValidation('phone', array('not_empty', 'numeric'));
  }


  /**
   * Callback for submitted forms
   */
  protected function DoSubmit() {
    return true;
  }

}
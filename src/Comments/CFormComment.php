<?php

namespace Anax\Comments;

class CFormComment extends \Mos\HTMLForm\CForm {


  /** Create all form elements and validation rules in the constructor.
   *
   */
  public function __construct($di, $page, $redirect) {
    parent::__construct();
    
    // Create the text element with a custom renderer
    $textarea = new \Mos\HTMLForm\CFormElementTextarea('comment', 
      array(
        'br' => null, 
        'render' => array($this, 'renderContentTexarea')
      ));
    $textarea['description'] = htmlentities("Du kan använda HTML-taggarna <a>, <strong>, <b>, <i> och <em>");

    $this->AddElement($textarea)
         
         // Add hidden page element field and render element with wrapping DIV
         ->addElement( new \Mos\HTMLForm\CFormElementHidden('page', array(
            'value' => $page, 
            'render' => array($this, 'renderCollapsibleStart'))))
         
         // Add hidden element with redirect URL
         ->addElement( new \Mos\HTMLForm\CFormElementHidden('redirect', array(
            'value' => $redirect )))
         
         // Add text field for name
         ->AddElement(new \Mos\HTMLForm\CFormElementText('name', array(
            'br' => null, 
            'value' => $di->session->get('comment-name'))))
         
         // Add e-mail field with gravatar class (binds with events in JS)
         ->AddElement(new \Mos\HTMLForm\CFormElementText('email', array(
            'br' => null, 
            'class' => 'gravatar', 
            'value' => $di->session->get('comment-email'))))
         
         // Add the field for the user website
         ->AddElement(new \Mos\HTMLForm\CFormElementText('web', array(
            'br' => null, 
            'value' => $di->session->get('comment-web'))))
         
         // Add submit button
         ->AddElement(new \Mos\HTMLForm\CFormElementSubmit('submit', array(
            'br' => null, 
            'callback'=>array($this, 'DoSubmit'), 
            'render' => array($this, 'renderCollapsibleEnd'))));

    // Set validation for elements
    $this->SetValidation('comment', array('not_empty'))
      ->SetValidation('name', array('not_empty'))
      ->SetValidation('email', array('email_adress'));

  }

  /**
   * Add a DIV before the first element of the form
   * @param  CFormElement   $element   The CFormElement object instance
   * @param  string         $html      The suggested HTML from CFormElement
   * @param  array          $variables The variables used by CFormElement to render the HTML
   * @return string         Returns the HTML for the element
   */
  public function renderCollapsibleStart($element, $html, $variables) {
    return "<div class='commentator-details'>{$html}";
  }

  /**
   * Close the wrapping DIV after the last element of the form
   * @param  CFormElement   $element   The CFormElement object instance
   * @param  string         $html      The suggested HTML from CFormElement
   * @param  array          $variables The variables used by CFormElement to render the HTML
   * @return string         Returns the HTML for the element
   */
  public function renderCollapsibleEnd($element, $html, $variables) {
    return "{$html}</div>";
  }

  /**
   * Render the textarea as we want it
   * @param  CFormElement   $element   The CFormElement object instance
   * @param  string         $html      The suggested HTML from CFormElement
   * @param  array          $variables The variables used by CFormElement to render the HTML
   * @return string         Returns the HTML for the element
   */
  public function renderContentTexarea($element, $html, $variables) {

    // We want to make use the HTML variables provided by CFormElement
    extract($variables);

    $avatar = "http://www.gravatar.com/avatar/?s=60";

    $email = $this->value('email');
    if( $email ) {
      $email = md5($email);
      $avatar = "http://www.gravatar.com/avatar/{$email}.jpg?s=60";
    }

    $html =  <<<EOD
<p class='textarea'>
  <img id="CommentGravatarImage" class="gravatar gravatar-small" src="$avatar" alt="[user gravatar]">
  <textarea id='$id'{$class}{$name}{$autofocus}{$required}{$readonly}{$placeholder}{$title}>{$onlyValue}</textarea>
</p>
{$messages}
<p class='instructions'>{$description}</p>
EOD;

    return $html;

  }

  /**
   * Override getHTML to add  the comment class and remove the fieldset
   */
  public function getHTML($options = []) {
    return parent::getHTML( array_merge_recursive(['class' => 'comment-form', 'use_fieldset' => false, 'use_buttonbar' => false], $options));
  }


  /**
   * Callback for submitted forms
   */
  protected function DoSubmit() {
    return true;
  }

}
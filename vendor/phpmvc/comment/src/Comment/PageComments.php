<?php

namespace Phpmvc\Comment;

/**
 * To quickly initialize comments for a specific page
 *
 */
class PageComments implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    private $pageIdentifier = null; // The unique ID for the page
    private $includeParameters = []; // Array of querystring parameters to include when uniquely identifying a page
    private $includeCss = true;

    /**
     * Disable load of external stylsheet 
     */
    function removeExternalCss() {
        $this->includeCss = false;
    }

    /**
     * Get the age of a timestamp in words
     * @param  int  $timestamp [description]
     * @return string The difference in words
     */
    function getTimeAgo($timestamp) {
        $timeAgo = new \TimeAgo();
        return $timeAgo->inWords(date('Y-m-d H:i', $timestamp));
    }

    /**
     * Create a unique identifier for the current page
     * so we know where the comment should be visible, and 
     * where to save a new one
     * @return [type] [description]
     */
    public function getPageIdentifier() {

        if(!$this->pageIdentifier) {
            $url = parse_url($this->di->request->getCurrentUrl());

            $path = '';
            if(is_array($this->includeParameters)) {
                foreach( $this->includeParameters as $param ) {
                    $path.= ($path ? "&" : "") . $param . "=" . urlencode($this->request->getGet($param));
                }
            }

            $path = $url['path'] . ($path ? '?' : '') . $path;

            $this->pageIdentifier = md5($path);
        }

        return $this->pageIdentifier;
    }

    /**
     * If a Querystring parameter should be used to define unique
     * comment locations, this is where you add them
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function includeParams($parameters) {
        $this->includeParameters = $parameters;
    }

    /**
     * Utility function to add commenting to a form
     * @param [type] $app [description]
     */
    public function addToPage($app) {

        $app->dispatcher->forward([
            'controller' => 'comment',
            'action'     => 'view'
        ]);

        $comments = new \Phpmvc\Comment\CommentsInSession();
        $comments->setDI($this->di);

        $m = array_merge([
            'title'     => $this->di->theme->getVariable('title'),
            'mail'      => null,
            'web'       => null,
            'name'      => null,
            'content'   => null,
            'output'    => null,
        ], $comments->getValidatedComment(), $comments->getAutoSavedFields(), ['id' => null]);

        $m['pageIdentifier'] = $this->getPageIdentifier();

        $app->theme->addJavaScript('js/comments.js');
        $app->views->add('comment/form', $m);

    }
    
}

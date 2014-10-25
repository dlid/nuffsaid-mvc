<?php

namespace Anax\Comments;
 
/**
 * Model for Comments.
 *
 */
class Comment extends \Anax\MVC\CDatabaseModel
{

    private $pageIdentifier = null; // The unique ID for the page
    private $pageUrl = null;
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
        $timeAgo = new TimeAgo();
        if( is_numeric($timestamp)) {
            $timestamp = date('Y-m-d H:i', $timestamp);
        }
        return $timeAgo->inWords($timestamp);
    }

    /**
    * Find and return all.
    *
    * @return array
    */
    public function findAll()
    {
      $this->db->select()
               ->from($this->getSource())
               ->join('page', 'page_id = page.id')
               ->orderBy('comment.created DESC');

      $this->db->execute();
      $this->db->setFetchModeClass(__CLASS__);
      return $this->db->fetchAll();
    }

    /**
     * Find all comments for a specific URL
     * @param  string  $url The URL
     */
    function findByUrl($url) {

        $page = $this->pages->getByUrl($url);

        if( $page ) {
            $page = $page->getProperties();
            $page_id = $page['id'];
        } else {
            $page_id = 0;
        }

        $this->db->select()
           ->from($this->getSource())
           ->where('page_id = ?')
           ->orderBy('created ASC');

      $this->db->execute([$page_id]);
      $this->db->setFetchModeClass(__CLASS__);
      return $this->db->fetchAll();

    }

    /**
     * Get the URL for the page to comment
     * @return string
     */
    public function getPageUrl() {
       if(!$this->pageUrl) {
            $url = parse_url($this->di->request->getCurrentUrl());

            $path = '';
            if(is_array($this->includeParameters)) {
                foreach( $this->includeParameters as $param ) {
                    $path.= ($path ? "&" : "") . $param . "=" . urlencode($this->request->getGet($param));
                }
            }

            $path = $url['path'] . ($path ? '?' : '') . $path;
            $this->pageUrl = $path;
        }

        return $this->pageUrl;
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
     * Add the Comment functions to the given region
     * @param [type] $app    [description]
     * @param string $region [description]
     */
    public function addToView($region = 'main') {
        
        $this->dispatcher->forward([
            'controller' => 'comment',
            'action'     => 'view',
            'params' => [$region]
        ]);

       $this->dispatcher->forward([
            'controller' => 'comment',
            'action'     => 'add',
            'params' => [$region]
        ]);

    }



}
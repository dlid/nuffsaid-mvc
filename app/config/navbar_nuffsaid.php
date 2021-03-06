<?php
/**
 * Config-file for navigation bar.
 *
 */
return [

    // Use for styling the menu
    'class' => 'navbar',

    // Here comes the menu strcture
    'items' => [

        // This is a menu item
        'home'  => [
            'text'  => 'Hem',   
            'url'   => '',  
            'title' => 'Startsidan'
        ],

        'questions'  => [
            'text'  => 'Frågor',   
            'url'   => 'questions',  
            'title' => 'Ställ eller besvara frågor'
        ],

        'tags'  => [
            'text'  => 'Tags',   
            'url'   => 'tags',  
            'title' => 'Hitta frågor via taggar'
        ],

        'users'  => [
            'text'  => 'Användare',   
            'url'   => 'users',  
            'title' => 'Hitta användare'
        ],

        'ask'  => [
            'text'  => 'Ställ en fråga',   
            'class' => 'right',
            'url'   => 'questions/ask',  
            'title' => 'Ställ en ny fråga'
        ],
 
      
    ],
 
    // Callback tracing the current selected menu item base on scriptname
    'callback' => function($url) {
        if ( $url == $this->di->get('request')->getRoute()) {
            return true; 
        } else if($url && strpos($this->di->get('request')->getRoute(), $url) === 0 && strpos($this->di->get('request')->getRoute(), 'questions/ask') === false) {
            return true;
        }
    },

    // Callback to create the urls
    'create_url' => function($url) {
        return $this->di->get('url')->create($url);
    },
];

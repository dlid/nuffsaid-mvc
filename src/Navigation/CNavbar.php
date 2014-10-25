<?php

namespace Anax\Navigation;

/**
 * Helper to create a navbar for sites by reading its configuration from file
 * and then applying some code while rendering the resultning navbar.
 *
 */
class CNavbar
{
    use \Anax\TConfigure,
        \Anax\DI\TInjectionAware;


    private $hiddenSubmenu = null;
    private $cached = null;

    public function getSubmenu() {
      $this->create();

      return  $this->hiddenSubmenu;
    }

    /**
     * Create a navigation bar / menu, with submenu.
     * 
     * @return string with html for the menu.
     *
     * @link http://dbwebb.se/coachen/skapa-en-dynamisk-navbar-meny-med-undermeny-via-php
     */
    public function create()
    {

        if( $this->cached ) return $this->cached;

        // Keep default options in an array and merge with incoming options that can override the defaults.
        $default = array(
          'id'      => null,
          'class'   => null,
          'wrapper' => 'nav',
          'maxDepth' => 0,
          'create_url' => function($url) {
            return $url;
          },
        );
        $menu = array_replace_recursive($default, $this->config);

        // Function to create urls
        $createUrl = $menu['create_url'];

        // Create the ul li menu from the array, use an anonomous recursive function that returns an array of values.
        $createMenu = function($items, $callback, $depth = 1, $parentIsSelected = false) use (&$createMenu, $createUrl, $menu) 
        {
          $html = null;
          $hasItemIsSelected = false;
          $activeSubmenu = "";




          foreach ($items as $item) {

            // has submenu, call recursivly and keep track on if the submenu has a selected item in it.
            $submenu        = null;
            $selectedParent = null;
            $url = $createUrl(isset($item['url']) ? $item['url'] : null);
            

            // Check if the current menuitem is selected
            $selected = $callback(isset($item['url']) ? $item['url'] : null) ? 'selected' : null;
            if ($selected) {
              $hasItemIsSelected = true;
            }

            if (isset($item['submenu'])) {
              list($tmpSubmenu, $selectedParent, $subMenuHtml) = $createMenu($item['submenu']['items'], $callback, $depth + 1, $selected == 'selected');
              $selectedParent = $selectedParent ? " selected-parent" : null;
              if($tmpSubmenu && ($selectedParent || $callback($item['url']))) {
                  $activeSubmenu.=$tmpSubmenu;
              }

              // Only include this depth level if wanted
              if( $menu['maxDepth'] == 0 || $depth + 1 <= $menu['maxDepth']) {
                $submenu = $tmpSubmenu;

                if(!$this->hiddenSubmenu) {
                  if($selected || $selectedParent) {
                    $this->hiddenSubmenu = "<a class='submenu-header" . ( $selected ? ' selected' : '') . "' href='{$url}'>{$item['text']}</a><nav>" . $tmpSubmenu . "</nav>";
                  }
                }

              } else {
                if($selected || $selectedParent) {
                  $this->hiddenSubmenu = "<a class='submenu-header" . ( $selected ? ' selected' : '') . "' href='{$url}'>{$item['text']}</a><nav>" . $tmpSubmenu . "</nav>";
                }
              }
            }

            $class = isset($item['class']) ? " " . $item['class'] : null;
            $selected = ($selected || $selectedParent) ? " class='${selected}{$selectedParent}{$class}' " : " class='" . trim($class) ."'";      
            
            $icon = null;
            if(isset($item['icon'])) {
              $icon = '<i class="icon fa fa-'.$item['icon'].'"></i>';
            }

            if( $item == '--') {
              $html.="\n<li><hr /></li>";
            } else {

              $html .= "\n<li{$selected}><a href='{$url}' title='{$item['title']}'>{$icon}<span class='label'>{$item['text']}</span></a>{$submenu}</li>\n";
            }
          }

          return array("\n<ul>$html</ul>\n", $hasItemIsSelected , $activeSubmenu);
        };

        // Call the anonomous function to create the menu, and submenues if any.
        list($html, $ignore, $submenu) = $createMenu($menu['items'], $menu['callback']);

        // Set the id & class element, only if it exists in the menu-array
        $id      = isset($menu['id'])    ? " id='{$menu['id']}'"       : null;
        $class   = isset($menu['class']) ? " class='{$menu['class']}'" : null;
        $wrapper = $menu['wrapper'];

        if( $submenu ){
          $submenu = "<{$wrapper}>{$submenu}</{$wrapper}>";
        }

        $this->cached = "\n<{$wrapper}{$id}{$class}>{$html}{$submenu}</{$wrapper}>\n";
        return $this->cached;
    }
}

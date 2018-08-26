<?php
/*
Plugin Name: weDocs Addon TOC+ Navigation
Description: Provides weDocs enhancement for custom navigation. This plugin requires weDocs https://wordpress.org/plugins/wedocs/ and Table of Contents Plus https://wordpress.org/plugins/table-of-contents-plus/
Version: 1.0
Author: Daisuke Kawabata
License: GPL2
*/

/**
 * Copyright (c) 2018 Daisuke Kawabata (email: daisuke.k@readyship.co). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDocs class
 *
 * @class WeDocs The class that holds the entire WeDocs plugin
 */
class weDocs_Enhancement_Class {
  /*
   * Main constructor
   */
  public function __construct() {
    add_action( 'init', array( $this, 'init' ) );
  }

  public function init() {
    $this->file_includes();
    add_filter('widget_text', 'do_shortcode');
    add_shortcode( 'wedocs_toc_navgation', array($this, 'create_wedocstocnavgation_shortcode') );
    add_action( 'wp_footer',  array($this, 'add_instantclick_into_footer'), 999999 );
  }
  /**
   * Load the required files
   *
   * @return void
   */
  function file_includes() {
    include_once dirname( __FILE__ ) . '/includes/class-wedocs-toc-nav-walker.php';
  }

  /*
   * Create Shortcode wedocs_toc_navgation
   * Use the shortcode: [wedocs_toc_navgation]
   * Use the shortcode: [wedocs_toc_navgation parent_post_id="123"]
   */
  function create_wedocstocnavgation_shortcode($atts) {
    // Attributes
    $atts = shortcode_atts(
      array(
        'parent_post_id' => '',
        'headings' => '',
      ),
      $atts,
      'wedocs_toc_navgation'
    );
    // Attributes in var
    $parent_post_id = $atts['parent_post_id'];

    global $post;

    $ancestors = array();
    $root      = $parent = false;

    if($parent_post_id !== '') {
      $parent = $parent_post_id;
    } elseif ( $post->post_parent ) {
      $ancestors = get_post_ancestors($post->ID);
      $root      = count($ancestors) - 1;
      $parent    = $ancestors[$root];
    } else {
      $parent = $post->ID;
    }

    $walker = new WeDocs_Toc_Nav_Walker_Docs();
    $children = wp_list_pages( array(
      'title_li'  => '',
      'order'     => 'menu_order',
      'child_of'  => $parent,
      'echo'      => false,
      'post_type' => 'docs',
      'walker'    => $walker
    ) );
    $html = '<h3 class="widget-title">'.get_post_field( 'post_title', $parent, 'display' ).'</h3>';
    if ($children) {
      // data-instant attr is for InstantClick.io
      $html .= '<ul class="doc-nav-list" data-instant>'.$children.'</ul>';
    }
    // TODO: save into transient?
    echo $html;
  }

  function add_instantclick_into_footer() {
    echo <<< EOM
<script src="https://cdnjs.cloudflare.com/ajax/libs/instantclick/3.0.1/instantclick.min.js" data-no-instant></script>
<script data-no-instant>InstantClick.init(true); // Whitelist mode</script>
EOM;
  }
}
new weDocs_Enhancement_Class;


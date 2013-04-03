<?php 
/*
Plugin Name: Zuppler Online Ordering
Plugin URI: http://api.zuppler.com/docs/wordpress-plugin.html
Description: This plugin lets you easily integrate Zuppler Online Ordering.
Author: Zuppler Dev Team
Author URI: http://zupplerworks.com/
Version: 1.1.2
*/

/*  Copyright 2012 Zuppler Dev Team

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('inc/Mustache.php');

class Zuppler_integration {
  var $load_menu_assets = false;
  var $load_reviews_assets = false;
  var $load_profile_assets = false;
  var $zupplerhost = "http://api.zuppler.com";
  var $channel_slug;
  var $channel_type; // 0 = regular; 1 = network
  var $restaurant_slug;
  var $appearence;
  var $custom_integration = false;
  var $plugin_url;
  
  // network vars
  var $load_network_assets = false;
  var $check_is_restaurant_open = false;
  var $script_is_restaurant_open = array();
  var $channel = array();
  var $integration_tag = "restaurant";
  var $pagination_tag = "restaurant_listing_page";
  var $listing_template;
  var $current_page = 1;

  function Zuppler_integration() {
    $this->channel_slug     = get_option('zuppler_channel_slug');
  	$this->channel_type     = get_option('zuppler_channel_type');
  	$this->restaurant_slug  = get_option('zuppler_restaurant_slug');
    $this->appearence       = get_option('zuppler_appearence');
    $this->transport_type   = get_option('zuppler_transport_type');
  	$this->listing_template = html_entity_decode(get_option('zuppler_listing_template'));
  	
  	$file = dirname(__FILE__) . '/zuppler-online-ordering.php';
    $this->plugin_url = plugin_dir_url($file);
  	
	  add_shortcode('zuppler',  array(&$this, 'zuppler_shortcodes') );
	  add_filter('widget_text',  'do_shortcode');
  	add_action('wp_footer', array(&$this, 'print_assets'));
  }
  
  function zuppler_shortcodes($atts, $content = null) {
    
    $t_account = '<div id="zuppler-account"></div>';
    $t_account_small  = ($this->appearence == 1 || $this->appearence == 2) ? $t_account : "";
    $t_account_big    = ($this->appearence == 3 || $this->appearence == 4) ? $t_account : "";
    $t_cart_position  = ($this->appearence == 1 || $this->appearence == 3) ? "left" : "right";
    
    $tmpl = '
      <a href="#zuppler-cart" class="z_view_cart" style="display:none;">View Order</a>
      <div id="z_content" class="cart-'.$t_cart_position.'">
        '.$t_account_big.'
        <div id="z_main"><div id="z_main_column">
          '.$t_account_small.'
          <div id="zuppler-menu"></div>
        </div></div>
        <div id="z_sidebar">
          <div id="zuppler-cart"></div>
        </div>
      </div>
    ';
    
    $str = "";
    
    foreach($atts as $key => $val) {
      
      if (array_key_exists('restaurant', $atts)) $this->custom_integration = $atts['restaurant'];

      if ($this->channel_type == 1 && isset($_GET[$this->integration_tag]) && $_GET[$this->integration_tag] != "") {
        $this->custom_integration = $_GET[$this->integration_tag];
      }

      if($key == "menu") {
        $this->load_menu_assets = true;
        $str .= $tmpl;
      }
      
      if($key == 'reviews') {
        if ($content == null) $content = "";
        $this->load_reviews_assets = true;
        $str .= "<div id='zuppler-{$key}'>{$content}</div>";
      }

      if($key == 'listing') {
        $this->load_network_assets = true;
        $str .= $this->show_restaurant_listing();
      }

      if(    $key == 'discounts'
          || $key == 'events'
          || $key == 'pictures'
          || $key == 'welcome'
          || $key == 'locations'
          || $key == 'hours'
          || $key == 'cuisines'
          || $key == 'amenities'
      ) {
        $this->load_profile_assets = true;
        $str .= "<div id='zuppler-{$key}'>{$content}</div>";
      }

      
    }
    return $str;
  }


  function print_assets() {
    if($this->load_menu_assets) {
      echo $this->prepare_menu_assets();
      echo $this->prepare_init();
    } else if($this->load_reviews_assets) {
      echo $this->prepare_reviews_assets();
      echo $this->prepare_init();
    } else if($this->load_profile_assets) {
      echo $this->prepare_profile_assets();
      echo $this->prepare_init();
    }
    if($this->check_is_restaurant_open) {
      echo $this->prepare_listing_assets();
    }
  }
  
  function prepare_listing_assets(){
    $plugin_url = plugins_url() . "/zuppler-online-ordering";
    $assets = "
    <script>window.jQuery || document.write('<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js\"><\/script>')</script>\n
    <script type='text/javascript' charset='utf-8' src='{$plugin_url}/js/restaurant-listing-utils.js'></script>\n
    <script type='text/javascript' charset='utf-8'>
      jQuery(document).ready(function($) {
        var restaurants = " . $this->script_is_restaurant_open . ";
        $.each(restaurants, function(i,v){
          var isopen = isRestaurantOpen(v),
              cls = (isopen) ? 'open' : 'closed';
              $('#' + i).removeClass('is_restaurant_open').addClass(cls).find('.is_restaurant_open').addClass(cls).html(cls);
        });
      });
    </script>";
    return $assets;
  }

  function prepare_menu_assets() {
    $assets = "";
    if(!empty($this->transport_type) && $this->transport_type == 1) {
      $assets .= "<script type='text/javascript' charset='utf-8'>window.zuppler_transport = 'xss';</script>\n";
    }
    $restaurant = (empty($this->custom_integration)) ? $this->restaurant_slug : $this->custom_integration;
    $assets .= "<script type='text/javascript' charset='utf-8' src='{$this->zupplerhost}/channels/{$this->channel_slug}/restaurants/{$restaurant}/menu.js{$transport}'></script>\n";
    return $assets;
  }
  
  function prepare_reviews_assets(){
    $restaurant = (empty($this->custom_integration)) ? $this->restaurant_slug : $this->custom_integration;
    $assets = "<script type='text/javascript' charset='utf-8' src='{$this->zupplerhost}/channels/{$this->channel_slug}/restaurants/{$restaurant}/reviews.js'></script>\n";
    return $assets;
  }

  function prepare_profile_assets() {
    $restaurant = (empty($this->custom_integration)) ? $this->restaurant_slug : $this->custom_integration;
    $assets = "<script type='text/javascript' charset='utf-8' src='{$this->zupplerhost}/channels/{$this->channel_slug}/restaurants/{$restaurant}/profile.js'></script>\n";
    
    return $assets;
  }


  function prepare_init() {
    $init = "
      <script id='customGallery' type='html/_tmpl'>
        <% _.each(obj, function(picture) { %>
          <% if(picture.available) { %>
            <img src='<%= picture.original %>' width='300' title='' />
          <% } %>
        <% }); %>
      </script>
      <script type='text/javascript' charset='utf-8'>
    ";
    
    if($this->load_profile_assets) {
      $init .= "
          function profileInit() {
            jQuery('#zuppler-welcome').ZupplerProfile('welcome_message');
            jQuery('#zuppler-cuisines').ZupplerProfile('cuisines');
            jQuery('#zuppler-amenities').ZupplerProfile('amenities');
            jQuery('#zuppler-events').ZupplerProfile('events');
            jQuery('#zuppler-discounts').ZupplerProfile('discounts');
            jQuery('#zuppler-pictures').ZupplerProfile('gallery', _.template(document.getElementById('customGallery').text));
            jQuery('#zuppler-locations').ZupplerProfile('addresses');
            jQuery('#zuppler-hours').ZupplerProfile('working_hours');
            if (typeof window['profileLoaded'] == 'function') profileLoaded();
          }
      ";
    }
    if($this->load_reviews_assets) {
      $init .= "
          function reviewsInit() {
            jQuery('#zuppler-reviews').ZupplerReviews({});
          }
      ";
    }
    $init .= "</script>";
    
    return $init;
  }

  /* network support */

  function show_restaurant_listing() {
    $this->get_channel_details();

    if(isset($_GET[$this->pagination_tag]) && $_GET[$this->pagination_tag] != "") $this->current_page = (int)$_GET[$this->pagination_tag];
    $restaurants = $this->get_restaurants($this->current_page);

    $str = "";
    $str .= $this->print_pagination();
    $str .= $this->print_restaurants(array('restaurants' => $restaurants));
    
    if(strpos($this->listing_template, 'is_restaurant_open') !== false) {
      $this->check_is_restaurant_open = true;
      foreach ($restaurants as $r) {
        $this->script_is_restaurant_open["restaurant_" . $r["id"]] = $r["restaurant"]["working_hours_info"];
      }
      $this->script_is_restaurant_open = JSON_encode($this->script_is_restaurant_open);
    }

    return $str;
  }

  function get_channel_details(){
    $data_url = $this->zupplerhost . "/channels/" . $this->channel_slug . ".json";
    
    $details = get_transient( $this->channel_slug . '_details' );
    if ( false === $details ) {
      $response = wp_remote_get($data_url, array( 'User-Agent' => 'WordPress Zuppler Plugin' ));
      $response_code = wp_remote_retrieve_response_code( $response );
      
      if($response_code == 200) {
        $details = wp_remote_retrieve_body( $response );
        set_transient( $this->channel_slug . '_details', $details, 600 );
      } else {
        return false;
      }
    }

    $details = json_decode( $details, true );
    $this->channel = $details;
    return true;
  }

  function get_restaurants($page = 1){
    $data_url = $this->zupplerhost . "/channels/" . $this->channel_slug . "/restaurants.json?page=" . $page;
    
    $restaurants = get_transient( $this->channel_slug . '_restaurants_' . $page );
    if ( false === $restaurants ) {
      $response = wp_remote_get($data_url, array( 'User-Agent' => 'WordPress Zuppler Plugin' ));
      $response_code = wp_remote_retrieve_response_code( $response );
      
      if($response_code == 200) {
        $restaurants = wp_remote_retrieve_body( $response );
        set_transient( $this->channel_slug . '_restaurants_' . $page , $restaurants, 600 );
      } else {
        return false;
      }
    }
    return json_decode( $restaurants, true );
  }


  function print_restaurants( $restaurants ){
    if(!empty($restaurants) && is_array($restaurants)) {
      $m = new Mustache;
      return $m->render($this->listing_template, $restaurants);
    } else {
      return "invalid object";
    }
  }

  function print_pagination(){
    $curr = $this->current_page;
    $total = (int)$this->channel["restaurant_count"];
    $pagination = $this->handle_pagination($total, $curr, 20, $this->pagination_tag);
    return $pagination;
  }

  function handle_pagination($total, $page, $shown, $tag) {
    $permalink = get_permalink();
    $pages = ceil( $total / $shown );
    $range_start = ( ($page >= 5) ? ($page - 3) : 1 );
    $range_end = ( (($page + 5) > $pages ) ? $pages : ($page + 3) );

    if ( $page > 1 ) {
      $r[] = '<span><a href="'. add_query_arg($tag, 1, $permalink) .'">&laquo; first</a></span>';
      $r[] = '<span><a href="'. add_query_arg($tag, $page - 1, $permalink) .'">&lsaquo; previous</a></span>';
      $r[] = ( ($range_start > 1) ? ' ... ' : '' );
    }

    if ( $range_end > 1 ) {
      foreach(range($range_start, $range_end) as $key => $value) {
        if ( $value == $page ) $r[] = '<span class="current_page">'. $value .'</span>'; 
        else $r[] = '<span><a href="'. add_query_arg($tag, $value, $permalink) .'">'. $value .'</a></span>'; 
      }
    }

    if ( ( $page ) < $pages ) {
      $r[] = ( ($range_end < $pages) ? ' ... ' : '' );
      $r[] = '<span><a href="'. add_query_arg($tag, $page + 1, $permalink) .'">next &rsaquo;</a></span>';
      $r[] = '<span><a href="'. add_query_arg($tag, $pages, $permalink) .'">last &raquo;</a></span>';
    }

    return ( (isset($r)) ? '<div class="restaurants_pagination">'. implode("\r\n", $r) .'</div>' : '');
  }
  
} /* END Zuppler_integration */


new Zuppler_integration;


function zuppler_online_ordering_admin() {
	include('zuppler-online-ordering-admin.php');
}
function zuppler_online_ordering_admin_actions() {
    add_menu_page('Zuppler Online Ordering Options', 'Zuppler Online Ordering', "edit_posts", "zuppler-online-ordering-options", "zuppler_online_ordering_admin", plugin_dir_url(__FILE__) . "images/zuppler-icon-16px.png");
}
add_action('admin_menu', 'zuppler_online_ordering_admin_actions');

function admin_register_head() {
  $styles_url = plugins_url() . "/zuppler-online-ordering/stylesheets/admin.css";
  wp_register_style('zuppler-admin-styles', $styles_url);
  wp_enqueue_style( 'zuppler-admin-styles');
}
add_action('admin_init', 'admin_register_head');


?>
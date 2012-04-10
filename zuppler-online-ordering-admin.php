<?php 
  $plugin_url = plugins_url() . "/zuppler-online-ordering";
  $shortname = "zuppler";
  $form_action = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

  echo '<div class="wrap zuppler-opts">';

  $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : "options";
  $tabs = array(
    'start' => 'Getting Started',
    'options' => 'Online Ordering Menu Options',
    'faq' => 'Support / FAQ',
    'signup' => 'Sign Up'
  );
  echo '<div id="icon-options-general" class="icon32 icon-zuppler"></div>';
  echo '<h2 class="nav-tab-wrapper">';
  foreach ( $tabs as $tab_key => $tab_caption ) {
    $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
    echo '<a class="nav-tab ' . $active . '" href="?page=zuppler-online-ordering-options&tab=' . $tab_key . '">' . $tab_caption . '</a>'; 
  }
  echo '</h2>';

  if( array_key_exists($current_tab, $tabs) ) include("inc/content-".$current_tab.".php");
  
  echo '</div>'; //end warp

?>
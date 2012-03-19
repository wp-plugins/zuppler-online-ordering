<?php 
  $plugin_url = plugins_url() . "/zuppler-online-ordering";
  $shortname = "zuppler";
  $form_action = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

  $default_listing_template = '{{#restaurants}}
<div class="restaurant is_restaurant_open" id="restaurant_{{id}}">
  {{#restaurant}}
    <h1>{{name}}</h1>
    {{#restaurant_logo}}
      <img src="{{thumb}}" /><br />
    {{/restaurant_logo}}
    <p><strong>Locations:</strong>
      {{#addresses}} <br />{{address}} {{/addresses}}
    </p>
    <p><strong>Cuisines:</strong> {{restaurant_cuisines}}</p>
    <p><strong>Services:</strong> {{restaurant_amenities}}</p>
    <p><strong>Hours of Operation:</strong> {{& working_hours}}</p>
    <p>The restaurant is <strong class="is_restaurant_open"></strong> now.</p>
  {{/restaurant}}
  <a href="order-online?restaurant={{permalink}}">Order Online</a>
</div>
{{/restaurants}}';


$options = array (

  array( "type" => "open", "name" => "Restaurant Options" ),
  array( 
    "name" => "Channel Slug",
    "desc"  => "Provided by Zuppler Staff. E.g: <strong>demorestaurant</strong>",
    "id" => $shortname."_channel_slug",
    "type" => "text" ),
  array( 
    "name" => "Restaurant Slug",
    "desc"  => "Provided by Zuppler Staff. E.g: <strong>site</strong><br /> If you have more than 1 restaurant location, you can provide the correspondent slug as an attribute to the shortcode. E.g. [zuppler pictures=1 <strong>restaurant=\"location2\"</strong>]",
    "id" => $shortname."_restaurant_slug",
    "type" => "text" ),
  array("type" => "close"),


  array( "type" => "open", "name" => "Order Online page appearence" ),

  array(
    "name" => "Choose your layout style",
    "id" => $shortname."_appearence",
    "type" => "radio",
    "appearence" => "large",
    "std" => "1",
    "options" => array(
      array("value" => "1", "label" => "<img src='" . $plugin_url . "/images/tmpl1.gif'>"),
      array("value" => "2", "label" => "<img src='" . $plugin_url . "/images/tmpl2.gif'>"),
      array("value" => "3", "label" => "<img src='" . $plugin_url . "/images/tmpl3.gif'>"),
      array("value" => "4", "label" => "<img src='" . $plugin_url . "/images/tmpl4.gif'>"),
    ) ),
  array("type" => "close"),


  array( "type" => "open", "name" => "Channel Options" ),
  array(
    "name" => "Zuppler Channel Type",
    "id" => $shortname."_channel_type",
    "type" => "radio",
    "std" => "0",
    "options" => array(
      array("value" => "0", "label" => "Regular"),
      array("value" => "1", "label" => "Network"),
    ) ),

  array( 
    "name" => "Restaurants listing template",
    "id" => $shortname."_listing_template",
    "type" => "textarea",
    "std" => $default_listing_template,
    "desc" => '
      Use the following shortcode to display your network restaurants: <strong>[zuppler listing=1]</strong><br />
      The Restaurant Listing is rendered using the <a href="http://mustache.github.com/" target="_blank">Mustache</a> template engine.
      Please read <a href="http://mustache.github.com/mustache.5.html" target="_blank">the documentation</a> for more details.<br />
      Check out the <a href="http://api.zuppler.com/channels/demorestaurant/restaurants.json" target="_blank">sample JSON</a> for more details about the object passed to our template.
    ' ),

  array("type" => "close"),

); // $options



if ( 'save' == $_POST['action'] ) {
  if ( get_magic_quotes_gpc() ) {
    $_POST      = array_map( 'stripslashes_deep', $_POST );
    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
  }
  foreach ($options as $value) {
    if( isset( $_POST[ $value['id'] ] ) ) {
      update_option( $value['id'], $_POST[ $value['id'] ]  ); 
    } else { 
      delete_option( $value['id'] ); 
    }
  }
  ?><div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div><?php
} else if( 'reset' == $_POST['action'] ) {
  foreach ($options as $value) {
    if(empty($value['std'])) delete_option( $value['id'] );
    else update_option( $value['id'], $_POST[ $value['std'] ]  );
  }
}


?>



<div class="wrap">
<h2>Zuppler Online Ordering Options</h2>

<div class="has-right-sidebar meta-box-sortables zuppler-options">
  <div class="inner-sidebar">
    <div class="postbox">
      <h3>HELP - Plugin Usage</h3>
      <div class="inside" style="font-size: 11px;margin: 6px 6px 8px;">
        <p>To display the Zuppler Menu, add the following shortcode to your post or page:</p>
        <div class="info">
          <strong>[zuppler menu=1]</strong>
        </div>
        <p>If you have more than 1 restaurant location, provide the <strong>restaurant</strong> slug along with the shortcode:</p>
        <div class="info">
          <strong>[zuppler menu=1 restaurant="location2"]</strong>
        </div>

        <p>Here is a list of all available shortcodes:</p>
        <div class="info">
          <strong>[zuppler menu=1]</strong><br />
          <strong>[zuppler reviews=1]</strong><br />
          <strong>[zuppler discounts=1]</strong><br />
          <strong>[zuppler events=1]</strong><br />
          <strong>[zuppler pictures=1]</strong><br />
          <strong>[zuppler welcome=1]</strong><br />
          <strong>[zuppler locations=1]</strong><br />
          <strong>[zuppler hours=1]</strong><br />
          <strong>[zuppler cuisines=1]</strong><br />
          <strong>[zuppler amenities=1]</strong>
        </div>

        <p>You can also combine the attributes to display more information in a sigle place:</p>
        <div class="info">
          <strong>[zuppler welcome=1 pictures=1]</strong>
        </div>


        <p>For more information about the Zuppler API please visit <a href="http://api.zuppler.com/docs" target="_blank">Zuppler documentation</a>.</p>
      </div>
    </div>
  </div>
  <div id="post-body">
    <div id="post-body-content">
      <form name="zuppler_form" id="zuppler_form" method="post" action="<?php echo $form_action; ?>">

        
<?php 
foreach ($options as $value) { 
  switch ( $value['type'] ) {
  
    case "open": ?>

        <div class="postbox">
          <div class="handlediv" title="Click to toggle"><br></div> 
          <h3 class="hndle"><?php echo $value['name']; ?></h3>
          <div class="inside">
          <table class="form-table">

    <?php break;
    
    case "close": ?>
    
          </table>
          </div>
        </div>

    <?php break;
    
    case 'text': ?>

      <tr id="<?php echo $value['id']; ?>_row">
        <th scope="row"><?php echo $value['name']; ?></th>
        <td>
          <input type="text" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo get_settings( $value['id'] ); } else { echo $value['std']; } ?>" class="regular-text" />
          <?php if (!empty($value['desc'])) { ?><br /><span class="description"><?php echo $value['desc']; ?></span><?php } ;?>
        </td>
      </tr>

    <?php break;
    
    case 'separator': ?>

      <tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr>
      <tr><td colspan="2">&nbsp;</td></tr>

    <?php break;
    
    case 'textarea': ?>

      <tr id="<?php echo $value['id']; ?>_row">
        <td colspan="2">
          <label><?php echo $value['name']; ?></label>
          <textarea name="<?php echo $value['id']; ?>" style="width:99%; height:300px;" class="z-code-editor"><?php if ( get_settings( $value['id'] ) != "") { echo get_settings( $value['id'] ); } else { echo $value['std']; } ?></textarea>
            <?php if (!empty($value['desc'])) { ?>
            <div style="clear:both;">
              <span class="description"><?php echo $value['desc']; ?></span>
            </div>
            <?php } ;?>
        </td>
      </tr>

    <?php break;
    
    case 'select': ?>

      <tr id="<?php echo $value['id']; ?>_row">
        <th scope="row"><?php echo $value['name']; ?></th>
        <td>
          <?php
            $selected = ( get_settings($value['id']) != "" ) ? get_settings($value['id']) : $value["std"];
          ?>
          <select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
            <?php foreach ($value['options'] as $option) { ?>
              <option value="<?php echo $option['value']; ?>" <?php if ( $selected == $option['value']) { echo ' selected="selected"'; } ?>>
                <?php echo $option['label']; ?>
              </option>
            <?php } ?>
          </select>
          <?php if (!empty($value['desc'])) { ?><br /><span class="description"><?php echo $value['desc']; ?></span><?php } ;?>
        </td>
      </tr>

    <?php break;

    case 'radio': ?>

      <tr id="<?php echo $value['id']; ?>_row">
        <?php if(isset($value['appearence']) && $value['appearence'] == 'large') { ?>
          <td colspan="2">
            <label><?php echo $value['name']; ?></label><br /><br />
        <? } else { ?>
          <th scope="row"><?php echo $value['name']; ?></th>
          <td>
        <?php } ?>
          <?php $selected = ( get_settings($value['id']) != "" ) ? get_settings($value['id']) : $value["std"]; ?>
          <?php foreach ($value['options'] as $option) { ?>
            <label>
              <input type="radio" name="<?php echo $value['id']; ?>" value="<?php echo $option['value']; ?>" <?php if ( $selected == $option['value']) { echo ' checked="checked"'; } ?>/> 
              <?php echo $option['label']; ?>
            </label>
          <?php } ?>

          <?php if (!empty($value['desc'])) { ?><br /><span class="description"><?php echo $value['desc']; ?></span><?php } ;?>
        </td>
      </tr>

    <?php break;
            
    case "checkbox": ?>

      <tr id="<?php echo $value['id']; ?>_row">
        <th scope="row"><?php echo $value['name']; ?></th>
        <td><? if(get_settings($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = ""; } ?>
          <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />
          <?php if (!empty($value['desc'])) { ?><br /><span class="description"><?php echo $value['desc']; ?></span><?php } ;?>
        </td>
      </tr>
            
     <?php break; 
  } 
}
?>

        <p class="submit">
          <input type="submit" name="Submit" value="<?php _e('Update Options', 'zuppler_tr' ) ?>" class="button-primary" />
          <input name="reset" type="submit" id="reset_template_button" value="reset" />
          <input type="hidden" name="action" value="save" id="zuppler_form_action" />
        </p>
      </form>
    </div>
  </div>
</div>


<script type="text/javascript" charset="utf-8">
  jQuery(document).ready( function($) {
    $('.postbox h3, .postbox .handlediv, .stuffbox h3').click( function() {
      $(this).parent().toggleClass('closed');
    });

    $('#reset_template_button').click(function(e){
      if(confirm("Do you really want to reset your options?")) {
        $('#zuppler_form_action').val('reset');
        $('#zuppler_form').submit();
      }
      e.preventDefault();
    });

    $('#zuppler_listing_template_row').toggle($("#zuppler_channel_type_row input[type='radio']").last().is(":checked"));
    $("#zuppler_channel_type_row input[type='radio']").change(function(){
      $('#zuppler_listing_template_row').toggle($("#zuppler_channel_type_row input[type='radio']").last().is(":checked"));
    });

  });
</script>

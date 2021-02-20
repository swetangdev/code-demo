<?php 
/* TO CENTER MAP LOCATION PIN IMAGE */
/* http://stackoverflow.com/questions/9321752/google-maps-v3-custom-marker-icon-does-not-keep-its-position-on-map */
 var marker_image = new google.maps.MarkerImage(
        '../Media/icon_maps_marker.png',
         null,
         // The origin for my image is 0,0.
         new google.maps.Point(0, 0),
         // The center of the image is 50,50 (my image is a circle with 100,100)
         new google.maps.Point(50, 50)
     );
    var marker = new google.maps.Marker({
        clickable: true,
        map: map,
        position: center_location,
        icon: marker_image,
    });
 ?>

<?php 

/* to use editor for '" posts page "' */
if( ! function_exists( 'fix_no_editor_on_posts_page' ) ) {

    /**
     * Add the wp-editor back into WordPress after it was removed in 4.2.2.
     *
     * @param Object $post
     * @return void
     */
    function fix_no_editor_on_posts_page( $post ) {
        if( isset( $post ) && $post->ID != get_option('page_for_posts') ) {
            return;
        }

        remove_action( 'edit_form_after_title', '_wp_posts_page_notice' );
        add_post_type_support( 'page', 'editor' );
    }
    add_action( 'edit_form_after_title', 'fix_no_editor_on_posts_page', 0 );
 }

add_filter( 'admin_post_thumbnail_html', 'add_featured_image_instruction');
function add_featured_image_instruction($content) {
	global $post;
	$post_type = get_post_type();
	if($post_type == 'page'){
		$content .= '<p>Full Banner Dimension : 1920 x 1280  <br/>
						Medium Banner Dimension : 1600 x 708 
		</p>';
	}
    return $content;
}

/*------------------ check youtube/vimeo link -----------------------------*/
function parse_youtube($link){
        $regexstr = '~
            # Match Youtube link and embed code
            (?:                             # Group to match embed codes
                (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
				|(?:                        # Group to match if older embed
                    (?:<object .*>)?      # Match opening Object tag
                    (?:<param .</param>)  # Match all param tags
                    (?:<embed [^>]*src=")?  # Match embed tag to the first quote of src
                )?                          # End older embed code group
            )?                              # End embed code groups
            (?:                             # Group youtube url
                https?:\/\/                 # Either http or https
                (?:[\w]+\.)*                # Optional subdomains
                (?:                         # Group host alternatives.
                youtu\.be/                  # Either youtu.be,
                | youtube\.com              # or youtube.com
                | youtube-nocookie\.com     # or youtube-nocookie.com
                )                           # End Host Group
                (?:\S*[^\w\-\s])?           # Extra stuff up to VIDEO_ID
                ([\w\-]{11})                # $1: VIDEO_ID is numeric
                [^\s]*                      # Not a space
            )                               # End group
            "?                              # Match end quote if part of src
            (?:[^>]*>)?                       # Match any extra stuff up to close brace
            (?:                             # Group to match last embed code
                </iframe>                 # Match the end of the iframe
                |</embed></object>          # or Match the end of the older embed
            )?                              # End Group of last bit of embed code
            ~ix';
        preg_match($regexstr, $link, $matches);
        return $matches[1];
}


/*--------Show Vimeo Embede URL For Vimeo---------------------*/

function parse_vimeo($link){
        $regexstr = '~
            # Match Vimeo link and embed code
            (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
            (?:                         # Group vimeo url
                https?:\/\/             # Either http or https
                (?:[\w]+\.)*            # Optional subdomains
                vimeo\.com              # Match vimeo.com
                (?:[\/\w]*\/videos?)?   # Optional video sub directory this handles groups links also
                \/                      # Slash before Id
                ([0-9]+)                # $1: VIDEO_ID is numeric
                [^\s]*                  # Not a space
            )                           # End group
            "?                          # Match end quote if part of src
            (?:[^>]*></iframe>)?        # Match the end of the iframe
            (?:<p>.*</p>)?              # Match any title information stuff
            ~ix';
        preg_match($regexstr, $link, $matches);
        return $matches[1];
    }
	
function video_url($url){
	$type = videoType($url);
	if($type == "youtube"){
		$vidUrlID = parse_youtube($url);
		$vidUrl = "https://www.youtube.com/embed/".$vidUrlID;
	} else if($type == "vimeo"){ 
		$vidUrlID = parse_vimeo($url);
		$vidUrl = "https://player.vimeo.com/video/".$vidUrlID;		
	} else { 
		$vidUrl = $url;		
	}
	return $vidUrl;
	
}
	
function videoType($url) {
    if (strpos($url, 'youtube') > 0) {
        return 'youtube';
    } elseif (strpos($url, 'vimeo') > 0) {
        return 'vimeo';
    }
}




/* to remove 1st image from content */
$posts = get_posts( array( 
    'post_type'      => 'post', 
    'posts_per_page' => 500, 
    'offset'         => 0, 
) );

foreach( $posts as $post ):
    // Update each post with your reg-ex content filter:
    $pid = wp_update_post( array( 
        'ID'           => $post->ID,
        'post_content' => preg_replace( "/<img[^>]+\>/i", "", $post->post_content, 1 )
    ) );
    // Show the update process:
    printf( '<p>Post with ID: %d was %s updated</p>', 
        $post->ID, 
        ( 0 < $pid ) ? '' : 'NOT' 
    );     
endforeach;



/* limit text */
function girl_limitText($string,$limit, $allowedTags = ""){
	if(!empty($string)){
		$string = strip_tags($string,$allowedTags);
		if (strlen($string) > $limit) {
			$stringCut = substr($string, 0, $limit);
			$string = substr($stringCut, 0, strrpos($stringCut, ' ')); 
		}
		return $string;
	}else{
		return false;
	}
}

function redirect_homepage() {
    if (is_404() || is_search()) {
        wp_redirect( home_url() );
    }
}
add_action( 'template_redirect', 'redirect_homepage' );

/* manage capabilities */
add_action('init', 'remove_specific_admin_cap');
function remove_specific_admin_cap(){
$user = wp_get_current_user();
	if( $user && isset($user->user_email)) {
		$role = get_role( 'administrator' );
		// $role->remove_cap( 'install_plugins' );
		$role->add_cap( 'install_plugins' );
	}
}


/* Create table dynamically in wordpress */
/* https://developer.wordpress.org/reference/functions/dbdelta/ */
global $wpdb;
 $table_name = $wpdb->prefix . 'dbdelta_test_001';
 $wpdb_collate = $wpdb->collate;
 $sql =
	 "CREATE TABLE {$table_name} (
	 id mediumint(8) unsigned NOT NULL auto_increment ,
	 first varchar(255) NULL,
	 PRIMARY KEY  (id),
	 KEY first (first)
	 )
	 COLLATE {$wpdb_collate}";

 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 dbDelta( $sql );

/* function to ADD ACF option page - ACF PRO */
$args = array(
'page_title' => 'General Setting',
'menu_title' => 'General Setting',
'menu_slug' => 'general-setting',
);
acf_add_options_page( $args );


$first_name = get_user_meta($staff_id, 'first_name', true);
$last_name = get_user_meta($staff_id, 'last_name', true);
$staff_name = trim($first_name.' '.$last_name);
$staff_name = empty($staff_name) ? $staffVal->display_name : $staff_name;

/* remove update notice for Math Captcha */
function remove_update_notifications($value) {
    if ( isset( $value ) && is_object( $value ) ) {
        unset( $value->response[ 'wp-math-captcha/wp-math-captcha.php' ] );
    }
    return $value;
}
add_filter( 'site_transient_update_plugins', 'remove_update_notifications' );

?>
<?php
wp_enqueue_script( 'maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyC1N181P8XUHtW38ub-l98zKMpAErT_drI&v=3&signed_in=false', array(), true );	
/* 
https://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/

 */
/**

 * Extend WordPress search to include custom fields
 *
 * http://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    
    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where ) {
    global $pagenow, $wpdb;
   
    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );
?>


<?php 
/* CUSTOM GET TEMPLATE PART */
function actus_get_template_part($slug = null, $name = null, array $params = array()) {
	global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
	do_action("get_template_part_{$slug}", $slug, $name);
	$templates = array();
	if (isset($name))
		$templates[] = "{$slug}-{$name}.php";
		$templates[] = "{$slug}.php";
		$_template_file = locate_template($templates, false, false);
	if (is_array($wp_query->query_vars)) {
		extract($wp_query->query_vars, EXTR_SKIP);
	}
	extract($params, EXTR_SKIP);
	require($_template_file);
}

 ?>

<?php 
// http://wordpress.stackexchange.com/questions/139269/wordpress-taxonomy-radio-buttons
// WordPress taxonomy radio buttons
function region_radio_checklist( $args ) {
    if ( ! empty( $args['taxonomy'] ) && $args['taxonomy'] === 'region' /* <== Change to your required taxonomy */ ) {
        if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) { // Don't override 3rd party walkers.
            if ( ! class_exists( 'WPSE_139269_Walker_Category_Radio_Checklist' ) ) {
                /**
                 * Custom walker for switching checkbox inputs to radio.
                 *
                 * @see Walker_Category_Checklist
                 */
                class WPSE_139269_Walker_Category_Radio_Checklist extends Walker_Category_Checklist {
                    function walk( $elements, $max_depth, $args = array() ) {
                        $output = parent::walk( $elements, $max_depth, $args );
                        $output = str_replace(
                            array( 'type="checkbox"', "type='checkbox'" ),
                            array( 'type="radio"', "type='radio'" ),
                            $output
                        );

                        return $output;
                    }
                }
            }

            $args['walker'] = new WPSE_139269_Walker_Category_Radio_Checklist;
        }
    }

    return $args;
}

add_filter( 'wp_terms_checklist_args', 'region_radio_checklist' );

 ?>
<?php 
/* Compare card logo image with Envelope logo image */
function compareImages( $images ) {
	if( !empty($images) ) {
			
		// create images
		$i1 = @imagecreatefromstring(file_get_contents($images[1]));
		$i2 = @imagecreatefromstring(file_get_contents($images[2]));
		 
		// check if we were given garbage
		if (!$i1) {
			//echo $images[1] . ' is not a valid image';
			//exit(1);
			return false;
		}
		if (!$i2) {
			//echo $images[2] . ' is not a valid image';
			//exit(1);
			return false;
		}
		 
		// dimensions of the first image
		$sx1 = imagesx($i1);
		$sy1 = imagesy($i1);
		 
		// compare dimensions
		if ($sx1 !== imagesx($i2) || $sy1 !== imagesy($i2)) {
			//echo "The images are not even the same size";
			//exit(1);
			return false;
		}
		 
		// increment this counter when encountering a pixel diff
		$different_pixels = 0;
		 
		// loop x and y
		for ($x = 0; $x < $sx1; $x++) {
			for ($y = 0; $y < $sy1; $y++) {
		 
				$rgb1 = imagecolorat($i1, $x, $y);
				$pix1 = imagecolorsforindex($i1, $rgb1);
		 
				$rgb2 = imagecolorat($i2, $x, $y);
				$pix2 = imagecolorsforindex($i2, $rgb2);
		 
				if ($pix1 !== $pix2) { // different pixel
					// increment and paint in the diff image
					$different_pixels++;
				}
		 
			}
		}
		 
		 
		if (!$different_pixels) {
			//echo "Image is the same";
			//exit(0);
			return true;
		} else {
			return false;
		}
	}
	return false;
}

?>
 
 
<?php 

/* Function to insert image */ 
function insertEventImage($pid) {
	require_once(ABSPATH . 'wp-admin/includes/image.php' );
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	$imagePathId =  media_handle_upload( 'event_image', $pid );  
	
	if ( !is_wp_error( $imagePathId ) ) {
		global $wpdb;
		$eventMetaTable = $wpdb->prefix.'eventmeta';
		$image_post_id = $imagePathId;
		$getEventImage = $wpdb->get_results( 'SELECT image_post_id FROM '.$eventMetaTable.' WHERE event_id = '.$pid, OBJECT );
		if($getEventImage != NULL){
			wp_delete_post( $getEventImage[0]->image_post_id);
		}
		return $image_post_id;
	} else {
		return false;
	}
}
?>


<?php 
/* get lat long using POST CODE / Address*/
function getLatLng($address) {
	$geoData = array();
	if(!empty($address)){
		$geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {
		  $geoData['lat'] = $geo['results'][0]['geometry']['location']['lat'];
		  $geoData['lng'] = $geo['results'][0]['geometry']['location']['lng'];
		}
	}
	return $geoData;
}
 ?>

<?php 
/* modify nav menu */

function wpa_remove_menu_item( $items, $menu, $args ) {
    if( !is_user_logged_in() ) {
		foreach ( $items as $key => $item ) {
			if ( 'Logout' == $item->title ) unset( $items[$key] );
			if ( 'Members Only Post' == $item->title ) unset( $items[$key] );		
			if ( 'Profile' == $item->title ) unset( $items[$key] );		
		}
	} else if( is_user_logged_in() ) {	
		foreach ( $items as $key => $item ) {
			if ( 'Login' == $item->title ) unset( $items[$key] );		
			if ( 'Logout' == $item->title ){$items[$key]->url=wp_logout_url(get_permalink(get_page_by_path('login')));}
		}
	}

    return $items;
}
add_filter( 'wp_get_nav_menu_items', 'wpa_remove_menu_item', 10, 3 );



?>

<?php 
//convert date format to mysql friendly
$endDate = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $_REQUEST['end_date']);

//
$contact_number = get_field('contact_number', 'option');
$email = get_field('email', 'option');

if(!empty($copy_right_text)) {
	$copy_right_text = $copy_right_text.' - ';
}
if(!empty($contact_number)) {
	$contact_number_text = 'Call: '.$contact_number;
	$contact_no = preg_replace('/\D/', '', $contact_number);
}
if(!empty($email)) {
	$email_text = 'Email: '.$email;
}

?>

<?php get_template_part( 'template-parts/icons', 'share' );  ?>

<?php 
//file upload 
add_action( 'wp_ajax_upload_kitchen_images', 'upload_kitchen_images' );
add_action( 'wp_ajax_nopriv_upload_kitchen_images', 'upload_kitchen_images' );   
function upload_kitchen_images(){
	require_once(ABSPATH . 'wp-admin/includes/image.php' );
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');


	echo  $Id =  media_handle_upload( 'file',0 );  
	die();
    
}

 ?>

<?php 

function actus_hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return implode(",", $rgb); // returns the rgb values separated by commas
   //return $rgb; // returns an array with the rgb values
}

 ?>

<?php
comment form validations
=========
/ following code for comment box velidation /
 jQuery("form#commentform input[type='submit']").click(function(){
   jQuery('form#commentform span.error-msg').remove();
   var data = {};
   var fieldName = '';
   var popMsg = '';
   var popError = false;
   var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
   jQuery('form#commentform').find('input[type="text"],input[type="email"], textarea').each(function(i, field) {
    data[field.name] = field.value;
    fieldName = field.name;
    fieldName = fieldName.replace("_", " ");
    if(field.name == 'email'){
  if(field.value == ''){
   jQuery('form#commentform input[name="'+field.name+'"]').after('<span class="error-msg">Please fill up <strong>'+fieldName+'</strong></span>');
   if(popError == false)
    popError = true;
  } else if (!filter.test(field.value)) {
   jQuery('form#commentform input[name="'+field.name+'"]').val('').after('<span class="error-msg">Please provide a valid email address</span>');
   if(popError == false)
    popError = true;
  }
    } else if(field.name == 'comment'){    
     if(field.value == ''){    
   jQuery('form#commentform textarea').after('<span class="error-msg">Please fill up <strong>'+fieldName+'</strong></span>');
    if(popError == false)
     popError = true;
     }
    } else {
  if(field.value == ''){  
   fieldName = (fieldName == 'author')?'name':fieldName;
   jQuery('form#commentform input[name="'+field.name+'"]').after('<span class="error-msg">Please fill up <strong>'+fieldName+'</strong></span>');
   if(popError == false)
    popError = true;
  }
    }      
   });
   if(popError == true){
    return false;
   }      
  });
  jQuery('form#commentform').find('input[type="text"]').each(function(i, field) {
   jQuery('form#commentform input[name="'+field.name+'"]').focus(function(){
    if(jQuery('form#commentform input[name="'+field.name+'"]').next('span.error-msg').length > 0){
  jQuery('form#commentform input[name="'+field.name+'"]').next('span.error-msg').remove();
    }
   });
  });
  jQuery(document).on('mouseover','span.error-msg',function(){
   jQuery(this).remove();
  });
  
  ?>
  
  
  <?php 
  
function coolum_link_fillter($link = null, $target = null)
{
	$href_link = null;
	// For external link condition
	if(!empty($link) && $link != null){
		if($link == '#' ){
			$href_link = $link;
			$target = '';
		} else {
			$url =  trim($link);
			if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
				$href_link= "http://" . $url;
			} else {
				$href_link = trim($link);
			}
		}
	}
	// For target condition
	if ($target == true){
		return 'href="'.$href_link.'" target="_blank"';
	}else{
		return 'href="'.$href_link.'"';
	}
}
  
  ?>
  
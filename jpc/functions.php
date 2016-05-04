<?php
/**
 * Twenty Twelve functions and definitions
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see https://codex.wordpress.org/Theme_Development and
 * https://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, @link https://codex.wordpress.org/Plugin_API
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

// Set up the content width value based on the theme's design and stylesheet.
if ( ! isset( $content_width ) )
	$content_width = 625;

/**
 * Twenty Twelve setup.
 *
 * Sets up theme defaults and registers the various WordPress features that
 * Twenty Twelve supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_setup() {
	/*
	 * Makes Twenty Twelve available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'twentytwelve' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentytwelve', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'twentytwelve' ) );

	/*
	 * This theme supports custom background color and image,
	 * and here we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}
add_action( 'after_setup_theme', 'twentytwelve_setup' );

/**
 * Add support for a custom header image.
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Return the Google font stylesheet URL if available.
 *
 * The use of Open Sans by default is localized. For languages that use
 * characters not supported by the font, the font can be disabled.
 *
 * @since Twenty Twelve 1.2
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function twentytwelve_get_font_url() {
	$font_url = '';

	/* translators: If there are characters in your language that are not supported
	 * by Open Sans, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'twentytwelve' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language,
		 * translate this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'twentytwelve' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$query_args = array(
			'family' => 'Open+Sans:400italic,700italic,400,700',
			'subset' => $subsets,
		);
		$font_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return $font_url;
}

/**
 * Enqueue scripts and styles for front-end.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	// Adds JavaScript for handling the navigation menu hide-and-show behavior.
	wp_enqueue_script( 'twentytwelve-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery' ), '20140711', true );

	$font_url = twentytwelve_get_font_url();
	if ( ! empty( $font_url ) )
		wp_enqueue_style( 'twentytwelve-fonts', esc_url_raw( $font_url ), array(), null );

	// Loads our main stylesheet.
	wp_enqueue_style( 'twentytwelve-style', get_stylesheet_uri() );

	// Loads the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'twentytwelve-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentytwelve-style' ), '20121010' );
	$wp_styles->add_data( 'twentytwelve-ie', 'conditional', 'lt IE 9' );
}
add_action( 'wp_enqueue_scripts', 'twentytwelve_scripts_styles' );

/**
 * Filter TinyMCE CSS path to include Google Fonts.
 *
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @uses twentytwelve_get_font_url() To get the Google Font stylesheet URL.
 *
 * @since Twenty Twelve 1.2
 *
 * @param string $mce_css CSS path to load in TinyMCE.
 * @return string Filtered CSS path.
 */
function twentytwelve_mce_css( $mce_css ) {
	$font_url = twentytwelve_get_font_url();

	if ( empty( $font_url ) )
		return $mce_css;

	if ( ! empty( $mce_css ) )
		$mce_css .= ',';

	$mce_css .= esc_url_raw( str_replace( ',', '%2C', $font_url ) );

	return $mce_css;
}
add_filter( 'mce_css', 'twentytwelve_mce_css' );

/**
 * Filter the page title.
 *
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since Twenty Twelve 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function twentytwelve_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );

/**
 * Filter the page menu arguments.
 *
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'twentytwelve_page_menu_args' );

/**
 * Register sidebars.
 *
 * Registers our main widget area and the front page widget areas.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'twentytwelve' ),
		'id' => 'sidebar-1',
		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'First Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-2',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Second Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-3',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'twentytwelve_widgets_init' );

if ( ! function_exists( 'twentytwelve_content_nav' ) ) :
/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_nav( $html_id ) {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo esc_attr( $html_id ); ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
		</nav><!-- .navigation -->
	<?php endif;
}
endif;

if ( ! function_exists( 'twentytwelve_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own twentytwelve_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite><b class="fn">%1$s</b> %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'twentytwelve_entry_meta' ) ) :
/**
 * Set up post entry meta.
 *
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentytwelve_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 *
 * @since Twenty Twelve 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function twentytwelve_body_class( $classes ) {
	$background_color = get_background_color();
	$background_image = get_background_image();

	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
		$classes[] = 'full-width';

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
			$classes[] = 'two-sidebars';
	}

	if ( empty( $background_image ) ) {
		if ( empty( $background_color ) )
			$classes[] = 'custom-background-empty';
		elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
			$classes[] = 'custom-background-white';
	}

	// Enable custom font class only if the font CSS is queued to load.
	if ( wp_style_is( 'twentytwelve-fonts', 'queue' ) )
		$classes[] = 'custom-font-enabled';

	if ( ! is_multi_author() )
		$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'twentytwelve_body_class' );

/**
 * Adjust content width in certain contexts.
 *
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
		global $content_width;
		$content_width = 960;
	}
}
add_action( 'template_redirect', 'twentytwelve_content_width' );

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function twentytwelve_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'twentytwelve_customize_register' );

/**
 * Enqueue Javascript postMessage handlers for the Customizer.
 *
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_customize_preview_js() {
	wp_enqueue_script( 'twentytwelve-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20141120', true );
}
add_action( 'customize_preview_init', 'twentytwelve_customize_preview_js' );

/**
* JPC Theme Actions
* 
* Enqueue JS and CSS for the theme
*
* Custom Theme Options
*
* @by John Perri Cruz 2016
* 
**/

/**
*
* Enqueue JS & CSS
*
**/

function register_admin_script(){
	if(is_admin()){
		wp_enqueue_style('backend-css-styles', get_template_directory_uri().'/css/backend.css');
	}
}


add_action( 'admin_enqueue_scripts', 'register_admin_script' );
function startwordpress_scripts(){

	/**
	*
	* Load on Frontend
	*
	**/
	
	if(!is_admin()){
		//CSS
		if(get_option('font_awesome') == "true") { 
			wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );
		}
		if(get_option('owl') == "true") { 
			wp_enqueue_style( 'owl-theme', '//cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css', array(), '', true );
			wp_enqueue_style( 'owl-min', '//cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css', array(), '', true );
			wp_enqueue_style( 'owl-min', '//cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.transitions.min.css', array(), '', true );	
		}		
		/**
		*
		* Load By Default
		*
		**/
		
		wp_enqueue_style( 'grid', get_template_directory_uri() . '/css/grid.css' );
		wp_enqueue_style( 'overlap', get_template_directory_uri() . '/css/overlap.css' );
		wp_enqueue_style( 'responsive', get_template_directory_uri() . '/css/responsive.css' );
		
		//JS
		if(get_option('jquery_min') == "true") { 
			wp_enqueue_script( 'jquery-min',  '//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js', array(), '', true );
		}
		if(get_option('owl') == "true") { 
			wp_enqueue_script( 'owl-js', '//cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js', array(), '', true );
		}	
		if(get_option('parallax') == "true") { 
			wp_enqueue_script( 'parallax', '//98dc1135b51d57ecfe2587983663a0675c3218de.googledrive.com/host/0B69r3TVJZkc4QnhwUGY3T1NsTFU/parallax.js', array(), '', true );
		}
		/**
		*
		* Load By Default
		*
		**/		
		
		wp_enqueue_script( 'jpc-script', get_template_directory_uri() . '/js/script.js', array(), '', true );
	}	
}
add_action( 'wp_enqueue_scripts', 'startwordpress_scripts' );

/**
*
* Register JPC Theme Options
*
**/

function custom_settings_add_menu() {
	  add_menu_page( 
		  'Theme Options', 			//Page Title
		  'Theme Options',  		//Menu Title
		  'manage_options', 		//Capability
		  'theme-options', 			//Page ID
		  'theme_settings_page',	//Functions
		  null, 					//Favicon
		  99						//Position
	  );
}

/**
*
* Theme Form Fields
*
**/

function theme_settings_page() {
	echo '<div class="wrap">';
		echo '<h1>JPC Theme Options</h1>';
		echo '<form method="post" action="options.php">';
			settings_fields( 'option-group' );
			do_settings_sections( 'option-group' );
			echo '<h3>I. Social Media</h3>';
			echo '<table class="jpc-table">';
				echo '<tr>';
					echo '<td>Facebook: </td>';
					echo '<td><input placeholder="Facebook" type="text" name="facebook" value="'. esc_attr( get_option('facebook') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Twitter: </td>';
					echo '<td><input placeholder="Twitter" type="text" name="twitter" value="'. esc_attr( get_option('twitter') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Google Plus: </td>';
					echo '<td><input placeholder="Google Plus" type="text" name="google_plus" value="'. esc_attr( get_option('google_plus') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>LinkedIn: </td>';
					echo '<td><input placeholder="LinkedIN" type="text" name="linkedin" value="'. esc_attr( get_option('linkedin') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Youtube: </td>';
					echo '<td><input placeholder="Youtube" type="text" name="youtube" value="'. esc_attr( get_option('youtube') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Instagram: </td>';
					echo '<td><input placeholder="Instagram" type="text" name="instagram" value="'. esc_attr( get_option('instagram') ).'" /></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Pinterest: </td>';
					echo '<td><input placeholder="Pinterest" type="text" name="pinterest" value="'. esc_attr( get_option('pinterest') ).'" /></td>';
				echo '</tr>';				
			echo '</table>';
			echo '<h3>II. Website Settings</h3>';
			echo '<table class="jpc-table">';
				echo '<tr>';
					echo '<td>Frontend Favicon: </td>';
					echo '<td><input placeholder="Frontend Favicon" type="text" name="favicon" value="'. esc_attr( get_option('favicon') ).'" /></td>';
				echo '</tr>';	
				echo '<tr>';
					echo '<td>Backend Favicon: </td>';
					echo '<td><input placeholder="Admin Backend Favicon" type="text" name="admin_favicon" value="'. esc_attr( get_option('admin_favicon') ).'" /></td>';
				echo '</tr>';		
				echo '<tr>';
					echo '<td>Force SSL: (Redirect To HTTPS) </td>';
					?><td><input type="checkbox" name="ssl" value="true" <?php if(get_option('ssl') == "true") echo "checked"; ?> /></td><?php
				echo '</tr>';					
			echo '</table>';
			echo '<h3>III. Enable Theme Features</h3>';
			echo '<table class="jpc-table">';	
				echo '<tr>';
					echo '<td>FontAwesome v4.4.0 : </td>';
					?><td><input type="checkbox" name="font_awesome" value="true" <?php if(get_option('font_awesome') == "true") echo "checked"; ?> /> <a target="_blank" href="https://fortawesome.github.io/Font-Awesome/icons/">Read Documentation</a></td><?php
				echo '</tr>';		
				echo '<tr>';
					echo '<td>jQuery min v1.11.2 : </td>';
					?><td><input type="checkbox" name="jquery_min" value="true" <?php if(get_option('jquery_min') == "true") echo "checked"; ?> /></td><?php
				echo '</tr>';		
				echo '<tr>';
					echo '<td>Owl Carousel v1.3: </td>';
					?><td><input type="checkbox" name="owl" value="true" <?php if(get_option('owl') == "true") echo "checked"; ?> /> <a target="_blank" href="http://www.owlcarousel.owlgraphic.com/demos/demos.html">Read Documentation</a> </td><?php
				echo '</tr>';	
				echo '<tr>';
					echo '<td>JS Parallax Scrolling : </td>';
					?><td><input type="checkbox" name="parallax" value="true" <?php if(get_option('parallax') == "true") echo "checked"; ?> /> Example :  $("SELECTOR").parallax("50%", 0.1); </td><?php
				echo '</tr>';				
			echo '</table>';	
			echo '<h3>IV. Copyright Section</h3>';
			echo '<table class="jpc-table">';	
				echo '<tr>';
					echo '<td>Copyright : </td>';
					echo '<td><textarea rows="6" type="text" name="copyright" value="'. esc_attr( get_option('copyright') ).'" >'. esc_attr( get_option('copyright') ).'</textarea></td>';
				echo '</tr>';		
				echo '<tr>';
					echo '<td>Developer : </td>';
					echo '<td><textarea rows="6" type="text" name="developer" value="'. esc_attr( get_option('developer') ).'" >'. esc_attr( get_option('developer') ).'</textarea></td>';
				echo '</tr>';				
			echo '</table>';
			submit_button();
		echo'</form>';
	echo'</div>';
}

/**
*
* Option POST Handler
*
**/

function register_theme_settings() {
	register_setting( 'option-group', 'facebook' );
	register_setting( 'option-group', 'twitter' );
	register_setting( 'option-group', 'google_plus' );
	register_setting( 'option-group', 'linkedin' );
	register_setting( 'option-group', 'youtube' );
	register_setting( 'option-group', 'instagram' );
	register_setting( 'option-group', 'pinterest' );
	register_setting( 'option-group', 'favicon' );
	register_setting( 'option-group', 'admin_favicon' );
	register_setting( 'option-group', 'ssl' );
	register_setting( 'option-group', 'font_awesome' );
	register_setting( 'option-group', 'jquery_min' );
	register_setting( 'option-group', 'owl' );
	register_setting( 'option-group', 'parallax' );
	register_setting( 'option-group', 'copyright' );
	register_setting( 'option-group', 'developer' );
	
}

function social_media_shortcode( $atts ) {
    extract(shortcode_atts(array(
        'mode' => 'type'
    ),$atts ));

    switch( $type ){
        case 'facebook': 
            $output = get_option('facebook');
            break;

        case 'twitter': 
            $output = esc_attr( get_option('twitter') );
            break;

        default:
            $output = '<div class="defaultshortcodecontent"></div>';
            break;
    }
 
    return $type;
}
add_shortcode( 'social-media', 'social_media_shortcode' ); //[social-media mode="facebook"]

/**
*
* Add Theme Action
*
**/

add_action( 'admin_menu', 'custom_settings_add_menu' );
add_action( 'admin_init', 'register_theme_settings' );

/**
*
* Get Current Year
*
**/

function get_present_year( $atts ){
	return date('Y');
}
add_shortcode( 'year', 'get_present_year' );

/**
*
* Admin Favicon
*
**/

function admin_favicon() {
	echo '<link href="'.get_option('admin_favicon').'" rel="icon" type="image/x-icon">';
}
add_action('admin_head', 'admin_favicon');

/**
*
* Redirect To HTTPS
*
**/

function redirectSSL(){
  if($_SERVER['HTTPS']!="on"){
     $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     header("Location:$redirect");
  }
}
if(get_option('ssl')=='true'){
	redirectSSL();
}
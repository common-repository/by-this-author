<?php

defined('ABSPATH') or die ('No direct access to this file.');

require_once('class-virtual-page.php');

class By_This_Author_People_Lists
{
	private static $ALPHABET = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	private static $FACILITY = '';
	private static $INDEX_NAMES = array();

	private static function reverse_translate_index_name($index_name)
	{
		return self::$INDEX_NAMES[ $index_name ];
	}

	private static function get_pages_by_people_index_slug($people_index_name)
	{
		//$terms = get_terms('author_index');
		//foreach($terms as $term) {
	  	return get_posts(array(
	    	'post_type' => 'page',
	      'tax_query' => array(
	      	array(
	        	'taxonomy' => 'people_index',
	          'field' => 'slug',
	          'terms' => $people_index_name
	        )
	      ),
	      'numberposts' => -1
			));
		//}
	}

	private static function get_posts_by_people_index_slug($people_index_name)
	{
		//$terms = get_terms('author_index');
		//foreach($terms as $term) {
	  	return get_posts(array(
	    	'post_type' => 'post',
	      'tax_query' => array(
	      	array(
	        	'taxonomy' => 'people_index',
	          'field' => 'slug',
	          'terms' => $people_index_name
	        )
	      ),
	      'numberposts' => -1
			));
		//}
	}

	private static function generate_list($name, $posts, $posts_per_page = null)
	{
		if (empty($posts))
			return '';

		$retval = $retval . '<ul>';
		foreach (array_slice($posts, 0, $posts_per_page) as $post_id)
			$retval = $retval . '<li><a href="' . get_permalink($post_id) . '">' . get_post($post_id)->post_title . '</a></li>';
		$retval = $retval . '</ul>';

		return $retval;
	}

	private static function get_authors_by_first_letter($letter)
	{
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'page',
			'tag' => 'cv-autori'
		);

		$query = new WP_Query( $args );
		$posts = $query->posts;

		if ( count($posts) > 0 and isset($letter) ) {
			$posts = array_filter($posts, function($a) use (&$letter){
				return strtoupper(mb_substr($a->post_title, 0, 1, 'UTF-8')) == strtoupper(mb_substr($letter, 0, 1, 'UTF-8'));
			});
		}

		if ( count($posts) > 0 ) {
			usort($posts, function($a, $b) {
				return strcasecmp($a->post_title, $b->post_title);
			});
		}

		return $posts;
	}

  private static function get_list_of_all_people($taxonomy)
	{
		return get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
	}

	private static function is_same_initial_ci($s1, $s2)
	{
		return strtoupper( mb_substr( $s1, 0, 1, 'UTF-8' ) ) == strtoupper( mb_substr( $s2, 0, 1, 'UTF-8' ) );
	}

	private static function get_list_of_people_with_initial($taxonomy, $initial)
	{
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		if ( count( $terms ) == 0 ) return array();

		$fterms = array();
		foreach ( $terms as $term ) if ( self::is_same_initial_ci( $term->slug, $initial ) )
		{
			array_push( $fterms, $term );
		}

		return $fterms;
	}

	private static function get_terms_count($slugs)
	{
		$people_terms = get_terms( array(
			'taxonomy' => 'people_index',
			'slug' => $slugs,
		) );
		$count = 0;
		foreach ( $people_terms as $pt ) $count = $count + $pt->count;
		return $count;
	}

	private static function generate_alphabetic_index($base_request_path, $taxonomy, &$content)
	{
		$content = '<p>';
		foreach ( self::$ALPHABET as $letter )
		{
			if ( count( self::get_list_of_people_with_initial( $taxonomy, $letter ) ) > 0 )
			{
		    $content = $content . '<a href="' . $base_request_path . strtolower( $letter ) . '/' . '">' . strtoupper( $letter ) . '</a>' . '&nbsp;';
			}
			else
			{
				$content = $content . strtoupper( $letter ) . '&nbsp;';
			}
		}
		$content = $content . '<a href="' . $base_request_path . '">' . __('all', 'by-this-author') . '</a>';
		$content = $content . '</p>';
		return $content;
	}

  private static function generate_list_of_people($base_request_path, array &$terms, &$content)
	{
		if ( count( $terms ) > 0 )
		{
			$content = $content . '<ul>';
			foreach ( $terms as $term )
			{
				if ( self::get_terms_count( $term->slug ) > 0 )
				{
					$content = $content . '<li><a href="' . $base_request_path .  $term->slug . '/">' . $term->name . '</a></li>';
				}
				else
				{
					$content = $content . '<li>' . $term->name . '</li>';
				}
			}
			$content = $content . '</ul>';
		}
		return $content;
	}

  private static function emit_page($title, &$content)
	{
		By_This_Author_Virtual_Page::create_from_content( array(
			'slug' => 'by-this-author',
			'title' => $title,
			'content' => $content
		) );
	}

	// private static function filter_by_index_name(ref $array, $index_name)
	// {
	// 	return array_filter(
	//     $array,
	//     function ($value) use($index_name) {
	//     	return $value['index_name'] == $index_name;
	//     }
	// 	);
	// }

	private static function is_virtual_page()
	{
		return true;
		$request_path = wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$virtual_path = Keep_In_Touch_Utils::get_page_path_from_slug('by-this-author');
		return ($request_path == $virtual_path);
	}

	private static function handle_virtual_page()
	{
		$indexes = array(
			array(
				'index' => 'people_index',
				'title_all' => __('All people', 'by-this-author'),
				'title_initial' => __('People starting with %s', 'by-this-author')
			),
			//array(
			//	'index' => 'member_index',
			//	'title_all' => __('All members', 'by-this-author'),
			//	'title_initial' => __('Members starting with %s', 'by-this-author')
			//),
			array(
				'index' => 'author_index',
				'title_all' => __('All authors', 'by-this-author'),
				'title_initial' => __('Authors starting with %s', 'by-this-author')
			),
		);

		$dirs = explode( '/', wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

		$r_request = array_pop( $dirs );
		$r_facility = array_pop( $dirs );
		if ( $r_facility != self::$FACILITY )
		{
			$r_parameter = $r_request;
			$r_request = $r_facility;
			$r_facility = array_pop( $dirs );
		}
		if ( $r_facility != self::$FACILITY )
		{
			$r_parameter = $r_request;
			if ( strlen( $r_parameter ) != 1 ) return;
			$r_request = $r_facility;
			$r_facility = array_pop( $dirs );
			if ( $r_facility != self::$FACILITY ) return;
		}

		array_push( $dirs, $r_facility );
		$base_request_path = implode( '/', $dirs ) . '/';

		foreach ( $indexes as $entry ) if ( self::reverse_translate_index_name( $r_request ) == $entry['index'] )
		{
			self::generate_alphabetic_index( $base_request_path . $r_request . '/', $entry['index'], $content );

			if ( $r_parameter )
			{
				self::generate_list_of_people( $base_request_path, self::get_list_of_people_with_initial( $entry['index'], $r_parameter ), $content );
				self::emit_page( sprintf( $entry['title_initial'], strtoupper( $r_parameter ) ), $content );
			}
			else
			{
				self::generate_list_of_people( $base_request_path, self::get_list_of_all_people( $entry['index'] ), $content );
				self::emit_page( $entry['title_all'], $content );
		  }
			return;
		}

		$person_slug = $r_request;
		$pages = self::get_pages_by_people_index_slug( $person_slug );
		$posts = self::get_posts_by_people_index_slug( $person_slug );
		By_This_Author_Virtual_Page::create_from_posts( 'by-this-author', array_merge( $pages, $posts ) );
		// $all = array_merge( $pages, $posts );
		// ? >
		// <h1>Some title</h1>
		// <p>Some text</p>
		// <p>And now the list</p>
		// <article>
		// 	<?php // Display blog posts on any page @ https://m0n.co/l
		// 	foreach( $all as $element)
		// 	{
		// 		? >
		// 		<h2><a href="<?php get_permalink($element); ? >" title="Read more"><?php get_the_title($element); ? ></a></h2>
		// 		<?php get_the_excerpt($element);
		// 	}? >
		// </article>
		// <?php
	}

	private static function taxonomies_init() {
		// create a new taxonomy
		$labels = array(
			'name'                           => __( 'People Index', 'by-this-author' ),
			'singular_name'                  => __( 'Person', 'by-this-author' ),
			'search_items'                   => __( 'Search People', 'by-this-author' ),
			'all_items'                      => __( 'All People', 'by-this-author' ),
			'edit_item'                      => __( 'Edit Person', 'by-this-author' ),
			'update_item'                    => __( 'Update Person', 'by-this-author' ),
			'add_new_item'                   => __( 'Add New Person', 'by-this-author' ),
			'new_item_name'                  => __( 'New Person Name', 'by-this-author' ),
			'menu_name'                      => __( 'People', 'by-this-author' ),
			'view_item'                      => __( 'View Person', 'by-this-author' ),
			'popular_items'                  => __( 'Popular Persons', 'by-this-author' ),
			'separate_items_with_commas'     => __( 'Separate persons with commas', 'by-this-author' ),
			'add_or_remove_items'            => __( 'Add or remove persons', 'by-this-author' ),
			'choose_from_most_used'          => __( 'Choose from the most used persons', 'by-this-author' ),
			'not_found'                      => __( 'No persons found', 'by-this-author' ),
		);
		register_taxonomy(
			'people_index',
			array('post', 'page'),
			array(
				'label' => __( 'People Index', 'by-this-author' ),
				'labels' => $labels,
				'rewrite' => array( 'slug' => 'people_index' ),
		));
		register_taxonomy(
			'author_index',
			'',
			array(
				'label' => __( 'Author Index' ),
				'rewrite' => array( 'slug' => 'author_index' ),
		));
		register_taxonomy(
			'member_index',
			'',
			array(
				'label' => __( 'Member Index' ),
				'rewrite' => array( 'slug' => 'member_index' ),
		));
	}

	static function remove_redirect_guess_404_permalink( $redirect_url ) {
	    if ( is_404() )
	        return false;
	    return $redirect_url;
	}

	static function init()
	{
		self::$FACILITY = __( 'people', 'by-this-author' );
		self::$INDEX_NAMES[ __( 'people_index', 'by-this-author' ) ] = 'people_index';
		self::$INDEX_NAMES[ __( 'author_index', 'by-this-author' ) ] = 'author_index';
		self::$INDEX_NAMES[ __( 'member_index', 'by-this-author' ) ] = 'member_index';
		self::taxonomies_init();
		add_filter('redirect_canonical', array('By_This_Author_People_Lists', 'remove_redirect_guess_404_permalink'));
		if (self::is_virtual_page()) self::handle_virtual_page();
	}
}

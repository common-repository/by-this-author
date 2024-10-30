<?php

/*
Plugin Name: By this Author
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the plugin.
Version: 1.1.0
Author: Racanu
#Author URI: http://URI_Of_The_Plugin_Author
Text Domain: by-this-author
#Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
#Network: Optional. Whether the plugin can only be activated network wide. Example: true
#License: A short license name. Example: GPL2
*/

defined('ABSPATH') or die ('No direct access to this file.');

require_once('by-this-author-people-lists.php');
require_once('by-this-author-settings.php');

class By_This_Author
{
	private static $ALPHABET = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	/*
	function title_filter($val)
	{
		return '|' . $val . '|';
	}
	*/

	private static function get_age( $ref_date, $end_date )
	{
		return floor( ( $end_date - $ref_date ) / 31556926 );
	}

	private static function get_posts_by_user_name($user_name)
	{
		$user_name = esc_sql($user_name);

		global $wpdb;

		$users = $wpdb->get_results('SELECT ID, display_name
			FROM $wpdb->users
			WHERE display_name LIKE '%esc_sql($user_name)%'
			ORDER BY display_name');

		if (empty($users))
		{
			return array();
		}

		$user = $users[0];
		return get_posts(array('author' => $user->ID, 'posts_per_page' => -1,));
	}

	private static function get_posts_by_category_name($category_name)
	{
		$cat_id = get_cat_ID($category_name);
		if ($cat_id == 0)
			return array();
		return get_posts(array('cat' => $cat_id, 'posts_per_page' => -1,));
	}

	private static function get_pages_by_tag($tag_name)
	{
		$tag_term = get_term_by('name', $tag_name, 'post_tag');
		if (!$tag_term)
			return array();
		return get_posts(array('tag_id' => $tag_term->term_id, 'posts_per_page' => -1, 'post_type' => 'page'));
	}

	private static function get_posts_by_tag($tag_name)
	{
		$tag_term = get_term_by('name', $tag_name, 'post_tag');
		if (!$tag_term)
			return array();
		return get_posts(array('tag_id' => $tag_term->term_id, 'posts_per_page' => -1, 'post_type' => 'post'));
	}

	static function sc_get_age_from_date( $atts )
	{
		// Extract shortcode atts
		extract( shortcode_atts( array(
			'ref_date'  => '',
			'end_date'  => date( 'Y-m-d' ),
			), $atts ) );
		return self::get_age( strtotime( $ref_date ), strtotime( $end_date ) );
	}

	static function sc_time_machine($atts)
	{
		// Extract shortcode atts
		extract( shortcode_atts( array(
			'ref_time'  => '',
			'future_text'  => '',
			'past_text' => '',
			), $atts ) );
		$start_of_today = date('Y-m-d H:i', strtotime(date('Y-m-d')));
		return (strtotime($start_of_today) <= strtotime($ref_time)) ? $future_text : $past_text;
	}

	static function sc_by_this_author($atts)
	{
		extract( shortcode_atts( array(
			'name'	         => '',
			'post_types'     => 'authored_by, attributed_to',
			'posts_per_page' => null,
			), $atts ) );

		#$post_types_list = split(' *, *', $post_types);
		$post_types_list = array_map('trim', explode(',', $post_types));

		$posts = array();
		$list = '';
		$post_types_text = '';

		if (in_array('authored_by', $post_types_list) and in_array('attributed_to', $post_types_list))
		{
			$posts = array_unique(array_merge(self::get_posts_by_user_name($name), self::get_posts_by_category_name($name), self::get_posts_by_tag($name)), SORT_REGULAR);
			$list = self::generate_list($name, $posts, $posts_per_page);
			$post_types_text = __('authored by or attributed to', 'by-this-author');
		}
		else if (in_array('authored_by', $post_types_list))
		{
			$posts = self::get_posts_by_user_name($name);
			$list = self::generate_list($name, $posts, $posts_per_page);
			$post_types_text = __('authored by', 'by-this-author');
		}
		else if (in_array('attributed_to', $post_types_list))
		{
			$posts = array_unique(array_merge(self::get_posts_by_category_name($name), self::get_posts_by_tag($name)), SORT_REGULAR);
			$list = self::generate_list($name, $posts, $posts_per_page);
			$post_types_text = __('attributed to', 'by-this-author');
		}
		else
		{
			return '<p>' . sprintf(__('Unknown post types: %s', 'by-this-author'), $post_types_text) . '</p>';
		}

		if (empty($posts))
			return '<p>' . sprintf(__('No posts %s %s found.', 'by-this-author'), $post_types_text, $name) . '</p>';

		return '<p>' . sprintf(__('Posts %s %s:', 'by-this-author'), $post_types_text, $name) . '</p>' . $list;
	}

	static function sc_list_authors($atts)
	{
		extract( shortcode_atts( array(
			'first_letter'	 => null,
			), $atts ) );

		if (!isset($first_letter))
		{
			foreach ( self::$ALPHABET as $letter )
			{
				$authors = self::get_authors_by_first_letter($letter);
				echo '<p>' . $letter;
				if ( count($authors) > 0 )
				{
					echo '<ul>';
					foreach($authors as $author)
					{
						echo '<li>' . $author->post_title . '</li>';
					}
					echo '</ul>';
					echo '</p>';
				}
				else
				{
					echo '<p>No autori</p>';
				}
			}
		}
		else
		{
				$authors = self::get_authors_by_first_letter($first_letter);
				if ( count($authors) > 0 )
				{
					echo '<ul>';
					foreach($authors as $author)
					{
						echo '<li>' . $author->post_title . '</li>';
					}
					echo '</ul>';
				}
				else
				{
					echo '<p>No autori</p>';
				}
		}
	}

	static function init()
	{
		load_plugin_textdomain( 'by-this-author', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_shortcode( 'by-this-author', array( get_class(), 'sc_by_this_author' ), 1 );
		add_shortcode( 'get-age', array( get_class(), 'sc_get_age_from_date' ), 1 );
		add_shortcode( 'time-machine', array( get_class(), 'sc_time_machine' ), 1 );
		add_shortcode( 'list-authors', array( get_class(), 'sc_list_authors' ), 1 );

		By_This_Author_People_Lists::init();
		By_This_Author_Settings::init();
	}
}

add_action('init', array('By_This_Author', 'init'));

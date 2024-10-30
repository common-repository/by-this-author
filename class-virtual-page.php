<?php

defined('ABSPATH') or die ('No direct access to this file.');

class By_This_Author_Virtual_Page
// From http://davejesch.com/wordpress/wordpress-tech/creating-virtual-pages-in-wordpress/
{
	private $slug = '';
	private $posts = array();

	private function __construct($slug, $posts)
	{
		if (!isset($slug))
			throw new Exception('No slug given for virtual page.');

		if (!isset($posts))
			throw new Exception('No posts given for virtual page.');

		// $content = '<p>Preliminary content</p>';
		// foreach  ($posts as $post )
		// {
		// 	//$content = $content . '<h2><a href="' . get_permalink($post) . '" title="Read more">' . get_the_title($post) . '</a></h2>';
		// 	$content = $content . '<h2>' . get_the_title($post) . '</h2>';
		// 	$content = $content . $post->post_content;
		// }
		// $this->posts = array( self::make_page_from_content( array( 'title' => 'Some title', 'slug' => 'preliminary', 'content' => $content ) ) );
		$this->slug = $slug;
		$this->posts = $posts;
		add_filter('the_posts', array($this, 'virtual_page'));
	}

	public static function create_from_content($args)
	{
		return new self($args['slug'], array(self::make_page_from_content($args)));
	}

	public static function create_from_posts($slug, $posts)
	{
		return new self($slug, $posts);
	}

	public static function make_page_from_content($args)
	{
		global $wp, $wp_query;

		if (!isset($args['slug']))
			throw new Exception('No slug given for virtual page.');

		//create a fake post intance
		$post = new stdClass;
		// fill properties of $post with everything a page in the database would have
		$post->ID = -1;                          // use an illegal value for page ID
		$post->post_author = isset($args['author']) ? $args['author'] : 1;
		$post->post_date = isset($args['date']) ? $args['date'] : current_time('mysql');
		$post->post_date_gmt = isset($args['date']) ? $args['date'] : current_time('mysql', 1);
		$post->post_content = isset($args['content']) ? $args['content'] : '';
		$post->post_title = isset($args['title']) ? $args['title'] : '';
		$post->post_excerpt = '';
		$post->post_status = 'publish';
		$post->comment_status = 'closed';
		$post->ping_status = 'closed';
		$post->post_password = '';
		$post->post_name = $args['slug'];
		$post->to_ping = '';
		$post->pinged = '';
		$post->modified = $post->post_date;
		$post->modified_gmt = $post->post_date_gmt;
		$post->post_content_filtered = '';
		$post->post_parent = 0;
		$post->guid = get_home_url('/' . $args['slug']);
		$post->menu_order = 0;
		$post->post_type = isset($args['type']) ? $args['type'] : 'page';
		$post->post_mime_type = '';
		$post->comment_count = 0;

		return $post;
	}

	// filter to create virtual page content
	public function virtual_page($posts)
	{
		global $wp, $wp_query;

		if (count($posts) == 0 /*and (strcasecmp($wp->request, $this->slug) == 0 || $wp->query_vars['page_id'] == $this->slug)*/)
		{
			// set filter results
			$posts = $this->posts;

			// reset wp_query properties to simulate a found page
			$wp_query->is_page = false;
			$wp_query->is_singular = true;
			$wp_query->is_home = false;
			$wp_query->is_archive = false;
			$wp_query->is_category = false;
			unset($wp_query->query['error']);
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404 = false;
		}

		return $posts;
	}
}

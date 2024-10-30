<?php

defined('ABSPATH') or die ('No direct access to this file.');

require_once('by-this-author-people-table.php');

class By_This_Author_Settings
{
	private static function find_term_from_slug($taxonomy, $slug)
	{
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'slug' => $slug ) );
		//echo '<p>is_null(slug) = ' . ( is_null( $slug ) ? 'true' : 'false' ) . '</p>';
		//echo '<p>slug == "" = ' . ( ( $slug == '' ) ? 'true' : 'false' ) . '</p>';
		//echo '<p>slug = ' . $slug . '</p>';
		//echo '<p>count(terms) = ' . count( $terms ) . '</p>';
		if ( count( $terms ) ) return $terms[0];
		return null;
	}

	private static function update_or_insert_term( $taxonomy, $args )
	{
		$term = self::find_term_from_slug( $taxonomy, $args['slug'] );
		//echo '<p>update_or_insert_term: term = ' . ( isset( $term ) ? 'not null' : 'null' ) . '</p>';
		if ( is_null( $term ) )
		{
			//echo '<p>update_or_insert_term: Inserting ' . $args['name'] . ', ' . $args['slug'] . ' in ' . $taxonomy . '</p>';
			wp_insert_term( $args['name'], $taxonomy, $args );
		}
		else
		{
			//echo '<p>update_or_insert_term: Updating ' . $term->slug . ' in ' . $taxonomy . ' to ' . $args['name'] . ', ' . $args['slug'] . '</p>';
			wp_update_term( $term->term_id, $taxonomy, $args );
		}
	}

	private static function delete_term( $taxonomy, $slug )
	{
		$term = self::find_term_from_slug( $taxonomy, $slug );
		//echo '<p>delete_term: term = ' . ( isset( $term ) ? 'not null' : 'null' ) . '</p>';
		if ( ! is_null( $term ) )
		{
			//echo '<p>delete_term: Deleting ' . $term->slug . ' from ' . $taxonomy . '</p>';
			wp_delete_term( $term->term_id, $taxonomy );
		}
	}



	/** *************************** RENDER TEST PAGE ********************************
	 *******************************************************************************
	 * This function renders the admin page and the example list table. Although it's
	 * possible to call prepare_items() and display() from the constructor, there
	 * are often times where you may need to include logic here between those steps,
	 * so we've instead called those methods explicitly. It keeps things flexible, and
	 * it's the way the list tables are used in the WordPress core.
	 */
	static function render_page()
	{
		if ( isset( $_POST['submit'] ) )
		{
			//echo '<p>REQUEST: ' . http_build_query($_REQUEST) . '</p>';
			//echo '<p>POST: ' . http_build_query($_POST) . '</p>';

			$fields = $_POST;
			$need_name = ( is_null( $fields['name'] ) or trim( $fields['name'] ) == '' );
			$need_slug = ( is_null( $fields['slug'] ) or trim( $fields['slug'] ) == '' );
			//echo '<p>need_name = ' . $need_name . ', need_slug = ' . $need_slug . '</p>';
			if ( $need_name or $need_slug )
			{
				$message = __('You must fill-in at least Name and Slug', 'by-this-author');
				$edit_term = $fields;
			}
			else
			{
				$found = ! is_null( self::find_term_from_slug( $taxonomy, $args['slug'] ) );

				self::update_or_insert_term( 'people_index', $fields );
				if ( $_POST['is_member'] == 'true' ) { self::update_or_insert_term( 'member_index', $fields ); } else { self::delete_term( 'member_index', $fields['slug'] ); }
				if ( $_POST['is_author'] == 'true' ) { self::update_or_insert_term( 'author_index', $fields ); } else { self::delete_term( 'author_index', $fields['slug'] ); }

				$message = sprintf( $found ? __('Person data updated for %s', 'by-this-author') : __('Person data added for %s', 'by-this-author'), $fields['name'] );
			}
		}
		else if ( $_REQUEST['action'] == 'edit' )
		{
			$terms = get_terms( array( 'taxonomy' => 'people_index', 'hide_empty' => false, 'slug' => $_REQUEST['slug'] ) );
			if ( count( $terms ) )
			{
				$edit_term = (array) $terms[0];
				$edit_term['is_member'] = term_exists( $_REQUEST['slug'], 'member_index' );
				$edit_term['is_author'] = term_exists( $_REQUEST['slug'], 'author_index' );
			}
			$_REQUEST['action'] = null;
			$_REQUEST['person'] = null;
		}
		else if ( $_REQUEST['action'] == 'delete' )
		{
			$terms = get_terms( array( 'taxonomy' => 'people_index', 'hide_empty' => false, 'slug' => $_REQUEST['slug'] ) );
			if ( count( $terms ) )
			{
				self::delete_term( 'people_index', $_REQUEST['slug'] );
				self::delete_term( 'member_index', $_REQUEST['slug'] );
				self::delete_term( 'author_index', $_REQUEST['slug'] );
				$message = sprintf( __('Person data deleted for %s', 'by-this-author'), $terms[0]->name );
			}
		}

		$peopleTable = new By_This_Author_People_Table();
    $peopleTable->prepare_items();
	?>

	<div class="wrap nosubsub">

		<h1><?php echo __('People', 'by-this-author'); ?></h1>
		<div id="ajax-response"></div>
		<?php if ( ! is_null( $message ) and trim( $message ) != '' ) echo '<div id="message" class="updated">' . $message . '</div>'; ?>

		<!--
		<form class="search-form wp-clearfix" method="get">
		<input type="hidden" name="taxonomy" value="people_index" />
		<p class="search-box">
		<input type="search" id="tag-search-input" name="s" value />
		<input type="submit" id="search-submit" class="button" value="Find tag" />
		</p>
		</form>
		-->

		<div id="col-container" class="wp-clearfix">

			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h2><?php echo __('Person Data', 'by-this-author'); ?></h2>
						<form id="edittag" method="post" action="<?php echo '?' . http_build_query($_REQUEST); ?>" class="validate">
							<input type="hidden" name="taxonomy" value="people_index"></input>
							<div class="form-field form-required <?php if ( $need_name ) echo 'form-invalid' ?>">
								<label><?php echo __('Name'); ?></label>
								<input name="name" id="name" type="text" size="40" aria-required="true" value="<?php echo $edit_term['name']; ?>" ></input>
								<p><?php echo __('The name is how it appears on your site.'); ?></p>
							</div>
							<div class="form-field form-required <?php if ( $need_slug ) echo 'form-invalid' ?>">
								<label><?php echo __('Slug'); ?></label>
								<input name="slug" id="slug" type="text" size="40" aria-required="false" value="<?php echo $edit_term['slug']; ?>"></input>
								<p><?php echo __('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p>
							</div>
							<div class="form-field">
								<label><?php echo __('Description'); ?></label>
								<textarea name="description" id="description" rows="5" cols="40"><?php echo $edit_term['description']; ?></textarea>
								<p><?php echo __('The description is not prominent by default; however, some themes may show it.'); ?></p>
							</div>
							<div class="form-field">
								<label><?php echo __('Is member', 'by-this-author'); ?></label>
								<input type="checkbox" name="is_member" id="is_member" <?php echo ( $edit_term['is_member'] ? 'checked="true"' : '' ); ?> value="true"></input>
							</div>
							<div class="form-field">
								<label><?php echo __('Is author', 'by-this-author'); ?></label>
								<input type="checkbox" name="is_author" id="is_author" <?php echo ( $edit_term['is_author'] ? 'checked="true"' : '' ); ?> value="true"></input>
							</div>
							<p class="submit">
								<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save data', 'by-this-author'); ?>" ></input>
								<input type="submit" name="cancel" id="cancel" class="button button-primary" value="<?php echo __('Cancel', 'by-this-author'); ?>" ></input>
							</p>
						</form>
					</div>
				</div>
		  </div>

			<div id="col-right">
				<div class="col-wrap">
					<?php $peopleTable->display() ?>
				</div>
			</div>

		</div>

	</div>
	<?php
	}

	static function add_menu_items()
	{
	    add_menu_page(__('People Edit', 'by-this-author'), __('People', 'by-this-author'), 'activate_plugins', 'bta_people_list', array(get_class(), 'render_page'), 'dashicons-admin-users', 26);
	}

	static function init()
	{
		add_action('admin_menu', array(get_class(), 'add_menu_items'));
	}
}

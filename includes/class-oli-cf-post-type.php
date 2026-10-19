<?php
/**
 * Register custom post type for contact forms.
 *
 * @package Oli_Contact_Form
 */

/**
 * Post type class.
 */
class Oli_CF_Post_Type {

	/**
	 * Register the custom post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Contact Forms', 'Post type general name', 'oli-contact-form' ),
			'singular_name'         => _x( 'Contact Form', 'Post type singular name', 'oli-contact-form' ),
			'menu_name'             => _x( 'Oli Contact Forms', 'Admin Menu text', 'oli-contact-form' ),
			'name_admin_bar'        => _x( 'Contact Form', 'Add New on Toolbar', 'oli-contact-form' ),
			'add_new'               => __( 'Add New', 'oli-contact-form' ),
			'add_new_item'          => __( 'Add New Contact Form', 'oli-contact-form' ),
			'new_item'              => __( 'New Contact Form', 'oli-contact-form' ),
			'edit_item'             => __( 'Edit Contact Form', 'oli-contact-form' ),
			'view_item'             => __( 'View Contact Form', 'oli-contact-form' ),
			'all_items'             => __( 'All Forms', 'oli-contact-form' ),
			'search_items'          => __( 'Search Contact Forms', 'oli-contact-form' ),
			'not_found'             => __( 'No contact forms found.', 'oli-contact-form' ),
			'not_found_in_trash'    => __( 'No contact forms found in Trash.', 'oli-contact-form' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-email',
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'supports'           => array( 'title' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'oli_contact_form', $args );
	}
}

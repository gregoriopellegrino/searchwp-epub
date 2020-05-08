<?php
	/*
	EPUB indexer for SearchWP

	@author      Gregorio Pellegrino

	@wordpress-plugin
	Plugin Name: SearchWP EPUB
	Plugin URI:  https://github.com/gregoriopellegrino/searchwp-epub
	Description: WordPress plugin that enables indexing of EPUBs in SearchWP
	Version:     0.1
	Author:      Gregorio Pellegrino
	Author URI:  https://effata.it
	Text Domain: search-wp
	License:     private
	GitHub Plugin URI: https://github.com/gregoriopellegrino/searchwp-epub
	*/

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	add_action('searchwp_index_post', function($post) {
		if ( 'application/epub+zip' === $this->post->post_mime_type ) {
			$document_content = get_post_meta( $post->ID, SEARCHWP_PREFIX . 'content', true );
			
			if ( empty( $document_content ) ) {
				require __DIR__ . '/vendor/autoload.php';
				$client = \Vaites\ApacheTika\Client::make('tika-app-1.24.1.jar');
			
				$filename = get_attached_file( absint( $post->ID ) );
			
				$document_content = $client->getText($filename);
			
				$document_content = sanitize_text_field( $document_content );
				delete_post_meta( $this->post->ID, SEARCHWP_PREFIX . 'content' );
				update_post_meta( $this->post->ID, SEARCHWP_PREFIX . 'content', $document_content );
			}
		}
	});
<?php
/*
EPUB indexer for SearchWP

@author      Gregorio Pellegrino

@wordpress-plugin
Plugin Name: SearchWP EPUB
Plugin URI:  https://github.com/gregoriopellegrino/searchwp-epub
Description: WordPress plugin that enables indexing of EPUBs in SearchWP
Version:     0.3.1
Author:      Gregorio Pellegrino
Author URI:  https://effata.it
Text Domain: search-wp
License:     private
GitHub Plugin URI: https://github.com/gregoriopellegrino/searchwp-epub
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'searchwp\entry\data', function( $data, \SearchWP\Entry $entry ) {
	if ( 'post' !== substr( 0, 4, $entry->get_source()->get_name() ) ) {
		return $entry;
	}

	$post = get_post( $entry->get_id() );

	if ( 'application/epub+zip' !== $post->post_mime_type ) {
		return $entry;
	}

	if ( ! metadata_exists( 'post', $post->ID, SEARCHWP_PREFIX . 'content' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';

		$client           = \Vaites\ApacheTika\Client::make( __DIR__ .'/tika-app-1.24.1.jar' );
		$filename         = get_attached_file( absint( $post->ID ) );
		$document_content = $client->getText( $filename );
		$document_content = sanitize_text_field( $document_content );

		update_post_meta( $post->ID, SEARCHWP_PREFIX . 'content', $document_content );
	}

	if ( empty( $document_content ) ) {
		$document_content = get_post_meta( $post->ID, SEARCHWP_PREFIX . 'content', true );
	}

	$metadata = (array) $client->getMetadata( $filename )->meta;

	unset( $metadata['X-Parsed-By'] );

	$data['document_content' ]                = $document_content;
	$data['meta']['searchwp_epub_metadata'] = $metadata;

	return $data;
}, 10, 2 );

add_action('searchwp_index_post', function($post) {
	if ( 'application/epub+zip' === $post->post_mime_type ) {
		if ( ! metadata_exists( 'post', $post->ID, SEARCHWP_PREFIX . 'content' ) ) {
			require __DIR__ . '/vendor/autoload.php';

			$client           = \Vaites\ApacheTika\Client::make( __DIR__ .'/tika-app-1.24.1.jar' );
			$filename         = get_attached_file( absint( $post->ID ) );
			$document_content = $client->getText( $filename );
			$document_content = sanitize_text_field( $document_content );

			update_post_meta( $post->ID, SEARCHWP_PREFIX . 'content', $document_content );
		}
	}
});

add_filter( 'searchwp_get_custom_fields', function( $custom, $post_id ) {
	if ( get_post_mime_type($post_id) === 'application/epub+zip' ) {
		require __DIR__ . '/vendor/autoload.php';

		$client   = \Vaites\ApacheTika\Client::make( __DIR__ .'/tika-app-1.24.1.jar' );
		$filename = get_attached_file( absint( $post->ID ) );
		$metadata = (array) $client->getMetadata( $filename )->meta;

		unset( $metadata['X-Parsed-By'] );

		$custom['searchwp_epub_metadata'] = $metadata;
	}

	return $custom;
}, 10, 2);

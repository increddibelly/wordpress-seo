<?php

namespace Yoast\WP\SEO\Builders;

use Yoast\WP\SEO\Helpers\Author_Archive_Helper;
use Yoast\WP\SEO\Models\Indexable;
use Yoast\WP\SEO\Repositories\Indexable_Repository;

/**
 * Author Builder for the indexables.
 *
 * Formats the author meta to indexable format.
 */
class Indexable_Author_Builder implements Indexable_Builder_Interface {
	use Indexable_Social_Image_Trait;

	/**
	 * The author archive helper.
	 *
	 * @var Author_Archive_Helper
	 */
	private $author_archive;

	/**
	 * The Indexable repository.
	 *
	 * @var Indexable_Repository
	 */
	private $indexable_repository;

	/**
	 * Indexable_Author_Builder constructor.
	 *
	 * @param Author_Archive_Helper $author_archive The author archive helper.
	 * @param Indexable_Repository  $indexable_repository The Indexable Repo.
	 */
	public function __construct(
		Author_Archive_Helper $author_archive,
		Indexable_Repository $indexable_repository
	) {
		$this->author_archive = $author_archive;
		$this->indexable_repository = $indexable_repository;
	}

	/**
	 * Formats the data.
	 *
	 * @param int       $user_id   The user to retrieve the indexable for.
	 * @param Indexable $indexable The indexable to format.
	 *
	 * @return bool|Indexable The extended indexable or false if something broke.
	 */
	public function build( $user_id, Indexable $indexable ) {
		$author = $this->indexable_repository->find_by_id_and_type( $indexable->author_id, 'user', false );
		if ( $author ) {
			return $author;
		}

		$meta_data = $this->get_meta_data( $user_id );

		$indexable->object_id              = $user_id;
		$indexable->object_type            = 'user';
		$indexable->permalink              = \get_author_posts_url( $user_id );
		$indexable->title                  = $meta_data['wpseo_title'];
		$indexable->description            = $meta_data['wpseo_metadesc'];
		$indexable->is_cornerstone         = false;
		$indexable->is_robots_noindex      = ( $meta_data['wpseo_noindex_author'] === 'on' );
		$indexable->is_robots_nofollow     = null;
		$indexable->is_robots_noarchive    = null;
		$indexable->is_robots_noimageindex = null;
		$indexable->is_robots_nosnippet    = null;
		$indexable->is_public              = ( $indexable->is_robots_noindex ) ? false : null;
		$indexable->has_public_posts       = $this->author_archive->author_has_public_posts( $user_id );
		$indexable->blog_id                = \get_current_blog_id();

		$this->reset_social_images( $indexable );
		$this->handle_social_images( $indexable );

		return $indexable;
	}

	/**
	 * Retrieves the meta data for this indexable.
	 *
	 * @param int $user_id The user to retrieve the meta data for.
	 *
	 * @return array List of meta entries.
	 */
	protected function get_meta_data( $user_id ) {
		$keys = [
			'wpseo_title',
			'wpseo_metadesc',
			'wpseo_noindex_author',
		];

		$output = [];
		foreach ( $keys as $key ) {
			$output[ $key ] = $this->get_author_meta( $user_id, $key );
		}

		return $output;
	}

	/**
	 * Retrieves the author meta.
	 *
	 * @param int    $user_id The user to retrieve the indexable for.
	 * @param string $key     The meta entry to retrieve.
	 *
	 * @return string The value of the meta field.
	 */
	protected function get_author_meta( $user_id, $key ) {
		$value = \get_the_author_meta( $key, $user_id );
		if ( \is_string( $value ) && $value === '' ) {
			return null;
		}

		return $value;
	}

	/**
	 * Finds an alternative image for the social image.
	 *
	 * @param Indexable $indexable The indexable.
	 *
	 * @return array|bool False when not found, array with data when found.
	 */
	protected function find_alternative_image( Indexable $indexable ) {
		$gravatar_image = \get_avatar_url(
			$indexable->object_id,
			[
				'size'   => 500,
				'scheme' => 'https',
			]
		);
		if ( $gravatar_image ) {
			return [
				'image'  => $gravatar_image,
				'source' => 'gravatar-image',
			];
		}

		return false;
	}

	/**
	 * Determines whether this builder understands the passed object type.
	 *
	 * @param string $object_type The object type to check.
	 *
	 * @return bool Whether or not this builder understands the $object_type.
	 */
	public function understands( $object_type ) {
		return $object_type === 'user' || $object_type === 'post';
	}

	/**
	 * Returns the build priority.
	 *
	 * @return int The build priority.
	 */
	public function priority() {
		return 2;
	}
}

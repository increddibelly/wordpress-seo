<?php

namespace Yoast\WP\SEO\Builders;

use Yoast\WP\SEO\Models\Indexable;
use Yoast\WP\SEO\Repositories\Indexable_Repository;

/**
 * Builder for the indexables.
 *
 * Creates all the indexables.
 */
class Indexable_Build_Manager {
	/**
	 * The array of all indexable builders we have
	 *
	 * @var Indexable_Builder_Interface[]
	 */
	private $builders;

	/**
	 * The indexable repository.
	 *
	 * @var Indexable_Repository
	 */
	private $indexable_repository;

	/**
	 * Returns the instance of this class constructed through the ORM Wrapper.
	 *
	 *
	 * @param Indexable_Builder_Interface ...$builders All the indexable builders we know.
	 */
	public function __construct(
		Indexable_Builder_Interface ...$builders
	) {
		$this->builders = $builders;
	}

	/**
	 * Sets the indexable repository. Done to avoid circular dependencies.
	 *
	 * @param Indexable_Repository $indexable_repository The indexable repository.
	 *
	 * @required
	 */
	public function set_indexable_repository( Indexable_Repository $indexable_repository ) {
		$this->indexable_repository = $indexable_repository;
	}

	/**
	 * Creates an indexable by its ID and type.
	 *
	 * @param int            $object_id   The indexable object ID.
	 * @param string         $object_type The indexable object type.
	 * @param Indexable|bool $indexable   Optional. An existing indexable to overwrite.
	 *
	 * @return bool|Indexable Instance of indexable. False when unable to build.
	 */
	public function build_for_id_and_type( $object_id, $object_type, $indexable = false ) {
		$indexable        = $this->ensure_indexable( $indexable );
		$indexable_before = $this->indexable_repository
			->query()
			->create( $indexable->as_array() );

		// find all Builders that understand the current object.
		$matching_builders = array_filter($this->builders,
			function ($builder) use ($object_type) {
			return $builder->understands($object_type);
		});

		// order by builder priority, to make the lowest priority go first.
		usort($matching_builder,
			function ($builder, $other_builder) {
			return $builder->priority() < $other_builder->priority();
		});

		foreach($matching_builders as $builder){
			$builder->build( $object_id, $indexable );
		}

		// Something went wrong building, create a false indexable.
		if ( $indexable === false ) {
			$indexable = $this->indexable_repository->query()->create(
				[
					'object_id'   => $object_id,
					'object_type' => $object_type,
					'post_status' => 'unindexed',
				]
			);
		}

		if ( \in_array( $object_type, [ 'post', 'term' ], true ) && $indexable->post_status !== 'unindexed' ) {
			$this->hierarchy_builder->build( $indexable );
		}

		$this->save_indexable( $indexable, $indexable_before );

		return $indexable;
	}

	/**
	 * Creates an indexable for the homepage.
	 *
	 * @param Indexable|bool $indexable Optional. An existing indexable to overwrite.
	 *
	 * @return Indexable The home page indexable.
	 */
	public function build_for_home_page( $indexable = false ) {
		$indexable_before = $this->ensure_indexable( $indexable );
		$indexable        = $this->home_page_builder->build( $indexable_before );

		return $this->save_indexable( $indexable, $indexable_before );
	}

	/**
	 * Creates an indexable for the date archive.
	 *
	 * @param Indexable|bool $indexable Optional. An existing indexable to overwrite.
	 *
	 * @return Indexable The date archive indexable.
	 */
	public function build_for_date_archive( $indexable = false ) {
		$indexable = $this->ensure_indexable( $indexable );
		$indexable = $this->date_archive_builder->build( $indexable );

		return $this->save_indexable( $indexable );
	}

	/**
	 * Creates an indexable for a post type archive.
	 *
	 * @param string         $post_type The post type.
	 * @param Indexable|bool $indexable Optional. An existing indexable to overwrite.
	 *
	 * @return Indexable The post type archive indexable.
	 */
	public function build_for_post_type_archive( $post_type, $indexable = false ) {
		$indexable_before = $this->ensure_indexable( $indexable );
		$indexable        = $this->post_type_archive_builder->build( $post_type, $indexable_before );

		return $this->save_indexable( $indexable, $indexable_before );
	}

	/**
	 * Creates an indexable for a system page.
	 *
	 * @param string         $object_sub_type The type of system page.
	 * @param Indexable|bool $indexable       Optional. An existing indexable to overwrite.
	 *
	 * @return Indexable The search result indexable.
	 */
	public function build_for_system_page( $object_sub_type, $indexable = false ) {
		$indexable_before = $this->ensure_indexable( $indexable );
		$indexable        = $this->system_page_builder->build( $object_sub_type, $indexable_before );

		return $this->save_indexable( $indexable, $indexable_before );
	}

	/**
	 * Ensures we have a valid indexable. Creates one if false is passed.
	 *
	 * @param Indexable|false $indexable The indexable.
	 *
	 * @return Indexable The indexable.
	 */
	private function ensure_indexable( $indexable ) {
		if ( ! $indexable ) {
			return $this->indexable_repository->query()->create();
		}

		return $indexable;
	}

	/**
	 * Saves and returns an indexable.
	 *
	 * @param Indexable      $indexable        The indexable.
	 * @param Indexable|null $indexable_before The indexable before possible changes.
	 *
	 * @return Indexable The indexable.
	 */
	private function save_indexable( $indexable, $indexable_before = null ) {
		if ( $indexable_before ) {
			/**
			 * Action: 'wpseo_save_indexable' - Allow developers to perform an action
			 * when the indexable is updated.
			 *
			 * @param Indexable The indexable before saving.
			 *
			 * @api Indexable The saved indexable.
			 */
			\do_action( 'wpseo_save_indexable', $indexable, $indexable_before );
		}

		$indexable->save();

		return $indexable;
	}
}

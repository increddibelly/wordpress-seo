<?php

namespace Yoast\WP\SEO\Builders;

/**
 * Interface Indexable_Builder_Interface
 *
 * @package Yoast\WP\SEO\Builders
 */
interface Indexable_Builder_Interface {

	/**
	 * Determines whether this builder understands the passed object type.
	 *
	 * @param string $object_type The object type to check.
	 *
	 * @return bool Whether or not this builder understands the $object_type.
	 */
	public function understands( $object_type );

	/**
	 * Returns the build priority.
	 *
	 * @return int The build priority.
	 */
	public function priority();

	/**
	 * Builds the indexable.
	 *
	 * @param int $object_id       The object ID.
	 * @param Indexable $indexable The indexable to build.
	 *
	 * @return Indexable The built indexable.
	 */
	public function build( $object_id, $indexable );
}

<?php

namespace Yoast\WP\SEO\Builders;

use Yoast\WP\SEO\Models\Indexable;

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
	 * @param string $object_type The type to determine priority for.
	 *
	 * @return int The build priority for the $object_type.
	 */
	public function priority( $object_type );

	/**
	 * Builds the indexable.
	 *
	 * @param int $object_id       The object ID.
	 * @param Indexable $indexable The indexable to build.
	 *
	 * @return bool|Indexable The built indexable or false if something broke.
	 */
	public function build( $object_id, Indexable $indexable );
}


/*
 *
	nice to have:

	const int BUILDER_PRIORITY_FIRST = 0;
	const int BUILDER_PRIORITY_PRETTY_HIGH = 1;
	const int BUILDER_PRIORITY_MAYBE_LATER = 2;

	this prevents arbitrary numbers messing up the execution priority
 */

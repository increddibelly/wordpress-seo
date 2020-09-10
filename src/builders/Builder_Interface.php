<?php


namespace Yoast\WP\SEO\Builders;


interface Indexable_Builder_Interface {

	/**
	 * @param $object_type
	 *
	 * @return bool Does this builder understand the @ref $object_type
	 */
	public function understands($object_type);

	/**
	 * The Builder with the lowest priority goes first
	 *
	 * @return int
	 */
	public function priority();

	/**
	 * Handle the actual building of this particular indexable
	 *
	 * @param Indexable $indexable
	 * @param Indexable $indexable_before
	 * @param int $object_id
	 *
	 * @return Indexable The indexable
	 */
	public function build( $indexable, $indexable_before, $object_id );
}

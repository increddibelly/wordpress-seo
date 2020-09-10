<?php

namespace Yoast\WP\SEO\Dependency_Injection;

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use Yoast\WP\SEO\Fruit_Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A pass is a step in the compilation process of the container.
 *
 * This step will automatically ensure all classes implementing the Integration interface
 * are registered with the Loader class.
 */
class Interface_Injection_Pass implements CompilerPassInterface {

	/**
	 * Checks all definitions to ensure all classes implementing the Integration interface
	 * are registered with the Loader class.
	 *
	 * @param ContainerBuilder $container The container.
	 */
	public function process( ContainerBuilder $container ) {
        try {
            $definitions = $container->getDefinitions();
        foreach ( $definitions as $definition ) {
            $definition_class = $definition->getClass();
            if ( ! \class_exists( $definition_class ) ) {
                continue;
            }
            $reflection    = new ReflectionClass( $definition_class );
            $constructor   = $reflection->getConstructor();
            if ( ! $constructor ) {
                continue;
            }
            $parameters     = $constructor->getParameters();
            $last_parameter = end( $parameters );
            if ( ! $last_parameter || ! $last_parameter->isVariadic() ) {
                continue;
            }
            /**
             * @var ReflectionNamedType
             */
            $type = $last_parameter->getType();
            if ( ! is_a( $type, ReflectionNamedType::class ) ) {
                continue;
            }
            var_dump( "Found class to inject!" );
            var_dump( $definition->getClass() );
            $argument_class       = $type->getName();
            var_dump( "Going to inject: " . $argument_class );
            $argument_definitions = \array_filter( $definitions, function ( $other_definition ) use( $argument_class, $definition ) {
                if ( $other_definition === $definition ) {
                    return false;
                }

                $other_class = $other_definition->getClass();
                return \is_subclass_of( $other_class, $argument_class );
            } );
            $index = $last_parameter->getPosition();
            foreach ( $argument_definitions as $argument_definition ) {
                $definition->setArgument( $index, new Reference( $argument_definition->getClass() ) );
                $index += 1;
            }
        }
        } catch ( Exception $e ) {
            var_dump( $e );
        }

	}
}

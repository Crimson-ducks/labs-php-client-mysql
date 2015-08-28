<?php
namespace Clearbooks\Labs;

use DI\Container;
use DI\ContainerBuilder;

class Bootstrap
{
    /**
     * @var Bootstrap
     */
    private static $instance = null;

    /**
     * @var Container
     */
    private $DIContainer;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @codeCoverageIgnore
     * @return Bootstrap
     */
    public static function getInstance()
    {
        if ( self::$instance == null ) {
            self::$instance = new Bootstrap();
        }

        return self::$instance;
    }

    /**
     * Initialization (e.g.: DI container)
     *
     * @param string[] $definitionProviderClasses
     */
    public function init( array $definitionProviderClasses )
    {
        if ( $this->initialized ) {
            return;
        }

        $containerBuilder = new ContainerBuilder();

        foreach ( $definitionProviderClasses as $definitionProviderClass ) {
            /** @var DIDefinitionProvider $definitionProvider */
            $definitionProvider = new $definitionProviderClass;

            $definitionPaths = $definitionProvider->getDefinitionPaths();
            foreach ( $definitionPaths as $definitionPath ) {
                $containerBuilder->addDefinitions( $definitionPath );
            }
        }

        $this->DIContainer = $containerBuilder->build();

        $this->initialized = true;
    }

    /**
     * @return Container
     */
    public function getDIContainer()
    {
        return $this->DIContainer;
    }
}

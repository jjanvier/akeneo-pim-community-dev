<?php

namespace Akeneo\Bundle\ElasticSearchBundle\IndexConfiguration;

/**
 * Simple data holder for the index configuration
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class IndexConfiguration
{
    /** @var array */
    protected $settings;

    /** @var array */
    protected $mappings;

    /** @var array */
    protected $aliases;

    /**
     * @param array $settings
     * @param array $mappings
     * @param array $aliases
     */
    public function __construct(array $settings, array $mappings, array $aliases)
    {
        $this->settings = $settings;
        $this->mappings = $mappings;
        $this->aliases = $aliases;
    }

    /**
     * Get the index settings configuration
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get the index mappings configuration
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Get the index aliases configuration
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }
}

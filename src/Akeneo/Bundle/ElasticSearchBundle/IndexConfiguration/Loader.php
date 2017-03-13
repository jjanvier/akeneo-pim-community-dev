<?php

namespace Akeneo\Bundle\ElasticSearchBundle\IndexConfiguration;

use Symfony\Component\Yaml\Parser;

/**
 * ElasticSearch configuration loader. Allows to load "index settings", "mappings" and "aliases".
 * To learn more, see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html}
 *
 * This loader is able to load the configuration from several different files. For instance, from the default
 * Akeneo file, and from a custom project file.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Loader
{
    /** @var array */
    private $configurationFiles;

    /**
     * @param array $configurationFiles
     */
    public function __construct(array $configurationFiles = [])
    {
        $this->configurationFiles = $configurationFiles;
    }

    public function load()
    {
        $settings = [];
        $mappings = [];
        $aliases = [];
        $yaml = new Parser();

        foreach ($this->configurationFiles as $configurationFile) {
            if (!is_readable($configurationFile)) {
                throw new \Exception(
                    sprintf('The elasticsearch configuration file "%s" is not readable.', $configurationFile)
                );
            }

            $configuration = $yaml->parse(file_get_contents($configurationFile));

            if (isset($configuration['settings'])) {
                $settings = array_merge_recursive($settings, $configuration['settings']);
            }
            if (isset($configuration['mappings'])) {
                $mappings = array_merge_recursive($mappings, $configuration['mappings']);
            }
            if (isset($configuration['aliases'])) {
                $aliases = array_merge_recursive($aliases, $configuration['aliases']);
            }
        }

        return new IndexConfiguration($settings, $mappings, $aliases);
    }
}

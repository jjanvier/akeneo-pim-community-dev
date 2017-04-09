<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;

/**
 * Collects advanced data about the host server of the PIM:
 * - Storage used (ORM or ODM)
 * - MySQL or MariaDB used + version
 * - MongoDB version (if used)
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StorageDataCollector implements DataCollectorInterface
{
    const DIVERGENT_MARIADB_VERSION = '10';

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return array_merge(
            $this->getStorageDriver(),
            $this->getStorageVersion()
        );
    }

    /**
     * Returns the storage driver used for the catalog (ORM or MongoDB).
     *
     * @return array
     */
    protected function getStorageDriver()
    {
        return ['pim_storage_driver' => 'doctrine/orm'];
    }

    /**
     * Returns MySQL/MariaDB version and, if used, MongoDB version.
     *
     * @return array
     */
    public function getStorageVersion()
    {
        $version = $this->getSQLVersion();

        return $version;
    }

    /**
     * Returns the version of MySQL or MariaDB.
     *
     * MySQL and MariaDB are fully compatible (for now) and the PHP driver cannot
     * tell the difference.
     * But as MariaDB started to diverge internally, the version numbers are now
     * different, with MariaDB tags starting at 10.*. So we assume that if
     * version is 10 or higher it is MariaDB, if lower it is MySQL.
     *
     * @return array
     */
    protected function getSQLVersion()
    {
        $version = mysqli_get_client_info();

        true === version_compare($version, static::DIVERGENT_MARIADB_VERSION, '>=') ?
            $storage = 'mariadb_version' :
            $storage = 'mysql_version';

        return [$storage => $version];
    }
}

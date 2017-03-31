<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Product family selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySelector implements SelectorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $locale = $configuration->offsetGetByPath('[source][locale_code]');

        $qb = $this->em->createQueryBuilder('f');
        $qb
            ->leftJoin('f.translations', 'ft', 'WITH', 'ft.locale = :dataLocale')
            ->addSelect('COALESCE(NULLIF(ft.label, \'\'), CONCAT(\'[\', f.code, \']\')) as familyLabel')
            ->setParameter('dataLocale', $locale);
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Index product in ElasticSearch when a product is saved.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductSubscriber implements EventSubscriberInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface          $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'indexProduct'];
    }

    /**
     * Normalizes product values into "indexing" format, sends it to ElasticSearch.
     *
     * @param GenericEvent $event
     */
    public function indexProduct(GenericEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $indexedProduct = $this->normalizer->normalize($product, 'indexing');
    }
}

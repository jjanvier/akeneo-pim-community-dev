<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductIndexer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
    }

    function let(NormalizerInterface $normalizer, Client $indexer)
    {
        $this->beConstructedWith($normalizer, $indexer, 'an_index_type_for_test_purpose');
    }

    function it_throws_an_exception_when_attempting_to_index_a_non_product(
        $normalizer,
        $indexer,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_index_a_product_without_id(
        $normalizer,
        $indexer,
        ProductInterface $aWrongProduct
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_non_product(
        $normalizer,
        $indexer,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($product, Argument::cetera())->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProduct, Argument::cetera())->shouldNotBeCalled();
        $indexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $indexer,
        ProductInterface $product,
        ProductInterface $aWrongProduct
    ) {
        $normalizer->normalize($product, Argument::cetera())->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProduct, Argument::cetera())->willReturn([]);
        $indexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product_without_associations($normalizer, $indexer, ProductInterface $product)
    {
        $normalizer->normalize($product, 'indexing')->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $indexer
            ->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $product->getAssociations()->willReturn([]);

        $this->index($product);
    }

    function it_bulk_indexes_products_without_associations(
        $normalizer,
        $indexer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $normalizer->normalize($product1, 'indexing')->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, 'indexing')->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value']
        ], 'id')->shouldBeCalled();

        $product1->getAssociations()->willReturn([]);
        $product2->getAssociations()->willReturn([]);

        $this->indexAll([$product1, $product2]);
    }

    function it_indexes_a_single_product_with_associations(
        $normalizer,
        $indexer,
        ProductInterface $product,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ArrayCollection $associations,
        AssociationInterface $association1,
        AssociationInterface $association2,
        \ArrayIterator $associationsIterator
    ) {
        $normalizer->normalize($product, 'indexing')->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $indexer
            ->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($associationsIterator);
        $associationsIterator->rewind()->shouldBeCalled();
        $associationsIterator->valid()->willReturn(true, true, false);
        $associationsIterator->current()->willReturn($association1, $association2);
        $associationsIterator->next()->shouldBeCalled();

        $association1->getProducts()->willReturn([$associatedProduct1]);
        $association2->getProducts()->willReturn([$associatedProduct2]);

        $normalizer
            ->normalize($associatedProduct1, 'indexing')
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer
            ->normalize($associatedProduct2, 'indexing')
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value']
        ], 'id')->shouldBeCalled();

        $associatedProduct1->getAssociations()->shouldNotBeCalled();
        $associatedProduct2->getAssociations()->shouldNotBeCalled();

        $this->index($product);
    }

    function it_bulk_indexes_products_with_associations(
        $normalizer,
        $indexer,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductInterface $associatedProduct3,
        ArrayCollection $associations1,
        ArrayCollection $associations2,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationInterface $association3,
        \ArrayIterator $associationsIterator1,
        \ArrayIterator $associationsIterator2
    ) {
        $normalizer->normalize($product1, 'indexing')->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, 'indexing')->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value']
        ], 'id')->shouldBeCalled();

        $product1->getAssociations()->willReturn($associations1);
        $product2->getAssociations()->willReturn($associations2);

        $product1->getAssociations()->willReturn($associations1);
        $associations1->getIterator()->willReturn($associationsIterator1);
        $associationsIterator1->rewind()->shouldBeCalled();
        $associationsIterator1->valid()->willReturn(true, true, false);
        $associationsIterator1->current()->willReturn($association1, $association2);
        $associationsIterator1->next()->shouldBeCalled();

        $product2->getAssociations()->willReturn($associations2);
        $associations2->getIterator()->willReturn($associationsIterator2);
        $associationsIterator2->rewind()->shouldBeCalled();
        $associationsIterator2->valid()->willReturn(true, true, false);
        $associationsIterator2->current()->willReturn($association3);
        $associationsIterator2->next()->shouldBeCalled();

        $association1->getProducts()->willReturn([$associatedProduct1, $product2]);
        $association2->getProducts()->willReturn([$associatedProduct2, $associatedProduct3]);
        $association3->getProducts()->willReturn([$associatedProduct1, $associatedProduct3, $product1]);

        $normalizer
            ->normalize($associatedProduct1, 'indexing')
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $normalizer
            ->normalize($associatedProduct2, 'indexing')
            ->willReturn(['id' => 'foobaz', 'a key' => 'another value']);
        $normalizer
            ->normalize($associatedProduct3, 'indexing')
            ->willReturn(['id' => 'barbaz', 'a key' => 'yet another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foobar', 'a key' => 'a value'],
            ['id' => 'foobaz', 'a key' => 'another value'],
            ['id' => 'barbaz', 'a key' => 'yet another value']
        ], 'id')->shouldBeCalled();

        $associatedProduct1->getAssociations()->shouldNotBeCalled();
        $associatedProduct2->getAssociations()->shouldNotBeCalled();
        $associatedProduct3->getAssociations()->shouldNotBeCalled();

        $this->indexAll([$product1, $product2]);
    }
}

<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Normalizer\Indexing\AttributeOptionNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionNormalizer::class);
    }

    function it_support_attribute_options(AttributeOptionInterface $option)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($option, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($option, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_attribute_options($stdNormalizer, AttributeOptionInterface $option)
    {
        $stdNormalizer->normalize($option, 'indexing', ['context'])->willReturn('option');

        $this->normalize($option, 'indexing', ['context'])->shouldReturn('option');
    }
}

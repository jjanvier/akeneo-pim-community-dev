<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Indexing\FileNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileNormalizer::class);
    }

    function it_supports_file_infos(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_file_infos($stdNormalizer, FileInfoInterface $fileInfo)
    {
        $stdNormalizer->normalize($fileInfo, 'indexing', ['context'])->willReturn('file_info');

        $this->normalize($fileInfo, 'indexing', ['context'])->shouldReturn('file_info');
    }
}

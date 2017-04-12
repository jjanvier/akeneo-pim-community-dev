<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Builder\FlexibleValuesBuilderInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FlexibleValuesInterface;

/**
 * Sets a media data in a product.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetter extends AbstractAttributeSetter
{
    /** @var FileStorerInterface */
    protected $storer;

    /** @var FileInfoRepositoryInterface */
    protected $repository;

    /**
     * @param FlexibleValuesBuilderInterface     $flexibleValuesBuilder
     * @param FileStorerInterface         $storer
     * @param FileInfoRepositoryInterface $repository
     * @param string[]                    $supportedTypes
     */
    public function __construct(
        FlexibleValuesBuilderInterface $flexibleValuesBuilder,
        FileStorerInterface $storer,
        FileInfoRepositoryInterface $repository,
        array $supportedTypes
    ) {
        parent::__construct($flexibleValuesBuilder);

        $this->storer = $storer;
        $this->repository = $repository;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :  "/absolute/file/path/filename.extension"
     */
    public function setAttributeData(
        FlexibleValuesInterface $flexibleValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);

        if (null === $data) {
            $file = null;
        } elseif (null === $file = $this->repository->findOneByIdentifier($data)) {
            $file = $this->storeFile($attribute, $data);
        }

        $this->flexibleValuesBuilder->addOrReplaceValue(
            $flexibleValues,
            $attribute,
            $options['locale'],
            $options['scope'],
            null !== $file ? $file->getKey() : null
        );
    }

    /**
     * TODO: inform the user that this could take some time.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyException If an invalid filePath is provided
     * @return FileInfoInterface|null
     */
    protected function storeFile(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return null;
        }

        $rawFile = new \SplFileInfo($data);

        if (!$rawFile->isFile()) {
            throw InvalidPropertyException::validPathExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $file = $this->storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);

        return $file;
    }
}

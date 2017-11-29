<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\EasyExtendsBundle\Bundle;

use Symfony\Component\Finder\Finder;

class PhpcrMetadata
{
    /**
     * @var string
     */
    protected $mappingDocumentDirectory;

    /**
     * @var string
     */
    protected $extendedMappingDocumentDirectory;

    /**
     * @var string
     */
    protected $documentDirectory;

    /**
     * @var string
     */
    protected $extendedDocumentDirectory;

    /**
     * @var string
     */
    protected $extendedSerializerDirectory;

    /**
     * @param BundleMetadata $bundleMetadata
     */
    public function __construct(BundleMetadata $bundleMetadata)
    {
        $this->mappingDocumentDirectory = sprintf('%s/Resources/config/doctrine/', $bundleMetadata->getBundle()->getPath());
        $this->extendedMappingDocumentDirectory = sprintf('%s/Resources/config/doctrine/', $bundleMetadata->getExtendedDirectory());
        $this->documentDirectory = sprintf('%s/PHPCR', $bundleMetadata->getBundle()->getPath());
        $this->extendedDocumentDirectory = sprintf('%s/PHPCR', $bundleMetadata->getExtendedDirectory());
        $this->extendedSerializerDirectory = sprintf('%s/Resources/config/serializer', $bundleMetadata->getExtendedDirectory());
    }

    /**
     * @return string
     */
    public function getMappingDocumentDirectory(): string
    {
        return $this->mappingDocumentDirectory;
    }

    /**
     * @return string
     */
    public function getExtendedMappingDocumentDirectory(): string
    {
        return $this->extendedMappingDocumentDirectory;
    }

    /**
     * @return string
     */
    public function getDocumentDirectory(): string
    {
        return $this->documentDirectory;
    }

    /**
     * @return string
     */
    public function getExtendedDocumentDirectory(): string
    {
        return $this->extendedDocumentDirectory;
    }

    /**
     * @return string
     */
    public function getExtendedSerializerDirectory(): string
    {
        return $this->extendedSerializerDirectory;
    }

    /**
     * @return array|\Iterator
     */
    public function getDocumentMappingFiles(): iterable
    {
        try {
            $f = new Finder();
            $f->name('*.phpcr.xml.skeleton');
            $f->in($this->getMappingDocumentDirectory());

            return $f->getIterator();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    public function getDocumentNames(): array
    {
        $names = [];

        try {
            $f = new Finder();
            $f->name('*.phpcr.xml.skeleton');
            $f->in($this->getMappingDocumentDirectory());

            foreach ($f->getIterator() as $file) {
                $name = explode('.', basename($file));
                $names[] = $name[0];
            }
        } catch (\Exception $e) {
        }

        return $names;
    }

    /**
     * @return array|\Iterator
     */
    public function getRepositoryFiles(): iterable
    {
        try {
            $f = new Finder();
            $f->name('*Repository.php');
            $f->in($this->getDocumentDirectory());

            return $f->getIterator();
        } catch (\Exception $e) {
            return [];
        }
    }
}
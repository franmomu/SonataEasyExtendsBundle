<?php

declare(strict_types=1);

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

final class OrmMetadata
{
    /**
     * @var string
     */
    private $mappingEntityDirectory;

    /**
     * @var string
     */
    private $extendedMappingEntityDirectory;

    /**
     * @var string
     */
    private $entityDirectory;

    /**
     * @var string
     */
    private $extendedEntityDirectory;

    /**
     * @var string
     */
    private $extendedSerializerDirectory;

    public function __construct(BundleMetadata $bundleMetadata)
    {
        $this->mappingEntityDirectory = sprintf('%s/Resources/config/doctrine/', $bundleMetadata->getBundle()->getPath());
        $this->extendedMappingEntityDirectory = sprintf('%s/Resources/config/doctrine/', $bundleMetadata->getExtendedDirectory());
        $this->entityDirectory = sprintf('%s/Entity', $bundleMetadata->getBundle()->getPath());
        $this->extendedEntityDirectory = sprintf('%s/Entity', $bundleMetadata->getExtendedDirectory());
        $this->extendedSerializerDirectory = sprintf('%s/Resources/config/serializer', $bundleMetadata->getExtendedDirectory());
    }

    public function getMappingEntityDirectory(): string
    {
        return $this->mappingEntityDirectory;
    }

    public function getExtendedMappingEntityDirectory(): string
    {
        return $this->extendedMappingEntityDirectory;
    }

    public function getEntityDirectory(): string
    {
        return $this->entityDirectory;
    }

    public function getExtendedEntityDirectory(): string
    {
        return $this->extendedEntityDirectory;
    }

    public function getExtendedSerializerDirectory(): string
    {
        return $this->extendedSerializerDirectory;
    }

    /**
     * @return array|\Iterator
     */
    public function getEntityMappingFiles(): iterable
    {
        try {
            $f = new Finder();
            $f->name('*.orm.xml.skeleton');
            $f->name('*.orm.yml.skeleton');
            $f->in($this->getMappingEntityDirectory());

            return $f->getIterator();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getEntityNames(): array
    {
        $names = [];

        try {
            $f = new Finder();
            $f->name('*.orm.xml.skeleton');
            $f->name('*.orm.yml.skeleton');
            $f->in($this->getMappingEntityDirectory());

            foreach ($f->getIterator() as $file) {
                $name = explode('.', $file->getFilename());
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
            $f->in($this->getEntityDirectory());

            return $f->getIterator();
        } catch (\Exception $e) {
            return [];
        }
    }
}

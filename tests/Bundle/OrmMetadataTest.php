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

namespace Sonata\EasyExtendsBundle\Tests\Bundle;

// Unfortunately phpunit cannot mock a class in chosen namespace.
// Therefore mocks are stored in Fixtures/bundle1 directory and required here.
require_once __DIR__.'/Fixtures/bundle1/SonataAcmeBundle.php';
require_once __DIR__.'/Fixtures/bundle2/dot.dot/DotBundle.php';
require_once __DIR__.'/Fixtures/bundle3/EmptyBundle.php';

use PHPUnit\Framework\TestCase;
use Sonata\AcmeBundle\SonataAcmeBundle;
use Sonata\DotBundle\SonataDotBundle;
use Sonata\EasyExtendsBundle\Bundle\BundleMetadata;
use Sonata\EasyExtendsBundle\Bundle\OrmMetadata;
use Sonata\EmptyBundle\SonataEmptyBundle;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class OrmMetadataTest extends TestCase
{
    public function testEntityNames(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $entityNames = $ormMetadata->getEntityNames();

        $this->assertCount(4, $entityNames);
        $this->assertContains('Block', $entityNames);
        $this->assertContains('Page', $entityNames);
    }

    public function testDirectoryWithDotInPath(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataDotBundle()));

        $entityNames = $ormMetadata->getEntityNames();

        $this->assertCount(4, $entityNames);
        $this->assertContains('Block', $entityNames);
        $this->assertContains('Page', $entityNames);
    }

    public function testGetMappingEntityDirectory(): void
    {
        $bundlePath = __DIR__.'/Fixtures/bundle1';
        $expectedDirectory = $bundlePath.'/Resources/config/doctrine/';

        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $ormMetadata->getMappingEntityDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedMappingEntityDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/Resources/config/doctrine/';

        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $ormMetadata->getExtendedMappingEntityDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetEntityDirectory(): void
    {
        $bundlePath = __DIR__.'/Fixtures/bundle1';
        $expectedDirectory = $bundlePath.'/Entity';

        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $ormMetadata->getEntityDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedEntityDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/Entity';

        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $ormMetadata->getExtendedEntityDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedSerializerDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/Resources/config/serializer';

        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $ormMetadata->getExtendedSerializerDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetEntityMappingFiles(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $filterIterator = $ormMetadata->getEntityMappingFiles();

        $files = [];
        foreach ($filterIterator as $file) {
            $files[] = $file->getFilename();
        }

        $this->assertInstanceOf('Iterator', $filterIterator);
        $this->assertContainsOnly(SplFileInfo::class, $filterIterator);
        $this->assertContains('Block.orm.xml.skeleton', $files);
        $this->assertContains('Page.orm.xml.skeleton', $files);
        $this->assertNotContains('Block.mongodb.xml.skeleton', $files);
        $this->assertNotContains('Page.mongodb.xml.skeleton', $files);
    }

    public function testGetEntityMappingFilesWithFilesNotFound(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataEmptyBundle()));

        $result = $ormMetadata->getEntityMappingFiles();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRepositoryFiles(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $filterIterator = $ormMetadata->getRepositoryFiles();

        $files = [];
        foreach ($filterIterator as $file) {
            $files[] = $file->getFilename();
        }

        $this->assertInstanceOf('Iterator', $filterIterator);
        $this->assertContainsOnly(SplFileInfo::class, $filterIterator);
        $this->assertContains('BlockRepository.php', $files);
        $this->assertContains('PageRepository.php', $files);
        $this->assertNotContains('Block.php', $files);
        $this->assertNotContains('Page.php', $files);
    }

    public function testGetRepositoryFilesWithFilesNotFound(): void
    {
        $ormMetadata = new OrmMetadata($this->getBundleMetadata(new SonataEmptyBundle()));

        $result = $ormMetadata->getRepositoryFiles();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    private function getBundleMetadata(BundleInterface $bundle): BundleMetadata
    {
        return new BundleMetadata($bundle, [
            'application_dir' => 'Application/:vendor',
            'namespace' => 'Application\\:vendor',
            'namespace_prefix' => '',
        ]);
    }
}

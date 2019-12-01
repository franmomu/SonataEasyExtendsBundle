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
use Sonata\EasyExtendsBundle\Bundle\PhpcrMetadata;
use Sonata\EmptyBundle\SonataEmptyBundle;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class PhpcrMetadataTest extends TestCase
{
    public function testDocumentNames(): void
    {
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $documentNames = $odmMetadata->getDocumentNames();

        $this->assertContains('Block', $documentNames);
        $this->assertContains('Page', $documentNames);
    }

    public function testDirectoryWithDotInPath(): void
    {
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataDotBundle()));

        $documentNames = $odmMetadata->getDocumentNames();

        $this->assertContains('Block', $documentNames);
        $this->assertContains('Page', $documentNames);
    }

    public function testGetMappingDocumentDirectory(): void
    {
        $bundlePath = __DIR__.'/Fixtures/bundle1';
        $expectedDirectory = $bundlePath.'/Resources/config/doctrine/';

        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $odmMetadata->getMappingDocumentDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedMappingDocumentDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/Resources/config/doctrine/';

        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $odmMetadata->getExtendedMappingDocumentDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetDocumentDirectory(): void
    {
        $bundlePath = __DIR__.'/Fixtures/bundle1';
        $expectedDirectory = $bundlePath.'/PHPCR';

        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $odmMetadata->getDocumentDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedDocumentDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/PHPCR';

        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $odmMetadata->getExtendedDocumentDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetExtendedSerializerDirectory(): void
    {
        $expectedDirectory = 'Application/Sonata/AcmeBundle/Resources/config/serializer';

        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $directory = $odmMetadata->getExtendedSerializerDirectory();

        $this->assertSame($expectedDirectory, $directory);
    }

    public function testGetDocumentMappingFiles(): void
    {
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $filterIterator = $odmMetadata->getDocumentMappingFiles();

        $files = [];
        foreach ($filterIterator as $file) {
            $files[] = $file->getFilename();
        }

        $this->assertInstanceOf('Iterator', $filterIterator);
        $this->assertContainsOnly(SplFileInfo::class, $filterIterator);
        $this->assertContains('Block.phpcr.xml.skeleton', $files);
        $this->assertContains('Page.phpcr.xml.skeleton', $files);
        $this->assertNotContains('Block.odm.xml.skeleton', $files);
        $this->assertNotContains('Page.odm.xml.skeleton', $files);
    }

    public function testGetDocumentMappingFilesWithFilesNotFound(): void
    {
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataEmptyBundle()));

        $result = $odmMetadata->getDocumentMappingFiles();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRepositoryFiles(): void
    {
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataAcmeBundle()));

        $filterIterator = $odmMetadata->getRepositoryFiles();

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
        $odmMetadata = new PhpcrMetadata($this->getBundleMetadata(new SonataEmptyBundle()));

        $result = $odmMetadata->getRepositoryFiles();

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

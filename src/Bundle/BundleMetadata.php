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

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class BundleMetadata
{
    /**
     * @var BundleInterface
     */
    private $bundle;

    /**
     * @var string
     */
    private $vendor;

    /**
     * @var bool
     */
    private $valid = false;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $extendedDirectory;

    /**
     * @var string
     */
    private $extendedNamespace;

    /**
     * @var array
     */
    private $configuration = [];

    /**
     * @var OrmMetadata
     */
    private $ormMetadata;

    /**
     * @var OdmMetadata
     */
    private $odmMetadata;

    /**
     * @var PhpcrMetadata
     */
    private $phpcrMetadata;

    /**
     * @var string
     */
    private $application;

    public function __construct(BundleInterface $bundle, array $configuration = [])
    {
        $this->bundle = $bundle;
        $this->configuration = $configuration;

        $this->buildInformation();
    }

    public function isExtendable(): bool
    {
        // does not extends Application bundle ...
        return !(
            0 === strpos($this->getClass(), $this->configuration['namespace'])
            || 0 === strpos($this->getClass(), 'Symfony')
        );
    }

    public function getClass(): string
    {
        return \get_class($this->bundle);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getExtendedDirectory(): string
    {
        return $this->extendedDirectory;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getExtendedNamespace(): string
    {
        return $this->extendedNamespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * return the bundle name.
     *
     * @return string return the bundle name
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function getBundle(): BundleInterface
    {
        return $this->bundle;
    }

    public function getOdmMetadata(): OdmMetadata
    {
        return $this->odmMetadata;
    }

    public function getOrmMetadata(): OrmMetadata
    {
        return $this->ormMetadata;
    }

    public function getPhpcrMetadata(): PhpcrMetadata
    {
        return $this->phpcrMetadata;
    }

    /**
     * build basic information and check if the bundle respect the following convention
     *   Vendor/BundleNameBundle/VendorBundleNameBundle.
     *
     * if the bundle does not respect this convention then the easy extends command will ignore
     * this bundle
     */
    private function buildInformation(): void
    {
        $information = explode('\\', $this->getClass());

        if (!$this->isExtendable()) {
            $this->valid = false;

            return;
        }

        if (3 !== \count($information)) {
            $this->valid = false;

            return;
        }

        if ($information[0].$information[1] !== $information[2]) {
            $this->valid = false;

            return;
        }

        $this->name = $information[\count($information) - 1];
        $this->vendor = $information[0];
        $this->namespace = sprintf('%s\\%s', $this->vendor, $information[1]);
        $this->extendedDirectory =
            str_replace(':vendor', $this->vendor, $this->configuration['application_dir']).
            \DIRECTORY_SEPARATOR.
            $information[1];
        $this->extendedNamespace = sprintf(
            '%s%s\\%s',
            $this->configuration['namespace_prefix'],
            str_replace(':vendor', $this->vendor, $this->configuration['namespace']),
            $information[1]
        );
        $this->application = explode('\\', $this->configuration['namespace'])[0];
        $this->valid = true;

        $this->ormMetadata = new OrmMetadata($this);
        $this->odmMetadata = new OdmMetadata($this);
        $this->phpcrMetadata = new PhpcrMetadata($this);
    }
}

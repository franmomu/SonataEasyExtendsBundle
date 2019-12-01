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

namespace Sonata\EasyExtendsBundle\Generator;

use Sonata\EasyExtendsBundle\Bundle\BundleMetadata;
use Symfony\Component\Console\Output\OutputInterface;

final class SerializerGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    private $entitySerializerTemplate;

    /**
     * @var string
     */
    private $documentSerializerTemplate;

    public function __construct()
    {
        $this->entitySerializerTemplate = (string) file_get_contents(
            __DIR__.'/../Resources/skeleton/serializer/entity.mustache'
        );
        $this->documentSerializerTemplate = (string) file_get_contents(
            __DIR__.'/../Resources/skeleton/serializer/document.mustache'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate(OutputInterface $output, BundleMetadata $bundleMetadata): void
    {
        $this->generateOrmSerializer($output, $bundleMetadata);
        $this->generateOdmSerializer($output, $bundleMetadata);
        $this->generatePhpcrSerializer($output, $bundleMetadata);
    }

    private function generateOrmSerializer(OutputInterface $output, BundleMetadata $bundleMetadata): void
    {
        $names = $bundleMetadata->getOrmMetadata()->getEntityNames();

        if (\is_array($names) && \count($names) > 0) {
            $output->writeln(' - Generating ORM serializer files');

            foreach ($names as $name) {
                $destFile = sprintf(
                    '%s/Entity.%s.xml',
                    $bundleMetadata->getOrmMetadata()->getExtendedSerializerDirectory(),
                    $name
                );

                $this->writeSerializerFile($output, $bundleMetadata, $this->entitySerializerTemplate, $destFile, $name);
            }
        }
    }

    private function generateOdmSerializer(OutputInterface $output, BundleMetadata $bundleMetadata): void
    {
        $names = $bundleMetadata->getOdmMetadata()->getDocumentNames();

        if (\is_array($names) && \count($names) > 0) {
            $output->writeln(' - Generating ODM serializer files');

            foreach ($names as $name) {
                $destFile = sprintf(
                    '%s/Document.%s.xml',
                    $bundleMetadata->getOdmMetadata()->getExtendedSerializerDirectory(),
                    $name
                );

                $this->writeSerializerFile(
                    $output,
                    $bundleMetadata,
                    $this->documentSerializerTemplate,
                    $destFile,
                    $name
                );
            }
        }
    }

    private function generatePhpcrSerializer(OutputInterface $output, BundleMetadata $bundleMetadata): void
    {
        $names = $bundleMetadata->getPhpcrMetadata()->getDocumentNames();

        if (\is_array($names) && \count($names) > 0) {
            $output->writeln(' - Generating PHPCR serializer files');

            foreach ($names as $name) {
                $destFile = sprintf(
                    '%s/Document.%s.xml',
                    $bundleMetadata->getPhpcrMetadata()->getExtendedSerializerDirectory(),
                    $name
                );

                $this->writeSerializerFile(
                    $output,
                    $bundleMetadata,
                    $this->documentSerializerTemplate,
                    $destFile,
                    $name
                );
            }
        }
    }

    private function writeSerializerFile(OutputInterface $output, BundleMetadata $bundleMetadata, string $template, string $destFile, string $name): void
    {
        if (is_file($destFile)) {
            $output->writeln(sprintf('   ~ <info>%s</info>', $name));
        } else {
            $output->writeln(sprintf('   + <info>%s</info>', $name));

            $string = Mustache::replace($template, [
                'name' => $name,
                'namespace' => $bundleMetadata->getExtendedNamespace(),
                'root_name' => strtolower(preg_replace('/[A-Z]/', '_\\0', $name)),
            ]);

            file_put_contents($destFile, $string);
        }
    }
}

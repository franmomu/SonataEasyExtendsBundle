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

namespace Sonata\EasyExtendsBundle\Command;

use Sonata\EasyExtendsBundle\Bundle\BundleMetadata;
use Sonata\EasyExtendsBundle\Generator\GeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Generate Application entities from bundle entities.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GenerateCommand extends Command
{
    protected static $defaultName = 'sonata:easy-extends:generate';

    private $kernel;
    private $generatorBundle;
    private $generatorOrm;
    private $generatorOdm;
    private $generatorPhpcr;
    private $generatorSerializer;

    public function __construct(
        KernelInterface $kernel,
        GeneratorInterface $generatorBundle,
        GeneratorInterface $generatorOrm,
        GeneratorInterface $generatorOdm,
        GeneratorInterface $generatorPhpcr,
        GeneratorInterface $generatorSerializer
    ) {
        $this->kernel = $kernel;
        $this->generatorBundle = $generatorBundle;
        $this->generatorOrm = $generatorOrm;
        $this->generatorOdm = $generatorOdm;
        $this->generatorPhpcr = $generatorPhpcr;
        $this->generatorSerializer = $generatorSerializer;


        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp(<<<'EOT'
The <info>easy-extends:generate:entities</info> command generating a valid bundle structure from a Vendor Bundle.

  <info>ie: ./app/console sonata:easy-extends:generate SonataUserBundle</info>
EOT
            );

        $this->setDescription('Create entities used by Sonata\'s bundles');

        $this->addArgument('bundle', InputArgument::IS_ARRAY, 'The bundle name to "easy-extends"');
        $this->addOption(
            'dest',
            'd',
            InputOption::VALUE_OPTIONAL,
            'The base folder where the Application will be created',
            false
        );
        $this->addOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'The namespace for the classes', false);
        $this->addOption('namespace_prefix', 'nsp', InputOption::VALUE_OPTIONAL, 'The namespace prefix for the classes', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $destOption = $input->getOption('dest');
        if ($destOption) {
            $dest = realpath($destOption);
            if (false === $dest) {
                throw new \RuntimeException(sprintf(
                    'The provided destination folder \'%s\' does not exist!',
                    $destOption
                ));
            }
        } else {
            $dest = $this->kernel->getProjectDir();
        }

        $namespace = $input->getOption('namespace');
        if ($namespace) {
            if (!preg_match('/^(?:(?:[[:alnum:]]+|:vendor)\\\\?)+$/', $namespace)) {
                throw new \InvalidArgumentException(sprintf(
                    'The provided namespace "%s" is not a valid namespace!',
                    $namespace
                ));
            }
        } else {
            $namespace = 'Application\:vendor';
        }

        $configuration = [
            'application_dir' => sprintf(
                '%s%s%s',
                $dest,
                \DIRECTORY_SEPARATOR,
                str_replace('\\', \DIRECTORY_SEPARATOR, $namespace)
            ),
            'namespace' => $namespace,
            'namespace_prefix' => '',
        ];

        if ($namespacePrefix = $input->getOption('namespace_prefix')) {
            $configuration['namespace_prefix'] = rtrim($namespacePrefix, '\\').'\\';
        }

        $bundleNames = $input->getArgument('bundle');

        if (empty($bundleNames)) {
            $output->writeln('');
            $output->writeln('<error>You must provide a bundle name!</error>');
            $output->writeln('');
            $output->writeln('  Bundles availables :');
            /** @var BundleInterface $bundle */
            foreach ($this->kernel->getBundles() as $bundle) {
                $bundleMetadata = new BundleMetadata($bundle, $configuration);

                if (!$bundleMetadata->isExtendable()) {
                    continue;
                }

                $output->writeln(sprintf('     - %s', $bundle->getName()));
            }

            $output->writeln('');
        } else {
            foreach ($bundleNames as $bundleName) {
                $processed = $this->generate($bundleName, $configuration, $output);

                if (!$processed) {
                    throw new \RuntimeException(sprintf(
                        '<error>The bundle \'%s\' does not exist or is not registered in the kernel!</error>',
                        $bundleName
                    ));
                }
            }
        }

        $output->writeln('done!');

        return 0;
    }

    /**
     * Generates a bundle entities from a bundle name.
     */
    protected function generate(string $bundleName, array $configuration, OutputInterface $output): bool
    {
        $processed = false;

        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle->getName() !== $bundleName) {
                continue;
            }

            $processed = true;
            $bundleMetadata = new BundleMetadata($bundle, $configuration);

            // generate the bundle file.
            if (!$bundleMetadata->isExtendable()) {
                $output->writeln(sprintf('Ignoring bundle : "<comment>%s</comment>"', $bundleMetadata->getClass()));

                continue;
            }

            // generate the bundle file
            if (!$bundleMetadata->isValid()) {
                $output->writeln(sprintf(
                    '%s : <comment>wrong directory structure</comment>',
                    $bundleMetadata->getClass()
                ));

                continue;
            }

            $output->writeln(sprintf('Processing bundle : "<info>%s</info>"', $bundleMetadata->getName()));

            $this->generatorBundle->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine ORM : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->generatorOrm->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine ODM : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->generatorOdm->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Doctrine PHPCR : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->generatorPhpcr->generate($output, $bundleMetadata);

            $output->writeln(sprintf('Processing Serializer config : "<info>%s</info>"', $bundleMetadata->getName()));
            $this->generatorSerializer->generate($output, $bundleMetadata);

            $output->writeln('');
        }

        return $processed;
    }
}

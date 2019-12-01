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

use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate Application entities from bundle entities.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DumpMappingCommand extends Command
{
    protected static $defaultName = 'sonata:easy-extends:dump-mapping';

    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Dump some mapping information (debug only)');

        $this->addArgument('manager', InputArgument::REQUIRED, 'The manager name to use');
        $this->addArgument('model', InputArgument::REQUIRED, 'The class to dump');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory = $this->registry
            ->getManager($input->getArgument('manager'))
            ->getMetadataFactory();

        $metadata = $factory->getMetadataFor($input->getArgument('model'));

        $cme = new ClassMetadataExporter();
        $exporter = $cme->getExporter('php');

        $output->writeln($exporter->exportClassMetadata($metadata));
        $output->writeln('Done!');

        return 0;
    }
}

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

namespace Sonata\EasyExtendsBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sonata\AcmeBundle\SonataAcmeBundle;
use Sonata\EasyExtendsBundle\Command\GenerateCommand;
use Sonata\EasyExtendsBundle\Generator\GeneratorInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class GenerateCommandTest extends TestCase
{
    /**
     * @dataProvider executeData
     */
    public function testExecute(array $args): void
    {
        $commandTester = $this->buildCommand();
        $commandTester->execute($args);

        $this->assertStringContainsString(
            'done!',
            $commandTester->getDisplay()
        );
    }

    public function executeData(): array
    {
        return [
            [
                [
                    '--dest' => 'src',
                    'bundle' => ['SonataAcmeBundle'],
                ],
            ],
            [
                [
                    '--dest' => 'src',
                    'bundle' => ['SonataAcmeBundle'],
                    '--namespace' => 'Application\\Sonata',
                ],
            ],
            [
                [
                    '--dest' => 'src',
                    'bundle' => ['SonataAcmeBundle'],
                    '--namespace_prefix' => 'App',
                ],
            ],
            [
                [
                    '--dest' => 'src',
                    'bundle' => ['SonataAcmeBundle'],
                    '--namespace' => 'Application\\Sonata',
                    '--namespace_prefix' => 'App',
                ],
            ],
        ];
    }

    public function testExecuteWrongDest(): void
    {
        $commandTester = $this->buildCommandToFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The provided destination folder 'fakedest' does not exist!");

        $commandTester->execute([
            '--dest' => 'fakedest',
        ]);
    }

    public function testNoArgument(): void
    {
        $commandTester = $this->buildCommandToFail();

        $commandTester->execute([
            '--dest' => 'src',
        ]);

        $this->assertStringContainsString(
            'You must provide a bundle name!',
            $commandTester->getDisplay()
        );
    }

    public function testFakeBundleName(): void
    {
        $commandTester = $this->buildCommandToFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The bundle 'FakeBundle' does not exist or is not registered in the kernel!");

        $commandTester->execute([
            '--dest' => 'src',
            'bundle' => ['FakeBundle'],
        ]);

        $this->assertStringContainsString(
            'You must provide a bundle name!',
            $commandTester->getDisplay()
        );
    }

    public function testNotExtendableBundle(): void
    {
        $commandTester = $this->buildCommandToFail(new \Symfony\Bundle\NotExtendableBundle());

        $commandTester->execute([
            '--dest' => 'src',
            'bundle' => ['NotExtendableBundle'],
        ]);

        $this->assertStringContainsString(
            sprintf('Ignoring bundle : "Symfony\Bundle\NotExtendableBundle"'),
            $commandTester->getDisplay()
        );
    }

    public function testInvalidFolderStructure(): void
    {
        $commandTester = $this->buildCommandToFail(new \Application\Sonata\NotExtendableBundle());

        $commandTester->execute([
            '--dest' => 'src',
            'bundle' => ['NotExtendableBundle'],
        ]);

        $this->assertStringContainsString(
            sprintf('Application\Sonata\NotExtendableBundle : wrong directory structure'),
            $commandTester->getDisplay()
        );
    }

    private function buildCommand(BundleInterface $kernelReturnValue = null): CommandTester
    {
        $kernel = $this->mockKernel($kernelReturnValue);

        $command = new GenerateCommand(
            $kernel,
            $this->mockGenerator(),
            $this->mockGenerator(),
            $this->mockGenerator(),
            $this->mockGenerator(),
            $this->mockGenerator()
        );

        return new CommandTester($command);
    }

    private function buildCommandToFail(BundleInterface $kernelReturnValue = null): CommandTester
    {
        $kernel = $this->createMock(KernelInterface::class);

        $kernel
            ->method('getBundles')
            ->willReturn([
                $kernelReturnValue ?: new SonataAcmeBundle(),
            ]);

        $command = new GenerateCommand(
            $kernel,
            $this->createMock(GeneratorInterface::class),
            $this->createMock(GeneratorInterface::class),
            $this->createMock(GeneratorInterface::class),
            $this->createMock(GeneratorInterface::class),
            $this->createMock(GeneratorInterface::class)
        );

        return new CommandTester($command);
    }

    private function mockKernel(?BundleInterface $kernelReturnValue)
    {
        $kernelMock = $this->createMock(KernelInterface::class);

        $kernelMock->expects($this->once())
            ->method('getBundles')
            ->willReturn([
                $kernelReturnValue ?: new SonataAcmeBundle(),
            ]);

        return $kernelMock;
    }

    private function mockGenerator()
    {
        $generatorMock = $this->createMock(GeneratorInterface::class);

        $generatorMock->expects($this->once())
            ->method('generate');

        return $generatorMock;
    }
}

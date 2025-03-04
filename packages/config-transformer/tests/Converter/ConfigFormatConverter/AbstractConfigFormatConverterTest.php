<?php

declare(strict_types=1);

namespace Symplify\ConfigTransformer\Tests\Converter\ConfigFormatConverter;

use Symplify\ConfigTransformer\Converter\ConfigFormatConverter;
use Symplify\ConfigTransformer\HttpKernel\ConfigTransformerKernel;
use Symplify\ConfigTransformer\ValueObject\Configuration;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractConfigFormatConverterTest extends AbstractKernelTestCase
{
    protected ConfigFormatConverter $configFormatConverter;

//    protected Configuration $configuration;

    protected SmartFileSystem $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(ConfigTransformerKernel::class);

        $this->configFormatConverter = $this->getService(ConfigFormatConverter::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    protected function doTestOutput(SmartFileInfo $fixtureFileInfo, Configuration $configuration): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos($fixtureFileInfo);

        $this->doTestFileInfo(
            $inputAndExpected->getInputFileInfo(),
            $inputAndExpected->getExpectedFileContent(),
            $fixtureFileInfo,
            $configuration
        );
    }

    protected function doTestFileInfo(
        SmartFileInfo $inputFileInfo,
        string $expectedContent,
        SmartFileInfo $fixtureFileInfo,
        Configuration $configuration
    ): void {
        $convertedContent = $this->configFormatConverter->convert($inputFileInfo, $configuration);
        StaticFixtureUpdater::updateFixtureContent($inputFileInfo, $convertedContent, $fixtureFileInfo);

        $this->assertSame($expectedContent, $convertedContent, $fixtureFileInfo->getRelativeFilePathFromCwd());
    }
}

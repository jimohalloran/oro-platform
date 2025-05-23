<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Async\PurgeEmailAttachmentsMessageProcessor;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\TestCase;

class PurgeEmailAttachmentsMessageProcessorTest extends TestCase
{
    /**
     * @dataProvider getSizeDataProvider
     */
    public function testShouldReturnCorrectAttachmentSizeByPayload(
        array $payload,
        int $parameterSize,
        int $expectedResult
    ): void {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects(self::any())
            ->method('get')
            ->with('oro_email.attachment_sync_max_size')
            ->willReturn($parameterSize);

        $processor = new PurgeEmailAttachmentsMessageProcessor(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(MessageProducerInterface::class),
            $this->createMock(JobRunner::class),
            $configManager
        );

        $actualResult = ReflectionUtil::callMethod($processor, 'getSize', [$payload]);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function getSizeDataProvider(): array
    {
        return [
            [
                'payload' => ['size' => null, 'all' => true],
                'parameterSize' => 10,
                'result' => 0
            ],
            [
                'payload' => ['size' => 2, 'all' => false],
                'parameterSize' => 10,
                'result' => 2000000
            ],
            [
                'payload' => ['size' => null, 'all' => false],
                'parameterSize' => 3,
                'result' => 3000000
            ]
        ];
    }
}

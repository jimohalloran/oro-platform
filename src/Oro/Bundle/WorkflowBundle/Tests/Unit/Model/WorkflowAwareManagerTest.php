<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAwareManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\EntityStub;

class WorkflowAwareManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var WorkflowManager|\PHPUnit\Framework\MockObject\MockObject */
    private $workflowManager;

    /** @var WorkflowAwareManager */
    private $manger;

    private string $testWorkflowName = 'test_workflow';

    #[\Override]
    protected function setUp(): void
    {
        $this->workflowManager = $this->createMock(WorkflowManager::class);
        $this->manger = new WorkflowAwareManager($this->workflowManager);
        $this->manger->setWorkflowName($this->testWorkflowName);
    }

    public function testInterface()
    {
        $workflowName = 'test_workflow1';

        $this->manger->setWorkflowName($workflowName);

        $this->assertEquals($workflowName, $this->manger->getWorkflowName());
    }

    public function testGetWorkflowItem()
    {
        $expectedItem = new WorkflowItem();
        $entity = new EntityStub(42);

        $this->workflowManager->expects($this->once())
            ->method('getWorkflowItem')
            ->with($entity, $this->testWorkflowName)
            ->willReturn($expectedItem);

        $this->assertSame($expectedItem, $this->manger->getWorkflowItem($entity));
    }

    public function testGetWorkflow()
    {
        $workflow = $this->createMock(Workflow::class);

        $this->workflowManager->expects($this->once())
            ->method('getWorkflow')
            ->with($this->testWorkflowName)
            ->willReturn($workflow);

        $this->assertSame($workflow, $this->manger->getWorkflow());
    }

    public function testStartWorkflow()
    {
        $startedItem = new WorkflowItem();
        $entity = new EntityStub(42);

        $this->workflowManager->expects($this->once())
            ->method('startWorkflow')
            ->with($this->testWorkflowName, $entity)
            ->willReturn($startedItem);

        $this->assertSame($startedItem, $this->manger->startWorkflow($entity));
    }
}

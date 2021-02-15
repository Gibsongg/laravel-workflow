<?php

namespace Tests;

use ReflectionException;
use Tests\Fixtures\TestCustomObject;
use Symfony\Component\Workflow\TransitionBlocker;
use Tests\Fixtures\TestModel;
use ZeroDaHero\LaravelWorkflow\Events\GuardEvent;
use ZeroDaHero\LaravelWorkflow\WorkflowRegistry;
use Illuminate\Support\Facades\Event;
use Workflow;

/**
 * @group integration
 */
class GuardExpressionLanguage extends BaseWorkflowTestCase
{

    public const MESSAGE_ERROR = 'The transition is blocked';

    public function testSubjectMethodStateWorkflow(): void
    {
        $registry = new WorkflowRegistry($this->getConfig());
        $subject = new TestModel;
        $workflow = $registry->get($subject);

        $this->assertEquals(true, $workflow->can($subject, 't1'));

        $workflow->apply($subject, 't1');

        $this->assertEquals(false, $workflow->can($subject, 't2'));
    }


    /**
     * @throws ReflectionException
     */
    public function testSubjectMethodStateMachine(): void
    {
        $registry = new WorkflowRegistry($this->getConfig('state_machine'));
        $subject = new TestModel;
        $workflow = $registry->get($subject);

        $this->assertEquals(true, $workflow->can($subject, 't1'));

        $workflow->apply($subject, 't1');

        $this->assertEquals(false, $workflow->can($subject, 't2'));
    }




    /**
     * Define environment setup.
     *
     * @param string $type
     * @return array
     */
    protected function getConfig(string $type = 'workflow'): array
    {
        return [
            'straight' => [
                'type' => $type,
                'supports' => [TestModel::class],
                'places' => ['a', 'b', 'c'],
                'marking_store' => [
                    'property' => 'state',
                ],
                'transitions' => [
                    't1' => [
                        'guard' => 'subject.isValidate()',
                        'from' => 'a',
                        'to' => 'b',
                    ],
                    't2' => [
                        'guard' => 'subject.isInvalidate()',
                        'from' => 'b',
                        'to' => 'c',
                    ],
                ],
                'initial_places' => 'a',
            ],
        ];
    }
}

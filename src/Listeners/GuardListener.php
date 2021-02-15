<?php

namespace ZeroDaHero\LaravelWorkflow\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\EventListener\GuardExpression;
use Symfony\Component\Workflow\TransitionBlocker;

class GuardListener implements EventSubscriberInterface
{

    /**
     * @var array
     */
    public $configuration;
    /**
     * @var array
     */
    public static $events = [];
    /**
     * @var ExpressionLanguage
     */
    public $expressionLanguage;

    public function __construct(array $configuration, ExpressionLanguage $expressionLanguage)
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
        static::$events = array_keys($configuration);
    }

    public function onTransition(GuardEvent $event, string $eventName): void
    {
        if (!isset($this->configuration[$eventName])) {
            return;
        }

        $eventConfiguration = (array)$this->configuration[$eventName];

        foreach ($eventConfiguration as $guard) {

            if ($guard instanceof GuardExpression) {
                $this->validateGuardExpression($event, $guard->getExpression());
            } else {
                $this->validateGuardExpression($event, $guard);
            }
        }

    }

    private function validateGuardExpression(GuardEvent $event, string $expression): void
    {
        if (!$this->expressionLanguage->evaluate($expression, $this->getVariables($event))) {
            $blocker = TransitionBlocker::createBlockedByExpressionGuardListener($expression);
            $event->addTransitionBlocker($blocker);
        }
    }

    private function getVariables(GuardEvent $event): array
    {
        //TODO: Add user instance
        return [
            'subject' => $event->getSubject(),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        $events = [];
        foreach (static::$events as $event) {
            $events[$event] = ['onTransition'];
        }

        return $events;
    }
}

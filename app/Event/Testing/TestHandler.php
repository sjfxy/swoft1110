<?php declare(strict_types=1);
namespace SwoftTest\Event\Testing;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Event\EventSubscriberInterface;

/**
 * Class TestHandler
 * @Listener(event="test.evt")
 */
class TestHandler implements EventHandlerInterface{

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        // TODO: Implement handle() method.
        $pos = __METHOD__;
        echo "handle the event '{$event->getName()}'on the: $pos\n";
    }
}


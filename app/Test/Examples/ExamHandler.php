<?php  declare(strict_types=1);
namespace App\Test\Event\Examples;
use Inhere\Event\EventHandlerInterface;
use Inhere\Event\EventInterface;

/**
 * 测试的Handler即可
 */
/**
 * Class SingleListener
 * @package Inhere\Event
 */
class ExamHandler implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function handle(EventInterface $event)
    {
        $pos = __METHOD__;
        echo "handle the event '{$event->getName()}' on the: $pos\n";

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Event;

use App\GitHub\EventHandler;
use App\GitHub\Status;
use App\GitHub\StatusUpdater;
use App\GitHub\UpdatesPullRequestStatus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EnvironmentDeploymentCompletedHandler implements MessageHandlerInterface
{
    use UpdatesPullRequestStatus;

    public function __construct(StatusUpdater $statusUpdater)
    {
        $this->statusUpdater = $statusUpdater;
    }

    public function __invoke(EnvironmentDeploymentCompleted $event)
    {
        $status = (new Status('success'))
            ->withDescription('Deployment completed');
        $this->updateStatus($event->getPullRequest(), $status);
    }
}

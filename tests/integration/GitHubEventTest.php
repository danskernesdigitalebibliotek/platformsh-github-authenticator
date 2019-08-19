<?php

declare(strict_types=1);

namespace App\Tests\integration;

use App\Command\GitHubEvent;
use App\Git\Synchronizer;
use GitWrapper\GitWrapper;
use Lpdigital\Github\Parser\WebhookResolver;
use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\file_get_contents as file_get_contents;
use function Safe\json_decode as json_decode;

class GitHubEventTest extends TestCase
{

    /* @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $workingDirectory;

    /* @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $targetDirectory;

    /* @var \GitWrapper\GitWorkingCopy */
    protected $targetRepo;

    public function setUp()
    {
        $this->workingDirectory = (new TemporaryDirectory())
            ->name('working')
            ->force()
            ->create();
        $this->targetDirectory = (new TemporaryDirectory())
            ->name('target')
            ->force()
            ->create();

        $git = new GitWrapper();
        $this->targetRepo = $git->init($this->targetDirectory->path());
    }

    public function testPullRequest()
    {
        $event = file_get_contents(__DIR__ . '/event.json');
        $json = json_decode($event, true);

        $command = new GitHubEvent(
            new WebhookResolver(),
            new Synchronizer(
                new GitWrapper(),
                $this->workingDirectory->path(),
                $this->targetDirectory->path()
            ),
            $json
        );

        $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $this->assertContains('changes', $this->targetRepo->getBranches()->all());
    }

    public function tearDown()
    {
        $this->workingDirectory->delete();
        $this->targetDirectory->delete();
    }
}

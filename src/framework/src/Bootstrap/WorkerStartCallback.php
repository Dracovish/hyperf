<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework\Bootstrap;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Framework\Event\OtherWorkerStart;
use Hyperf\Framework\SwooleEvent;
use Hyperf\Memory\AtomicManager;
use Hyperf\Memory\LockManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Swoole\Server as SwooleServer;

class WorkerStartCallback
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle Swoole onWorkerStart event.
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {
        $this->eventDispatcher->dispatch(new BeforeWorkerStart($server, $workerId));
        try {
            // Atomic and Lock have to initializes before worker start.
            $atomic = AtomicManager::get(SwooleEvent::ON_WORKER_START);
            $lock = LockManager::get(SwooleEvent::ON_WORKER_START);
            $lockedWorkerId = null;
            if ($lock->trylock()) {
                $lockedWorkerId = $workerId;
                // Only running in one worker.
                $this->logger->debug("Worker#${lockedWorkerId} got the lock.");
                $this->eventDispatcher->dispatch(new MainWorkerStart($server, $lockedWorkerId));
                $lock->unlock();
                $atomic->wakeup($server->setting['worker_num'] - 1);
            } else {
                $this->logger->debug("Worker#${workerId} wating ...");
                $atomic->wait();
            }
            if ($workerId !== $lockedWorkerId) {
                $this->eventDispatcher->dispatch(new OtherWorkerStart($server, $workerId));
            }
            $this->logger->info("Worker#${workerId} started.");
        } catch (RuntimeException $e) {
            $this->logger->warning('Worker atomic and lock initialize fail.');
        } finally {
            LockManager::clear(SwooleEvent::ON_WORKER_START);
            AtomicManager::clear(SwooleEvent::ON_WORKER_START);
        }
        $this->eventDispatcher->dispatch(new AfterWorkerStart($server, $workerId));
    }
}
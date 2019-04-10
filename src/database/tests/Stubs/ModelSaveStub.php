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

namespace HyperfTest\Database\Stubs;

use Mockery;
use Hyperf\Utils\Context;
use Hyperf\Database\Model\Model;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Database\ConnectionInterface as Connection;

class ModelSaveStub extends Model
{
    protected $table = 'save_stub';

    protected $guarded = ['id'];

    public function save(array $options = []): bool
    {
        Context::set('__model.saved', true);
        return true;
    }

    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }

    public function getConnection(): ConnectionInterface
    {
        $mock = Mockery::mock(Connection::class);
        $mock->shouldReceive('getQueryGrammar')->andReturn(Mockery::mock(Grammar::class));
        $mock->shouldReceive('getPostProcessor')->andReturn(Mockery::mock(Processor::class));
        $mock->shouldReceive('getName')->andReturn('name');

        return $mock;
    }
}
<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests\Feature;

use Iprote\TcbCms\Facades\TCB;
use Iprote\TcbCms\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_is_bound(): void
    {
        $this->assertInstanceOf(\Iprote\TcbCms\TCBManager::class, TCB::getFacadeRoot());
    }
}

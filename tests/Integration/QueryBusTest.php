<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use RemotelyLiving\PHPQueryBus\QueryBus;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class QueryBusTest extends AbstractTestCase
{
    private QueryBus $queryBus;

    protected function setUp(): void
    {
        $this->queryBus = $this->createConfiguredQueryBus();
    }

    public function testSingleResult(): void
    {
        $query = new Stubs\GetUserQuery('uuid');
        /** @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserResult $result */
        $result = $this->queryBus->handle($query);
        $this->assertSame('uuid', $result->getUser()->id);
        $this->assertNull($result->getUserProfileResult());
    }

    public function testCompositeResult(): void
    {
        $query = new Stubs\GetUserQuery('uuid');
        $query->includeProfile();

        /** @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserResult $result */
        $result = $this->queryBus->handle($query);
        $this->assertSame('uuid', $result->getUser()->id);
        $this->assertSame(
            Stubs\GetUserProfileHandler::USERNAME,
            $result->getUserProfileResult()->getUsername()
        );
        $this->assertEquals(
            Stubs\GetUserProfileHandler::PREFERENCES,
            $result->getUserProfileResult()->getPreferences()
        );
    }
}

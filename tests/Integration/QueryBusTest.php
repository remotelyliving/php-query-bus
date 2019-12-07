<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class QueryBusTest extends AbstractTestCase
{

    /**
     * @var \RemotelyLiving\PHPQueryBus\QueryBus
     */
    private $queryBus;

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
        $expectedJsonEncoding = <<<EOT
{
    "user": {
        "id": "uuid"
    },
    "profile": null
}
EOT;
        $this->assertSame($expectedJsonEncoding, json_encode($result, JSON_PRETTY_PRINT));
    }

    public function testCompositeResult(): void
    {
        $query = new Stubs\GetUserQuery('uuid');
        $query->addUserProfile();

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
        $expectedJsonEncoding = <<<EOT
{
    "user": {
        "id": "uuid"
    },
    "profile": {
        "preferences": {
            "foo": "bar"
        },
        "username": "christian.thomas"
    }
}
EOT;
        $this->assertSame($expectedJsonEncoding, json_encode($result, JSON_PRETTY_PRINT));
    }
}

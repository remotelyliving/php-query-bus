[![Build Status](https://travis-ci.com/remotelyliving/php-query-bus.svg?branch=master)](https://travis-ci.org/remotelyliving/php-query-bus)
[![Total Downloads](https://poser.pugx.org/remotelyliving/php-query-bus/downloads)](https://packagist.org/packages/remotelyliving/php-query-bus)
[![Coverage Status](https://coveralls.io/repos/github/remotelyliving/php-query-bus/badge.svg?branch=master)](https://coveralls.io/github/remotelyliving/php-query-bus?branch=master) 
[![License](https://poser.pugx.org/remotelyliving/php-query-bus/license)](https://packagist.org/packages/remotelyliving/php-query-bus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/remotelyliving/php-query-bus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/remotelyliving/php-query-bus/?branch=master)

# php-query-bus: A Query Bus Implementation For PHP

### Use Cases

If you want a light weight compliment to your Command Bus for CQRS, hopefully this library helps out.
It's very similar to a Command Bus, but it returns a Result. 

I've used magical data loading solutions before, but good old fashioned set of specific Query, Result, and Handler objects for a given Use Case
is generally more performant, predictable, and explicit than array or magic-based implementations. 

### Installation

```sh
composer require remotelyliving/php-query-bus
```

### Usage

#### Create the Query Resolver 

The resolver can have handlers added manually or locate them in a PSR-11 Service Container
Queries are mapped 1:1 with a handler and are mapped by the Query class name as the lookup key.
```php
$resolver = Resolver::create($serviceContainer) // can locate in service container
    ->pushHandler(MyQueryHandler1::class, new MyQueryHandler1()) // can locate in a local map
    ->pushHandlerDeferred(MyQueryHandler2::class, $lazyCreateMethod); // can locate deferred to save un unnecessary object creation

```

#### Create the Query Bus

The Query Bus takes in a Query Resolver and pushes whatever Middleware you want on the stack.
```php
$queryBus = QueryBus::create($resolver)
    ->pushMiddleware($myMiddleware1);

$query = new MyQuery1('id');
$result = $queryBus->handle($result);
```

That's really all there is to it!

### Query

The DTO's for this library are left intentionally unimplemented. They are just interfaces to implement.
Eventually all magic breaks down somewhere and I'm not providing any here. My suggestion for Query objects
is to keep them as a DTO of what you need to query your data source by. 

An example query might look like this:

```php
class GetUserQuery implements Interfaces\Query
{
    /**
     * @var bool
     */
    private $shouldIncludeProfile = false;

    /**
     * @var string
     */
    private $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function includeProfile(): self
    {
        $this->shouldIncludeProfile = true;
        return $this;
    }

    public function getGetUserProfileQuery(): ?GetUserProfileQuery
    {
        return ($this->shouldIncludeProfile)
            ? new GetUserProfileQuery($this->userId)
            : null;
    }
}
```

As you can see, it's just a few getters and option builder.

### Result

The Result is similarly unimplemented. 
Results must implement `\JsonSerializable` but that's about it.
They can have their own custom getters for your use case. An example Result for the `GetUserQuery` above might look like:

```php
class GetUserResult implements Result
{

    /**
     * @var \stdClass|null
     */
    private $user;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserProfileResult|null
     */
    private $userProfileResult;

    public function __construct(?\stdClass $user, GetUserProfileResult $userProfileResult = null)
    {
        $this->user = $user;
        $this->userProfileResult = $userProfileResult;
    }

    public function getUser(): ?\stdClass
    {
        return $this->user;
    }

    public function getUserProfileResult(): ?GetUserProfileResult
    {
        return $this->userProfileResult;
    }

    public function jsonSerialize(): array
    {
        return [
            'user' => $this->getUser(),
            'profile' => $this->getUserProfileResult(),
        ];
    }
}
``` 

As you can see, it's not too hard to start building Result graphs for outputting a response or to feed another part of your app. 

### Handler

The handlers are where the magic happens. Inject what ever repository or ORM you need to load data.
It will ask the query for query parameters and return a result. You can also request other query results inside a handler from the bus.
Going with our GetUserQuery example, a Handler could look like:

```php
class GetUserHandler implements Handler
{
    public function handle(Query $query, QueryBus $bus): Result
    {
        $user = $this->userRepository->getUserById($query->getUserId());

        return ($query->getGetUserProfileQuery())
            ? new GetUserResult($user, $bus->handle($query->getGetUserProfileQuery()))
            : new GetUserResult($user);
    }
}
```

### Middleware

There are a few middleware that this library ships with. Take a look and see if any are worth pushing on to the stack.
The default execution order is LIFO and the signature very simple.

A Middleware must return an instance of Result and be callable. That's it!

An example Middleware could be as simple as this:

```php
$cachingMiddleware = function (Query $query, callable $next) use ($queryCacher) : Result {
    if ($query instanceof CacheableQuery) {
        return $queryCacher->get($query, function () use ($next, $query) { return $next($query); });
    }
   
    return $next($query);
};
```

### Future Future Development

- Result Filtering (should be done at a query level, but would be nice to be able to specify sparse field sets
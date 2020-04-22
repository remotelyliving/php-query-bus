[![Build Status](https://travis-ci.com/remotelyliving/php-query-bus.svg?branch=master)](https://travis-ci.org/remotelyliving/php-query-bus)
[![Total Downloads](https://poser.pugx.org/remotelyliving/php-query-bus/downloads)](https://packagist.org/packages/remotelyliving/php-query-bus)
[![Coverage Status](https://coveralls.io/repos/github/remotelyliving/php-query-bus/badge.svg?branch=master)](https://coveralls.io/github/remotelyliving/php-query-bus?branch=master) 
[![License](https://poser.pugx.org/remotelyliving/php-query-bus/license)](https://packagist.org/packages/remotelyliving/php-query-bus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/remotelyliving/php-query-bus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/remotelyliving/php-query-bus/?branch=master)

# php-query-bus: 🚍 A Query Bus Implementation For PHP 🚍

### Use Cases

If you want a light weight compliment to your Command Bus for CQRS, hopefully this library helps out.
It's very similar to a Command Bus, but it returns a Result. 

I've used magical data loading solutions before, but good old fashioned set of specific Query, Result, and Handler objects for a given Use Case
is generally more performant, predictable, and explicit than magic-based implementations. 

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

Middleware is any callable that returns a Result. Some base middleware is included: [src/Middleware](https://github.com/remotelyliving/php-query-bus/tree/master/src/Middleware)

That's really all there is to it!

### Query

The DTO's for this library are left intentionally unimplemented. They are just interfaces to implement.
My suggestion for Query objects is to keep them as a DTO of what you need to query your data source by. 

An example query might look like this:

```php
class GetUserQuery implements Interfaces\Query
{
    private bool $shouldIncludeProfile = false;

    private string $userId;

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
}
```

As you can see, it's just a few getters and option builder.

### Result

The Result is similarly unimplemented except for the provided [AbstractResult](https://github.com/remotelyliving/php-query-bus/blob/master/src/AbstractResult.php).
Results can have their own custom getters for your use case. An example Result for the `GetUserQuery` above might look like:

```php
class GetUserResult extends \RemotelyLiving\PHPQueryBus\AbstractResult implements \JsonSerializable
{
    private User $user;

    private ?UserProfile $userProfile;

    public function __construct(User $user, ?UserProfile $userProfile)
    {
        $this->user = $user;
        $this->userProfileResult = $userProfile;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function jsonSerialize(): array
    {
        return [
            'user' => $this->getUser(),
            'profile' => $this->getUserProfile(),
        ];
    }
}
``` 

As you can see, it's not too hard to start building Result graphs for outputting a response or to feed another part of your app. 

### Handler

The handlers are where the magic happens. Inject what ever repository, API Client, or ORM you need to load data.
It will ask the query for query parameters and return a result. You can also request other query results inside a handler from the bus.
Going with our GetUserQuery example, a Handler could look like:

```php
class GetUserHandler implements Interfaces\Handler
{
    public function handle(Interfaces\Query $query, Interfaces\QueryBus $bus): Interfaces\Result
    {
        try {
            $user = $this->userRepository->getUserById($query->getUserId());
        } catch (ConnectectionError $e) {
            // can handle exceptions without blowing up and instead use messaging via
            // AbstractResult::getErrors() and AbstractResultHasErrors()
            return AbstractResult::withErrors($e);
        }

        
        if (!$user) {
            // can handle nullish cases by returning not found
            return AbstractResult::notFound();
        }
       
        if (!$query->shouldIncludeProfile()) {
            return new GetUserResult($user, null);
        }

        $profileResult = $bus->handle(new GetUserProfileQuery($query->getUserId()));

        return ($profileResult->isNotFound())
            ? new GetUserResult($user, null)
            : new GetUserResult($user, $profileResult->getUserProfile());
    }
}
```

### Middleware

There are a few [Middleware](https://github.com/remotelyliving/php-query-bus/tree/master/src/Middleware) that this library ships with.
The default execution order is LIFO and the signature very simple.

A Middleware must return an instance of Result and be callable. That's it!

An example Middleware could be as simple as this:

```php
$cachingMiddleware = function (Interfaces\Query $query, callable $next) use ($queryCacher) : Interfaces\Result {
    if ($query instanceof Interfaces\CacheableQuery) {
        return $queryCacher->get($query, function () use ($next, $query) { return $next($query); });
    }
   
    return $next($query);
};
```

#### [QueryCacher](https://github.com/remotelyliving/php-query-bus/blob/master/src/Middleware/QueryCacher.php)
This middleware provides some interesting query caching by utilizing [Probabilistic Early Cache Expiry](https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration)
to help prevent cache stampedes. To be cached, a Query must implement the [CacheableQuery](https://github.com/remotelyliving/php-query-bus/blob/master/src/Interfaces/CacheableQuery.php) interface.
To recompute cache simply fire off a Query with the value of `CacheableQuery::shouldReloadResult()` returning true.

#### [QueryLogger](https://github.com/remotelyliving/php-query-bus/blob/master/src/Middleware/QueryLogger.php)
Helpful for debugging, but best left for dev and stage environments.

#### [ResultErrorLogger](https://github.com/remotelyliving/php-query-bus/blob/master/src/Middleware/ResultErrorLogger.php)
Helpful for debugging and alerting based on your logging setup.

#### [PerfBudgetLogger](https://github.com/remotelyliving/php-query-bus/blob/master/src/Middleware/QueryLogger.php)
Allows you to set certain rough performance thresholds and log with something has gone over that threshold.

### Future Future Development

- Result Filtering (should be done at a query level, but would be nice to be able to specify sparse field sets

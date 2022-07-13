### The `resources` directory

In there you'll find additional resources which may come in handy during testing or development:

- An insomnia collection


### Why using the utf8mb4_general_ci collation?

Because it supports characters from languages other than latin-based ones.

> If we have API consumers from Asia, and we use latin1, then our database won't be able to store chinese or japanese characters. 


### Why do you set indentation to 2 characters for all files except php?

Because many JS projects or snippets use the Airbnb coding style, which recommends 2 spaces for indentation.

Additionally -even though symfony use 4 chars for .yaml files, the most common practice is 2 empty spaces.


### CORS

- I'm enabling CORS for demonstration purposes
- The https://geometry-calc.demo domain is from a local react app, which can be found here

# Assumptions
- The API should handle requests from a test environment
- The API should handle requests from one and only one front-end app (either an SPA or a common website)
- The API could handle requests from mobile apps (for which CORS is not required)

# Security
The approach taken for this demo is quite simple, I call it `last link controller` and it works as follows:

1. We use middlewares working as `vertical security layers`.
2. Any middleware could use one or several `horizontal security layers`.
3. The first middleware checks for a valid request (see The RequestMiddleware.php class).
4. The second middleware handles authorization and authentication (see The AuthMiddleware.php class).
4. The third middleware validates the request body (see The InputMiddleware.php class).
5. The next middleware classes handle whatever your business need to handle (work your magic there).
6. The last middleware, if any, is responsible for actually calling a controller (hence the name) from the codebase.

> Note: the last middleware is not necessary for this demo because we're using Symfony, it could be necessary depending on how your codebase is implemented or how the framework of your choice works. Take a look at the example bellow.

```php
use Psr\Http\Message\ResponseInterface;

/**
 * Handle actions for the 'last link' in the middleware 'chain'.
 * In this example I'm using the FatFree microframework.
 * 
 * @param ServerRequestInterface $request
 * @param RequestHandlerInterface $handler
 * @return ResponseInterface
 */
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // TODO: do your 'last' middleware magic here

    return $this->f3->call(
        $this->controller,
        [$this->f3, $this->params, $this->alias],
        $this->hooks
    );
}
```


# Middleware

Symfony does not provide a middleware package, so we have 2 options here: we either implement one (e.g. using the chain of responsibility pattern) or we use a 3rd party package. For this demo, I'm using kafkiansky/symfony-middleware which is PSR-compatible.


## The Request Middleware

This class handles validation of the actual request, in the example you can see how it checks for valid and required headers.

This class is the first layer of `security` for the codebase, as well as the `security` config group (see config/packages/symiddleware.yaml). It validates the request in 5 steps:

1. Headers (i.e. allowed, not allowed and exact-match)
2. The request method (optional, e.g. when you implement an API Gateway service perhaps you decide or are required to accept only POST requests)
3. The remote address (optional, e.g. when your service accepts requests from specific resources such as websites, SPAs or other APIs)
4. Log errors, if any
5. If there are errors the API -by default, **redirects** a 404 **page** (i.e. an 302 http response)

> **Why redirecting to a 404 page?**
> 
> Because in this "layer" we don't yet care about the request body, only its headers and the requester ip address.
> If the remote address is not a match, it could probably mean that somebody is trying to access our API without authorization; and we don't want that, do we...?
>
> Therefore, we redirect the requester to a standard webpage hosted behind a different domain. I know this is not much of a security practice but at least it could cause some confusion. Either way, this behaviour could be easily changed in the `RequestMiddleware.php` file.


## The AuthMiddleware.php class

This class handles authentication and authentication.


## The InputMiddleware.php class


# Testing

## The Approach

I frequently use Behat for Behaviour Driven Development, this time I tried the `FriendsOfBehat/SymfonyExtension` package.
Unfortunately, it didn't work =(. When trying to run behat from the terminal, an `Environment variable not found: "CORS_ALLOW_ORIGIN"` error is thrown, even though such value is set in both the env.test and phpunit.xml.dist files.
Eventually I'll try to use Behat without any bundle, for now I'm using Application Tests as fallback.

> **Why using .feature files for testing is a good thing?**
> 
> Yes, defining tests in a code-based fashion makes us feel more geeks. But we don't work alone, do we?
> Implementing test cases could be the responsibility of the Dev or QA/QC teams (or both),
> while defining them could easily be the responsibility of Product Owners, management roles or even the C-Level (we commonly see this in tech-based startups).
> 
> As such, they'll be more accurate and productive using a natural language (i.e. English) rather than a programming language when defining the acceptance criteria for new features and improvements.
> This in turn lightens-up a little the work load from testers and/or developers; additionally, it helps to create a strong bond between technical and non-technical folks.

## Unit Tests
You can run tests using the bin/phpunit command:

```sh
php bin/phpunit
php bin/phpunit tests/Form
php bin/phpunit tests/Form/UserTypeTest.php
```


# Apache Virtual Host Setup

# PSR Standards

# The StandardOutput class


# The BaseController class

## The Standard Output Approach

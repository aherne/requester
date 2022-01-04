# Lucinda URL Requester

Table of contents:

- [About](#about)
- [Running Single Requests](#running-single-requests)
    - [File Uploading](#file-uploading)
    - [File Downloading](#file-downloading)
- [Running Multiple Asynchronous Requests](#running-multiple-asynchronous-requests)
- [Running Cookie Sharing Synchronous Requests](#running-cookie-sharing-synchronous-requests)
    - [Working With HTTP Cookies](#working-with-http-cookies)
- [Working With Responses](#working-with-responses)
- [Examples](#examples)
  - [HTTP GET Request](#http-get-request)
  - [HTTP POST Request](#http-post-request)
  - [HTTP PUT Request](#http-put-request)
  - [HTTP DELETE Request](#http-delete-request)
- [Error Handling](#error-handling)

## About

This API is a light weight cURL wrapper aimed at completely hiding chaotic native library underneath through a full featured OOP layer that is easy and logical to work with. It is built as an antithesis of Guzzle, the "industry standard" today, by being:

- **self-reliant**: unlike Guzzle, which has no less than 40 dependencies, it depends only on 1 library for unit testing
- **very simple**: each class is designed to cover an aspect of a URL request and response processing, but only a limited number are relevant for developers
- **very fast**: all code inside is developed on "less is more" paradigm: no over abstraction, no line of code more than strictly needed

Library is 99% unit tested (some areas of cURL functionality have zero documentation), fully PSR-4 compliant, only requiring PHP8.1+ interpreter and cURL extension. For installation you just need to write this in console:

```console
composer require lucinda/requester
```

Then use one of main classes provided:

- [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php): encapsulates a single HTTP/HTTPs request over cURL (covering curl_* functions)
    - [Lucinda\URL\FileUpload](https://github.com/aherne/requester/blob/v2.0/src/FileUpload.php): specializes [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php) for file upload
    - [Lucinda\URL\FileDownload](https://github.com/aherne/requester/blob/v2.0/src/FileDownload.php): specializes [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php) for file download
- [Lucinda\URL\MultiRequest](https://github.com/aherne/requester/blob/v2.0/src/MultiRequest.php): encapsulates simultaneous multi HTTP/HTTPs requests over cURL (covering curl_multi_* functions)
- [Lucinda\URL\SharedRequest](https://github.com/aherne/requester/blob/v2.0/src/SharedRequest.php): encapsulates multi HTTP/HTTPs requests able to share cookies/session over cURL (covering curl_share_* functions)

Each of above classes branches through its methods to deeper classes that become relevant depending on the complexity of request. The end result of every request is a [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) object, encapsulating all response information (headers, body, status).

## Running single requests

Performing a single HTTPs request is as simple as this:

```php
$request = new Lucinda\URL\Request("https://www.lucinda-framework.com");
$response = $request->execute();
```

This automatically sends target host embedded CAINFO certificate along with URL in order to establish a TLS connection then receives a [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) object. The class in charge of single requests is [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php), defining following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | ?string $url = null | void | Opens a cURL handle and optionally sets target URL |
| setURL | string $url | void | Manually sets request URL<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if url is invalid</small> |
| setMethod | [Lucinda\URL\Request\Method](https://github.com/aherne/requester/blob/v2.0/src/Request/Method.php) $method | void | Sets request method as one of [Lucinda\URL\Request\Method](https://github.com/aherne/requester/blob/v2.0/src/Request/Method.php) enum values otherwise assumes GET!<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if request method is invalid</small> |
| setParameters | array $parameters = [] | [Lucinda\URL\Request\Parameters](https://github.com/aherne/requester/blob/v2.0/src/Request/Parameters.php) | Sets POST request parameters directly (by key and value) or delegates to specialized class. |
| setRaw | string $body | void | Sets binary request parameters, available if request method is POST / GET / DELETE!<br/> |
| setHeaders | void | [Lucinda\URL\Request\Headers](https://github.com/aherne/requester/blob/v2.0/src/Request/Headers.php) | Sets request headers by delegating to specialized class |
| setSSL | string $certificateAuthorityBundlePath | [Lucinda\URL\Request\SSL](https://github.com/aherne/requester/blob/v2.0/src/Request/SSL.php) | Sets custom certificate authority bundle to use in SSL requests and delegates to specialized class.<br/><small>If not used, API will employ embedded **cacert.pem** file downloaded from [official site](https://curl.haxx.se/ca/cacert.pem)</small>! |
| setCustomOption | int $curlopt,<br/>mixed $value | void | Sets a custom CURLOPT_* request option not covered by API already.<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if option is already covered.</small> |
| execute | bool $returnTransfer = true,<br/>int $maxRedirectionsAllowed = 0,<br/>int $timeout = 300000 | [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) | Executes request and produces a response.<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if invalid request options combination was found.<br/>Throws [Lucinda\URL\ResponseException](https://github.com/aherne/requester/blob/v2.0/src/ResponseException.php) if response retrieval failed (eg: response exceeded timeout).</small> |

### File Uploading

API comes with [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php) extensions specifically designed for file upload. To upload a file you must use [Lucinda\URL\FileUpload](https://github.com/aherne/requester/blob/v2.0/src/FileUpload.php), which comes with following additional public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __destruct | void | void | Closes file handles created by setFile method below |
| setMethod | [Lucinda\URL\Request\Method](https://github.com/aherne/requester/blob/v2.0/src/Request/Method.php) $method | void | Specializes parent method to only allow POST and PUT requests |
| setFile | string $path | void | Sets absolute location of file to upload, available if request method is PUT!<br/><small>Throws [Lucinda\URL\FileNotFoundException](https://github.com/aherne/requester/blob/v2.0/src/FileNotFoundException.php) if file isn't found.</small> |
| setRaw | string $body | void | Sets binary body of file to upload, available if request method is POST!<br/> |
| setProgressHandler | [Lucinda\URL\Request\Progress](https://github.com/aherne/requester/blob/v2.0/src/Request/Progress.php) $progressHandler | void | Sets handle to use in tracking upload progress. |
| execute | bool $returnTransfer = true,<br/>int $maxRedirectionsAllowed = 0,<br/>int $timeout = 300000 | [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) | Uploads file, updating response status accordingly. |

Example (uploads LOCAL_FILE_PATH to REMOTE_FILE_PATH):

```php
$request = new Lucinda\URL\FileUpload(REMOTE_FILE_PATH);
$request->setMethod(Lucinda\URL\Request\Method::PUT);
$request->setFile(LOCAL_FILE_PATH);
$request->execute();
```
### File Downloading

API comes with [Lucinda\URL\Request](https://github.com/aherne/requester/blob/v2.0/src/Request.php) extensions specifically designed for file download. To download a file you must use [Lucinda\URL\FileDownload](https://github.com/aherne/requester/blob/v2.0/src/FileDownload.php), which comes with following additional public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| setMethod | [Lucinda\URL\Request\Method](https://github.com/aherne/requester/blob/v2.0/src/Request/Method.php) $method | void | Specializes parent method to only allow GET requests |
| setFile | string $path | void | Sets absolute location where file will be downloaded (incl. file name and extension)!<br/><small>Using this method is **mandatory**!</small> |
| setProgressHandler | [Lucinda\URL\Request\Progress](https://github.com/aherne/requester/blob/v2.0/src/Request/Progress.php) $progressHandler | void | Sets handle to use in tracking download progress. |
| execute | bool $returnTransfer = true,<br/>int $maxRedirectionsAllowed = 0,<br/>int $timeout = 300000 | [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) | Downloads file, updating response status accordingly. |

Example (downloads REMOTE_FILE_PATH into LOCAL_FILE_PATH):

```php
$request = new Lucinda\URL\FileDownload(REMOTE_FILE_PATH);
$request->setMethod(Lucinda\URL\Request\Method::GET);
$request->setFile(LOCAL_FILE_PATH);
$request->execute();
```

## Running multiple asynchronous requests

Performing multiple requests at once it is no less simple:

```php
$multiRequest = new Lucinda\URL\MultiRequest(Lucinda\URL\Request\Pipelining::HTTP2);
$multiRequest->add(new Lucinda\URL\Request("https://www.lucinda-framework.com/news"));
$multiRequest->add(new Lucinda\URL\Request("https://www.lucinda-framework.com/tutorials"));
$responses = $multiRequest->execute();
```

This executes two requests simultaneously using HTTP2 pipelining and receives a Response object array. The class in charge of single requests is [Lucinda\URL\MultiRequest](https://github.com/aherne/requester/blob/v2.0/src/MultiRequest.php), defining following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | [Lucinda\URL\Request\Pipelining](https://github.com/aherne/requester/blob/v2.0/src/Request/Pipelining.php) $pipeliningOption | void | Initiates a multi URL connection based on one of enum values |
| add | Request $request | void | Adds request to execution pool. |
| setCustomOption | int $curlMultiOpt,<br/>mixed $value | void | Sets a custom CURLMOPT_* request option not covered by API already.<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if option is already covered |
| execute | bool $returnTransfer = true,<br/>int $maxRedirectionsAllowed = 0,<br/>int $timeout = 300000 | [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php)[] | Validates requests in pool then executes them asynchronously in order to produce responses.<br/><small>Throws [Lucinda\URL\RequestException](https://github.com/aherne/requester/blob/v2.0/src/RequestException.php) if invalid request options combination was found.<br/>Throws [Lucinda\URL\ResponseException](https://github.com/aherne/requester/blob/v2.0/src/ResponseException.php) if response retrieval failed (eg: response exceeded timeout).</small> |

Unlike native curl_multi, responses will be received in the order requests were pooled! Execution will be performed depending on pipelining option (see constructor):

- *DISABLED*: connections are not pipelined
- *HTTP1*: attempts to pipeline HTTP/1.1 requests on connections that are already established
- *HTTP2*: attempts to multiplex the new transfer over an existing connection if HTTP/2
- *HTTP1_HTTP2*: attempts pipelining and multiplexing independently of each other

## Running cookie sharing synchronous requests

To make multiple requests share cookies/dns, it is as simple as:

```php
$sharedRequest = new Lucinda\URL\SharedRequest(Lucinda\URL\Request\ShareType::COOKIE);
$request1 = new Lucinda\URL\Request("http://www.example.com/page1");
$multiRequest->add($request1);
$request2 = new Lucinda\URL\Request("http://www.example.com/page1");
$multiRequest->add($request2);
$response1 = $request1->execute();
$response2 = $request2->execute();
```

This very poorly documented feature makes 2nd request able to see cookies in first request. The class in charge of cookie-sharing requests is [Lucinda\URL\SharedRequest](https://github.com/aherne/requester/blob/v2.0/src/SharedRequest.php), defining following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | [Lucinda\URL\Request\ShareType](https://github.com/aherne/requester/blob/v2.0/src/Request/ShareType.php) $shareOption | void | Initiates a shared URL connection based on one of enum values |
| add | Request $request | void | Adds request to share pool. |

Unlike the other two classes of running requests, each request must be executed manually in order to produce a response! Cookie sharing will be performed depending on share type option (see constructor):

- *COOKIES*: connections will share HTTP cookies
- *DNS*: connections will share cached DNS hosts
- *SSL_SESSION*: connections will share SSL session IDs

### Working with HTTP cookies

API comes with a number of classes for working with cookies, whose area is IN BETWEEN requests and responses:

- [Lucinda\URL\Cookies](https://github.com/aherne/requester/blob/v2.0/src/Cookies.php): implements general cookies handling operations (based on cURL standards)
- [Lucinda\URL\Cookies\Cookie](https://github.com/aherne/requester/blob/v2.0/src/Cookies/Cookie.php): implements logic of individual cookie (based on HTTP set-cookie header standards)
- [Lucinda\URL\Cookies\CookieParser](https://github.com/aherne/requester/blob/v2.0/src/Cookies/CookieParser.php): interface defining blueprints for encapsulating/decapsulating cookies into/from
    - *headers*: via [Lucinda\URL\Cookies\CookieHeader](https://github.com/aherne/requester/blob/v2.0/src/Cookies/CookieHeader.php)
    - *files*: via [Lucinda\URL\Cookies\CookieFile](https://github.com/aherne/requester/blob/v2.0/src/Cookies/CookieFile.php)

They are rarely needed in everyday usage, so for more info click on their links to see documentation. To see how they can be used, please check respective unit tests!

## Working with responses

The end result of every successful request (defined as one that received ANY response) is encapsulated into a [Lucinda\URL\Response](https://github.com/aherne/requester/blob/v2.0/src/Response.php) object that includes:

- *response status*: HTTP status that came with response
- *response headers*: HTTP headers received along with response
- *response body*: actual contents of response (minus headers and status)
- *response statistics*: as supplied by API or cURL driver underneath (eg: total duration)

All this information can be queried via following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getDuration | void | int | Gets total response duration in milliseconds |
| getStatusCode | void | int | Gets response HTTP status code |
| getURL | void | string | Gets url requested |
| getBody | void | string | Gets response body |
| getHeaders | void | string[string] | Gets response headers by name and value |
| getCustomOption | int $curlinfo | mixed | Gets value of a custom CURLINFO_* response option not covered by API already.<br/><small>Throws [Lucinda\URL\ResponseException](https://github.com/aherne/requester/blob/v2.0/src/ResponseException.php) if option is already covered</small> |

## Examples

### HTTP GET Request

```php
# any request that doesn't *setMethod* is considered GET by default
$request = new Lucinda\URL\Request("https://www.example.com/?id=1");
$response = $request->execute();
```

### HTTP POST Request

```php
# any POST request MUST *setParameters*
$request = new Lucinda\URL\Request("https://www.example.com/add");
$request->setMethod(\Lucinda\URL\Request\Method::POST);
$request->setParameters(["asd"=>"fgh", "qwe"=>"rty"]);
$response = $request->execute();
```

### HTTP PUT Request

```php
# any PUT request MUST *setRaw* AND stringify parameters
$request = new Lucinda\URL\Request("https://www.example.com/edit");
$request->setMethod(\Lucinda\URL\Request\Method::PUT);
$request->setRaw(http_build_query(["id"=>1, "qwe"=>"tyu"]));
$response = $request->execute();
```

### HTTP DELETE Request

```php
# any DELETE request MUST *setRaw* AND stringify parameters
$request = new Lucinda\URL\Request("https://www.example.com/delete");
$request->setMethod(\Lucinda\URL\Request\Method::DELETE);
$request->setRaw(http_build_query(["id"=>1]));
$response = $request->execute();
```

## Error handling

Following exceptions may be thrown during request-response process by this API:

- [Lucinda\URL\Request\Exception](https://github.com/aherne/requester/blob/v2.0/src/Request/Exception.php): if an error has occurred in processing request (request is invalid) BEFORE request being sent. Thrown on logical errors defined by this API.
- [Lucinda\URL\Response\Exception](https://github.com/aherne/requester/blob/v2.0/src/Response/Exception.php): if an error has occurred in receiving response (eg: target host is not responding) AFTER request was sent (covering curl_*err* and curl_multi_*err* functions)
- [Lucinda\URL\FileNotFoundException](https://github.com/aherne/requester/blob/v2.0/src/FileNotFoundException.php): if request referenced a local file that doesn't exist.

A few observations:

- **No support for curl_share_*err* errors**, for whom there is zero documentation in both PHP cURL documentation as well as the C library it wraps
- **As long as response is received for a request, no exception is thrown!** API does not consider statuses such as 404 or 500 to be errors by default, so it is up to developers to decide how to handle them

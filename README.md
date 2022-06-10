Behatch contexts
================

Behat Open Api contexts provide most common Api Behat tests.

Installation
------------

This extension requires:

* Behat 3+
* Mink extension

### Project dependency

1. [Install Composer](https://getcomposer.org/download/)
2. Require the package with Composer:

```
$ composer require --dev twentytwo-labs/behat-open-api
```

3. Activate extension by specifying its class in your `behat.yml`:

```yaml
# behat.yml
default:
    # ...
    extensions:
       TwentytwoLabs\BehatOpenApiExtension:
          schemaFile: "file:///path/to/your/schema" #optinal
```

Usage
-----

In `behat.yml`, enable desired contexts:

```yaml
default:
    suites:
        default:
            contexts:
                - TwentytwoLabs\BehatOpenApiExtension\Context\DebugContext: ~
                - TwentytwoLabs\BehatOpenApiExtension\Context\RestContext: ~
                - TwentytwoLabs\BehatOpenApiExtension\Context\JsonContext: ~
                - TwentytwoLabs\BehatOpenApiExtension\Context\OpenApiContext: ~
```

### Examples
- ``TwentytwoLabs\BehatOpenApiExtension\Context\DebugContext``
  - `@Then print last response headers`
  - `@Then print profiler link`


- ``TwentytwoLabs\BehatOpenApiExtension\Context\JsonContext``
    - `@Then the response should be in JSON`
    - `@Then the response should not be in JSON`
    - `@Then the JSON node :node should be equal to :text`
    - `@Then the JSON nodes should be equal to:`
    - `@Then the JSON node :node should match :pattern`
    - `@Then the JSON node :node should be null`
    - `@Then the JSON node :node should not be null`
    - `@Then the JSON node :node should be true`
    - `@Then the JSON node :node should be false`
    - `@Then the JSON node :node should be equal to the string :text`
    - `@Then the JSON node :node should be equal to the number :number`
    - `@Then the JSON node :node should have :count element(s)`
    - `@Then the JSON node :node should contain :text`
    - `@Then the JSON node :node should not contain :text`
    - ```
      @Then the JSON nodes should contain:
        | ... |
      ```
    - ```
        @Then the JSON nodes should not contain:
          | ... |
        ```
    - `@Then the JSON node :name should exist`
    - `@Then the JSON node :name should not exist`
    - `@Then the JSON should be equal to:`
    - ```
        @Then I should see JSON with key :
          | ... |
      ```
    - ```
        @Then the JSON should be match to:
          | ... |
      ```
      This step link to [Array-comparator](https://github.com/TwentytwoLabs/array-comparator package)
- `TwentytwoLabs\BehatOpenApiExtension\Context\OpenApiContext`
  - `@Then the response should be valid according to the operation id :operationId`
- `TwentytwoLabs\BehatOpenApiExtension\Context\RestContext`
  - `@Then I add :name header equal to :value`
  - `@Given I send a :method request to :path`
  - ```
        @Given I send a :method request to :path with parameters:
          | key | value |
          | ... | ..... |
      ```
  - ```
     @Given I send a :method request to :path with body:
     """
     {
      #...
     }
     """
    ```
  - `@Then /^the response status code should be equal to (?P<code>\d+)$/`
  - ```
     @Then the response should be equal to:
     """
      {
        # ...
      }
     """
    ```
  - `@Then the response should be empty`
  - `@Then the header :name should be equal to :value`
  - `@Then the header :name should not be equal to :value`
  - `@Then the header :name should contain :value`
  - `@Then the header :name should not contain :value`
  - `@Then the header :name should not exist`
  - `@Then the header :name should match :regex`
  - `@Then the header :name should not match :regex`
  - `@Then the response should expire in the future`
  - `@Then the response should be encoded in :encoding`

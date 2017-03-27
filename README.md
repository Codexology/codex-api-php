# codex-api-php
Codex API PHP Library

# installation 

Require the CodexAPI Client through 

```
require '../src/CodexApiClient.php';
```

A composer package is coming soon

Configure the API Client with credentials created in the API manager in your Codex.

```
$email = "INFO@DOMAIN.COM";
$password = "PASSWORD";

$client = new CodexApiClient("triton",$email,$password);
```



# usage


Getting items from the API is easy from that point


```
$members = $client->mambo();
```

A full list of endpoints is coming to these docs soon.
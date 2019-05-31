MediaWiki Special:RunJobs Signature Generator
=============================================

MediaWiki has a "special page" called RunJobs. That's in quotes because it's not really a special page. It's really a web service you have to hit over HTTP POST that will run some jobs. However, you have to be authorized in a pretty esoteric way in order to get it to work.

Using Special:RunJobs
---------------------

In order to make Special:RunJobs actually run jobs, you have to hit the page with HTTP POST. Typical web requests use GET. One way to do a POST request is to use your browser's developer tools (probably ctrl-shift-J to open them) then paste something like this and hit the `ENTER` key:

```javascript
$.post(
	"https://example.com/demo/index.php/Special:RunJobs",
	{
		"tasks"     : "dummy",
		"signature" : "<this is a super secret value I haven't explained yet>",
		"sigexpiry" : 1559347681,
		"type"      : "replaceText",
		"maxjobs"   : 200,
		"maxtime"   : 55
	},
	function ( response ) {
		console.log(response);
	}
);
```

If you actually attempted to run that, you were probably disappointed. You don't own `example.com` and your `signature` field needs to be set properly (I'll explain that below). In this example I ran jobs of type `replaceText` with some maximums on job numbers and time.

Getting a valid signature
-------------------------

If getting a valid signature was easy there would be no reason for this repository. In order to generate a signature, you're first going to have to know the `$wgSecretKey` of the wiki you want to hit (`example.com/demo` above). Unless you own this server or are highly trusted by the owner you probably will not be given this information. Just quit now. If you do know it, or can get it, continue reading.

This repository will help generate a signature _for each unique request_. That means if you change anything about the request you need to generate a new signature. New job type? Get a new signature. Run more jobs a time? Get a new signature.

### Setup config.php

First, copy `config.example.php` to `config.php` and replace the values with ones that work for you. Here's where you'll need `$wgSecretKey`. You also need a local copy of the MediaWiki source code. It doesn't have to be serving pages anywhere...it just needs to be somewhere reachable on your computer. Preferably it should be close to the version running on the server you want to hit. You'll put the path to the MediaWiki code into `config.php`, too.

### Run sig-gen.php

Now run `php sig-gen.php replaceText 1 30` and you'll get output like:

```
Format your POST data like:
{
	"tasks"     : "dummy",
	"signature" : "7590856b64d65fadc2bd1a903c8cb9ca8b5d5ac4",
	"sigexpiry" : 1559349050,
	"type"      : "replaceText",
	"maxjobs"   : 1,
	"maxtime"   : 30
}
```

### Sending POST with your new signature

Now just do the POST command from the top of this file, but replace the data with the output of `sig-gen.php`.

```javascript
$.post(
	"https://example.com/demo/index.php/Special:RunJobs",
	{ "PUT YOUR DATA HERE" },
	function ( response ) {
		console.log(response);
		if ( response.status === 202 ) {
			// FIXME, abort ajax request if 202 given
		}
	}
);
```

# SquirrelMail JMAP
The JMAP Plugin for SquirrelMail provides [JMAP](https://jmap.io/) support for SquirrelMail-based systems by exposing a RESTful API Endpoint which speaks the JMAP Protocol.

Please note that this version is still in its early stages.

The following data types are currently supported by the JMAP Plugin for SquirrelMail:

* Contacts over the JMAP for Contacts protocol
* Calendars over the JMAP for Calendars protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format
* Tasks over the JMAP for Tasks protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format
* Files over the upcoming JMAP for Files protocol

## Installation
1. ☁ Clone this plugin into the `plugins` folder of your SquirrelMail: `git clone https://github.com/audriga/jmap-squirrelmail jmap` (Make sure the folder is named `jmap`). Then `cd jmap`.
1. ✅ In the folder of the plugin, edit the config file `conf/config.php.sample` and store it under `conf/config.php`
1. Run `make` to initialize the project for the default PHP version (8.1). Use other build targets (e.g. `make php56_mode` or `make php70_mode`) instead, in case you are using a different version.
1. 🎉 Partytime! Help fix [some issues](https://github.com/audriga/jmap-squirrelmail/issues) and [send us some pull requests](https://github.com/audriga/jmap-squirrelmail/pulls) 👍

## Usage
Set up your favorite client to talk to SquirrelMail's JMAP API.

## Development
### Installation
1. Run `make update` after `make`

### Tests
Run `make fulltest` to run linting and unit tests.

For debugging purposes it makes sense to throw some cURL calls at the API. For example, this is how you tell the JMAP API to return all CalendarEvents:
```
curl <squirrelmail-address>/plugins/jmap/jmap.php -u <username>:<password> -d '{"using":["urn:ietf:params:jmap:calendars"],"methodCalls":[["CalendarEvent/get", {"accountId":"<username>"}, "0"]]}'
```

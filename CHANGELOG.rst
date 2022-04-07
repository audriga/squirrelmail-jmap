======================================
SquirrelMail JMAP Plugin Release Notes
======================================

.. contents:: Topics

v0.12.6
=======

Release Summary
---------------
Yet another hotfix release for #5735 (tough Nut to crack)

v0.12.5
=======

Release Summary
---------------
Hotfix release for #5735 (tough Nut to crack)

Details
-------
* Add debug log for decoding task description
* Define custom encoding callback function
* Fix sanitizing of Address object

v0.12.4
=======

Release Summary
---------------
Hotfix release

Details
-------
* Get configured encoding for account #5735
* Convert HTML newline character #5735

v0.12.2
=======

Release Summary
---------------
Hotfix release

SQMail
------
* Calendars: Handle all escape chars #5716

v0.12.1
=======

Release Summary
---------------
More logging and small improvements for CalendarEvent and ContactGroups

SQMail
------
* Contacts: Handle more edge cases for ContactGroups #5314
* Calendars: Handle two edge cases for CalendarEvents #5697

v0.12.0
=======

Release Summary
---------------
Support ContactGroups

SQMail
------
* Contacts: Add ID property to Contacts
* Contacts: Support ContactGroups

v0.11.3
=======

Release Summary
---------------
Minor fix and simpler build process

SQMail
------
* Tasks: Do not return empty Task for empty accounts #5594

v0.11.0
=======

Release Summary
---------------
Various fixes and logging improvements

SQMail
------
* Contact: Support displayname #5376
* File: Read hidden files #5203

v0.10.0
=======

Release Summary
---------------
Adds logging and various fixes

SQMail
------
* Support Graylog and file logger #5439
* Add fix for SQMail task due date format #5464
* Return less empty values #5460
* Fix admin-auth for files and calendars #5470

v0.9.0
======

Release Summary
---------------
Adds Calendar folder and fixes several calendar issues

SQMail
------
* Calendar: Add initial folder version #5308
* Contact: Support gender and spouse #5376

v0.8.0
======

Release Summary
---------------
Support different auth

SQMail
------
* Support customer CGI auth #5317

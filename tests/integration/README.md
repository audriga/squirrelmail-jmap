# Integration tests

This folder contains two things:

* **PHPUnit tests**: Some PHPUnit 5 tests that can be places inside the folder
  of the Nutsmail source code, so we can test part of its functionality without
  having an actual instance
* **Install Plugin**: An script that places the SQMail JMAP Plugin inside some
  local directory (outdated and broken)

## PHPUnit tests

### Requirements

* Nutsmail source code

### Usage

Downgrade to a version of PHPUnit that is compabible with PHP 5.6 and pull dev
dependencies by executing for the root plugin folder:

```
$ make update_for_promail_integration_tests
```

Place the JMAP plugin inside nutsmail's `plugins/` folder:

```
$ cp -r . /path/to/nutsmail/source/plugins/jmap/
```

Run the PHPUnit tests with PHP 5.6 from the `plugins/` folder:

```
$ cd /path/to/nutsmail/source/plugins/
$ podman run --rm --name php56 -v "$PWD":"$PWD" -w "$PWD"\
  docker.io/phpdockerio/php56-cli sh -c\
  "jmap/vendor/bin/phpunit jmap/tests/integration/"
```

## Install Plugin

### Requirements

* Install ansible and podman

### Usage

**JMAP Plugin installation**

* Start squirrelmail via `ansible-playbook install_plugin.yml --connection local` (WIP most is still written in `setup.sh`)


* You should then be able to run the phpunit tests under `phpunit/`.

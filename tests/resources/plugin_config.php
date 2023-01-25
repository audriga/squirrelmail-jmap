<?php

namespace Squirrel;

class Config
{
    // ********************** //
    // SQMail-specific configuration
    // ********************** //
    // Admin users for Webclient admin auth. Users should exist on the webclient
    public static $adminUsers = ['wp13405851-test'];

    // AjaXplorer file root (absolute path)
    public static $filesRoot = '../../data/file_share/data/personal/';

    // ********************** //
    /// Logging configuration
    // ********************** //
    // Logging will be chosen automatically depending on what PSR-3 logger is included.
    // Only a single logger will be used. However, you can force disallow certain loggers manually.

    // Allow FileLogger (also as fallback in case no other is working)
    public static $allowFileLog = true;

    // Allow Graylog logger (in case included)
    public static $allowGraylog = true;

    // PSR 3 log level to use
    public static $logLevel = \Psr\Log\LogLevel::DEBUG;

    /// File-logger configuration
    // Path to log file
    public static $logPath = __DIR__ . '/../log.log';

    /// Graylog configuration
    // Graylog endpoint to use
    public static $graylogEndpoint = 'logging.operations.audriga.com';

    // Graylog Port to use
    public static $graylogPort = 12201;

    // Allow self-signed certs
    public static $graylogAllowSelfSigned = false;

    // Use TLS
    public static $graylogUseTls = false;
}

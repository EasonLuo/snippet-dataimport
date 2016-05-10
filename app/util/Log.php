<?php

/**
 * This is used for error logging (never used)
 * @var unknown
 */
define ( "LOG4PHP_DIR", "lib/log4php" );
require_once LOG4PHP_DIR . '/Logger.php';
Logger::configure ( array (
		'appenders' => array (
				'default' => array (
						'class' => 'LoggerAppenderDailyFile',
						'layout' => array (
								'class' => 'LoggerLayoutPattern',
								'params' => array (
										'conversionPattern' => '%date %logger %-5level %msg%n' 
								) 
						),
						'params' => array (
								'datePattern' => 'd-m-YY',
								'file' => '/log/fingerprint_%s.log' 
						) 
				) 
		),
		'rootLogger' => array (
				'appenders' => array (
						'default' 
				) 
		) 
) );


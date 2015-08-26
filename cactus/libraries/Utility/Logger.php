<?php

namespace Utility;

require_once __ROOT__ . '/vendors/log4php/src/main/php/Logger.php';

class Logger extends \Logger {
    const LOGGER_WORKER = 'worker';
    const LOGGER_QUEUE = 'queue';
    const LOGGER_SEARCH = 'search';
    const LOGGER_EMAIL = 'email';
    const LOGGER_SMS = 'sms';
    const LOGGER_PUSH = 'push';

	public static function autoload() {
		parent::configure([
			'appenders' => [
				'ConsoleAppender' => [
					'class' => 'LoggerAppenderConsole',
					'layout' => [
						'class' => 'LoggerLayoutSimple',
					],
				],
				'FileAppender' => [
					'class' => 'LoggerAppenderDailyFile',
					'layout' => [
						'class' => 'LoggerLayoutSimple'
					],
					'params' => [
						'file' => __LOGP__ . '/log-%s',
						'append' => true,
					],
				],
                'SearchFileAppender' => [
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => [
                        'class' => 'LoggerLayoutSimple'
                    ],
                    'params' => [
                        'file' => __LOGP__ . '/log-search-%s',
                        'append' => true,
                    ],
                ],
				'WorkerFileAppender' => [
					'class' => 'LoggerAppenderDailyFile',
					'layout' => [
						'class' => 'LoggerLayoutSimple'
					],
					'params' => [
						'file' => __LOGP__ . '/log-worker-%s',
						'append' => true,
					],
				],
                'QueueFileAppender' => [
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => [
                        'class' => 'LoggerLayoutSimple'
                    ],
                    'params' => [
                        'file' => __LOGP__ . '/log-queue-%s',
                        'append' => true,
                    ],
                ],
                'EmailFileAppender' => [
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => [
                        'class' => 'LoggerLayoutSimple'
                    ],
                    'params' => [
                        'file' => __LOGP__ . '/log-email-%s',
                        'append' => true,
                    ],
                ],
                'SmsFileAppender' => [
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => [
                        'class' => 'LoggerLayoutSimple'
                    ],
                    'params' => [
                        'file' => __LOGP__ . '/log-sms-%s',
                        'append' => true,
                    ],
                ],
                'PushFileAppender' => [
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => [
                        'class' => 'LoggerLayoutSimple'
                    ],
                    'params' => [
                        'file' => __LOGP__ . '/log-push-%s',
                        'append' => true,
                    ],
                ],
			],
			'rootLogger' => [
				'appenders' => ['ConsoleAppender'],
			],
			'loggers' => [
                'search' => [
                    'appenders' => ['SearchFileAppender'],
                ],
				'worker' => [
					'appenders' => ['WorkerFileAppender'],
				],
                'queue' => [
                    'appenders' => ['QueueFileAppender'],
                ],
                'email' => [
                    'appenders' => ['EmailFileAppender'],
                ],
                'sms' => [
                    'appenders' => ['SmsFileAppender'],
                ],
                'push' => [
                    'appenders' => ['PushFileAppender'],
                ],
			],
		]);
	}
}
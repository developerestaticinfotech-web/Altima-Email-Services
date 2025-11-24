<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RabbitMQ Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for RabbitMQ connection settings
    | used by the email microservice for queue operations.
    |
    */

    'host' => env('RABBITMQ_HOST', 'localhost'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),

    /*
    |--------------------------------------------------------------------------
    | Queue Names
    |--------------------------------------------------------------------------
    |
    | Define the queue names used by the email microservice.
    |
    */

    'queues' => [
        'email_send' => env('RABBITMQ_EMAIL_SEND_QUEUE', 'email.send'),
        'email_sync_user' => env('RABBITMQ_EMAIL_SYNC_USER_QUEUE', 'email.sync.user'),
        'email_inbound' => env('RABBITMQ_EMAIL_INBOUND_QUEUE', 'email.inbound'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Additional connection settings for RabbitMQ.
    |
    */

    'connection' => [
        'heartbeat' => env('RABBITMQ_HEARTBEAT', 60),
        'read_write_timeout' => env('RABBITMQ_READ_WRITE_TIMEOUT', 60),
        'connection_timeout' => env('RABBITMQ_CONNECTION_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Consumer Settings
    |--------------------------------------------------------------------------
    |
    | Settings for message consumers.
    |
    */

    'consumer' => [
        'prefetch_count' => env('RABBITMQ_PREFETCH_COUNT', 1),
        'no_ack' => env('RABBITMQ_NO_ACK', false),
        'exclusive' => env('RABBITMQ_EXCLUSIVE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Publisher Settings
    |--------------------------------------------------------------------------
    |
    | Settings for message publishers.
    |
    */

    'publisher' => [
        'mandatory' => env('RABBITMQ_MANDATORY', false),
        'immediate' => env('RABBITMQ_IMMEDIATE', false),
        'ticket' => env('RABBITMQ_TICKET', null),
    ],
]; 
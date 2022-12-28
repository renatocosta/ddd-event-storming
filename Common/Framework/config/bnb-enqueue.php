<?php
return [
    "consumers_bootstrap" => base_path("routes/events.php"),
    "default_connection" => "default",
    "connections" => [
        "default" => [
            "driver" => 'rdkafka',
            "config" => [
                'global' => [
                    'metadata.broker.list' => env('KAFKA_BROKER_SERVERS'),
                    'security.protocol' => 'SASL_PLAINTEXT',
                    'sasl.mechanisms' => 'SCRAM-SHA-512',
                    'sasl.username' => env('KAFKA_SASL_USERNAME'),
                    'sasl.password' => env('KAFKA_SASL_PASSWORD'),
                    'group.id' => env('KAFKA_GROUP_ID_DEFAULT'),
                ],
                'topic' => [],
            ]
        ],
    ]
];

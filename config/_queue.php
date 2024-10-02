<?php

return [
    'class' => \yii\queue\db\Queue::class,
    'serializer' => \yii\queue\serializers\JsonSerializer::class,
    'mutex' => \yii\mutex\PgsqlMutex::class
];

<?php

use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Facades\Schema as SchemaFacade;

require dirname(__DIR__).'/vendor/autoload.php';

if (!class_exists('DB')) {
    class_alias(DBFacade::class, 'DB');
}

if (!class_exists('Schema')) {
    class_alias(SchemaFacade::class, 'Schema');
}

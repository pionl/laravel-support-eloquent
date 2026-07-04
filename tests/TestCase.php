<?php

namespace Pion\Support\Eloquent\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Capsule $capsule;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        Container::setInstance($container);
        Facade::setFacadeApplication($container);

        $this->capsule = new Capsule($container);
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $container->instance('db', $this->capsule->getDatabaseManager());
        $container->bind('db.schema', fn () => $this->capsule->getConnection()->getSchemaBuilder());

        $this->migrateSchema();
        $this->seedData();
    }

    private function migrateSchema(): void
    {
        $schema = $this->capsule->schema();

        $schema->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        $schema->create('profiles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('active')->default(true);
            $table->string('nickname');
        });

        $schema->create('posts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('status');
            $table->string('title');
        });
    }

    private function seedData(): void
    {
        TestUser::query()->insert([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);

        TestProfile::query()->insert([
            ['id' => 10, 'user_id' => 1, 'active' => 1, 'nickname' => 'alpha'],
            ['id' => 20, 'user_id' => 2, 'active' => 0, 'nickname' => 'beta'],
        ]);

        TestPost::query()->insert([
            ['id' => 100, 'user_id' => 1, 'status' => 'published', 'title' => 'First'],
            ['id' => 101, 'user_id' => 1, 'status' => 'draft', 'title' => 'Second'],
            ['id' => 102, 'user_id' => 1, 'status' => 'published', 'title' => 'Third'],
            ['id' => 103, 'user_id' => 2, 'status' => 'published', 'title' => 'Fourth'],
        ]);
    }
}

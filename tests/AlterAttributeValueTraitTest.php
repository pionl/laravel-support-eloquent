<?php

namespace Pion\Support\Eloquent\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Pion\Support\Eloquent\Traits\AlterAttributeValueTrait;

class AlterAttributeValueTraitTest extends TestCase
{
    public function testItAppliesConfiguredAttributeTransformations(): void
    {
        $model = new AttributeMutationModel();

        $model->setAttribute('name', '<b>Alice</b>');
        $model->setAttribute('description', '   ');
        $model->setAttribute('price', '13,3');
        $model->setAttribute('published_at', '2026-03-17');

        $this->assertSame('Alice', $model->getAttributes()['name']);
        $this->assertNull($model->getAttributes()['description']);
        $this->assertSame(13.3, $model->getAttributes()['price']);
        $this->assertSame('2026-03-17', $model->getAttributes()['published_at']);
        $this->assertInstanceOf(Carbon::class, $model->getAttribute('published_at'));
        $this->assertSame('2026-03-17', $model->getAttribute('published_at')->format('Y-m-d'));
    }
}

class AttributeMutationModel extends Model
{
    use AlterAttributeValueTrait;

    protected $guarded = [];

    protected $cleanAttributes = ['name'];

    protected $nullEmptyAttributes = ['description'];

    protected $normalizeFloatAttributes = ['price'];

    protected $dateAttributes = ['published_at'];

    protected $dateFormats = [
        'published_at' => 'Y-m-d',
    ];
}

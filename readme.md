# Laravel's Eloquent support package
Contains a set of traits for the eloquent model. In future can contain more set of classes/traits for the eloquent database.

## Installation

    composer require pion/laravel-support-eloquent


## Attribute value altering traits

Set of traits that will change the attribute value.

### CleanHTMLFromAttributeTrait

Enables automatic attribute value cleaning from HTML for all attributes, by limiting only desired by `$cleanAttributes` 
property or by limiting which attributes can have html `$dontCleanAttributes`. You can use the `$stripHtmlTags` property to 
specify tags which should not be stripped.

For manual usage use `CleanHTMLTrait` with `tryToCleanAttributeValue($key, $value)` method.

#### Null only given attributes:

```php
public $cleanAttributes = [
    "name"
];
```
 
#### Don't null provided attributes:

```php
public $dontCleanAttributes = [
    "name"
];
```

### NullEmptyStringAttributeTrait

Enables the automatic nulling of empty string value (like from post or set). You can provide
list of columns keys to allow only specified columns (use `$nullEmptyAttributes`) to be set to null or you can provide a
list of columns keys to ignore while trying to null the value (use `$dontNullEmptyAttributes`). They can be set in construct
or as property.

For manual usage use `NullEmptyStringTrait` with `tryToNullAttributeValue($key, $value)` method.

#### Null only given attributes:

```php
public $nullEmptyAttributes = [
    "name"
];
```
 
#### Don't null provided attributes:

```php
public $dontNullEmptyAttributes = [
    "name"
];
```

### NormalizeFloatAttributeTrait

Converts float string to float value with comma support (floatval fails to convert 13,3 to 13.3) by setting list of attributes via `$normalizeFloatAttributes`.

```
public $normalizeFloatAttributes = [
    'price',
    'discount',
];
```

For manual usage use `NormalizeFloatTrait ` with `tryToNormalizeFloatAttributeValue($key, $value)` method.

### DateAttributeValueTrait

Converts allowed attributes (by settings `$dateAttributes`) to carbon instance without any format limitation. Tries to parse any format.

```php
public $dateAttributes = ['custom_date'];
```

For manual usage use `DateAttributeTrait` with `tryToConvertAttributeValueToDate($key, $value)` method.

All values are converted via `toArray` method to default format. You can customize date formats by attribute by setting `dateFormats`:

```php
public $dateFormats = [
    'born' => 'Y-m-d'
];
```

### Running multiple trait functions

#### Using all attributes traits

To apply all traits that are currently implemented use `AlterAttributeValueTrait`.

#### Manual

Unfortunately traits can't override same method (in this case `setAttribute`). For this purpose, you must override the `setAttribute`
method by your self and call the desired trait method by your self. 

Every trait has own __manual__ method that tries to alter the value. Use appropriate trait (`NullEmptyStringTrait`, `CleanHTMLTrait`, etc).

For chaining the value you can use helper function `alter_attribute_value`.

```php
/**
 * Set a given attribute on the model.
 *
 * @param  string $key
 * @param  mixed  $value
 */
public function setAttribute($key, $value)
{
    parent::setAttribute($key, alter_attribute_value($key, $value, $this, [
        'tryToCleanAttributeValue',
        'tryToNullAttributeValue'
    ]));
}
```

## Relation Traits

### RelationJoinTrait
Enables to create a join SQL statement that will construct the relation model and stores it into relations (so you don't
need to eager load the relation). The model is created from the relation function (the key you provide). You can create a
custom aliases to fix custom relation naming.

In default usage will load all columns from schema, for better perfomance you can provide a set of columns to load from
the relation. You can't provide a '*' as column.

`$relationAliases` -  A list of relations that has different method name than the table. 

Can be defined in model like this:

```php
protected $relationAliases = [
    "activity_type" => "type"
];
```

Then you can call it in standard way `modelJoin("type")` for a ActivityType model class.
       
#### Example

The basic method support custom columns, where condition, join operator and join type.

##### All columns

```php
Model::modelJoin("type")->get()
```
    
##### Desired columns (recommended)

```php
Model::modelJoin("type", ["name", "id", "color"])->get();
```

Then you can get the object by standard relation way:

```php
$model->type->color
```
    
But be carefull, can be null (default is LEFT connection)!

##### Desired columns with inner join

```php
Model::modelJoin("type", ["name", "id", "color"], "inner")->get();
```

##### Method

Docs is provided in code.

```php
modelJoin($query, $relation_name, $operatorOrColumns = '=', $type = 'left', $where = false, $columns = array())
```

#### Advanced example

Docs is provided in code. Uses table as a relation function.

```php
joinWithSelect($query, $table, $one, $operatorOrColumns, $two, $type = "left", $where = false, $columns = array())
```

### RelationCountTrait
Enables to count a related models. In future will prepare better docs.

#### Example
Usage of where: 

```php    
$count = $model->relationCountWithWhere("user_permission", "user_id", $user, "App\\Models\\User");
```

Calling the function again will use the cache in relations array. After this call you can also use

```php
$model->user_permission_{ForeignKey}_{userIdValueForWhere} which will the object of User model with count attribute.
```

You can also get the where index by passing variable which will be overided by the reference:

```php
$index = "user_permission";
$model->relationCountWithWhere($index, "user_id", $user, "App\\Models\\User");
```
    
Simple call will return count and the index will be stored in $model->user

```php
$model->relationCount("user", "App\\Models\\User") 
```
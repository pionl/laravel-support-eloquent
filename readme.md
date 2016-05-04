# Laravel's Eloquent support package
Contains a set of traits for the eloquent model. In future can contain more set of classes/traits for the eloquent database.

## RelationJoinTrait
Enables to create a join SQL statement that will construct the relation model and stores it into relations (so you don't
need to eager load the relation). The model is created from the relation function (the key you provide). You can create a
custom aliases to fix custom relation naming.

In default usage will load all columns from schema, for better perfomance you can provide a set of columns to load from
the relation. You can't provide a '*' as column.

`$relationAliases` -  A list of relations that has different method name than the table. 

Can be defined in model like this:

    protected $relationAliases = [
        "activity_type" => "type"
    ];

Then you can call it in standart way `modelJoin("type")` for a ActivityType model class.
       
### Example

The basic method support custom columns, where condition, join operator and join type.

#### All columns

    Model::modelJoin("type")->get()
    
#### Desired columns (recommended)

    Model::modelJoin("type", ["name", "id", "color"])->get();

Then you can get the object by standart relation way:

    $model->type->color
    
But be carefull, can be null (default is LEFT connection)!

#### Desired columns with inner join

Model::modelJoin("type", ["name", "id", "color"], "inner")->get();

#### Method

Docs is provided in code.

    modelJoin($query, $relation_name, $operatorOrColumns = '=', $type = 'left', $where = false, $columns = array())

### Advanced example

Docs is provided in code. Uses table as a relation function.

    joinWithSelect($query, $table, $one, $operatorOrColumns, $two, $type = "left", $where = false, $columns = array())

## NullEmptyStringAttributeTrait
Enables the automatic nulling of empty string value (like from post or set). You can provide
list of columns keys to allow only specified columns (use `$nullEmptyColumns`) to be nulled or you can provide a list of columns keys to ignore
while trying to null the value (use `$nullIgnoreColumns`).

### Example usage
Extend the construct on your Eloquent model like this.

Null only:

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        
        $this->nullEmptyColumns = [
            "name"
        ];
    }
    
Don't null provided colums:

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        
        $this->nullIgnoreColumns = [
            "name"
        ];
    }

## RelationCountTrait
Enables to count a related models. In future will prepare better docs.

### Example
Usage of where: 
    
    $count = $model->relationCountWithWhere("user_permission", "user_id", $user, "App\\Models\\User");

Calling the function again will use the cache in relations array. After this call you can also use

    $model->user_permission_{ForeignKey}_{userIdValueForWhere} which will the object of User model with count attribute.

You can also get the where index by passing variable which will be overided by the reference:

    $index = "user_permission";
    $model->relationCountWithWhere($index, "user_id", $user, "App\\Models\\User");
    
Simple call will return count and the index will be stored in $model->user

    $model->relationCount("user", "App\\Models\\User") 
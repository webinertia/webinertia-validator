<?php

declare(strict_types=1);

namespace Webinertia\Validator;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;
use Traversable;

use function array_key_exists;
use function password_verify;

final class DbStoredPassword extends AbstractValidator implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    public const INVALID_PASSWORD               = 'invalidPassword';
    public const OPTION_MISSING_ADAPTER         = 'missingAdapterOption';
    public const OPTION_MISSING_TABLE           = 'missingTableOption';
    public const OPTION_MISSING_PASSWORD_COLUMN = 'missingPasswordOption';
    public const OPTION_MISSING_PK_COLUMN       = 'missingPKColumnOption';
    public const OPTION_MISSING_PK_VALUE        = 'missingPKValueOption';

    protected string $passwordColumn;

    protected array|string $pkColumn;

    protected array|string|int $pkValue;

    protected Select $select;

    protected ?string $schema = null;

    protected TableIdentifier|string $table;

    protected $messageTemplates = [
        self::INVALID_PASSWORD               => 'The Supplied password is not valid.',
        self::OPTION_MISSING_ADAPTER         => 'Missing adapter option.',
        self::OPTION_MISSING_TABLE           => 'Missing table or schema option.',
        self::OPTION_MISSING_PASSWORD_COLUMN => 'Missing password column option.',
        self::OPTION_MISSING_PK_COLUMN       => 'Missing primary key name or array for a compound key.',
        self::OPTION_MISSING_PK_VALUE        => 'Missing primary key values or array of values for compound key',
    ];

    protected $messageVariables = [
        'adapter'        => ['options' => 'adapter'], // provided by AdapterAwareTrait
        'table'          => ['options' => 'table'],
        'passwordColumn' => ['options' => 'password_column'],
        'pkColumn  '     => ['options' => 'pkColumn'],
        'pkValue'        => ['options' => 'pkValue'],
    ];

    protected $options = [
        'table'           => null, // string table name or TableIdentifier instance
        'password_column' => null, // password column
        'pkColumn'        => null, // primary key of passed table
        'pkValue'         => null, // value for pk
    ];

    public function __construct(
        Traversable|array $options = []
    ) {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        // if (! array_key_exists('adapter', $options) && ! $this->adapter instanceof AdapterInterface) {
        //     throw new InvalidArgumentException(
        //         'adapter option must be passed or an instance of AdapterInterface must be set on this instance!'
        //     );
        // }

        if (array_key_exists('adapter', $options) && $options['adapter'] instanceof AdapterInterface) {
            // prefer the passed adapter
            $this->setDbAdapter($options['adapter']);
        }

        if (! array_key_exists('select', $options)) {
            if (! array_key_exists('password_column', $options)) {
                throw new InvalidArgumentException('password_column option is missing!');
            }

            $this->passwordColumn = $options['password_column'];

            if (! array_key_exists('pkColumn', $options)) {
                throw new InvalidArgumentException('pkColumn option is missing!');
            }

            $this->pkColumn = $options['pkColumn'];

            if (! array_key_exists('pkValue', $options)) {
                throw new InvalidArgumentException('pkValue option is missing!');
            }

            $this->pkValue = $options['pkValue'];

            if (! array_key_exists('table', $options) && ! array_key_exists('schema', $options)) {
                throw new InvalidArgumentException('table or schema option missing!');
            }

            if (array_key_exists('table', $options)) {
                $this->table = $options['table'];
            }

            if (array_key_exists('schema', $options)) {
                $this->schema = $options['schema'];
            }
        } else {
            $this->select = $options['select'];
        }

    }

    public function isValid($value)
    {
        $isValid    = false;
        $storedData = $this->query();
        if (isset($storedData[$this->passwordColumn])) {
            $isValid = password_verify($value, $storedData[$this->passwordColumn]);
        }
        return $isValid;
    }

    protected function getSelect(): Select
    {
        if (isset($this->select) && $this->select instanceof Select) {
            return $this->select;
        }

        // gotta build one
        $select = new Select();
        $tableIdentifier = new TableIdentifier($this->table, $this->schema);
        $select->from($tableIdentifier)->columns([$this->pkColumn, $this->passwordColumn]);
        $where = new Where();
        // todo: stopped here, wire select instance
        $where->equalTo($this->pkColumn, $this->pkValue);
        $select->where($where);
        $this->select = $select;
        return $this->select;
    }

    protected function query(): bool|array
    {
        $sql       = new Sql($this->adapter);
        $select    = $this->getSelect();
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        return $result->current();
    }
}

<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class AbstractQueryHandler
 *
 * Provides common functionality for all database query handlers.
 * @deprecated - was used by database-driven data sources
 */
abstract class AbstractQueryHandler implements DataFormatHandlerInterface
{
    /**
     * @var array Configuration parameters for the query.
     */
    protected array $params;

    /**
     * Constructor.
     *
     * @param array $params Array containing keys such as hostname, port, default_db, username, password, query, etc.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}
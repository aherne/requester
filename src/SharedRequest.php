<?php
namespace Lucinda\URL;

use Lucinda\URL\Connection\Shared as SharedConnection;
use Lucinda\URL\Request\ShareType;

/**
 * Encapsulates a shared request, able to exchange cookies and session between multiple Request instances
 */
class SharedRequest
{
    private $connection;
    
    /**
     * Initiates a shared URL connection based on one of ShareType enum values
     *
     * @param ShareType $share One of enum values (eg: ShareType::COOKIES)
     */
    public function __construct(int $type = ShareType::COOKIES)
    {
        $this->connection = new SharedConnection();
        $this->connection->set(CURLSHOPT_SHARE, $type);
    }
    
    /**
     * Adds request to be shared
     *
     * @param Request $request
     */
    public function add(Request $request): void
    {
        $this->connection->add($request->getConnection());
    }
}

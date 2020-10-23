<?php
namespace Lucinda\URL;

use Lucinda\URL\Request\ShareType;

/**
 * Encapsulates a shared request, able to exchange cookies and session between multiple Request instances
 * 
 * TODO: investigate curl_share_strerror && curl_share_errno
 */
class SharedRequest
{
    private $connection;
    private $children = [];
    
    /**
     * Initiates a shared URL connection based on one of ShareType enum values
     * 
     * @param ShareType $share One of enum values (eg: ShareType::COOKIES)
     */
    public function __construct(int $type = ShareType::COOKIES)
    {
        $this->connection = \curl_share_init();
        \curl_share_setopt( $this->connection, CURLSHOPT_SHARE, $type);
    }
    
    /**
     * Adds request to be shared
     * 
     * @param Request $request
     */
    public function add(Request $request): void
    {
        \curl_setopt($request->getDriver(), CURLOPT_SHARE, $this->connection);
        $this->children[] = $request;
        return $request;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        \curl_share_close($this->connection);
        // force handles to be garbage collected here
        foreach($this->children as $child) {
            $child->__destruct(); // forces garbage collector to occur here
        }
    }
}


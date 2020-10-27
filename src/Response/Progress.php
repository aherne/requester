<?php
namespace Lucinda\URL\Response;

/**
 * Encapsulates operations to perform as file download/upload progresses (wrapping CURLOPT_PROGRESSFUNCTION)
 */
interface Progress
{
    /**
     * Gets buffer interval by which handle is triggered
     * 
     * @return int
     */
    function getBufferSize(): int;
    
    /**
     * Method to execute on each buffer download/upload interval
     * 
     * @param int $totalSize
     * @param int $processedSize
     */
    function handle(int $totalSize, int $processedSize): void;
}


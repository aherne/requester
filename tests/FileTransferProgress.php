<?php
namespace Test\Lucinda\URL;

use Lucinda\URL\Response\Progress;

class FileTransferProgress implements Progress
{
    private $chunks = [];
    
    public function getBufferSize(): int
    {
        return 128;
    }
    
    public function handle(int $totalSize, int $processedSize): void
    {
        $this->chunks[] = ["processed"=>$processedSize, "total"=>$totalSize];
    }
    
    public function getChunks(): array
    {
        return $this->chunks;
    }
}

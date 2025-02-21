<?php
namespace Federaliser\Dataformats;

/**
 * Interface DataFormatHandlerInterface
 * All handlers must implement the handle() method.
 */
interface DataFormatHandlerInterface
{
    /**
     * Process the input (from URL or command) and return a normalized array.
     *
     * @return array
     */
    public function handle(): array;
    
}
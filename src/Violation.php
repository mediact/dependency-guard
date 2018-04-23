<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Php\SymbolIteratorInterface;

class Violation implements ViolationInterface
{
    /** @var string */
    private $message;

    /** @var CandidateInterface */
    private $result;

    /**
     * Constructor.
     *
     * @param string             $message
     * @param CandidateInterface $result
     */
    public function __construct(string $message, CandidateInterface $result)
    {
        $this->message = $message;
        $this->result  = $result;
    }

    /**
     * Get the violation message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the composer package.
     *
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->result->getPackage();
    }

    /**
     * Get the symbols.
     *
     * @return SymbolIteratorInterface
     */
    public function getSymbols(): SymbolIteratorInterface
    {
        return $this->result->getSymbols();
    }

    /**
     * Specify data that should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'package' => $this->getPackage()->getName(),
            'symbols' => $this->getSymbols()
        ];
    }
}

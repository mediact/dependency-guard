<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Violation;

use JsonSerializable;
use Mediact\DependencyGuard\Candidate\CandidateInterface;

interface ViolationInterface extends CandidateInterface, JsonSerializable
{
    /**
     * Get the violation message.
     *
     * @return string
     */
    public function getMessage(): string;
}

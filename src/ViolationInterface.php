<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

interface ViolationInterface extends CandidateInterface
{
    /**
     * Get the violation message.
     *
     * @return string
     */
    public function getMessage(): string;
}

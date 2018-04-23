<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use JsonSerializable;

interface ViolationInterface extends CandidateInterface, JsonSerializable
{
    /**
     * Get the violation message.
     *
     * @return string
     */
    public function getMessage(): string;
}

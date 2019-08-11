<?php

declare(strict_types = 1);

namespace Pu239;

use Delight\Auth\Role;

/**
 * Class Roles.
 */
final class Roles
{
    const CODER = Role::DEVELOPER;
    const UPLOADER = Role::CONTRIBUTOR;

    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface ResourceMatcherInterface
{
    public function match(string $grantedExpression, string $requestedResourceExpression): bool;
}

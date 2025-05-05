<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ResourceMatcher;
use PHPUnit\Framework\TestCase;

class ResourceMatcherTest extends TestCase
{
    public function testEqualExpressionsMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertTrue($matcher->match('GRANTED_RESOURCE_EXPRESSION', 'GRANTED_RESOURCE_EXPRESSION'));
        $this->assertTrue($matcher->match('resource', 'resource'));
        $this->assertTrue($matcher->match('resource::1', 'resource::1'));
    }

    public function testWildcardExpressionMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertTrue($matcher->match('resource::*', 'resource::1'));
    }

    public function testWildcardExpressionDoesNotMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertFalse($matcher->match('namespace::*', 'resource::2'));
    }

    public function testMultipleNamespaceExpressionMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertTrue($matcher->match('namespace::{sub-namespace-1,sub-namespace-2}::1', 'namespace::sub-namespace-1::1'));
        $this->assertTrue($matcher->match('namespace::{sub-namespace-1,sub-namespace-2}::1', 'namespace::sub-namespace-2::1'));
    }

    public function testMultipleNamespaceExpressionDoesNotMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertFalse($matcher->match('namespace::{sub-namespace-1,sub-namespace-2}::1', 'namespace::sub-namespace-3::1'));
    }

    public function testMultipleNamespaceWithWildcardExpressionMatch()
    {
        $matcher = new ResourceMatcher();

        $this->assertTrue($matcher->match('namespace::{sub-namespace-1,sub-namespace-2}::*', 'namespace::sub-namespace-1::1'));
        $this->assertTrue($matcher->match('namespace::{sub-namespace-1,sub-namespace-2}::*', 'namespace::sub-namespace-2::1'));
    }
}

<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class ResourceMatcher implements ResourceMatcherInterface
{
    /**
     * Compares two resource expressions for matching.
     *
     * Given two expressions, we must check if the requested expression is wrapped by the given expression.
     * Since expressions can contain wildcards, such as "*", and namespaces such as "anything", it's possible that an 
     * expression allows access to a set of resources.
     *
     * If you pass an expression like 'namespace1::sub-namespace1::*, the '*' part points to all later namespaces and resources.
     * If you pass an expression like 'namespace1::sub-namespace1::*::resource1::sub-resource1', the '*' part points to the 'resource1::sub-resource1' resources located at the namespaces between 'namespace1::sub-namespace1' and 'resource1::sub-resource1'.
     * If you pass an expression like 'namespace1::{sub-namespace1,sub-namespace2}::*' the '*' part points to all resources located at the namespaces between 'namespace1::sub-namespace1' and 'namespace1::sub-namespace2'.
     * If you pass an expression like 'namespace1::sub-namespace1::{resource1,resource2}', the '{resource1,resource2}' part points to the 'resource1' and 'resource2' resources located at the namespace 'namespace1::sub-namespace1'.
     *
     * @param string $grantedExpression The first resource expression to compare.
     * @param string $requestedResourceExpression The second resource expression to compare.
     *
     * @return bool Returns true if the two resource expressions are identical, otherwise false.
     */
    public function match(string $grantedExpression, string $requestedResourceExpression): bool
    {
        $grantedExpression = strtolower($grantedExpression);
        $requestedResourceExpression = strtolower($requestedResourceExpression);

        $grantedExpressionSegments = $this->parseExpression($grantedExpression);
        $requestedResourceExpressionSegments = $this->parseExpression($requestedResourceExpression);

        if (empty($grantedExpressionSegments) || empty($requestedResourceExpressionSegments)) {
            return false;
        }

        return $this->matchSegments($grantedExpressionSegments, $requestedResourceExpressionSegments);
    }

    private function parseExpression(string $expression): array
    {
        if (empty($expression)) {
            return [];
        }

        $segments = explode('::', $expression);

        return array_map(function ($segment) {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                return array_map('trim', explode(',', trim($segment, '{}')));
            }

            return [$segment];
        }, $segments);
    }

    private function matchSegments(array $givenSegments, array $requestedSegments): bool
    {
        return $this->matchSegmentsAt($givenSegments, $requestedSegments, 0, 0);
    }

    private function matchSegmentsAt(array $givenSegments, array $requestedSegments, int $givenIndex, int $requestedIndex): bool
    {
        if ($givenIndex === count($givenSegments)) {
            return $requestedIndex === count($requestedSegments);
        }

        $givenSegment = $givenSegments[$givenIndex];

        if (in_array('*', $givenSegment, true)) {
            if ($givenIndex === count($givenSegments) - 1) {
                return true;
            }

            for ($nextRequestedIndex = $requestedIndex; $nextRequestedIndex <= count($requestedSegments); $nextRequestedIndex++) {
                if ($this->matchSegmentsAt($givenSegments, $requestedSegments, $givenIndex + 1, $nextRequestedIndex)) {
                    return true;
                }
            }

            return false;
        }

        if ($requestedIndex >= count($requestedSegments)) {
            return false;
        }

        if (!$this->segmentsMatch($givenSegment, $requestedSegments[$requestedIndex])) {
            return false;
        }

        return $this->matchSegmentsAt($givenSegments, $requestedSegments, $givenIndex + 1, $requestedIndex + 1);
    }

    private function segmentsMatch(array $givenSegment, array $requestedSegment): bool
    {
        foreach ($givenSegment as $option) {
            if (in_array($option, $requestedSegment, true)) {
                return true;
            }
        }

        return false;
    }
}

export class RolesAndPermissionsResourceMatcher {
  match(grantedExpression: string, requestedResourceExpression: string): boolean {
    const normalizedGrantedExpression = grantedExpression.toLowerCase();
    const normalizedRequestedExpression = requestedResourceExpression.toLowerCase();

    const grantedSegments = this.parseExpression(normalizedGrantedExpression);
    const requestedSegments = this.parseExpression(normalizedRequestedExpression);

    if (grantedSegments.length === 0 || requestedSegments.length === 0) {
      return false;
    }

    return this.matchSegmentsAt(grantedSegments, requestedSegments, 0, 0);
  }

  private parseExpression(expression: string): string[][] {
    if (expression === '') {
      return [];
    }

    return expression.split('::').map((segment) => {
      if (segment.charAt(0) === '{' && segment.charAt(segment.length - 1) === '}') {
        return segment
          .substring(1, segment.length - 1)
          .split(',')
          .map((value) => value.trim());
      }

      return [segment];
    });
  }

  private matchSegmentsAt(
    grantedSegments: string[][],
    requestedSegments: string[][],
    grantedIndex: number,
    requestedIndex: number,
  ): boolean {
    if (grantedIndex === grantedSegments.length) {
      return requestedIndex === requestedSegments.length;
    }

    const grantedSegment = grantedSegments[grantedIndex];

    if (grantedSegment.indexOf('*') !== -1) {
      if (grantedIndex === grantedSegments.length - 1) {
        return true;
      }

      for (let nextRequestedIndex = requestedIndex; nextRequestedIndex <= requestedSegments.length; nextRequestedIndex += 1) {
        if (this.matchSegmentsAt(grantedSegments, requestedSegments, grantedIndex + 1, nextRequestedIndex)) {
          return true;
        }
      }

      return false;
    }

    if (requestedIndex >= requestedSegments.length) {
      return false;
    }

    if (!this.segmentsMatch(grantedSegment, requestedSegments[requestedIndex])) {
      return false;
    }

    return this.matchSegmentsAt(grantedSegments, requestedSegments, grantedIndex + 1, requestedIndex + 1);
  }

  private segmentsMatch(grantedSegment: string[], requestedSegment: string[]): boolean {
    return grantedSegment.some((option) => requestedSegment.indexOf(option) !== -1);
  }
}

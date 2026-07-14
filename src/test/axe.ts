import axe from 'axe-core';
import { expect } from 'vitest';

export async function expectNoA11yViolations(container: Element): Promise<void> {
  const result = await axe.run(container);
  expect(
    result.violations.map(({ id, impact, nodes }) => ({ id, impact, nodes: nodes.length })),
  ).toEqual([]);
}

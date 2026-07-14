import { expect, test } from 'vitest';

test('unit tests run in a DOM', () => {
  document.body.innerHTML = '<main>Dorzak</main>';
  expect(document.querySelector('main')).toHaveTextContent('Dorzak');
});

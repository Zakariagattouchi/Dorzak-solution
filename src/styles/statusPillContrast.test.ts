import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';
import { expect, test } from 'vitest';

const readStyle = (path: string): string =>
  readFileSync(new URL(path, pathToFileURL(`${process.cwd()}/`)), 'utf8');

const escapeRegExp = (value: string): string => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const declarationsFor = (source: string, selector: string): Map<string, string> => {
  const block = source.match(new RegExp(`${escapeRegExp(selector)}\\s*\\{([^}]*)\\}`));
  expect(block, `missing ${selector} rule`).not.toBeNull();

  return new Map(
    [...block![1].matchAll(/([\w-]+)\s*:\s*([^;]+);/g)].map((match) => [match[1], match[2].trim()]),
  );
};

const tokenValues = (source: string): Map<string, string> =>
  new Map(
    [...source.matchAll(/(--[\w-]+)\s*:\s*(#[\da-f]{6})\s*;/gi)].map((match) => [
      match[1],
      match[2].toLowerCase(),
    ]),
  );

const resolveColor = (
  value: string | undefined,
  tokens: Map<string, string>,
  label: string,
): { reference: string; value: string } => {
  const reference = value?.match(/^var\((--[\w-]+)\)$/)?.[1];
  expect(reference, `${label} must use a custom-property reference`).toBeTruthy();
  const color = tokens.get(reference!);
  expect(color, `${label} references a missing or non-hex token`).toMatch(/^#[\da-f]{6}$/);
  return { reference: reference!, value: color! };
};

const relativeLuminance = (hex: string): number => {
  const channels = hex
    .slice(1)
    .match(/.{2}/g)!
    .map((channel) => Number.parseInt(channel, 16) / 255)
    .map((channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4));
  return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
};

const contrastRatio = (foreground: string, background: string): number => {
  const luminances = [relativeLuminance(foreground), relativeLuminance(background)].sort(
    (a, b) => b - a,
  );
  return (luminances[0] + 0.05) / (luminances[1] + 0.05);
};

test('status-pill token pairs meet small-text contrast', () => {
  const components = readStyle('src/styles/components.css');
  const tokens = tokenValues(readStyle('src/styles/tokens.css'));
  const expectedBackgrounds = {
    success: '--color-success-soft',
    warning: '--color-warning-soft',
    danger: '--color-error-soft',
    primary: '--color-primary-soft',
    info: '--color-info-soft',
  } as const;
  const stableForegrounds = {
    danger: '--color-error',
    primary: '--color-primary',
    info: '--color-info',
  } as const;

  for (const variant of Object.keys(expectedBackgrounds) as (keyof typeof expectedBackgrounds)[]) {
    const declarations = declarationsFor(components, `.status-pill.${variant}`);
    const foreground = resolveColor(declarations.get('color'), tokens, `${variant} foreground`);
    const background = resolveColor(
      declarations.get('background-color'),
      tokens,
      `${variant} background`,
    );
    expect(background.reference).toBe(expectedBackgrounds[variant]);
    if (variant in stableForegrounds) {
      expect(foreground.reference).toBe(
        stableForegrounds[variant as keyof typeof stableForegrounds],
      );
    }

    const ratio = contrastRatio(foreground.value, background.value);
    expect.soft(ratio, `${variant} contrast is ${ratio.toFixed(3)}:1`).toBeGreaterThanOrEqual(4.5);
  }
});

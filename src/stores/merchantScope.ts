let generation = 0;

export class MerchantScopeChangedError extends Error {
  constructor() {
    super('Merchant session changed.');
    this.name = 'MerchantScopeChangedError';
  }
}

export function captureMerchantScope(): number {
  return generation;
}

export function invalidateMerchantScope(): void {
  generation += 1;
}

export function isMerchantScopeCurrent(scope: number): boolean {
  return scope === generation;
}

export function requireCurrentMerchantScope(scope: number): void {
  if (!isMerchantScopeCurrent(scope)) {
    throw new MerchantScopeChangedError();
  }
}

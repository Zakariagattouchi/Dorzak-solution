import { useSettingsStore } from '../stores/settingsStore';

export function useMoney() {
  const { currency, currencySymbol, symbolPlacement } = useSettingsStore((state) => state.accountInfo);

  return (value: number, fractionDigits = 2, currencyOverride?: string) => {
    const amount = value.toLocaleString('en-US', {
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    });
    const code = currencyOverride ?? currency;
    const known: Record<string, string> = { QAR: 'QAR', USD: '$', EUR: '€', GBP: '£', CAD: 'CA$', BRL: 'R$', AUD: 'A$' };
    const symbol = known[code] ?? (currencyOverride || currencySymbol);
    return symbolPlacement === 'AFTER' ? `${amount} ${symbol}` : `${symbol}${symbol.length > 1 ? ' ' : ''}${amount}`;
  };
}

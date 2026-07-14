import { useSettingsStore } from '../stores/settingsStore';

export interface MoneyFormat {
  currency: string;
  currencySymbol: string;
  symbolPlacement: 'BEFORE' | 'AFTER';
}

export function formatMoney(
  value: number,
  format: MoneyFormat,
  fractionDigits = 2,
  currencyOverride?: string,
): string {
  const amount = value.toLocaleString('en-US', {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
  });
  const code = currencyOverride ?? format.currency;
  const known: Record<string, string> = {
    QAR: 'QAR',
    USD: '$',
    EUR: '€',
    GBP: '£',
    CAD: 'CA$',
    BRL: 'R$',
    AUD: 'A$',
  };
  const symbol = known[code] ?? (currencyOverride || format.currencySymbol);
  return format.symbolPlacement === 'AFTER'
    ? `${amount} ${symbol}`
    : `${symbol}${symbol.length > 1 ? ' ' : ''}${amount}`;
}

export function useMoney() {
  const format = useSettingsStore((state) => state.accountInfo);
  return (value: number, fractionDigits = 2, currencyOverride?: string) =>
    formatMoney(value, format, fractionDigits, currencyOverride);
}

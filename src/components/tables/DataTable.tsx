import React, { useState } from 'react';
import { CheckboxInput } from '../forms/CheckboxInput';

export interface Column<T> {
  key: string;
  header: string;
  render?: (row: T) => React.ReactNode;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  data: T[];
  keyExtractor: (row: T) => string;
  onRowClick?: (row: T) => void;
  selectable?: boolean;
}

export function DataTable<T>({
  columns,
  data,
  keyExtractor,
  onRowClick,
  selectable = true,
}: DataTableProps<T>) {
  const [selectedKeys, setSelectedKeys] = useState<Set<string>>(new Set());

  const toggleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedKeys(new Set(data.map(keyExtractor)));
    } else {
      setSelectedKeys(new Set());
    }
  };

  const toggleRow = (key: string) => {
    const next = new Set(selectedKeys);
    if (next.has(key)) next.delete(key);
    else next.add(key);
    setSelectedKeys(next);
  };

  const allSelected = data.length > 0 && selectedKeys.size === data.length;

  return (
    <div className="data-table-container">
      <table className="data-table">
        <thead>
          <tr>
            {selectable && (
              <th style={{ width: '40px' }}>
                <CheckboxInput checked={allSelected} onChange={toggleSelectAll} />
              </th>
            )}
            {columns.map((col) => (
              <th key={col.key}>{col.header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.length === 0 ? (
            <tr>
              <td
                colSpan={columns.length + (selectable ? 1 : 0)}
                style={{ textAlign: 'center', padding: '32px', color: 'var(--text-muted)' }}
              >
                No records found.
              </td>
            </tr>
          ) : (
            data.map((row) => {
              const key = keyExtractor(row);
              const isSelected = selectedKeys.has(key);
              return (
                <tr
                  key={key}
                  onClick={() => onRowClick && onRowClick(row)}
                  style={{ cursor: onRowClick ? 'pointer' : 'default' }}
                >
                  {selectable && (
                    <td onClick={(e) => e.stopPropagation()}>
                      <CheckboxInput checked={isSelected} onChange={() => toggleRow(key)} />
                    </td>
                  )}
                  {columns.map((col) => (
                    <td key={col.key}>{col.render ? col.render(row) : (row as any)[col.key]}</td>
                  ))}
                </tr>
              );
            })
          )}
        </tbody>
      </table>
    </div>
  );
}

import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { beforeEach, expect, test, vi } from 'vitest';
import { ImpersonationBanner } from './ImpersonationBanner';

const authDouble = vi.hoisted(() => ({
  stopImpersonating: vi.fn(),
}));

vi.mock('../../stores/authStore', () => ({
  useAuthStore: () => ({
    impersonating: 'Merchant A',
    stopImpersonating: authDouble.stopImpersonating,
  }),
}));

beforeEach(() => {
  authDouble.stopImpersonating.mockReset();
});

test('keeps impersonation active after a failed exit and permits a successful retry', async () => {
  const user = userEvent.setup();
  authDouble.stopImpersonating
    .mockRejectedValueOnce({ message: 'Unauthenticated.', status: 401 })
    .mockResolvedValueOnce(false)
    .mockResolvedValueOnce(true);

  render(
    <MemoryRouter initialEntries={['/merchant']}>
      <Routes>
        <Route path="/merchant" element={<ImpersonationBanner />} />
        <Route path="/platform" element={<p>Platform console</p>} />
      </Routes>
    </MemoryRouter>,
  );

  const exit = screen.getByRole('button', { name: 'Exit impersonation' });
  await user.click(exit);

  expect(await screen.findByRole('status')).toHaveTextContent(/impersonation is still active/i);
  expect(screen.getByRole('alert')).toHaveTextContent('Merchant A');
  expect(screen.queryByText('Platform console')).not.toBeInTheDocument();
  expect(exit).toBeEnabled();

  await user.click(exit);
  expect(screen.queryByRole('status')).not.toBeInTheDocument();
  expect(screen.getByRole('alert')).toHaveTextContent('Merchant A');
  expect(screen.queryByText('Platform console')).not.toBeInTheDocument();

  await user.click(exit);
  expect(await screen.findByText('Platform console')).toBeInTheDocument();
  expect(authDouble.stopImpersonating).toHaveBeenCalledTimes(3);
});

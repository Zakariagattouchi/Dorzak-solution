import React from 'react';
import { RouterProvider } from 'react-router-dom';
import { router } from './router/routes';
import './styles/base.css';
import './styles/layout.css';
import './styles/components.css';
import './styles/forms.css';
import './styles/tables.css';
import './styles/modals.css';
import './styles/animations.css';
import { LocaleBridge } from './i18n/LocaleBridge';

export const App: React.FC = () => {
  return (
    <>
      <LocaleBridge />
      <RouterProvider router={router} />
    </>
  );
};

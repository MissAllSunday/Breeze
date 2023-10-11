import React from 'react';
import { createRoot } from 'react-dom/client';

import Wall from './Wall';

const rootElement = (document.getElementById('root') ?? document.createElement('div'));
const root = createRoot(rootElement);
const wallType = rootElement.getAttribute('wallType') ?? 'profile';
// @ts-expect-error Backend variable or default value
const pagination: number = window.breezePagination ?? 5;

root.render(
  <React.StrictMode>
    <Wall wallType={wallType} pagination={pagination} />
  </React.StrictMode>,
);

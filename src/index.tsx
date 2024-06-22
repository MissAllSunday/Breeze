import React from 'react';
import { createRoot } from 'react-dom/client';

import AboutMe from './components/AboutMe';
import Tabs from './components/Tabs';
import Wall from './Wall';

const rootElement = (document.getElementById('root') ?? document.createElement('div'));
const root = createRoot(rootElement);
const wallType = rootElement.getAttribute('wallType') ?? 'profile';
// @ts-expect-error settings are loaded server side
const pagination: number = window.breezePagination ?? 5;

root.render(
    <React.StrictMode>
      <Tabs>
          <Wall wallType={wallType} pagination={pagination} />
          <AboutMe />
      </Tabs>
    </React.StrictMode>,
);

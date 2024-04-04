import React from 'react';
import { createRoot } from 'react-dom/client';

import Wall from './Wall';

const rootElement = (document.getElementById('root') ?? document.createElement('div'));
const root = createRoot(rootElement);
const wallType = rootElement.getAttribute('wallType') ?? 'profile';
// @ts-expect-error settings are loaded server side
const pagination: number = window.breezePagination ?? 5;

// @ts-expect-error editor gets defined serverside
const smfEditor = window.sceditor ?? null;

root.render(
    <React.StrictMode>
        <Wall wallType={wallType} pagination={pagination} smfEditor={smfEditor}/>
    </React.StrictMode>,
);
